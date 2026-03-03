<?php

namespace App\Controllers;

use Core\Auth;
use App\Models\User;
use PDO;

class UserController
{
    public function index(): void
    {
        if (!Auth::check() || (!Auth::canManageSystem() && !Auth::isManager())) {
            flash_set('error', __('Access denied.'));
            redirect('dashboard');
        }
        $pdo = db();
        $stmt = $pdo->query('SELECT id, name, email, role, status, approved_at FROM users WHERE deleted_at IS NULL ORDER BY role, name');
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $content = $this->render('users/index', ['users' => $users]);
        $this->layout($content);
    }

    public function create(): void
    {
        if (!Auth::check() || !Auth::canManageSystem()) {
            flash_set('error', __('Access denied.'));
            redirect('dashboard');
        }
        $content = $this->render('users/form', ['user' => null]);
        $this->layout($content);
    }

    public function store(): void
    {
        if (!Auth::check() || !Auth::canManageSystem()) {
            flash_set('error', __('Access denied.'));
            redirect('dashboard');
        }
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $preferredLanguage = $_POST['preferred_language'] ?? null;
        if (!in_array($preferredLanguage, ['en', 'pt_BR'], true)) {
            $preferredLanguage = null;
        }
        $role = $_POST['role'] ?? 'student';
        if (!in_array($role, ['admin', 'manager', 'teacher', 'student'], true)) {
            $role = 'student';
        }
        if ($name === '' || $email === '') {
            flash_set('error', __('Name and email are required.'));
            redirect('users/create');
        }
        $pdo = db();
        $stmt = $pdo->prepare('SELECT 1 FROM users WHERE email = ? AND deleted_at IS NULL');
        $stmt->execute([$email]);
        if ($stmt->fetchColumn()) {
            flash_set('error', __('This email is already registered.'));
            redirect('users/create');
        }
        $status = in_array($role, ['admin', 'manager', 'teacher'], true) ? 'active' : 'pending';
        $hash = password_hash($password ?: bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role, preferred_language, status, approved_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$name, $email, $hash, $role, $preferredLanguage, $status, $status === 'active' ? date('Y-m-d H:i:s') : null]);
        flash_set('success', __('User created successfully.'));
        redirect('users');
    }

    public function approve(): void
    {
        if (!Auth::check() || (!Auth::canManageSystem() && !Auth::isManager())) {
            flash_set('error', __('Access denied.'));
            redirect('dashboard');
        }
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash_set('error', __('User not found.'));
            redirect('users');
        }
        $pdo = db();
        $stmt = $pdo->prepare('SELECT id, role, status FROM users WHERE id = ? AND deleted_at IS NULL');
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user || $user['role'] !== 'student') {
            flash_set('error', __('User not found.'));
            redirect('users');
        }
        if ($user['status'] !== 'pending') {
            flash_set('error', __('Registration is not pending.'));
            redirect('users');
        }
        $stmt = $pdo->prepare('UPDATE users SET status = ?, approved_at = ? WHERE id = ?');
        $stmt->execute(['active', date('Y-m-d H:i:s'), $id]);
        flash_set('success', __('Registration approved.'));
        redirect('users');
    }

    public function reject(): void
    {
        if (!Auth::check() || (!Auth::canManageSystem() && !Auth::isManager())) {
            flash_set('error', __('Access denied.'));
            redirect('dashboard');
        }
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash_set('error', __('User not found.'));
            redirect('users');
        }
        $pdo = db();
        $stmt = $pdo->prepare('SELECT id, role, status FROM users WHERE id = ? AND deleted_at IS NULL');
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user || $user['role'] !== 'student') {
            flash_set('error', __('User not found.'));
            redirect('users');
        }
        if ($user['status'] !== 'pending') {
            flash_set('error', __('Registration is not pending.'));
            redirect('users');
        }
        $stmt = $pdo->prepare('UPDATE users SET status = ?, approved_at = ? WHERE id = ?');
        $stmt->execute(['rejected', null, $id]);
        flash_set('success', __('Registration rejected.'));
        redirect('users');
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
