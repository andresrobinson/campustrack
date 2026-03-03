<?php

namespace App\Controllers;

use Core\Auth;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\Course;

class EnrollmentController
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
        if (!Auth::canManageCourses() && !$this->isTeacherOfClass($classId)) {
            flash_set('error', __('Access denied.'));
            redirect('classes');
        }
        $enrollments = Enrollment::getByClass(db(), $classId);
        $pending = Enrollment::getPendingByClass(db(), $classId);
        $content = $this->render('enrollments/index', [
            'class' => $class,
            'enrollments' => $enrollments,
            'pending' => $pending,
        ]);
        $this->layout($content);
    }

    public function create(): void
    {
        if (!Auth::check() || !Auth::canManageCourses()) {
            flash_set('error', __('Access denied.'));
            redirect('classes');
        }
        $classId = (int) ($_GET['class_id'] ?? 0);
        $class = $classId ? SchoolClass::find(db(), $classId) : null;
        if (!$class) {
            flash_set('error', __('Class not found.'));
            redirect('classes');
        }
        $students = User::students(db());
        $content = $this->render('enrollments/form', ['class' => $class, 'students' => $students]);
        $this->layout($content);
    }

    public function store(): void
    {
        if (!Auth::check() || !Auth::canManageCourses()) {
            flash_set('error', __('Access denied.'));
            redirect('classes');
        }
        $classId = (int) ($_POST['class_id'] ?? 0);
        $studentId = (int) ($_POST['student_id'] ?? 0);
        $approve = isset($_POST['approve']);
        $class = SchoolClass::find(db(), $classId);
        if (!$class || $studentId <= 0) {
            flash_set('error', __('Class and student are required.'));
            redirect('classes');
        }
        $pdo = db();
        if (Enrollment::exists($pdo, $classId, $studentId)) {
            flash_set('error', __('Student is already enrolled in this class.'));
            redirect('enrollments/create?class_id=' . $classId);
        }
        $status = $approve ? 'approved' : 'pending';
        Enrollment::create($pdo, $classId, $studentId, $status, $approve ? Auth::id() : null);
        $hasOther = Enrollment::hasOtherEnrollmentInCourse($pdo, (int) $class['course_id'], $studentId, $classId);
        if ($hasOther) {
            $message = $approve
                ? __('Enrollment approved. Note: student is already enrolled in another class of this course.')
                : __('Enrollment requested. Note: student is already enrolled in another class of this course.');
        } else {
            $message = $approve ? __('Enrollment approved.') : __('Enrollment requested.');
        }
        flash_set('success', $message);
        redirect('enrollments?class_id=' . $classId);
    }

    public function approve(): void
    {
        if (!Auth::check()) {
            redirect('login');
        }
        $id = (int) ($_POST['id'] ?? 0);
        $enrollment = $this->getEnrollment($id);
        if (!$enrollment) {
            redirect('classes');
        }
        if (!Auth::canManageCourses() && !$this->isTeacherOfClass($enrollment['class_id'])) {
            flash_set('error', __('Access denied.'));
            redirect('enrollments?class_id=' . $enrollment['class_id']);
        }
        if ($enrollment['status'] !== 'pending') {
            flash_set('error', __('Enrollment is not pending.'));
            redirect('enrollments?class_id=' . $enrollment['class_id']);
        }
        $pdo = db();
        Enrollment::approve($pdo, $id, Auth::id());
        $hasOther = Enrollment::hasOtherEnrollmentInCourse($pdo, (int) $enrollment['course_id'], (int) $enrollment['student_id'], (int) $enrollment['class_id']);
        if ($hasOther) {
            flash_set('success', __('Enrollment approved. Note: student is already enrolled in another class of this course.'));
        } else {
            flash_set('success', __('Enrollment approved.'));
        }
        redirect('enrollments?class_id=' . $enrollment['class_id']);
    }

    public function reject(): void
    {
        if (!Auth::check()) {
            redirect('login');
        }
        $id = (int) ($_POST['id'] ?? 0);
        $enrollment = $this->getEnrollment($id);
        if (!$enrollment) {
            redirect('classes');
        }
        if (!Auth::canManageCourses() && !$this->isTeacherOfClass($enrollment['class_id'])) {
            flash_set('error', __('Access denied.'));
            redirect('enrollments?class_id=' . $enrollment['class_id']);
        }
        Enrollment::reject(db(), $id);
        flash_set('success', __('Enrollment rejected.'));
        redirect('enrollments?class_id=' . $enrollment['class_id']);
    }

    private function getEnrollment(int $id): ?array
    {
        $stmt = db()->prepare(
            'SELECT e.*, cl.course_id FROM enrollments e JOIN classes cl ON e.class_id = cl.id WHERE e.id = ? AND e.deleted_at IS NULL AND cl.deleted_at IS NULL'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
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
