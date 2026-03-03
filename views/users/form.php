<?php
/** @var array|null $user */
$isEdit = $user !== null;
?>
<div class="mb-4">
    <h1 class="mb-0"><?= __('Add user') ?></h1>
</div>
<div class="card">
    <div class="card-body">
        <form method="post" action="<?= url('users/create') ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label"><?= __('Name') ?></label>
                    <input type="text" class="form-control" id="name" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label"><?= __('email') ?></label>
                    <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label for="password" class="form-label"><?= __('password') ?></label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="<?= __('Leave blank to generate') ?>">
                </div>
                <div class="col-md-6">
                    <label for="role" class="form-label"><?= __('role_label') ?></label>
                    <select class="form-select" id="role" name="role">
                        <option value="teacher" <?= ($_POST['role'] ?? '') === 'teacher' ? 'selected' : '' ?>><?= __('Teacher') ?></option>
                        <option value="manager" <?= ($_POST['role'] ?? '') === 'manager' ? 'selected' : '' ?>><?= __('Manager') ?></option>
                        <option value="student" <?= ($_POST['role'] ?? '') === 'student' ? 'selected' : '' ?>><?= __('Student') ?></option>
                        <option value="admin" <?= ($_POST['role'] ?? '') === 'admin' ? 'selected' : '' ?>><?= __('Administrator') ?></option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary"><?= __('Create') ?></button>
                    <a href="<?= url('users') ?>" class="btn btn-secondary"><?= __('Cancel') ?></a>
                </div>
            </div>
        </form>
    </div>
</div>
