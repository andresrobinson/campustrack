<?php
/** @var array|null $class */
/** @var array $courses */
/** @var array $teachers */
/** @var array $assignedTeacherIds */
$isEdit = $class !== null;
$assignedTeacherIds = $assignedTeacherIds ?? [];
?>
<div class="mb-4">
    <h1 class="mb-0"><?= $isEdit ? __('Edit class') : __('Add class') ?></h1>
</div>
<div class="card">
    <div class="card-body">
        <form method="post" action="<?= $isEdit ? url('classes/update') : url('classes/create') ?>">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= (int) $class['id'] ?>">
            <?php endif; ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="course_id" class="form-label"><?= __('Course') ?></label>
                    <select class="form-select" id="course_id" name="course_id" required>
                        <option value=""><?= __('Select course') ?></option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= (int) $c['id'] ?>" <?= ($class['course_id'] ?? (int)($_POST['course_id'] ?? 0)) === (int)$c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['code'] . ' – ' . $c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="code" class="form-label"><?= __('Code') ?></label>
                    <input type="text" class="form-control" id="code" name="code" required maxlength="50" value="<?= htmlspecialchars($class['code'] ?? $_POST['code'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label for="name" class="form-label"><?= __('Name') ?></label>
                    <input type="text" class="form-control" id="name" name="name" required maxlength="255" value="<?= htmlspecialchars($class['name'] ?? $_POST['name'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label for="description" class="form-label"><?= __('Description') ?></label>
                    <textarea class="form-control" id="description" name="description" rows="2"><?= htmlspecialchars($class['description'] ?? $_POST['description'] ?? '') ?></textarea>
                </div>
                <div class="col-md-4">
                    <label for="start_date" class="form-label"><?= __('Start date') ?></label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?= htmlspecialchars($class['start_date'] ?? $_POST['start_date'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label"><?= __('End date') ?></label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?= htmlspecialchars($class['end_date'] ?? $_POST['end_date'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label for="default_lecture_duration_minutes" class="form-label"><?= __('Lecture duration (min)') ?></label>
                    <input type="number" class="form-control" id="default_lecture_duration_minutes" name="default_lecture_duration_minutes" min="1" max="480" value="<?= htmlspecialchars($class['default_lecture_duration_minutes'] ?? $_POST['default_lecture_duration_minutes'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label for="auto_lectures_count" class="form-label"><?= __('Auto-generate number of lectures') ?></label>
                    <input type="number" class="form-control" id="auto_lectures_count" name="auto_lectures_count" min="0" max="365" placeholder="0 = manual" value="<?= htmlspecialchars($class['auto_lectures_count'] ?? $_POST['auto_lectures_count'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label for="status" class="form-label"><?= __('Class status') ?></label>
                    <?php $status = $class['status'] ?? $_POST['status'] ?? 'open'; ?>
                    <select class="form-select" id="status" name="status">
                        <option value="open" <?= $status === 'open' ? 'selected' : '' ?>><?= __('Open (teachers can edit grades)') ?></option>
                        <option value="closed" <?= $status === 'closed' ? 'selected' : '' ?>><?= __('Closed (grades locked for teachers)') ?></option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label"><?= __('Teachers') ?></label>
                    <select class="form-select" name="teacher_ids[]" multiple size="5">
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= (int) $t['id'] ?>" <?= in_array((int)$t['id'], $assignedTeacherIds) ? 'selected' : '' ?>><?= htmlspecialchars($t['name'] . ' (' . $t['email'] . ')') ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted"><?= __('Hold Ctrl/Cmd to select multiple.') ?></small>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary"><?= $isEdit ? __('Save') : __('Create') ?></button>
                    <a href="<?= url('classes') ?>" class="btn btn-secondary"><?= __('Cancel') ?></a>
                </div>
            </div>
        </form>
    </div>
</div>
