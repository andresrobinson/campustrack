<?php
/** @var array $settings */
$current = $settings['default_language'] ?? lang();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0"><?= __('settings') ?></h1>
</div>
<div class="card">
    <div class="card-body">
        <form method="post" action="<?= url('settings/language') ?>">
            <div class="mb-3">
                <label for="default_language" class="form-label"><?= __('Default language') ?></label>
                <select id="default_language" name="default_language" class="form-select">
                    <option value="en" <?= $current === 'en' ? 'selected' : '' ?>>English</option>
                    <option value="pt_BR" <?= $current === 'pt_BR' ? 'selected' : '' ?>>Português (Brasil)</option>
                </select>
                <small class="text-muted"><?= __('This affects the interface language for all users.') ?></small>
            </div>
            <button type="submit" class="btn btn-primary"><?= __('Save') ?></button>
        </form>
    </div>
</div>

