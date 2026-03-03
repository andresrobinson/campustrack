<?php

namespace App\Models;

use PDO;

class SchoolClass
{
    public static function all(PDO $pdo, ?int $courseId = null, bool $includeDeleted = false): array
    {
        $sql = 'SELECT cl.*, c.code AS course_code, c.name AS course_name FROM classes cl JOIN courses c ON cl.course_id = c.id WHERE 1=1';
        if ($courseId !== null) {
            $sql .= ' AND cl.course_id = ?';
        }
        if (!$includeDeleted) {
            $sql .= ' AND cl.deleted_at IS NULL AND c.deleted_at IS NULL';
        }
        $sql .= ' ORDER BY c.code, cl.code';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($courseId !== null ? [$courseId] : []);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find(PDO $pdo, int $id, bool $includeDeleted = false): ?array
    {
        $sql = 'SELECT cl.*, c.code AS course_code, c.name AS course_name, c.attendance_mode, c.default_lecture_duration_minutes AS course_default_duration FROM classes cl JOIN courses c ON cl.course_id = c.id WHERE cl.id = ?';
        if (!$includeDeleted) {
            $sql .= ' AND cl.deleted_at IS NULL AND c.deleted_at IS NULL';
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function create(PDO $pdo, array $data, int $userId): int
    {
        $stmt = $pdo->prepare(
            'INSERT INTO classes (course_id, code, name, description, start_date, end_date, default_lecture_duration_minutes, auto_lectures_count, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            (int) $data['course_id'],
            $data['code'],
            $data['name'],
            $data['description'] ?? null,
            $data['start_date'] ?: null,
            $data['end_date'] ?: null,
            isset($data['default_lecture_duration_minutes']) ? (int) $data['default_lecture_duration_minutes'] : null,
            isset($data['auto_lectures_count']) ? (int) $data['auto_lectures_count'] : null,
            $data['status'] ?? 'open',
            $userId,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(PDO $pdo, int $id, array $data, int $userId): bool
    {
        $stmt = $pdo->prepare(
            'UPDATE classes SET course_id = ?, code = ?, name = ?, description = ?, start_date = ?, end_date = ?, default_lecture_duration_minutes = ?, auto_lectures_count = ?, status = ?, updated_by = ? WHERE id = ? AND deleted_at IS NULL'
        );
        return $stmt->execute([
            (int) $data['course_id'],
            $data['code'],
            $data['name'],
            $data['description'] ?? null,
            $data['start_date'] ?: null,
            $data['end_date'] ?: null,
            isset($data['default_lecture_duration_minutes']) ? (int) $data['default_lecture_duration_minutes'] : null,
            isset($data['auto_lectures_count']) ? (int) $data['auto_lectures_count'] : null,
            $data['status'] ?? 'open',
            $userId,
            $id,
        ]);
    }

    public static function softDelete(PDO $pdo, int $id): bool
    {
        $stmt = $pdo->prepare('UPDATE classes SET deleted_at = NOW() WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function codeExists(PDO $pdo, string $code, ?int $excludeId = null): bool
    {
        $sql = 'SELECT 1 FROM classes WHERE code = ? AND deleted_at IS NULL';
        $params = [$code];
        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    public static function getTeachers(PDO $pdo, int $classId): array
    {
        $stmt = $pdo->prepare(
            'SELECT u.id, u.name, u.email FROM class_teachers ct JOIN users u ON ct.teacher_id = u.id WHERE ct.class_id = ? AND ct.deleted_at IS NULL AND u.deleted_at IS NULL ORDER BY u.name'
        );
        $stmt->execute([$classId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function setTeachers(PDO $pdo, int $classId, array $teacherIds): void
    {
        $pdo->prepare('UPDATE class_teachers SET deleted_at = NOW() WHERE class_id = ?')->execute([$classId]);
        foreach (array_filter(array_map('intval', $teacherIds)) as $tid) {
            if ($tid <= 0) continue;
            $pdo->prepare('INSERT INTO class_teachers (class_id, teacher_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE deleted_at = NULL')->execute([$classId, $tid]);
        }
    }

    public static function addTeacher(PDO $pdo, int $classId, int $teacherId): void
    {
        $stmt = $pdo->prepare('INSERT IGNORE INTO class_teachers (class_id, teacher_id) VALUES (?, ?)');
        $stmt->execute([$classId, $teacherId]);
    }

    public static function removeTeacher(PDO $pdo, int $classId, int $teacherId): void
    {
        $stmt = $pdo->prepare('UPDATE class_teachers SET deleted_at = NOW() WHERE class_id = ? AND teacher_id = ?');
        $stmt->execute([$classId, $teacherId]);
    }

    public static function getTeacherIds(PDO $pdo, int $classId): array
    {
        $stmt = $pdo->prepare('SELECT teacher_id FROM class_teachers WHERE class_id = ? AND deleted_at IS NULL');
        $stmt->execute([$classId]);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'teacher_id');
    }

    public static function syncTeachers(PDO $pdo, int $classId, array $teacherIds): void
    {
        $pdo->prepare('UPDATE class_teachers SET deleted_at = NOW() WHERE class_id = ?')->execute([$classId]);
        foreach (array_filter(array_map('intval', $teacherIds)) as $tid) {
            if ($tid <= 0) continue;
            $pdo->prepare('INSERT INTO class_teachers (class_id, teacher_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE deleted_at = NULL')->execute([$classId, $tid]);
        }
    }
}
