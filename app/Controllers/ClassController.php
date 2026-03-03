<?php

namespace App\Controllers;

use Core\Auth;
use App\Models\Course;
use App\Models\SchoolClass;
use App\Models\User;

class ClassController
{
    public function index(): void
    {
        if (!Auth::check() || !Auth::canManageCourses()) {
            flash_set('error', __('Access denied.'));
            redirect('dashboard');
        }
        $courseId = isset($_GET['course_id']) ? (int) $_GET['course_id'] : null;
        $classes = SchoolClass::all(db(), $courseId);
        $course = $courseId ? Course::find(db(), $courseId) : null;
        $content = $this->render('classes/index', [
            'classes' => $classes,
            'course' => $course,
            'courseId' => $courseId,
        ]);
        $this->layout($content);
    }

    public function create(): void
    {
        if (!Auth::check() || !Auth::canManageCourses()) {
            flash_set('error', __('Access denied.'));
            redirect('dashboard');
        }
        $courses = Course::all(db());
        $content = $this->render('classes/form', ['class' => null, 'courses' => $courses]);
        $this->layout($content);
    }

    public function store(): void
    {
        if (!Auth::check() || !Auth::canManageCourses()) {
            flash_set('error', __('Access denied.'));
            redirect('dashboard');
        }
        $data = $this->formData();
        if ($data['course_id'] <= 0 || $data['code'] === '' || $data['name'] === '') {
            flash_set('error', __('Course, code and name are required.'));
            redirect('classes/create');
        }
        if (SchoolClass::codeExists(db(), $data['code'])) {
            flash_set('error', __('This code is already in use.'));
            redirect('classes/create');
        }
        $id = SchoolClass::create(db(), $data, Auth::id());
        $teacherIds = array_filter(array_map('intval', $_POST['teacher_ids'] ?? []));
        if (!empty($teacherIds)) {
            SchoolClass::syncTeachers(db(), $id, $teacherIds);
        }
        flash_set('success', __('Class created successfully.'));
        redirect('classes?course_id=' . $data['course_id']);
    }

    public function edit(): void
    {
        if (!Auth::check() || !Auth::canManageCourses()) {
            flash_set('error', __('Access denied.'));
            redirect('dashboard');
        }
        $id = (int) ($_GET['id'] ?? 0);
        $class = $id ? SchoolClass::find(db(), $id) : null;
        if (!$class) {
            flash_set('error', __('Class not found.'));
            redirect('classes');
        }
        $courses = Course::all(db());
        $teachers = User::teachers(db());
        $assignedTeacherIds = SchoolClass::getTeacherIds(db(), $id);
        $content = $this->render('classes/form', [
            'class' => $class,
            'courses' => $courses,
            'teachers' => $teachers,
            'assignedTeacherIds' => $assignedTeacherIds,
        ]);
        $this->layout($content);
    }

    public function update(): void
    {
        if (!Auth::check() || !Auth::canManageCourses()) {
            flash_set('error', __('Access denied.'));
            redirect('dashboard');
        }
        $id = (int) ($_POST['id'] ?? 0);
        $class = $id ? SchoolClass::find(db(), $id) : null;
        if (!$class) {
            flash_set('error', __('Class not found.'));
            redirect('classes');
        }
        $data = $this->formData();
        if ($data['course_id'] <= 0 || $data['code'] === '' || $data['name'] === '') {
            flash_set('error', __('Course, code and name are required.'));
            redirect('classes/edit?id=' . $id);
        }
        if (SchoolClass::codeExists(db(), $data['code'], $id)) {
            flash_set('error', __('This code is already in use.'));
            redirect('classes/edit?id=' . $id);
        }
        SchoolClass::update(db(), $id, $data, Auth::id());
        $teacherIds = array_filter(array_map('intval', $_POST['teacher_ids'] ?? []));
        SchoolClass::syncTeachers(db(), $id, $teacherIds);
        flash_set('success', __('Class updated successfully.'));
        redirect('classes?course_id=' . $data['course_id']);
    }

    public function delete(): void
    {
        if (!Auth::check() || !Auth::canManageCourses()) {
            flash_set('error', __('Access denied.'));
            redirect('dashboard');
        }
        $id = (int) ($_POST['id'] ?? 0);
        if ($id && SchoolClass::find(db(), $id)) {
            SchoolClass::softDelete(db(), $id);
            flash_set('success', __('Class deleted.'));
        }
        redirect('classes');
    }

    private function formData(): array
    {
        return [
            'course_id' => (int) ($_POST['course_id'] ?? 0),
            'code' => trim($_POST['code'] ?? ''),
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'start_date' => trim($_POST['start_date'] ?? '') ?: null,
            'end_date' => trim($_POST['end_date'] ?? '') ?: null,
            'default_lecture_duration_minutes' => $_POST['default_lecture_duration_minutes'] ?? null,
            'auto_lectures_count' => $_POST['auto_lectures_count'] ?? null,
            'status' => $_POST['status'] ?? 'open',
        ];
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
