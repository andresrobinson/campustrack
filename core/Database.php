<?php

namespace Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $pdo = null;

    public static function get(array $config): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $config['db_host'],
            $config['db_name'],
            $config['db_charset']
        );
        try {
            self::$pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            $msg = $e->getMessage();
            if (strpos($msg, '1049') !== false) {
                $msg = 'Unknown database. Create it in MySQL (e.g. CREATE DATABASE ' . $config['db_name'] . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;) or set the correct db_name in config/database.php';
            }
            throw new \RuntimeException('Database connection failed: ' . $msg);
        }
        return self::$pdo;
    }
}
