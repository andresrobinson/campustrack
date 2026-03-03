<?php
/** @var array|null $assessment */
/** @var array $class */
$isEdit = $assessment !== null;
?>
<div class="mb-4">
    <h1 class="mb-0"><?= $isEdit ? __('Edit assessment') : __('Add assessment') ?></h1>
</div>
<p class="text-muted"><?= __('classes') ?>: <strong><?= htmlspecialchars($class['name']) ?></strong> (<?= htmlspecialchars($class['code']) ?>)</p>
<div class="card">
    <div class="card-body">
        <form method="post" action="<?= $isEdit ? url('assessments/update') : url('assessments/create') ?>">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= (int) $assessment['id'] ?>">
            <?php else: ?>
                <input type="hidden" name="class_id" value="<?= (int) $class['id'] ?>">
            <?php endif; ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label"><?= __('Name') ?></label>
                    <input type="text" class="form-control" id="name" name="name" required value="<?= htmlspecialchars($assessment['name'] ?? $_POST['name'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label for="position" class="form-label">#</label>
                    <input type="number" class="form-control" id="position" name="position" min="1" max="50" value="<?= htmlspecialchars($assessment['position'] ?? $_POST['position'] ?? 1) ?>">
                </div>
                <div class="col-md-3">
                    <label for="max_score" class="form-label"><?= __('Max score') ?></label>
                    <input type="number" step="0.01" class="form-control" id="max_score" name="max_score" min="0" value="<?= htmlspecialchars($assessment['max_score'] ?? $_POST['max_score'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label for="description" class="form-label"><?= __('Description') ?></label>
                    <textarea class="form-control" id="description" name="description" rows="2"><?= htmlspecialchars($assessment['description'] ?? $_POST['description'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary"><?= $isEdit ? __('Save') : __('Create') ?></button>
                    <a href="<?= url('assessments', ['class_id' => $class['id']]) ?>" class="btn btn-secondary"><?= __('Cancel') ?></a>
                </div>
            </div>
        </form>
    </div>
</div>

