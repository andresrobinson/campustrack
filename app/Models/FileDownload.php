<?php

namespace App\Models;

use PDO;

class FileDownload
{
    public static function log(PDO $pdo, int $fileId, int $userId): void
    {
        $stmt = $pdo->prepare(
            'INSERT INTO file_downloads (file_id, user_id, downloaded_at) VALUES (?, ?, NOW())'
        );
        $stmt->execute([$fileId, $userId]);
    }

    public static function getByFile(PDO $pdo, int $fileId): array
    {
        $stmt = $pdo->prepare(
            'SELECT d.*, u.name, u.email FROM file_downloads d JOIN users u ON d.user_id = u.id WHERE d.file_id = ? ORDER BY d.downloaded_at DESC'
        );
        $stmt->execute([$fileId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

