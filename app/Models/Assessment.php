<?php

namespace App\Models;

use PDO;

class Assessment
{
    public static function allByClass(PDO $pdo, int $classId): array
    {
        $stmt = $pdo->prepare(
            'SELECT * FROM assessments WHERE class_id = ? AND deleted_at IS NULL ORDER BY position, id'
        );
        $stmt->execute([$classId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find(PDO $pdo, int $id): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM assessments WHERE id = ? AND deleted_at IS NULL');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function create(PDO $pdo, array $data, int $userId): int
    {
        $stmt = $pdo->prepare(
            'INSERT INTO assessments (class_id, name, description, position, max_score, created_by) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            (int) $data['class_id'],
            $data['name'],
            $data['description'] ?? null,
            (int) ($data['position'] ?? 1),
            $data['max_score'] !== null && $data['max_score'] !== '' ? (float) $data['max_score'] : null,
            $userId,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(PDO $pdo, int $id, array $data, int $userId): bool
    {
        $stmt = $pdo->prepare(
            'UPDATE assessments SET name = ?, description = ?, position = ?, max_score = ?, updated_by = ? WHERE id = ? AND deleted_at IS NULL'
        );
        return $stmt->execute([
            $data['name'],
            $data['description'] ?? null,
            (int) ($data['position'] ?? 1),
            $data['max_score'] !== null && $data['max_score'] !== '' ? (float) $data['max_score'] : null,
            $userId,
            $id,
        ]);
    }

    public static function softDelete(PDO $pdo, int $id): bool
    {
        $stmt = $pdo->prepare('UPDATE assessments SET deleted_at = NOW() WHERE id = ?');
        return $stmt->execute([$id]);
    }
}

