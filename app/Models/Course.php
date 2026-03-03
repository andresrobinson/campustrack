<?php

namespace App\Models;

use PDO;

class Course
{
    public static function all(PDO $pdo, bool $includeDeleted = false): array
    {
        $sql = 'SELECT c.*, u.name AS created_by_name FROM courses c LEFT JOIN users u ON c.created_by = u.id WHERE 1=1';
        if (!$includeDeleted) {
            $sql .= ' AND c.deleted_at IS NULL';
        }
        $sql .= ' ORDER BY c.code';
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find(PDO $pdo, int $id, bool $includeDeleted = false): ?array
    {
        $sql = 'SELECT c.*, u.name AS created_by_name FROM courses c LEFT JOIN users u ON c.created_by = u.id WHERE c.id = ?';
        if (!$includeDeleted) {
            $sql .= ' AND c.deleted_at IS NULL';
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function create(PDO $pdo, array $data, int $userId): int
    {
        $stmt = $pdo->prepare(
            'INSERT INTO courses (code, name, description, is_public, attendance_mode, default_lecture_duration_minutes, has_grading, gpa_formula, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['code'],
            $data['name'],
            $data['description'] ?? null,
            !empty($data['is_public']) ? 1 : 0,
            $data['attendance_mode'] ?? 'simple',
            isset($data['default_lecture_duration_minutes']) ? (int) $data['default_lecture_duration_minutes'] : null,
            !empty($data['has_grading']) ? 1 : 0,
            $data['gpa_formula'] ?? 'average',
            $userId,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(PDO $pdo, int $id, array $data, int $userId): bool
    {
        $stmt = $pdo->prepare(
            'UPDATE courses SET code = ?, name = ?, description = ?, is_public = ?, attendance_mode = ?, default_lecture_duration_minutes = ?, has_grading = ?, gpa_formula = ?, updated_by = ? WHERE id = ? AND deleted_at IS NULL'
        );
        return $stmt->execute([
            $data['code'],
            $data['name'],
            $data['description'] ?? null,
            !empty($data['is_public']) ? 1 : 0,
            $data['attendance_mode'] ?? 'simple',
            isset($data['default_lecture_duration_minutes']) ? (int) $data['default_lecture_duration_minutes'] : null,
            !empty($data['has_grading']) ? 1 : 0,
            $data['gpa_formula'] ?? 'average',
            $userId,
            $id,
        ]);
    }

    public static function softDelete(PDO $pdo, int $id): bool
    {
        $stmt = $pdo->prepare('UPDATE courses SET deleted_at = NOW() WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function codeExists(PDO $pdo, string $code, ?int $excludeId = null): bool
    {
        $sql = 'SELECT 1 FROM courses WHERE code = ? AND deleted_at IS NULL';
        $params = [$code];
        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }
}
