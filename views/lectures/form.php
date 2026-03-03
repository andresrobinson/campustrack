<?php
/** @var array|null $lecture */
/** @var array $class */
$isEdit = $lecture !== null;
$duration = $isEdit ? ($lecture['duration_minutes'] ?? 60) : ($class['default_lecture_duration_minutes'] ?? $class['course_default_duration'] ?? 60);
?>
<div class="mb-4">
    <h1 class="mb-0"><?= $isEdit ? __('Edit lecture') : __('Add lecture') ?></h1>
</div>
<p class="text-muted"><?= htmlspecialchars($class['course_name'] . ' – ' . $class['code']) ?></p>
<div class="card">
    <div class="card-body">
        <form method="post" action="<?= $isEdit ? url('lectures/update') : url('lectures/create') ?>">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= (int) $lecture['id'] ?>">
            <?php else: ?>
                <input type="hidden" name="class_id" value="<?= (int) $class['id'] ?>">
            <?php endif; ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="lecture_date" class="form-label"><?= __('Date') ?></label>
                    <input type="date" class="form-control" id="lecture_date" name="lecture_date" required value="<?= htmlspecialchars($isEdit ? ($lecture['lecture_date'] ?? '') : '') ?: ($_POST['lecture_date'] ?? date('Y-m-d')) ?>">
                </div>
                <div class="col-md-4">
                    <label for="start_time" class="form-label"><?= __('Time') ?></label>
                    <input type="time" class="form-control" id="start_time" name="start_time" value="<?= htmlspecialchars(($isEdit && !empty($lecture['start_time'])) ? substr($lecture['start_time'], 0, 5) : ($_POST['start_time'] ?? '09:00')) ?>">
                </div>
                <div class="col-md-4">
                    <label for="duration_minutes" class="form-label"><?= __('Duration (minutes)') ?></label>
                    <input type="number" class="form-control" id="duration_minutes" name="duration_minutes" min="1" max="480" value="<?= (int)($isEdit ? ($lecture['duration_minutes'] ?? $duration) : ($_POST['duration_minutes'] ?? $duration)) ?>">
                </div>
                <div class="col-md-6">
                    <label for="location" class="form-label"><?= __('Location / Room') ?></label>
                    <input type="text" class="form-control" id="location" name="location" maxlength="255" value="<?= htmlspecialchars($isEdit ? ($lecture['location'] ?? '') : '') ?: ($_POST['location'] ?? '') ?>">
                </div>
                <?php if (!$isEdit): ?>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_extra" id="is_extra" value="1" <?= !empty($_POST['is_extra']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_extra"><?= __('Extra / make-up lecture') ?></label>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary"><?= $isEdit ? __('Save') : __('Create') ?></button>
                    <a href="<?= url('lectures', ['class_id' => $class['id']]) ?>" class="btn btn-secondary"><?= __('Cancel') ?></a>
                </div>
            </div>
        </form>
    </div>
</div>
