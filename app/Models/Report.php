<?php

namespace App\Models;

use PDO;

class Report
{
    /** Total planned minutes for a class (sum of all lecture durations) */
    public static function getClassPlannedMinutes(PDO $pdo, int $classId): int
    {
        $stmt = $pdo->prepare('SELECT COALESCE(SUM(duration_minutes), 0) FROM lectures WHERE class_id = ? AND deleted_at IS NULL');
        $stmt->execute([$classId]);
        return (int) $stmt->fetchColumn();
    }

    /** Credited minutes per student for a class: student_id => total credited */
    public static function getCreditedByClass(PDO $pdo, int $classId): array
    {
        $stmt = $pdo->prepare(
            'SELECT a.student_id, COALESCE(SUM(a.credited_minutes), 0) AS total FROM attendance a
             JOIN lectures l ON a.lecture_id = l.id
             WHERE l.class_id = ? AND a.deleted_at IS NULL AND l.deleted_at IS NULL
             GROUP BY a.student_id'
        );
        $stmt->execute([$classId]);
        $out = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $out[(int) $row['student_id']] = (int) $row['total'];
        }
        return $out;
    }

    /** Per-class report: list of enrolled students with credited minutes and planned total */
    public static function getClassReportData(PDO $pdo, int $classId): array
    {
        $students = Enrollment::getApprovedStudentsByClass($pdo, $classId);
        $credited = self::getCreditedByClass($pdo, $classId);
        $planned = self::getClassPlannedMinutes($pdo, $classId);
        foreach ($students as &$s) {
            $s['credited_minutes'] = $credited[(int) $s['id']] ?? 0;
        }
        unset($s);
        return ['students' => $students, 'planned_minutes' => $planned];
    }

    /** Credited minutes per class for a student: class_id => total credited */
    public static function getCreditedByStudent(PDO $pdo, int $studentId): array
    {
        $stmt = $pdo->prepare(
            'SELECT l.class_id, COALESCE(SUM(a.credited_minutes), 0) AS total FROM attendance a
             JOIN lectures l ON a.lecture_id = l.id
             WHERE a.student_id = ? AND a.deleted_at IS NULL AND l.deleted_at IS NULL
             GROUP BY l.class_id'
        );
        $stmt->execute([$studentId]);
        $out = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $out[(int) $row['class_id']] = (int) $row['total'];
        }
        return $out;
    }

    /** Per-student report: list of enrolled classes with credited and planned minutes */
    public static function getStudentReportData(PDO $pdo, int $studentId): array
    {
        $stmt = $pdo->prepare(
            'SELECT cl.id AS class_id, cl.code AS class_code, cl.name AS class_name, c.name AS course_name
             FROM enrollments e
             JOIN classes cl ON e.class_id = cl.id
             JOIN courses c ON cl.course_id = c.id
             WHERE e.student_id = ? AND e.status = ? AND e.deleted_at IS NULL AND cl.deleted_at IS NULL AND c.deleted_at IS NULL
             ORDER BY c.code, cl.code'
        );
        $stmt->execute([$studentId, 'approved']);
        $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $credited = self::getCreditedByStudent($pdo, $studentId);
        foreach ($classes as &$cl) {
            $cl['credited_minutes'] = $credited[(int) $cl['class_id']] ?? 0;
            $cl['planned_minutes'] = self::getClassPlannedMinutes($pdo, (int) $cl['class_id']);
        }
        unset($cl);
        return $classes;
    }

    /** Matrix: students (rows) x classes (columns), cell = credited minutes. Optionally filter by course_id. */
    public static function getMatrixData(PDO $pdo, ?int $courseId = null): array
    {
        if ($courseId) {
            $stmt = $pdo->prepare(
                'SELECT DISTINCT cl.id, cl.code, cl.name AS class_name, c.name AS course_name
                 FROM classes cl JOIN courses c ON cl.course_id = c.id
                 WHERE cl.deleted_at IS NULL AND c.deleted_at IS NULL AND c.id = ?
                 ORDER BY cl.code'
            );
            $stmt->execute([$courseId]);
        } else {
            $stmt = $pdo->query(
                'SELECT cl.id, cl.code, cl.name AS class_name, c.name AS course_name
                 FROM classes cl JOIN courses c ON cl.course_id = c.id
                 WHERE cl.deleted_at IS NULL AND c.deleted_at IS NULL
                 ORDER BY c.code, cl.code'
            );
        }
        $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $pdo->prepare(
            'SELECT DISTINCT u.id, u.name FROM enrollments e
             JOIN users u ON e.student_id = u.id
             WHERE e.status = ? AND e.deleted_at IS NULL AND u.deleted_at IS NULL
             ORDER BY u.name'
        );
        $stmt->execute(['approved']);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $matrix = [];
        foreach ($students as $s) {
            $row = ['id' => (int) $s['id'], 'name' => $s['name'], 'classes' => []];
            foreach ($classes as $cl) {
                $cid = (int) $cl['id'];
                $row['classes'][$cid] = 0;
            }
            $matrix[(int) $s['id']] = $row;
        }
        foreach ($classes as $cl) {
            $cid = (int) $cl['id'];
            $credited = self::getCreditedByClass($pdo, $cid);
            foreach ($credited as $studentId => $mins) {
                if (isset($matrix[$studentId])) {
                    $matrix[$studentId]['classes'][$cid] = $mins;
                }
            }
        }
        return ['students' => array_values($matrix), 'classes' => $classes];
    }
}
