<?php

function config(string $key, $default = null)
{
    static $config = null;
    if ($config === null) {
        $config = array_merge(
            require __DIR__ . '/../config/app.php',
            require __DIR__ . '/../config/database.php'
        );
    }
    return $config[$key] ?? $default;
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = \Core\Database::get([
            'db_host' => config('db_host'),
            'db_name' => config('db_name'),
            'db_user' => config('db_user'),
            'db_pass' => config('db_pass'),
            'db_charset' => config('db_charset'),
        ]);
    }
    return $pdo;
}

function lang(): string
{
    static $lang = null;
    if ($lang === null) {
        $userLang = $_SESSION['user_lang'] ?? null;
        if ($userLang) {
            $lang = $userLang;
        } else {
            try {
                $pdo = db();
                $stmt = $pdo->query("SELECT value FROM settings WHERE `key` = 'default_language' LIMIT 1");
                $row = $stmt->fetch();
                $lang = $row ? $row['value'] : 'en';
            } catch (Throwable $e) {
                $lang = 'en';
            }
        }
    }
    return $lang;
}

function __(string $key, array $replace = []): string
{
    static $translations = [];
    $locale = lang();
    if (!isset($translations[$locale])) {
        $file = __DIR__ . '/../lang/' . $locale . '.php';
        $translations[$locale] = file_exists($file) ? require $file : [];
    }
    $text = $translations[$locale][$key] ?? $key;
    foreach ($replace as $k => $v) {
        $text = str_replace(':' . $k, (string) $v, $text);
    }
    return $text;
}

function url(string $path = '', array $query = []): string
{
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
    if ($base === '' || $base === '\\') {
        $base = '/';
    }
    $path = ltrim($path, '/');
    $script = $base === '/' ? '/index.php' : $base . '/index.php';
    $query = array_merge($path !== '' ? ['route' => $path] : [], $query);
    return $script . (empty($query) ? '' : '?' . http_build_query($query));
}

function redirect(string $path, int $code = 302, array $query = []): void
{
    if (str_contains($path, '?')) {
        [$path, $q] = explode('?', $path, 2);
        parse_str($q, $parsed);
        $query = array_merge($parsed, $query);
    }
    header('Location: ' . url($path, $query), true, $code);
    exit;
}

function flash(string $key): ?string
{
    $msg = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $msg;
}

function flash_set(string $key, string $message): void
{
    $_SESSION['flash'][$key] = $message;
}
