<?php

namespace App\Models;

use PDO;

class AssessmentGrade
{
    public static function getMapByAssessment(PDO $pdo, int $assessmentId): array
    {
        $stmt = $pdo->prepare(
            'SELECT * FROM assessment_grades WHERE assessment_id = ? AND deleted_at IS NULL'
        );
        $stmt->execute([$assessmentId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['student_id']] = $row;
        }
        return $map;
    }

    public static function save(PDO $pdo, int $assessmentId, int $studentId, float $score, int $recordedBy): void
    {
        $stmt = $pdo->prepare(
            'UPDATE assessment_grades SET score = ?, recorded_by = ?, deleted_at = NULL WHERE assessment_id = ? AND student_id = ?'
        );
        $stmt->execute([$score, $recordedBy, $assessmentId, $studentId]);
        if ($stmt->rowCount() === 0) {
            $ins = $pdo->prepare(
                'INSERT INTO assessment_grades (assessment_id, student_id, score, recorded_by) VALUES (?, ?, ?, ?)'
            );
            $ins->execute([$assessmentId, $studentId, $score, $recordedBy]);
        }
    }

    public static function delete(PDO $pdo, int $assessmentId, int $studentId): void
    {
        $stmt = $pdo->prepare(
            'UPDATE assessment_grades SET deleted_at = NOW() WHERE assessment_id = ? AND student_id = ?'
        );
        $stmt->execute([$assessmentId, $studentId]);
    }
}

