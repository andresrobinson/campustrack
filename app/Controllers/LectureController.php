<?php

namespace App\Controllers;

use Core\Auth;
use App\Models\Lecture;
use App\Models\SchoolClass;
use App\Models\Course;

class LectureController
{
    public function index(): void
    {
        if (!Auth::check()) {
            redirect('login');
        }
        $classId = (int) ($_GET['class_id'] ?? 0);
        if ($classId <= 0) {
            flash_set('error', __('Select a class.'));
            redirect('classes');
        }
        $class = SchoolClass::find(db(), $classId);
        if (!$class) {
            flash_set('error', __('Class not found.'));
            redirect('classes');
        }
        $canEdit = Auth::canManageCourses() || $this->isTeacherOfClass($classId);
        $lectures = Lecture::allByClass(db(), $classId);
        $content = $this->render('lectures/index', [
            'lectures' => $lectures,
            'class' => $class,
            'canEdit' => $canEdit,
        ]);
        $this->layout($content);
    }

    public function create(): void
    {
        if (!Auth::check()) {
            redirect('login');
        }
        $classId = (int) ($_GET['class_id'] ?? 0);
        $class = $classId ? SchoolClass::find(db(), $classId) : null;
        if (!$class) {
            flash_set('error', __('Class not found.'));
            redirect('classes');
        }
        if (!Auth::canManageCourses() && !$this->isTeacherOfClass($classId)) {
            flash_set('error', __('Access denied.'));
            redirect('lectures?class_id=' . $classId);
        }
        $content = $this->render('lectures/form', ['lecture' => null, 'class' => $class]);
        $this->layout($content);
    }

    public function store(): void
    {
        if (!Auth::check()) {
            redirect('login');
        }
        $classId = (int) ($_POST['class_id'] ?? 0);
        $class = $classId ? SchoolClass::find(db(), $classId) : null;
        if (!$class) {
            flash_set('error', __('Class not found.'));
            redirect('classes');
        }
        if (!Auth::canManageCourses() && !$this->isTeacherOfClass($classId)) {
            flash_set('error', __('Access denied.'));
            redirect('lectures?class_id=' . $classId);
        }
        $duration = (int) ($_POST['duration_minutes'] ?? 0);
        if ($duration <= 0) {
            $duration = (int) ($class['default_lecture_duration_minutes'] ?? $class['course_default_duration'] ?? 60);
        }
        $data = [
            'class_id' => $classId,
            'lecture_date' => trim($_POST['lecture_date'] ?? ''),
            'start_time' => trim($_POST['start_time'] ?? '') ?: null,
            'duration_minutes' => $duration,
            'location' => trim($_POST['location'] ?? '') ?: null,
            'is_extra' => isset($_POST['is_extra']),
        ];
        if ($data['lecture_date'] === '') {
            flash_set('error', __('Date is required.'));
            redirect('lectures/create?class_id=' . $classId);
        }
        Lecture::create(db(), $data, Auth::id());
        flash_set('success', __('Lecture created.'));
        redirect('lectures?class_id=' . $classId);
    }

    public function edit(): void
    {
        if (!Auth::check()) {
            redirect('login');
        }
        $id = (int) ($_GET['id'] ?? 0);
        $lecture = $id ? Lecture::find(db(), $id) : null;
        if (!$lecture) {
            flash_set('error', __('Lecture not found.'));
            redirect('classes');
        }
        $class = SchoolClass::find(db(), $lecture['class_id']);
        if (!Auth::canManageCourses() && !$this->isTeacherOfClass($lecture['class_id'])) {
            flash_set('error', __('Access denied.'));
            redirect('lectures?class_id=' . $lecture['class_id']);
        }
        $content = $this->render('lectures/form', ['lecture' => $lecture, 'class' => $class]);
        $this->layout($content);
    }

    public function update(): void
    {
        if (!Auth::check()) {
            redirect('login');
        }
        $id = (int) ($_POST['id'] ?? 0);
        $lecture = $id ? Lecture::find(db(), $id) : null;
        if (!$lecture) {
            flash_set('error', __('Lecture not found.'));
            redirect('classes');
        }
        if (!Auth::canManageCourses() && !$this->isTeacherOfClass($lecture['class_id'])) {
            flash_set('error', __('Access denied.'));
            redirect('lectures?class_id=' . $lecture['class_id']);
        }
        $data = [
            'lecture_date' => trim($_POST['lecture_date'] ?? ''),
            'start_time' => trim($_POST['start_time'] ?? '') ?: null,
            'duration_minutes' => (int) ($_POST['duration_minutes'] ?? $lecture['duration_minutes']),
            'location' => trim($_POST['location'] ?? '') ?: null,
        ];
        if ($data['lecture_date'] === '') {
            flash_set('error', __('Date is required.'));
            redirect('lectures/edit?id=' . $id);
        }
        Lecture::update(db(), $id, $data, Auth::id());
        flash_set('success', __('Lecture updated.'));
        redirect('lectures?class_id=' . $lecture['class_id']);
    }

    public function delete(): void
    {
        if (!Auth::check()) {
            redirect('login');
        }
        $id = (int) ($_POST['id'] ?? 0);
        $lecture = $id ? Lecture::find(db(), $id) : null;
        if (!$lecture) {
            redirect('classes');
        }
        if (!Auth::canManageCourses() && !$this->isTeacherOfClass($lecture['class_id'])) {
            flash_set('error', __('Access denied.'));
            redirect('lectures?class_id=' . $lecture['class_id']);
        }
        Lecture::softDelete(db(), $id);
        flash_set('success', __('Lecture deleted.'));
        redirect('lectures?class_id=' . $lecture['class_id']);
    }

    public function generate(): void
    {
        if (!Auth::check() || !Auth::canManageCourses()) {
            flash_set('error', __('Access denied.'));
            redirect('classes');
        }
        $classId = (int) ($_POST['class_id'] ?? 0);
        $class = $classId ? SchoolClass::find(db(), $classId) : null;
        if (!$class) {
            flash_set('error', __('Class not found.'));
            redirect('classes');
        }
        $count = (int) ($_POST['count'] ?? 0);
        $startDate = trim($_POST['start_date'] ?? '');
        if ($count <= 0 || $startDate === '') {
            flash_set('error', __('Start date and number of lectures are required.'));
            redirect('lectures?class_id=' . $classId);
        }
        $duration = (int) ($class['default_lecture_duration_minutes'] ?? $class['course_default_duration'] ?? 60);
        $startTime = trim($_POST['start_time'] ?? '') ?: null;
        $location = trim($_POST['location'] ?? '') ?: null;
        $created = Lecture::generateRecurring(db(), $classId, $startDate, $startTime, $duration, $count, Auth::id(), $location);
        flash_set('success', __(':count lectures created.', ['count' => $created]));
        redirect('lectures?class_id=' . $classId);
    }

    private function isTeacherOfClass(int $classId): bool
    {
        if (!Auth::isTeacher()) {
            return false;
        }
        $stmt = db()->prepare('SELECT 1 FROM class_teachers WHERE class_id = ? AND teacher_id = ? AND deleted_at IS NULL');
        $stmt->execute([$classId, Auth::id()]);
        return (bool) $stmt->fetchColumn();
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
