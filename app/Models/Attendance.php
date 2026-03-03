<?php

namespace App\Models;

use PDO;

class Attendance
{
    public static function getByLecture(PDO $pdo, int $lectureId): array
    {
        $stmt = $pdo->prepare(
            'SELECT a.*, u.name AS student_name FROM attendance a JOIN users u ON a.student_id = u.id WHERE a.lecture_id = ? AND a.deleted_at IS NULL ORDER BY u.name'
        );
        $stmt->execute([$lectureId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getMapByLecture(PDO $pdo, int $lectureId): array
    {
        $rows = self::getByLecture($pdo, $lectureId);
        $map = [];
        foreach ($rows as $r) {
            $map[(int) $r['student_id']] = $r;
        }
        return $map;
    }

    /** Compute credited minutes: simple = present full / absent 0; detailed = present full, late 0.5x, excused full, absent 0 */
    public static function creditedMinutes(int $durationMinutes, string $status, string $attendanceMode): int
    {
        if ($attendanceMode === 'simple') {
            return $status === 'present' ? $durationMinutes : 0;
        }
        switch ($status) {
            case 'present':
            case 'excused':
                return $durationMinutes;
            case 'late':
                return (int) round($durationMinutes * 0.5);
            case 'absent':
            default:
                return 0;
        }
    }

    public static function save(PDO $pdo, int $lectureId, int $studentId, string $status, int $creditedMinutes, int $recordedBy): void
    {
        $stmt = $pdo->prepare('UPDATE attendance SET status = ?, credited_minutes = ?, recorded_by = ?, deleted_at = NULL WHERE lecture_id = ? AND student_id = ?');
        $stmt->execute([$status, $creditedMinutes, $recordedBy, $lectureId, $studentId]);
        if ($stmt->rowCount() === 0) {
            $ins = $pdo->prepare('INSERT INTO attendance (lecture_id, student_id, status, credited_minutes, recorded_by) VALUES (?, ?, ?, ?, ?)');
            $ins->execute([$lectureId, $studentId, $status, $creditedMinutes, $recordedBy]);
        }
    }

    public static function deleteByLectureStudent(PDO $pdo, int $lectureId, int $studentId): void
    {
        $stmt = $pdo->prepare('UPDATE attendance SET deleted_at = NOW() WHERE lecture_id = ? AND student_id = ?');
        $stmt->execute([$lectureId, $studentId]);
    }
}
