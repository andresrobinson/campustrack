<?php

namespace App\Controllers;

use Core\Auth;

class AuthController
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            redirect('dashboard');
        }
        $error = flash('error');
        $success = flash('success');
        require __DIR__ . '/../../views/auth/login.php';
    }

    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($email === '' || $password === '') {
            flash_set('error', __('Invalid email or password.'));
            redirect('login');
        }
        $pdo = db();
        if (!Auth::login($email, $password, $pdo)) {
            flash_set('error', __('Invalid email or password.'));
            redirect('login');
        }
        redirect('dashboard');
    }

    public function logout(): void
    {
        Auth::logout();
        redirect('login');
    }

    public function showRegister(): void
    {
        if (Auth::check()) {
            redirect('dashboard');
        }
        $error = flash('error');
        $success = flash('success');
        require __DIR__ . '/../../views/auth/register.php';
    }

    public function register(): void
    {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirmation'] ?? '';

        if ($name === '' || $email === '' || $password === '') {
            flash_set('error', __('Name, email and password are required.'));
            redirect('register');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash_set('error', __('Invalid email address.'));
            redirect('register');
        }

        if ($password !== $passwordConfirm) {
            flash_set('error', __('Passwords do not match.'));
            redirect('register');
        }

        $pdo = db();
        $stmt = $pdo->prepare('SELECT 1 FROM users WHERE email = ? AND deleted_at IS NULL');
        $stmt->execute([$email]);
        if ($stmt->fetchColumn()) {
            flash_set('error', __('This email is already registered.'));
            redirect('register');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role, status, approved_at) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $name,
            $email,
            $hash,
            'student',
            'pending',
            null,
        ]);

        flash_set('success', __('Registration received. Please wait for manager approval before logging in.'));
        redirect('login');
    }
}
