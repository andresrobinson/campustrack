<?php

namespace App\Controllers;

use Core\Auth;
use App\Models\File;
use App\Models\FileDownload;
use App\Models\SchoolClass;
use App\Models\Course;
use App\Models\Enrollment;

class FileController
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

        $user = Auth::user();
        $pdo = db();
        $isManager = Auth::canManageCourses() || Auth::canManageSystem();
        $isTeacher = $this->isTeacherOfClass($classId);
        $isStudent = Auth::isStudent();
        $isEnrolled = false;

        if ($isStudent) {
            $stmt = $pdo->prepare(
                'SELECT 1 FROM enrollments WHERE class_id = ? AND student_id = ? AND status = ? AND deleted_at IS NULL'
            );
            $stmt->execute([$classId, $user['id'], 'approved']);
            $isEnrolled = (bool) $stmt->fetchColumn();
        }

        if (!($isManager || $isTeacher || ($isStudent && $isEnrolled))) {
            flash_set('error', __('Access denied.'));
            redirect('classes');
        }

        $includeHidden = $isManager || $isTeacher;
        $courseFiles = File::forCourse($pdo, $class['course_id'], $includeHidden);
        $classFiles = File::forClass($pdo, $classId, $includeHidden);

        $content = $this->render('files/index', [
            'class' => $class,
            'course' => $course,
            'courseFiles' => $courseFiles,
            'classFiles' => $classFiles,
            'canManage' => $isManager || $isTeacher,
        ]);
        $this->layout($content);
    }

    public function upload(): void
    {
        if (!Auth::check()) {
            redirect('login');
        }
        $classId = (int) ($_POST['class_id'] ?? 0);
        $scope = $_POST['scope'] ?? 'class';
        $class = $classId ? SchoolClass::find(db(), $classId) : null;
        if (!$class) {
            flash_set('error', __('Class not found.'));
            redirect('classes');
        }
        $pdo = db();
        $isManager = Auth::canManageCourses() || Auth::canManageSystem();
        $isTeacher = $this->isTeacherOfClass($classId);
        if (!($isManager || $isTeacher)) {
            flash_set('error', __('Access denied.'));
            redirect('files?class_id=' . $classId);
        }
        if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            flash_set('error', __('File upload failed.'));
            redirect('files?class_id=' . $classId);
        }

        $file = $_FILES['file'];
        $originalName = $file['name'];
        $size = (int) $file['size'];
        $tmpName = $file['tmp_name'];
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = ['pdf','doc','docx','xls','xlsx','ppt','pptx','odt','ods','png','jpg','jpeg','gif','zip'];
        if (!in_array($ext, $allowed, true)) {
            flash_set('error', __('File type not allowed.'));
            redirect('files?class_id=' . $classId);
        }

        $root = dirname(__DIR__, 2);
        $subdir = $scope === 'course' ? 'uploads/course_' . $class['course_id'] : 'uploads/class_' . $classId;
        $uploadDir = $root . DIRECTORY_SEPARATOR . $subdir;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $safeName = preg_replace('/[^A-Za-z0-9_\.-]/', '_', $originalName);
        $storageName = uniqid('', true) . '_' . $safeName;
        $destPath = $uploadDir . DIRECTORY_SEPARATOR . $storageName;

        if (!move_uploaded_file($tmpName, $destPath)) {
            flash_set('error', __('File upload failed.'));
            redirect('files?class_id=' . $classId);
        }

        $data = [
            'course_id' => $scope === 'course' ? (int) $class['course_id'] : null,
            'class_id' => $scope === 'class' ? $classId : null,
            'title' => trim($_POST['title'] ?? $originalName),
            'description' => trim($_POST['description'] ?? ''),
            'storage_path' => $subdir . '/' . $storageName,
            'original_name' => $originalName,
            'mime_type' => $file['type'] ?? null,
            'size_bytes' => $size,
            'available_from' => $_POST['available_from'] ?: null,
            'available_until' => $_POST['available_until'] ?: null,
        ];
        File::create($pdo, $data, Auth::id());
        flash_set('success', __('File uploaded.'));
        redirect('files?class_id=' . $classId);
    }

    public function edit(): void
    {
        if (!Auth::check()) {
            redirect('login');
        }
        $id = (int) ($_GET['id'] ?? 0);
        $file = $id ? File::find(db(), $id) : null;
        if (!$file) {
            flash_set('error', __('File not found.'));
            redirect('classes');
        }
        $classId = (int) ($file['class_id'] ?? 0);
        if ($classId <= 0) {
            // Course-level file: pick any class from that course for redirect
            $class = $this->findAnyClassForCourse((int) $file['course_id']);
        } else {
            $class = SchoolClass::find(db(), $classId);
        }
        if (!$class) {
            flash_set('error', __('Class not found.'));
            redirect('classes');
        }
        $isManager = Auth::canManageCourses() || Auth::canManageSystem();
        $isTeacher = $this->isTeacherOfClass((int) $class['id']);
        if (!($isManager || $isTeacher)) {
            flash_set('error', __('Access denied.'));
            redirect('files?class_id=' . $class['id']);
        }
        $course = Course::find(db(), $class['course_id']);
        $content = $this->render('files/form', [
            'file' => $file,
            'class' => $class,
            'course' => $course,
        ]);
        $this->layout($content);
    }

    public function update(): void
    {
        if (!Auth::check()) {
            redirect('login');
        }
        $id = (int) ($_POST['id'] ?? 0);
        $file = $id ? File::find(db(), $id) : null;
        if (!$file) {
            flash_set('error', __('File not found.'));
            redirect('classes');
        }
        $classId = (int) ($file['class_id'] ?? 0);
        $class = $classId > 0 ? SchoolClass::find(db(), $classId) : $this->findAnyClassForCourse((int) $file['course_id']);
        if (!$class) {
            flash_set('error', __('Class not found.'));
            redirect('classes');
        }
        $isManager = Auth::canManageCourses() || Auth::canManageSystem();
        $isTeacher = $this->isTeacherOfClass((int) $class['id']);
        if (!($isManager || $isTeacher)) {
            flash_set('error', __('Access denied.'));
            redirect('files?class_id=' . $class['id']);
        }
        $title = trim($_POST['title'] ?? '');
        if ($title === '') {
            flash_set('error', __('Name is required.'));
            redirect('files/edit?id=' . $id);
        }
        $data = [
            'title' => $title,
            'description' => trim($_POST['description'] ?? ''),
            'available_from' => $_POST['available_from'] ?: null,
            'available_until' => $_POST['available_until'] ?: null,
        ];
        File::update(db(), $id, $data, Auth::id());
        flash_set('success', __('File updated.'));
        redirect('files?class_id=' . $class['id']);
    }

    public function delete(): void
    {
        if (!Auth::check()) {
            redirect('login');
        }
        $id = (int) ($_POST['id'] ?? 0);
        $file = $id ? File::find(db(), $id) : null;
        if (!$file) {
            redirect('classes');
        }
        $classId = (int) ($file['class_id'] ?? 0);
        $class = $classId > 0 ? SchoolClass::find(db(), $classId) : $this->findAnyClassForCourse((int) $file['course_id']);
        if (!$class) {
            redirect('classes');
        }
        $isManager = Auth::canManageCourses() || Auth::canManageSystem();
        $isTeacher = $this->isTeacherOfClass((int) $class['id']);
        if (!($isManager || $isTeacher)) {
            flash_set('error', __('Access denied.'));
            redirect('files?class_id=' . $class['id']);
        }
        File::softDelete(db(), $id);
        flash_set('success', __('File deleted.'));
        redirect('files?class_id=' . $class['id']);
    }

    public function logs(): void
    {
        if (!Auth::check() || !(Auth::canManageCourses() || Auth::canManageSystem())) {
            flash_set('error', __('Access denied.'));
            redirect('dashboard');
        }
        $id = (int) ($_GET['id'] ?? 0);
        $file = $id ? File::find(db(), $id) : null;
        if (!$file) {
            flash_set('error', __('File not found.'));
            redirect('classes');
        }
        $classId = (int) ($file['class_id'] ?? 0);
        $class = $classId > 0 ? SchoolClass::find(db(), $classId) : $this->findAnyClassForCourse((int) $file['course_id']);
        if (!$class) {
            flash_set('error', __('Class not found.'));
            redirect('classes');
        }
        $logs = FileDownload::getByFile(db(), $id);
        $content = $this->render('files/logs', [
            'file' => $file,
            'class' => $class,
            'logs' => $logs,
        ]);
        $this->layout($content);
    }
    public function download(): void
    {
        if (!Auth::check()) {
            redirect('login');
        }
        $id = (int) ($_GET['id'] ?? 0);
        $file = $id ? File::find(db(), $id) : null;
        if (!$file) {
            http_response_code(404);
            echo 'File not found';
            return;
        }
        $pdo = db();
        $user = Auth::user();
        $isManager = Auth::canManageCourses() || Auth::canManageSystem();
        $classId = (int) ($file['class_id'] ?? 0);
        $courseId = (int) ($file['course_id'] ?? 0);
        $authorized = false;

        if ($isManager) {
            $authorized = true;
        } elseif ($classId > 0) {
            $class = SchoolClass::find($pdo, $classId);
            if ($class && $this->isTeacherOfClass($classId)) {
                $authorized = true;
            } elseif (Auth::isStudent()) {
                $stmt = $pdo->prepare(
                    'SELECT 1 FROM enrollments WHERE class_id = ? AND student_id = ? AND status = ? AND deleted_at IS NULL'
                );
                $stmt->execute([$classId, $user['id'], 'approved']);
                $authorized = (bool) $stmt->fetchColumn();
            }
        } elseif ($courseId > 0) {
            // Course-level file: teacher of any class in course or student enrolled in any class
            if ($this->isTeacherOfAnyClassInCourse($courseId)) {
                $authorized = true;
            } elseif (Auth::isStudent()) {
                $stmt = $pdo->prepare(
                    'SELECT 1 FROM enrollments e JOIN classes cl ON e.class_id = cl.id
                     WHERE e.student_id = ? AND e.status = ? AND e.deleted_at IS NULL AND cl.course_id = ? AND cl.deleted_at IS NULL'
                );
                $stmt->execute([$user['id'], 'approved', $courseId]);
                $authorized = (bool) $stmt->fetchColumn();
            }
        }

        // Availability for students
        if ($authorized && Auth::isStudent()) {
            $now = new \DateTimeImmutable();
            if (!empty($file['available_from']) && $now < new \DateTimeImmutable($file['available_from'])) {
                $authorized = false;
            }
            if (!empty($file['available_until']) && $now > new \DateTimeImmutable($file['available_until'])) {
                $authorized = false;
            }
        }

        if (!$authorized) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        // Stream file
        $root = dirname(__DIR__, 2);
        $path = $root . DIRECTORY_SEPARATOR . $file['storage_path'];
        if (!is_file($path) || !is_readable($path)) {
            http_response_code(404);
            echo 'File not found';
            return;
        }

        FileDownload::log($pdo, $id, $user['id']);

        $mime = $file['mime_type'] ?: 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . (string) filesize($path));
        header('Content-Disposition: attachment; filename="' . basename($file['original_name']) . '"');
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit;
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

    private function isTeacherOfAnyClassInCourse(int $courseId): bool
    {
        if (!Auth::isTeacher()) {
            return false;
        }
        $stmt = db()->prepare(
            'SELECT 1 FROM class_teachers ct
             JOIN classes cl ON ct.class_id = cl.id
             WHERE ct.teacher_id = ? AND cl.course_id = ? AND ct.deleted_at IS NULL AND cl.deleted_at IS NULL'
        );
        $stmt->execute([Auth::id(), $courseId]);
        return (bool) $stmt->fetchColumn();
    }

    private function findAnyClassForCourse(int $courseId): ?array
    {
        $stmt = db()->prepare('SELECT * FROM classes WHERE course_id = ? AND deleted_at IS NULL ORDER BY id LIMIT 1');
        $stmt->execute([$courseId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
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

