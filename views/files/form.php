<?php
/** @var array $file */
/** @var array $class */
/** @var array|null $course */
?>
<div class="mb-4">
    <h1 class="mb-0"><?= __('Edit file') ?></h1>
</div>
<p class="text-muted">
    <?= __('classes') ?>:
    <strong><?= htmlspecialchars($class['name']) ?></strong>
    (<?= htmlspecialchars($class['code']) ?>)
</p>
<div class="card">
    <div class="card-body">
        <form method="post" action="<?= url('files/update') ?>">
            <input type="hidden" name="id" value="<?= (int) $file['id'] ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="title" class="form-label"><?= __('Name') ?></label>
                    <input type="text" class="form-control" id="title" name="title" required value="<?= htmlspecialchars($file['title']) ?>">
                </div>
                <div class="col-md-6">
                    <label for="description" class="form-label"><?= __('Description') ?></label>
                    <input type="text" class="form-control" id="description" name="description" value="<?= htmlspecialchars($file['description'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label for="available_from" class="form-label"><?= __('Available from') ?></label>
                    <input type="datetime-local" class="form-control" id="available_from" name="available_from"
                           value="<?= $file['available_from'] ? date('Y-m-d\TH:i', strtotime($file['available_from'])) : '' ?>">
                </div>
                <div class="col-md-4">
                    <label for="available_until" class="form-label"><?= __('Available until') ?></label>
                    <input type="datetime-local" class="form-control" id="available_until" name="available_until"
                           value="<?= $file['available_until'] ? date('Y-m-d\TH:i', strtotime($file['available_until'])) : '' ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label"><?= __('Original file') ?></label>
                    <div class="form-control-plaintext">
                        <?= htmlspecialchars($file['original_name']) ?>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary"><?= __('Save') ?></button>
                    <a href="<?= url('files', ['class_id' => $class['id']]) ?>" class="btn btn-secondary"><?= __('Cancel') ?></a>
                </div>
            </div>
        </form>
    </div>
</div>

