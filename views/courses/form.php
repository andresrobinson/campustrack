<?php
/** @var array|null $course */
$isEdit = $course !== null;
?>
<div class="mb-4">
    <h1 class="mb-0"><?= $isEdit ? __('Edit course') : __('Add course') ?></h1>
</div>
<div class="card">
    <div class="card-body">
        <form method="post" action="<?= $isEdit ? url('courses/update') : url('courses/create') ?>">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= (int) $course['id'] ?>">
            <?php endif; ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="code" class="form-label"><?= __('Code') ?></label>
                    <input type="text" class="form-control" id="code" name="code" required maxlength="50" value="<?= htmlspecialchars($course['code'] ?? $_POST['code'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label for="name" class="form-label"><?= __('Name') ?></label>
                    <input type="text" class="form-control" id="name" name="name" required maxlength="255" value="<?= htmlspecialchars($course['name'] ?? $_POST['name'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label for="description" class="form-label"><?= __('Description') ?></label>
                    <textarea class="form-control" id="description" name="description" rows="2"><?= htmlspecialchars($course['description'] ?? $_POST['description'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label"><?= __('Attendance mode') ?></label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="attendance_mode" id="mode_simple" value="simple" <?= ($course['attendance_mode'] ?? $_POST['attendance_mode'] ?? 'simple') === 'simple' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="mode_simple"><?= __('Simple (Present / Absent, full hours if present)') ?></label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="attendance_mode" id="mode_detailed" value="detailed" <?= ($course['attendance_mode'] ?? $_POST['attendance_mode'] ?? '') === 'detailed' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="mode_detailed"><?= __('Detailed (Absent 0, Late 0.5x, Present 1x, Excused 1x)') ?></label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="default_lecture_duration_minutes" class="form-label"><?= __('Default lecture duration (minutes)') ?></label>
                    <input type="number" class="form-control" id="default_lecture_duration_minutes" name="default_lecture_duration_minutes" min="1" max="480" value="<?= htmlspecialchars($course['default_lecture_duration_minutes'] ?? $_POST['default_lecture_duration_minutes'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label"><?= __('Grading') ?></label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="has_grading" id="has_grading" value="1" <?= (!empty($course['has_grading']) || (!isset($course['has_grading']) && empty($_POST))) || !empty($_POST['has_grading']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="has_grading"><?= __('Enable grading for this course') ?></label>
                    </div>
                    <div class="mt-2">
                        <label for="gpa_formula" class="form-label"><?= __('GPA formula') ?></label>
                        <select class="form-select" id="gpa_formula" name="gpa_formula">
                            <?php $gpaFormula = $course['gpa_formula'] ?? $_POST['gpa_formula'] ?? 'average'; ?>
                            <option value="average" <?= $gpaFormula === 'average' ? 'selected' : '' ?>><?= __('Average of all grades') ?></option>
                            <option value="weighted_second" <?= $gpaFormula === 'weighted_second' ? 'selected' : '' ?>><?= __('(First + 2 x Second) / 2') ?></option>
                        </select>
                        <small class="text-muted"><?= __('Uses first two assessments in creation order for weighted formula.') ?></small>
                    </div>
                </div>
                <div class="col-12 mt-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_public" id="is_public" value="1" <?= !empty($course['is_public']) || !empty($_POST['is_public']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_public"><?= __('Public (students can request enrollment)') ?></label>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary"><?= $isEdit ? __('Save') : __('Create') ?></button>
                    <a href="<?= url('courses') ?>" class="btn btn-secondary"><?= __('Cancel') ?></a>
                </div>
            </div>
        </form>
    </div>
</div>
