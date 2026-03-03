<?php

namespace App\Models;

use PDO;

class File
{
    public static function forCourse(PDO $pdo, int $courseId, bool $includeHidden = true): array
    {
        $sql = 'SELECT * FROM files WHERE course_id = ? AND deleted_at IS NULL';
        if (!$includeHidden) {
            $sql .= ' AND (available_from IS NULL OR available_from <= NOW()) AND (available_until IS NULL OR available_until >= NOW())';
        }
        $sql .= ' ORDER BY created_at DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$courseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function forClass(PDO $pdo, int $classId, bool $includeHidden = true): array
    {
        $sql = 'SELECT * FROM files WHERE class_id = ? AND deleted_at IS NULL';
        if (!$includeHidden) {
            $sql .= ' AND (available_from IS NULL OR available_from <= NOW()) AND (available_until IS NULL OR available_until >= NOW())';
        }
        $sql .= ' ORDER BY created_at DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$classId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find(PDO $pdo, int $id): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM files WHERE id = ? AND deleted_at IS NULL');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function create(PDO $pdo, array $data, int $userId): int
    {
        $stmt = $pdo->prepare(
            'INSERT INTO files (course_id, class_id, title, description, storage_path, original_name, mime_type, size_bytes, available_from, available_until, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['course_id'] ?? null,
            $data['class_id'] ?? null,
            $data['title'],
            $data['description'] ?? null,
            $data['storage_path'],
            $data['original_name'],
            $data['mime_type'] ?? null,
            $data['size_bytes'] ?? null,
            $data['available_from'] ?? null,
            $data['available_until'] ?? null,
            $userId,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(PDO $pdo, int $id, array $data, int $userId): bool
    {
        $stmt = $pdo->prepare(
            'UPDATE files SET title = ?, description = ?, available_from = ?, available_until = ?, updated_by = ? WHERE id = ? AND deleted_at IS NULL'
        );
        return $stmt->execute([
            $data['title'],
            $data['description'] ?? null,
            $data['available_from'] ?? null,
            $data['available_until'] ?? null,
            $userId,
            $id,
        ]);
    }

    public static function softDelete(PDO $pdo, int $id): bool
    {
        $stmt = $pdo->prepare('UPDATE files SET deleted_at = NOW() WHERE id = ?');
        return $stmt->execute([$id]);
    }
}

