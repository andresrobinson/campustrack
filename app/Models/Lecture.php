<?php

namespace App\Models;

use PDO;

class Lecture
{
    public static function allByClass(PDO $pdo, int $classId, bool $includeDeleted = false): array
    {
        $sql = 'SELECT * FROM lectures WHERE class_id = ?';
        if (!$includeDeleted) {
            $sql .= ' AND deleted_at IS NULL';
        }
        $sql .= ' ORDER BY lecture_date, start_time';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$classId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find(PDO $pdo, int $id, bool $includeDeleted = false): ?array
    {
        $sql = 'SELECT l.*, c.name AS course_name, cl.name AS class_name, cl.code AS class_code FROM lectures l JOIN classes cl ON l.class_id = cl.id JOIN courses c ON cl.course_id = c.id WHERE l.id = ?';
        if (!$includeDeleted) {
            $sql .= ' AND l.deleted_at IS NULL';
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function create(PDO $pdo, array $data, int $userId): int
    {
        $stmt = $pdo->prepare(
            'INSERT INTO lectures (class_id, lecture_date, start_time, duration_minutes, location, is_extra, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            (int) $data['class_id'],
            $data['lecture_date'],
            $data['start_time'] ?? null,
            (int) $data['duration_minutes'],
            $data['location'] ?? null,
            !empty($data['is_extra']) ? 1 : 0,
            $userId,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(PDO $pdo, int $id, array $data, int $userId): bool
    {
        $stmt = $pdo->prepare(
            'UPDATE lectures SET lecture_date = ?, start_time = ?, duration_minutes = ?, location = ?, updated_by = ? WHERE id = ? AND deleted_at IS NULL'
        );
        return $stmt->execute([
            $data['lecture_date'],
            $data['start_time'] ?? null,
            (int) $data['duration_minutes'],
            $data['location'] ?? null,
            $userId,
            $id,
        ]);
    }

    public static function softDelete(PDO $pdo, int $id): bool
    {
        $stmt = $pdo->prepare('UPDATE lectures SET deleted_at = NOW() WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /** Generate lectures by simple recurrence: same weekday each week from start_date, N times */
    public static function generateRecurring(PDO $pdo, int $classId, string $startDate, ?string $startTime, int $durationMinutes, int $count, int $userId, ?string $location = null): int
    {
        $created = 0;
        $date = new \DateTimeImmutable($startDate);
        $groupId = (int) $pdo->query('SELECT COALESCE(MAX(recurrence_group_id), 0) + 1 FROM lectures')->fetchColumn();
        $stmt = $pdo->prepare(
            'INSERT INTO lectures (class_id, lecture_date, start_time, duration_minutes, location, is_extra, recurrence_group_id, created_by) VALUES (?, ?, ?, ?, ?, 0, ?, ?)'
        );
        for ($i = 0; $i < $count; $i++) {
            $stmt->execute([
                $classId,
                $date->format('Y-m-d'),
                $startTime,
                $durationMinutes,
                $location,
                $groupId,
                $userId,
            ]);
            $created++;
            $date = $date->modify('+7 days');
        }
        return $created;
    }
}
