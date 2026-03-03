<?php

namespace App\Controllers;

use Core\Auth;
use App\Models\Course;
use App\Models\SchoolClass;
use App\Models\User;

class CourseController
{
    public function index(): void
    {
        if (!Auth::check() || !Auth::canManageCourses()) {
            flash_set('error', __('Access denied.'));
            redirect('dashboard');
        }
        $courses = Course::all(db());
        $content = $this->render('courses/index', ['courses' => $courses]);
        $this->layout($content);
    }

    public function create(): void
    {
        if (!Auth::check() || !Auth::canManageCourses()) {
            flash_set('error', __('Access denied.'));
            redirect('dashboard');
        }
        $content = $this->render('courses/form', ['course' => null]);
        $this->layout($content);
    }

    public function store(): void
    {
        if (!Auth::check() || !Auth::canManageCourses()) {
            flash_set('error', __('Access denied.'));
            redirect('dashboard');
        }
        $data = [
            'code' => trim($_POST['code'] ?? ''),
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'is_public' => isset($_POST['is_public']),
            'attendance_mode' => $_POST['attendance_mode'] ?? 'simple',
            'default_lecture_duration_minutes' => $_POST['default_lecture_duration_minutes'] ?? null,
            'has_grading' => isset($_POST['has_grading']),
            'gpa_formula' => $_POST['gpa_formula'] ?? 'average',
        ];
        if ($data['code'] === '' || $data['name'] === '') {
            flash_set('error', __('Code and name are required.'));
            redirect('courses/create');
        }
        if (Course::codeExists(db(), $data['code'])) {
            flash_set('error', __('This code is already in use.'));
            redirect('courses/create');
        }
        Course::create(db(), $data, Auth::id());
        flash_set('success', __('Course created successfully.'));
        redirect('courses');
    }

    public function edit(): void
    {
        if (!Auth::check() || !Auth::canManageCourses()) {
            flash_set('error', __('Access denied.'));
            redirect('dashboard');
        }
        $id = (int) ($_GET['id'] ?? 0);
        $course = $id ? Course::find(db(), $id) : null;
        if (!$course) {
            flash_set('error', __('Course not found.'));
            redirect('courses');
        }
        $content = $this->render('courses/form', ['course' => $course]);
        $this->layout($content);
    }

    public function update(): void
    {
        if (!Auth::check() || !Auth::canManageCourses()) {
            flash_set('error', __('Access denied.'));
            redirect('dashboard');
        }
        $id = (int) ($_POST['id'] ?? 0);
        $course = $id ? Course::find(db(), $id) : null;
        if (!$course) {
            flash_set('error', __('Course not found.'));
            redirect('courses');
        }
        $data = [
            'code' => trim($_POST['code'] ?? ''),
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'is_public' => isset($_POST['is_public']),
            'attendance_mode' => $_POST['attendance_mode'] ?? 'simple',
            'default_lecture_duration_minutes' => $_POST['default_lecture_duration_minutes'] ?? null,
            'has_grading' => isset($_POST['has_grading']),
            'gpa_formula' => $_POST['gpa_formula'] ?? 'average',
        ];
        if ($data['code'] === '' || $data['name'] === '') {
            flash_set('error', __('Code and name are required.'));
            redirect('courses/edit?id=' . $id);
        }
        if (Course::codeExists(db(), $data['code'], $id)) {
            flash_set('error', __('This code is already in use.'));
            redirect('courses/edit?id=' . $id);
        }
        Course::update(db(), $id, $data, Auth::id());
        flash_set('success', __('Course updated successfully.'));
        redirect('courses');
    }

    public function delete(): void
    {
        if (!Auth::check() || !Auth::canManageCourses()) {
            flash_set('error', __('Access denied.'));
            redirect('dashboard');
        }
        $id = (int) ($_POST['id'] ?? 0);
        if ($id && Course::find(db(), $id)) {
            Course::softDelete(db(), $id);
            flash_set('success', __('Course deleted.'));
        }
        redirect('courses');
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
