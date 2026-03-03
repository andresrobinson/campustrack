<?php

namespace App\Controllers;

use Core\Auth;
use App\Models\Report;
use App\Models\SchoolClass;
use App\Models\Course;
use App\Models\User;

class ReportController
{
    public function class(): void
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
        // Admin/manager or teachers of this class only
        if (!Auth::canManageCourses() && !$this->isTeacherOfClass($classId)) {
            flash_set('error', __('Access denied.'));
            redirect('classes');
        }
        $course = Course::find(db(), $class['course_id']);
        $data = Report::getClassReportData(db(), $classId);
        $content = $this->render('reports/class', [
            'class' => $class,
            'course' => $course,
            'students' => $data['students'],
            'planned_minutes' => $data['planned_minutes'],
        ]);
        $this->layout($content);
    }

    public function student(): void
    {
        if (!Auth::check()) {
            redirect('login');
        }
        $current = Auth::user();
        $studentId = (int) ($_GET['student_id'] ?? 0);
        if ($current['role'] === 'student') {
            $studentId = $current['id'];
        } elseif ($studentId <= 0) {
            flash_set('error', __('Select a student.'));
            redirect('dashboard');
        }
        $student = User::find(db(), $studentId);
        if (!$student) {
            flash_set('error', __('Student not found.'));
            redirect('dashboard');
        }
        // Admin/manager can see any; students see themselves; teachers for now unrestricted
        $classes = Report::getStudentReportData(db(), $studentId);
        $content = $this->render('reports/student', [
            'student' => $student,
            'classes' => $classes,
        ]);
        $this->layout($content);
    }

    public function matrix(): void
    {
        if (!Auth::check() || !Auth::canManageCourses()) {
            flash_set('error', __('Access denied.'));
            redirect('dashboard');
        }
        $courseId = isset($_GET['course_id']) ? (int) $_GET['course_id'] : null;
        $pdo = db();
        $data = Report::getMatrixData($pdo, $courseId ?: null);
        $courses = Course::all($pdo);
        $content = $this->render('reports/matrix', [
            'matrix' => $data,
            'courses' => $courses,
            'selectedCourseId' => $courseId,
        ]);
        $this->layout($content);
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

