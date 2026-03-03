<!DOCTYPE html>
<html lang="<?= htmlspecialchars(lang()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('Register') ?> - <?= htmlspecialchars(__('app_name')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light min-vh-100 d-flex align-items-center justify-content-center">
    <div class="card shadow-sm" style="width: 100%; max-width: 450px;">
        <div class="card-body p-4">
            <h2 class="card-title mb-3"><?= __('Register') ?></h2>
            <p class="text-muted mb-4 small"><?= __('Register as a student. A manager must approve your account before you can log in.') ?></p>
            <?php if (isset($error) && $error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if (isset($success) && $success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <form method="post" action="<?= url('register') ?>">
                <div class="mb-3">
                    <label for="name" class="form-label"><?= __('Name') ?></label>
                    <input type="text" class="form-control" id="name" name="name" required
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label"><?= __('email') ?></label>
                    <input type="email" class="form-control" id="email" name="email" required
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label"><?= __('password') ?></label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label"><?= __('Confirm password') ?></label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 mb-3"><?= __('Create account') ?></button>
                <div class="text-center">
                    <a href="<?= url('login') ?>" class="small"><?= __('Already registered? Login') ?></a>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

