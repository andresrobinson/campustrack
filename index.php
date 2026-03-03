<?php

require __DIR__ . '/core/helpers.php';

session_start();

date_default_timezone_set(config('timezone', 'UTC'));

require __DIR__ . '/core/Database.php';
require __DIR__ . '/core/Auth.php';
require __DIR__ . '/core/Router.php';

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base = __DIR__ . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative = substr($class, $len);
    $file = $base . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

$router = new \Core\Router();

$router->get('/', function () {
    if (!\Core\Auth::check()) {
        redirect('login');
    }
    redirect('dashboard');
});

$router->get('/login', [\App\Controllers\AuthController::class, 'showLogin']);
$router->post('/login', [\App\Controllers\AuthController::class, 'login']);
$router->post('/logout', [\App\Controllers\AuthController::class, 'logout']);

// Password reset
$router->get('/password/forgot', [\App\Controllers\PasswordController::class, 'showForgot']);
$router->post('/password/forgot', [\App\Controllers\PasswordController::class, 'sendReset']);
$router->get('/password/reset', [\App\Controllers\PasswordController::class, 'showReset']);
$router->post('/password/reset', [\App\Controllers\PasswordController::class, 'reset']);

// Student self-registration
$router->get('/register', [\App\Controllers\AuthController::class, 'showRegister']);
$router->post('/register', [\App\Controllers\AuthController::class, 'register']);

$router->get('/dashboard', [\App\Controllers\DashboardController::class, 'index']);

// Student area
$router->get('/student/classes', [\App\Controllers\StudentController::class, 'classes']);
$router->post('/student/enroll', [\App\Controllers\StudentController::class, 'requestEnrollment']);
$router->post('/student/enroll-by-code', [\App\Controllers\StudentController::class, 'enrollWithCode']);

// Courses (admin/manager)
$router->get('/courses', [\App\Controllers\CourseController::class, 'index']);
$router->get('/courses/create', [\App\Controllers\CourseController::class, 'create']);
$router->post('/courses/create', [\App\Controllers\CourseController::class, 'store']);
$router->get('/courses/edit', [\App\Controllers\CourseController::class, 'edit']);
$router->post('/courses/update', [\App\Controllers\CourseController::class, 'update']);
$router->post('/courses/delete', [\App\Controllers\CourseController::class, 'delete']);

// Classes (admin/manager)
$router->get('/classes', [\App\Controllers\ClassController::class, 'index']);
$router->get('/classes/create', [\App\Controllers\ClassController::class, 'create']);
$router->post('/classes/create', [\App\Controllers\ClassController::class, 'store']);
$router->get('/classes/edit', [\App\Controllers\ClassController::class, 'edit']);
$router->post('/classes/update', [\App\Controllers\ClassController::class, 'update']);
$router->post('/classes/delete', [\App\Controllers\ClassController::class, 'delete']);

// Lectures (admin/manager + assigned teachers)
$router->get('/lectures', [\App\Controllers\LectureController::class, 'index']);
$router->get('/lectures/create', [\App\Controllers\LectureController::class, 'create']);
$router->post('/lectures/create', [\App\Controllers\LectureController::class, 'store']);
$router->get('/lectures/edit', [\App\Controllers\LectureController::class, 'edit']);
$router->post('/lectures/update', [\App\Controllers\LectureController::class, 'update']);
$router->post('/lectures/delete', [\App\Controllers\LectureController::class, 'delete']);
$router->post('/lectures/generate', [\App\Controllers\LectureController::class, 'generate']);

// Attendance
$router->get('/attendance', [\App\Controllers\AttendanceController::class, 'index']);
$router->post('/attendance/save', [\App\Controllers\AttendanceController::class, 'save']);

// Enrollments
$router->get('/enrollments', [\App\Controllers\EnrollmentController::class, 'index']);
$router->get('/enrollments/create', [\App\Controllers\EnrollmentController::class, 'create']);
$router->post('/enrollments/create', [\App\Controllers\EnrollmentController::class, 'store']);
$router->post('/enrollments/approve', [\App\Controllers\EnrollmentController::class, 'approve']);
$router->post('/enrollments/reject', [\App\Controllers\EnrollmentController::class, 'reject']);

// Reports
$router->get('/reports/class', [\App\Controllers\ReportController::class, 'class']);
$router->get('/reports/student', [\App\Controllers\ReportController::class, 'student']);
$router->get('/reports/matrix', [\App\Controllers\ReportController::class, 'matrix']);

// Settings
$router->get('/settings', [\App\Controllers\SettingsController::class, 'index']);
$router->post('/settings/language', [\App\Controllers\SettingsController::class, 'updateLanguage']);

// Users (admin only)
$router->get('/users', [\App\Controllers\UserController::class, 'index']);
$router->get('/users/create', [\App\Controllers\UserController::class, 'create']);
$router->post('/users/create', [\App\Controllers\UserController::class, 'store']);
// Student registration approval (admin/manager)
$router->post('/users/approve', [\App\Controllers\UserController::class, 'approve']);
$router->post('/users/reject', [\App\Controllers\UserController::class, 'reject']);

// Invite codes
$router->get('/invites', [\App\Controllers\InviteCodeController::class, 'index']);
$router->get('/invites/create', [\App\Controllers\InviteCodeController::class, 'create']);
$router->post('/invites/create', [\App\Controllers\InviteCodeController::class, 'store']);
$router->get('/invites/class', [\App\Controllers\InviteCodeController::class, 'createForClass']);
$router->post('/invites/class', [\App\Controllers\InviteCodeController::class, 'storeForClass']);

// Assessments & grades
$router->get('/assessments', [\App\Controllers\AssessmentController::class, 'index']);
$router->get('/assessments/create', [\App\Controllers\AssessmentController::class, 'create']);
$router->post('/assessments/create', [\App\Controllers\AssessmentController::class, 'store']);
$router->get('/assessments/edit', [\App\Controllers\AssessmentController::class, 'edit']);
$router->post('/assessments/update', [\App\Controllers\AssessmentController::class, 'update']);
$router->post('/assessments/delete', [\App\Controllers\AssessmentController::class, 'delete']);
$router->get('/assessments/grades', [\App\Controllers\AssessmentController::class, 'grades']);
$router->post('/assessments/grades/save', [\App\Controllers\AssessmentController::class, 'saveGrades']);

// Files library
$router->get('/files', [\App\Controllers\FileController::class, 'index']);
$router->post('/files/upload', [\App\Controllers\FileController::class, 'upload']);
$router->get('/files/edit', [\App\Controllers\FileController::class, 'edit']);
$router->post('/files/update', [\App\Controllers\FileController::class, 'update']);
$router->post('/files/delete', [\App\Controllers\FileController::class, 'delete']);
$router->get('/files/download', [\App\Controllers\FileController::class, 'download']);
$router->get('/files/logs', [\App\Controllers\FileController::class, 'logs']);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = isset($_GET['route']) ? '/' . trim($_GET['route'], '/') : '/';
$router->dispatch($method, $uri);
