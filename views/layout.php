<!DOCTYPE html>
<html lang="<?= htmlspecialchars(lang()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(__('app_name')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light min-vh-100 d-flex flex-column">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?= url('dashboard') ?>"><?= htmlspecialchars(__('app_name')) ?></a>
            <?php if (\Core\Auth::check()): ?>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="<?= url('dashboard') ?>"><?= __('dashboard') ?></a></li>
                    <?php if (\Core\Auth::isStudent()): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= url('reports/student') ?>"><?= __('My grades') ?></a></li>
                    <?php endif; ?>
                    <?php if (\Core\Auth::canManageCourses()): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= url('courses') ?>"><?= __('courses') ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= url('classes') ?>"><?= __('classes') ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= url('reports/matrix') ?>"><?= __('reports') ?></a></li>
                    <?php endif; ?>
                    <?php if (\Core\Auth::canManageSystem()): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= url('users') ?>"><?= __('Users') ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= url('settings') ?>"><?= __('settings') ?></a></li>
                    <?php endif; ?>
                </ul>
                <div class="navbar-nav ms-auto align-items-center gap-2">
                    <span class="nav-link text-white py-0"><?= htmlspecialchars(\Core\Auth::user()['name']) ?> (<?= htmlspecialchars(\Core\Auth::user()['role']) ?>)</span>
                    <form action="<?= url('logout') ?>" method="post" class="d-inline">
                        <button type="submit" class="btn btn-outline-light btn-sm"><?= __('logout') ?></button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </nav>
    <main class="container py-4 flex-grow-1">
        <?php if ($success = flash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($error = flash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?= $content ?? '' ?>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
