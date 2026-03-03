<?php

namespace App\Controllers;

use Core\Auth;
use App\Models\Assessment;
use App\Models\AssessmentGrade;
use App\Models\SchoolClass;
use App\Models\Enrollment;
use App\Models\Course;

class AssessmentController
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
        $course = Course::find(db(), $class['course_id']);
        if (empty($course['has_grading'])) {
            flash_set('error', __('Grading is disabled for this course.'));
            redirect('classes?course_id=' . $class['course_id']);
        }
        $canEdit = Auth::canManageCourses() || $this->isTeacherOfClass($classId);
        $assessments = Assessment::allByClass(db(), $classId);
        $content = $this->render('assessments/index', [
            'class' => $class,
            'course' => $course,
            'assessments' => $assessments,
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
        if (!$this->canEditClass($class)) {
            flash_set('error', __('Access denied.'));
            redirect('assessments?class_id=' . $classId);
        }
        $content = $this->render('assessments/form', [
            'assessment' => null,
            'class' => $class,
        ]);
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
        if (!$this->canEditClass($class)) {
            flash_set('error', __('Access denied.'));
            redirect('assessments?class_id=' . $classId);
        }
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            flash_set('error', __('Name is required.'));
            redirect('assessments/create?class_id=' . $classId);
        }
        $data = [
            'class_id' => $classId,
            'name' => $name,
            'description' => trim($_POST['description'] ?? ''),
            'position' => (int) ($_POST['position'] ?? 1),
            'max_score' => $_POST['max_score'] ?? null,
        ];
        Assessment::create(db(), $data, Auth::id());
        flash_set('success', __('Assessment created.'));
        redirect('assessments?class_id=' . $classId);
    }

    public function edit(): void
    {
        if (!Auth::check()) {
            redirect('login');
        }
        $id = (int) ($_GET['id'] ?? 0);
        $assessment = $id ? Assessment::find(db(), $id) : null;
        if (!$assessment) {
            flash_set('error', __('Assessment not found.'));
            redirect('classes');
        }
        $class = SchoolClass::find(db(), $assessment['class_id']);
        if (!$this->canEditClass($class)) {
            flash_set('error', __('Access denied.'));
            redirect('assessments?class_id=' . $assessment['class_id']);
        }
        $content = $this->render('assessments/form', [
            'assessment' => $assessment,
            'class' => $class,
        ]);
        $this->layout($content);
    }

    public function update(): void
    {
        if (!Auth::check()) {
            redirect('login');
        }
        $id = (int) ($_POST['id'] ?? 0);
        $assessment = $id ? Assessment::find(db(), $id) : null;
        if (!$assessment) {
            flash_set('error', __('Assessment not found.'));
            redirect('classes');
        }
        $class = SchoolClass::find(db(), $assessment['class_id']);
        if (!$this->canEditClass($class)) {
            flash_set('error', __('Access denied.'));
            redirect('assessments?class_id=' . $assessment['class_id']);
        }
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            flash_set('error', __('Name is required.'));
            redirect('assessments/edit?id=' . $id);
        }
        $data = [
            'name' => $name,
            'description' => trim($_POST['description'] ?? ''),
            'position' => (int) ($_POST['position'] ?? 1),
            'max_score' => $_POST['max_score'] ?? null,
        ];
        Assessment::update(db(), $id, $data, Auth::id());
        flash_set('success', __('Assessment updated.'));
        redirect('assessments?class_id=' . $assessment['class_id']);
    }

    public function delete(): void
    {
        if (!Auth::check()) {
            redirect('login');
        }
        $id = (int) ($_POST['id'] ?? 0);
        $assessment = $id ? Assessment::find(db(), $id) : null;
        if (!$assessment) {
            redirect('classes');
        }
        $class = SchoolClass::find(db(), $assessment['class_id']);
        if (!$this->canEditClass($class)) {
            flash_set('error', __('Access denied.'));
            redirect('assessments?class_id=' . $assessment['class_id']);
        }
        Assessment::softDelete(db(), $id);
        flash_set('success', __('Assessment deleted.'));
        redirect('assessments?class_id=' . $assessment['class_id']);
    }

    public function grades(): void
    {
        if (!Auth::check()) {
            redirect('login');
        }
        $assessmentId = (int) ($_GET['assessment_id'] ?? 0);
        $assessment = $assessmentId ? Assessment::find(db(), $assessmentId) : null;
        if (!$assessment) {
            flash_set('error', __('Assessment not found.'));
            redirect('classes');
        }
        $class = SchoolClass::find(db(), $assessment['class_id']);
        if (!$class) {
            flash_set('error', __('Class not found.'));
            redirect('classes');
        }
        $course = Course::find(db(), $class['course_id']);
        $canEdit = $this->canEditClass($class);
        $students = Enrollment::getApprovedStudentsByClass(db(), $class['id']);
        $grades = AssessmentGrade::getMapByAssessment(db(), $assessmentId);
        $content = $this->render('assessments/grades', [
            'assessment' => $assessment,
            'class' => $class,
            'course' => $course,
            'students' => $students,
            'grades' => $grades,
            'canEdit' => $canEdit,
        ]);
        $this->layout($content);
    }

    public function saveGrades(): void
    {
        if (!Auth::check()) {
            redirect('login');
        }
        $assessmentId = (int) ($_POST['assessment_id'] ?? 0);
        $assessment = $assessmentId ? Assessment::find(db(), $assessmentId) : null;
        if (!$assessment) {
            flash_set('error', __('Assessment not found.'));
            redirect('classes');
        }
        $class = SchoolClass::find(db(), $assessment['class_id']);
        if (!$class) {
            flash_set('error', __('Class not found.'));
            redirect('classes');
        }
        // Teachers cannot edit when class is closed
        if ($class['status'] === 'closed' && !Auth::canManageCourses()) {
            flash_set('error', __('Class is closed. Grades cannot be edited.'));
            redirect('assessments/grades?assessment_id=' . $assessmentId);
        }
        if (!$this->isTeacherOfClass($class['id']) && !Auth::canManageCourses()) {
            flash_set('error', __('Access denied.'));
            redirect('assessments/grades?assessment_id=' . $assessmentId);
        }
        $scale = self::getGradingScale();
        $pdo = db();
        foreach ($_POST['score'] ?? [] as $studentId => $rawScore) {
            $studentId = (int) $studentId;
            if ($studentId <= 0) {
                continue;
            }
            $trim = trim((string) $rawScore);
            if ($trim === '') {
                // empty: treat as delete
                AssessmentGrade::delete($pdo, $assessmentId, $studentId);
                continue;
            }
            $score = (float) str_replace(',', '.', $trim);
            if ($score < 0) {
                $score = 0;
            }
            if ($scale === '0_10' && $score > 10) {
                $score = 10;
            }
            if ($scale === '0_100' && $score > 100) {
                $score = 100;
            }
            AssessmentGrade::save($pdo, $assessmentId, $studentId, $score, Auth::id());
        }
        flash_set('success', __('Grades saved.'));
        redirect('assessments/grades?assessment_id=' . $assessmentId);
    }

    private static function getGradingScale(): string
    {
        try {
            $stmt = db()->prepare("SELECT value FROM settings WHERE `key` = 'grading_scale' LIMIT 1");
            $stmt->execute();
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $scale = $row['value'] ?? '0_10';
            return in_array($scale, ['0_10', '0_100'], true) ? $scale : '0_10';
        } catch (\Throwable $e) {
            return '0_10';
        }
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

    private function canEditClass(array $class): bool
    {
        if (Auth::canManageCourses()) {
            return true;
        }
        if ($class['status'] === 'closed') {
            return false;
        }
        return $this->isTeacherOfClass((int) $class['id']);
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

