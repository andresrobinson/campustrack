<?php

namespace App\Controllers;

use Core\Auth;

class DashboardController
{
    public function index(): void
    {
        if (!Auth::check()) {
            redirect('login');
        }
        $user = Auth::user();
        $success = flash('success');
        require __DIR__ . '/../../views/dashboard/index.php';
    }
}
