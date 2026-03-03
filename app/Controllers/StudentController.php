<?php

namespace App\Controllers;

use Core\Auth;
use App\Models\SchoolClass;
use App\Models\Course;
use App\Models\Enrollment;
use PDO;

class StudentController
{
    public function classes(): void
    {
        if (!Auth::check() || !Auth::isStudent()) {
            redirect('login');
        }
        $pdo = db();
        $studentId = Auth::id();

        // Current/past enrollments for this student
        $stmt = $pdo->prepare(
            'SELECT e.*, cl.code AS class_code, cl.name AS class_name, c.code AS course_code, c.name AS course_name
             FROM enrollments e
             JOIN classes cl ON e.class_id = cl.id
             JOIN courses c ON cl.course_id = c.id
             WHERE e.student_id = ? AND e.deleted_at IS NULL AND cl.deleted_at IS NULL AND c.deleted_at IS NULL
             ORDER BY c.code, cl.code'
        );
        $stmt->execute([$studentId]);
        $my = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Available classes from public courses where the student is not already enrolled/pending
        $stmt = $pdo->prepare(
            'SELECT cl.*, c.code AS course_code, c.name AS course_name
             FROM classes cl
             JOIN courses c ON cl.course_id = c.id
             WHERE c.is_public = 1
               AND cl.deleted_at IS NULL
               AND c.deleted_at IS NULL
               AND NOT EXISTS (
                   SELECT 1 FROM enrollments e
                   WHERE e.class_id = cl.id AND e.student_id = ? AND e.deleted_at IS NULL
               )
             ORDER BY c.code, cl.code'
        );
        $stmt->execute([$studentId]);
        $available = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $content = $this->render('student/classes', [
            'my' => $my,
            'available' => $available,
        ]);
        $this->layout($content);
    }

    public function requestEnrollment(): void
    {
        if (!Auth::check() || !Auth::isStudent()) {
            redirect('login');
        }
        $classId = (int) ($_POST['class_id'] ?? 0);
        $studentId = Auth::id();
        if ($classId <= 0) {
            flash_set('error', __('Select a class.'));
            redirect('student/classes');
        }
        $pdo = db();
        $class = SchoolClass::find($pdo, $classId);
        if (!$class) {
            flash_set('error', __('Class not found.'));
            redirect('student/classes');
        }
        $course = Course::find($pdo, (int) $class['course_id']);
        if (!$course || empty($course['is_public'])) {
            flash_set('error', __('This class does not accept self-enrollment.'));
            redirect('student/classes');
        }
        if (Enrollment::exists($pdo, $classId, $studentId)) {
            flash_set('error', __('You are already enrolled in this class.'));
            redirect('student/classes');
        }
        $hasOther = Enrollment::hasOtherEnrollmentInCourse($pdo, (int) $class['course_id'], $studentId, $classId);
        Enrollment::create($pdo, $classId, $studentId, 'pending', null);
        if ($hasOther) {
            flash_set('success', __('Enrollment requested. Note: you are already enrolled in another class of this course.'));
        } else {
            flash_set('success', __('Enrollment requested.'));
        }
        redirect('student/classes');
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

