<?php

namespace App\Controllers;

use PDO;

class PasswordController
{
    public function showForgot(): void
    {
        $error = flash('error');
        $success = flash('success');
        require __DIR__ . '/../../views/auth/forgot.php';
    }

    public function sendReset(): void
    {
        $email = trim($_POST['email'] ?? '');
        if ($email === '') {
            flash_set('error', __('Email is required.'));
            redirect('password/forgot');
        }
        $pdo = db();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND deleted_at IS NULL');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + 3600);
            $stmt = $pdo->prepare(
                'INSERT INTO password_resets (user_id, email, token, expires_at) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$user['id'], $email, $token, $expiresAt]);
            // In a real app, send email here. For now, we display the link.
            flash_set('success', __('A reset link has been generated (development only). Use the URL shown below to reset your password.'));
            $_SESSION['last_reset_token'] = $token;
        } else {
            // Do not reveal whether the email exists
            flash_set('success', __('If this email exists, a reset link has been generated.'));
        }
        redirect('password/forgot');
    }

    public function showReset(): void
    {
        $token = $_GET['token'] ?? '';
        if ($token === '') {
            flash_set('error', __('Invalid or expired password reset token.'));
            redirect('password/forgot');
        }
        $pdo = db();
        $stmt = $pdo->prepare(
            'SELECT * FROM password_resets WHERE token = ? AND used_at IS NULL AND expires_at > NOW()'
        );
        $stmt->execute([$token]);
        $reset = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$reset) {
            flash_set('error', __('Invalid or expired password reset token.'));
            redirect('password/forgot');
        }
        $error = flash('error');
        $success = flash('success');
        require __DIR__ . '/../../views/auth/reset.php';
    }

    public function reset(): void
    {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirmation'] ?? '';
        if ($token === '' || $password === '') {
            flash_set('error', __('Password is required.'));
            redirect('password/reset?token=' . urlencode($token));
        }
        if ($password !== $passwordConfirm) {
            flash_set('error', __('Passwords do not match.'));
            redirect('password/reset?token=' . urlencode($token));
        }
        $pdo = db();
        $stmt = $pdo->prepare(
            'SELECT * FROM password_resets WHERE token = ? AND used_at IS NULL AND expires_at > NOW()'
        );
        $stmt->execute([$token]);
        $reset = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$reset) {
            flash_set('error', __('Invalid or expired password reset token.'));
            redirect('password/forgot');
        }
        $userId = (int) $reset['user_id'];
        if ($userId <= 0) {
            flash_set('error', __('User not found.'));
            redirect('password/forgot');
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE users SET password_hash = ?, failed_login_attempts = 0, locked_until = NULL WHERE id = ?');
        $stmt->execute([$hash, $userId]);
        $stmt = $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = ?');
        $stmt->execute([$reset['id']]);
        flash_set('success', __('Password has been reset. You can log in now.'));
        redirect('login');
    }
}

