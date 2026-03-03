<!DOCTYPE html>
<html lang="<?= htmlspecialchars(lang()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('password') ?> - <?= htmlspecialchars(__('app_name')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light min-vh-100 d-flex align-items-center justify-content-center">
    <div class="card shadow-sm" style="width: 100%; max-width: 420px;">
        <div class="card-body p-4">
            <h2 class="card-title mb-3"><?= __('Reset password') ?></h2>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                    <?php if (!empty($_SESSION['last_reset_token'])): ?>
                        <hr>
                        <small class="text-muted d-block mb-1"><?= __('Development reset link') ?>:</small>
                        <code><?= htmlspecialchars(url('password/reset', ['token' => $_SESSION['last_reset_token']])) ?></code>
                    <?php endif; ?>
                </div>
                <?php unset($_SESSION['last_reset_token']); ?>
            <?php endif; ?>
            <form method="post" action="<?= url('password/forgot') ?>">
                <div class="mb-3">
                    <label for="email" class="form-label"><?= __('email') ?></label>
                    <input type="email" class="form-control" id="email" name="email" required
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <button type="submit" class="btn btn-primary w-100"><?= __('Send reset link') ?></button>
            </form>
            <hr class="my-3">
            <div class="text-center">
                <a href="<?= url('login') ?>" class="small"><?= __('Back to login') ?></a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

