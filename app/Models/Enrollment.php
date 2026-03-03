<?php

namespace App\Models;

use PDO;

class Enrollment
{
    public static function getApprovedStudentIdsByClass(PDO $pdo, int $classId): array
    {
        $stmt = $pdo->prepare('SELECT student_id FROM enrollments WHERE class_id = ? AND status = ? AND deleted_at IS NULL ORDER BY student_id');
        $stmt->execute([$classId, 'approved']);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'student_id');
    }

    public static function getApprovedStudentsByClass(PDO $pdo, int $classId): array
    {
        $stmt = $pdo->prepare(
            'SELECT u.id, u.name, u.email FROM enrollments e JOIN users u ON e.student_id = u.id WHERE e.class_id = ? AND e.status = ? AND e.deleted_at IS NULL AND u.deleted_at IS NULL ORDER BY u.name'
        );
        $stmt->execute([$classId, 'approved']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getByClass(PDO $pdo, int $classId): array
    {
        $stmt = $pdo->prepare(
            'SELECT e.*, u.name AS student_name, u.email AS student_email FROM enrollments e JOIN users u ON e.student_id = u.id WHERE e.class_id = ? AND e.deleted_at IS NULL ORDER BY e.status, u.name'
        );
        $stmt->execute([$classId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getPendingByClass(PDO $pdo, int $classId): array
    {
        $stmt = $pdo->prepare(
            'SELECT e.*, u.name AS student_name, u.email AS student_email FROM enrollments e JOIN users u ON e.student_id = u.id WHERE e.class_id = ? AND e.status = ? AND e.deleted_at IS NULL ORDER BY e.requested_at'
        );
        $stmt->execute([$classId, 'pending']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create(PDO $pdo, int $classId, int $studentId, string $status = 'pending', ?int $approvedBy = null): int
    {
        $stmt = $pdo->prepare('INSERT INTO enrollments (class_id, student_id, status, approved_at, approved_by) VALUES (?, ?, ?, ?, ?)');
        $approvedAt = $status === 'approved' ? date('Y-m-d H:i:s') : null;
        $stmt->execute([$classId, $studentId, $status, $approvedAt, $status === 'approved' ? $approvedBy : null]);
        return (int) $pdo->lastInsertId();
    }

    public static function approve(PDO $pdo, int $id, int $approvedBy): bool
    {
        $stmt = $pdo->prepare('UPDATE enrollments SET status = ?, approved_at = NOW(), approved_by = ? WHERE id = ? AND deleted_at IS NULL');
        return $stmt->execute(['approved', $approvedBy, $id]);
    }

    public static function reject(PDO $pdo, int $id): bool
    {
        $stmt = $pdo->prepare('UPDATE enrollments SET status = ? WHERE id = ? AND deleted_at IS NULL');
        return $stmt->execute(['rejected', $id]);
    }

    public static function exists(PDO $pdo, int $classId, int $studentId): bool
    {
        $stmt = $pdo->prepare('SELECT 1 FROM enrollments WHERE class_id = ? AND student_id = ? AND deleted_at IS NULL');
        $stmt->execute([$classId, $studentId]);
        return (bool) $stmt->fetchColumn();
    }

    public static function hasOtherEnrollmentInCourse(PDO $pdo, int $courseId, int $studentId, ?int $excludeClassId = null): bool
    {
        $sql = 'SELECT 1
                FROM enrollments e
                JOIN classes cl ON e.class_id = cl.id
                WHERE e.student_id = ?
                  AND cl.course_id = ?
                  AND e.status = ?
                  AND e.deleted_at IS NULL
                  AND cl.deleted_at IS NULL';
        $params = [$studentId, $courseId, 'approved'];
        if ($excludeClassId !== null) {
            $sql .= ' AND e.class_id != ?';
            $params[] = $excludeClassId;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }
}
