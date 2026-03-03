<?php

namespace App\Controllers;

use Core\Auth;
use App\Models\Lecture;
use App\Models\SchoolClass;
use App\Models\Enrollment;
use App\Models\Attendance;
use App\Models\Course;

class AttendanceController
{
    public function index(): void
    {
        if (!Auth::check()) {
            redirect('login');
        }
        $lectureId = (int) ($_GET['lecture_id'] ?? 0);
        $lecture = $lectureId ? Lecture::find(db(), $lectureId) : null;
        if (!$lecture) {
            flash_set('error', __('Lecture not found.'));
            redirect('classes');
        }
        $class = SchoolClass::find(db(), $lecture['class_id']);
        if (!$class) {
            flash_set('error', __('Class not found.'));
            redirect('classes');
        }
        $course = Course::find(db(), $class['course_id']);
        if (!Auth::canManageCourses() && !$this->isTeacherOfClass($lecture['class_id'])) {
            flash_set('error', __('Access denied.'));
            redirect('lectures?class_id=' . $lecture['class_id']);
        }
        $students = Enrollment::getApprovedStudentsByClass(db(), $lecture['class_id']);
        $attendanceMap = Attendance::getMapByLecture(db(), $lectureId);
        $attendanceMode = $course['attendance_mode'] ?? 'simple';
        $durationMinutes = (int) $lecture['duration_minutes'];
        $content = $this->render('attendance/index', [
            'lecture' => $lecture,
            'class' => $class,
            'students' => $students,
            'attendanceMap' => $attendanceMap,
            'attendanceMode' => $attendanceMode,
            'durationMinutes' => $durationMinutes,
        ]);
        $this->layout($content);
    }

    public function save(): void
    {
        if (!Auth::check()) {
            redirect('login');
        }
        $lectureId = (int) ($_POST['lecture_id'] ?? 0);
        $lecture = $lectureId ? Lecture::find(db(), $lectureId) : null;
        if (!$lecture) {
            flash_set('error', __('Lecture not found.'));
            redirect('classes');
        }
        if (!Auth::canManageCourses() && !$this->isTeacherOfClass($lecture['class_id'])) {
            flash_set('error', __('Access denied.'));
            redirect('lectures?class_id=' . $lecture['class_id']);
        }
        $class = SchoolClass::find(db(), $lecture['class_id']);
        $course = $class ? Course::find(db(), $class['course_id']) : null;
        $course = $course ?: ['attendance_mode' => 'simple'];
        $attendanceMode = $course['attendance_mode'];
        $durationMinutes = (int) $lecture['duration_minutes'];
        $pdo = db();
        foreach ($_POST['attendance'] ?? [] as $studentId => $status) {
            $studentId = (int) $studentId;
            if ($studentId <= 0) continue;
            if (!in_array($status, ['present', 'absent', 'late', 'excused'], true)) {
                $status = 'absent';
            }
            if ($attendanceMode === 'simple' && !in_array($status, ['present', 'absent'], true)) {
                $status = $status === 'late' ? 'present' : ($status === 'excused' ? 'present' : $status);
            }
            $credited = Attendance::creditedMinutes($durationMinutes, $status, $attendanceMode);
            Attendance::save($pdo, $lectureId, $studentId, $status, $credited, Auth::id());
        }
        flash_set('success', __('Attendance saved.'));
        redirect('attendance?lecture_id=' . $lectureId);
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
