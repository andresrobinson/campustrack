<?php

namespace App\Controllers;

use Core\Auth;
use App\Models\InviteCode;
use App\Models\SchoolClass;
use PDO;

class InviteCodeController
{
    public function index(): void
    {
        if (!Auth::check() || !Auth::canManageSystem()) {
            flash_set('error', __('Access denied.'));
            redirect('dashboard');
        }
        $codes = InviteCode::all(db());
        $content = $this->render('invites/index', ['codes' => $codes]);
        $this->layout($content);
    }

    public function create(): void
    {
        if (!Auth::check() || !Auth::canManageSystem()) {
            flash_set('error', __('Access denied.'));
            redirect('dashboard');
        }
        $content = $this->render('invites/form_registration', ['invite' => null]);
        $this->layout($content);
    }

    public function store(): void
    {
        if (!Auth::check() || !Auth::canManageSystem()) {
            flash_set('error', __('Access denied.'));
            redirect('dashboard');
        }
        $pdo = db();
        $code = trim($_POST['code'] ?? '');
        if ($code === '') {
            $code = InviteCode::generateCode();
        }
        $maxUses = $_POST['max_uses'] ?? null;
        $expiresAt = trim($_POST['expires_at'] ?? '');
        $data = [
            'code' => $code,
            'course_id' => null,
            'class_id' => null,
            'auto_approve' => !empty($_POST['auto_approve']),
            'max_uses' => $maxUses,
            'expires_at' => $expiresAt !== '' ? $expiresAt : null,
            'note' => trim($_POST['note'] ?? ''),
            'created_by' => Auth::id(),
        ];
        InviteCode::create($pdo, $data);
        flash_set('success', __('Invite code created.'));
        redirect('invites');
    }

    public function createForClass(): void
    {
        if (!Auth::check()) {
            redirect('login');
        }
        $classId = (int) ($_GET['class_id'] ?? 0);
        if ($classId <= 0) {
            flash_set('error', __('Class not found.'));
            redirect('classes');
        }
        $pdo = db();
        $class = SchoolClass::find($pdo, $classId);
        if (!$class) {
            flash_set('error', __('Class not found.'));
            redirect('classes');
        }
        // Only managers/admins or teachers of this class
        $allowed = Auth::canManageCourses();
        if (!$allowed && Auth::isTeacher()) {
            $stmt = $pdo->prepare('SELECT 1 FROM class_teachers WHERE class_id = ? AND teacher_id = ? AND deleted_at IS NULL');
            $stmt->execute([$classId, Auth::id()]);
            $allowed = (bool) $stmt->fetchColumn();
        }
        if (!$allowed) {
            flash_set('error', __('Access denied.'));
            redirect('classes');
        }
        $content = $this->render('invites/form_class', ['class' => $class]);
        $this->layout($content);
    }

    public function storeForClass(): void
    {
        if (!Auth::check()) {
            redirect('login');
        }
        $classId = (int) ($_POST['class_id'] ?? 0);
        if ($classId <= 0) {
            flash_set('error', __('Class not found.'));
            redirect('classes');
        }
        $pdo = db();
        $class = SchoolClass::find($pdo, $classId);
        if (!$class) {
            flash_set('error', __('Class not found.'));
            redirect('classes');
        }
        $allowed = Auth::canManageCourses();
        if (!$allowed && Auth::isTeacher()) {
            $stmt = $pdo->prepare('SELECT 1 FROM class_teachers WHERE class_id = ? AND teacher_id = ? AND deleted_at IS NULL');
            $stmt->execute([$classId, Auth::id()]);
            $allowed = (bool) $stmt->fetchColumn();
        }
        if (!$allowed) {
            flash_set('error', __('Access denied.'));
            redirect('classes');
        }
        $code = trim($_POST['code'] ?? '');
        if ($code === '') {
            $code = InviteCode::generateCode();
        }
        $maxUses = $_POST['max_uses'] ?? null;
        $expiresAt = trim($_POST['expires_at'] ?? '');
        $data = [
            'code' => $code,
            'course_id' => (int) $class['course_id'],
            'class_id' => $classId,
            'auto_approve' => !empty($_POST['auto_approve']),
            'max_uses' => $maxUses,
            'expires_at' => $expiresAt !== '' ? $expiresAt : null,
            'note' => trim($_POST['note'] ?? ''),
            'created_by' => Auth::id(),
        ];
        InviteCode::create($pdo, $data);
        flash_set('success', __('Invite code created.'));
        redirect('enrollments?class_id=' . $classId);
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

