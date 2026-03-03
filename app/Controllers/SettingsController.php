<?php

namespace App\Controllers;

use Core\Auth;

class SettingsController
{
    public function index(): void
    {
        if (!Auth::check() || !Auth::canManageSystem()) {
            flash_set('error', __('Access denied.'));
            redirect('dashboard');
        }
        $pdo = db();
        $stmt = $pdo->prepare("SELECT `key`, `value` FROM settings WHERE `key` IN ('default_language')");
        $stmt->execute();
        $settings = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $settings[$row['key']] = $row['value'];
        }
        $content = $this->render('settings/index', [
            'settings' => $settings,
        ]);
        $this->layout($content);
    }

    public function updateLanguage(): void
    {
        if (!Auth::check() || !Auth::canManageSystem()) {
            flash_set('error', __('Access denied.'));
            redirect('dashboard');
        }
        $lang = $_POST['default_language'] ?? 'pt_BR';
        if (!in_array($lang, ['en', 'pt_BR'], true)) {
            $lang = 'pt_BR';
        }
        $pdo = db();
        // Upsert
        $stmt = $pdo->prepare('INSERT INTO settings (`key`, `value`, created_at, updated_at) VALUES (?,?,NOW(),NOW()) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), updated_at = VALUES(updated_at)');
        $stmt->execute(['default_language', $lang]);
        flash_set('success', __('Settings saved.'));
        redirect('settings');
    }

    private function render(string $view, array $data = []): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require __DIR__ . '/../../views/' . $view . '.php';
        return ob_get_clean();
    }

    private function layout(string $content): void
    {
        require __DIR__ . '/../../views/layout.php';
    }
}

