<?php

namespace Core;

class Auth
{
    private const SESSION_KEY = 'user_id';
    private const MAX_FAILED_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES = 15;

    public static function login(string $email, string $password, \PDO $pdo): bool
    {
        $stmt = $pdo->prepare(
            'SELECT id, name, email, password_hash, role, status, preferred_language, failed_login_attempts, locked_until
             FROM users WHERE email = ? AND deleted_at IS NULL'
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user) {
            return false;
        }
        if (!empty($user['locked_until']) && $user['locked_until'] > date('Y-m-d H:i:s')) {
            return false;
        }
        if ($user['status'] !== 'active') {
            return false;
        }
        $hash = $user['password_hash'];
        $valid = password_verify($password, $hash)
            || (strlen($hash) === 64 && hash_equals(hash('sha256', $password), $hash));
        if (!$valid) {
            $stmt = $pdo->prepare(
                'UPDATE users
                 SET failed_login_attempts = failed_login_attempts + 1,
                     locked_until = CASE
                         WHEN failed_login_attempts + 1 >= :max THEN DATE_ADD(NOW(), INTERVAL :mins MINUTE)
                         ELSE locked_until
                     END
                 WHERE id = :id'
            );
            $stmt->execute([
                ':max' => self::MAX_FAILED_ATTEMPTS,
                ':mins' => self::LOCKOUT_MINUTES,
                ':id' => $user['id'],
            ]);
            return false;
        }
        // Successful login: reset counters
        $stmt = $pdo->prepare(
            'UPDATE users SET failed_login_attempts = 0, locked_until = NULL WHERE id = ?'
        );
        $stmt->execute([$user['id']]);
        $_SESSION[self::SESSION_KEY] = (int) $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_lang'] = $user['preferred_language'] ?? null;
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
