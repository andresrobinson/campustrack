<?php

namespace Core;

class Auth
{
    private const SESSION_KEY = 'user_id';

    public static function login(string $email, string $password, \PDO $pdo): bool
    {
        $stmt = $pdo->prepare(
            'SELECT id, name, email, password_hash, role, status FROM users WHERE email = ? AND deleted_at IS NULL'
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user) {
            return false;
        }
        if ($user['status'] !== 'active') {
            return false;
        }
        $hash = $user['password_hash'];
        $valid = password_verify($password, $hash)
            || (strlen($hash) === 64 && hash_equals(hash('sha256', $password), $hash));
        if (!$valid) {
            return false;
        }
        $_SESSION[self::SESSION_KEY] = (int) $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        return true;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function id(): ?int
    {
        return isset($_SESSION[self::SESSION_KEY]) ? (int) $_SESSION[self::SESSION_KEY] : null;
    }

    public static function user(): ?array
    {
        $id = self::id();
        if ($id === null) {
            return null;
        }
        return [
            'id' => $id,
            'name' => $_SESSION['user_name'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'role' => $_SESSION['user_role'] ?? 'student',
        ];
    }

    public static function check(): bool
    {
        return self::id() !== null;
    }

    public static function isAdmin(): bool
    {
        return ($_SESSION['user_role'] ?? '') === 'admin';
    }

    public static function isManager(): bool
    {
        return ($_SESSION['user_role'] ?? '') === 'manager';
    }

    public static function isTeacher(): bool
    {
        return ($_SESSION['user_role'] ?? '') === 'teacher';
    }

    public static function isStudent(): bool
    {
        return ($_SESSION['user_role'] ?? '') === 'student';
    }

    public static function canManageSystem(): bool
    {
        return self::isAdmin();
    }

    public static function canManageCourses(): bool
    {
        return self::isAdmin() || self::isManager();
    }
}
