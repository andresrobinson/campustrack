<?php

namespace App\Models;

use PDO;

class User
{
    public static function allByRole(PDO $pdo, string $role, bool $activeOnly = true): array
    {
        $sql = 'SELECT id, name, email, status, approved_at FROM users WHERE role = ? AND deleted_at IS NULL';
        if ($activeOnly) {
            $sql .= ' AND status = ?';
        }
        $sql .= ' ORDER BY name';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($activeOnly ? [$role, 'active'] : [$role]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function teachers(PDO $pdo, bool $activeOnly = true): array
    {
        return self::allByRole($pdo, 'teacher', $activeOnly);
    }

    public static function students(PDO $pdo, bool $activeOnly = true): array
    {
        return self::allByRole($pdo, 'student', $activeOnly);
    }

    public static function find(PDO $pdo, int $id): ?array
    {
        $stmt = $pdo->prepare('SELECT id, name, email, role, status FROM users WHERE id = ? AND deleted_at IS NULL');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
