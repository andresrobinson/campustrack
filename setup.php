<?php

// Simple CLI setup script for CampusTrack
// - Creates the database if it does not exist
// - Runs all SQL files in the migrations/ directory in order

if (php_sapi_name() !== 'cli') {
    echo "This script is intended to be run from the command line (php setup.php).\n";
}

echo "CampusTrack setup starting...\n\n";

$root = __DIR__;
$configFile = $root . '/config/database.php';

if (!file_exists($configFile)) {
    fwrite(STDERR, "ERROR: config/database.php not found. Copy and adjust this file before running setup.\n");
    exit(1);
}

$dbConfig = require $configFile;

$host = $dbConfig['db_host'] ?? '127.0.0.1';
$dbName = $dbConfig['db_name'] ?? null;
$user = $dbConfig['db_user'] ?? 'root';
$pass = $dbConfig['db_pass'] ?? '';
$charset = $dbConfig['db_charset'] ?? 'utf8mb4';

if (empty($dbName)) {
    fwrite(STDERR, "ERROR: db_name is empty in config/database.php.\n");
    exit(1);
}

try {
    echo "Connecting to MySQL server on {$host}...\n";
    $dsnServer = "mysql:host={$host};charset={$charset}";
    $serverPdo = new PDO($dsnServer, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "Ensuring database '{$dbName}' exists...\n";
    $serverPdo->exec(
        "CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET {$charset} COLLATE {$charset}_unicode_ci"
    );

    echo "Connecting to database '{$dbName}'...\n";
    $dsnDb = "mysql:host={$host};dbname={$dbName};charset={$charset}";
    $pdo = new PDO($dsnDb, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    fwrite(STDERR, "ERROR: Failed to connect or create database: " . $e->getMessage() . "\n");
    exit(1);
}

// Create migrations table if not exists
echo "Preparing migrations table...\n";
$pdo->exec(
    "CREATE TABLE IF NOT EXISTS migrations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL UNIQUE,
        applied_at DATETIME NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET={$charset}"
);

// Find migration files
$migrationsDir = $root . '/migrations';
if (!is_dir($migrationsDir)) {
    fwrite(STDERR, "ERROR: migrations directory not found at {$migrationsDir}\n");
    exit(1);
}

$files = glob($migrationsDir . '/*.sql');
sort($files, SORT_STRING);

if (empty($files)) {
    echo "No migration files found in migrations/.\n";
    exit(0);
}

// Load applied migrations
$applied = [];
$stmt = $pdo->query('SELECT filename FROM migrations');
foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $fname) {
    $applied[$fname] = true;
}

echo "Running migrations...\n";

foreach ($files as $file) {
    $base = basename($file);
    if (isset($applied[$base])) {
        echo " - Skipping {$base} (already applied)\n";
        continue;
    }

    echo " - Applying {$base}... ";
    $sql = file_get_contents($file);
    if ($sql === false) {
        echo "FAILED (could not read file)\n";
        continue;
    }

    try {
        // Simple execution: file should contain valid SQL for this database
        $pdo->exec($sql);
        $insert = $pdo->prepare('INSERT INTO migrations (filename, applied_at) VALUES (?, NOW())');
        $insert->execute([$base]);
        echo "OK\n";
    } catch (PDOException $e) {
        echo "FAILED\n";
        fwrite(STDERR, "    Error applying {$base}: " . $e->getMessage() . "\n");
        fwrite(STDERR, "    You may need to fix the issue and re-run this script.\n");
        exit(1);
    }
}

echo "\nAll done! Database is ready.\n";

