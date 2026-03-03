<?php

namespace App\Models;

use PDO;

class InviteCode
{
    public static function generateCode(int $length = 10): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $max = strlen($alphabet) - 1;
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $alphabet[random_int(0, $max)];
        }
        return $code;
    }

    public static function create(PDO $pdo, array $data): int
    {
        $stmt = $pdo->prepare(
            'INSERT INTO invite_codes (code, course_id, class_id, auto_approve, max_uses, used_count, expires_at, note, created_by)
             VALUES (?, ?, ?, ?, ?, 0, ?, ?, ?)'
        );
        $stmt->execute([
            $data['code'],
            $data['course_id'] ?? null,
            $data['class_id'] ?? null,
            !empty($data['auto_approve']) ? 1 : 0,
            isset($data['max_uses']) && $data['max_uses'] !== '' ? (int) $data['max_uses'] : null,
            $data['expires_at'] ?? null,
            $data['note'] ?? null,
            $data['created_by'] ?? null,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function all(PDO $pdo): array
    {
        $stmt = $pdo->prepare(
            'SELECT ic.*, c.code AS course_code, c.name AS course_name, cl.code AS class_code, cl.name AS class_name
             FROM invite_codes ic
             LEFT JOIN courses c ON ic.course_id = c.id
             LEFT JOIN classes cl ON ic.class_id = cl.id
             WHERE ic.deleted_at IS NULL
             ORDER BY ic.created_at DESC'
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findByCode(PDO $pdo, string $code): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM invite_codes WHERE code = ? AND deleted_at IS NULL');
        $stmt->execute([$code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function isUsable(array $invite): bool
    {
        if (!empty($invite['expires_at']) && $invite['expires_at'] < date('Y-m-d H:i:s')) {
            return false;
        }
        if ($invite['max_uses'] !== null && $invite['used_count'] >= $invite['max_uses']) {
            return false;
        }
        return true;
    }

    public static function incrementUse(PDO $pdo, int $id): void
    {
        $stmt = $pdo->prepare('UPDATE invite_codes SET used_count = used_count + 1 WHERE id = ?');
        $stmt->execute([$id]);
    }
}

