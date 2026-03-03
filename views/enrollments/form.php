<?php
/** @var array $class */
/** @var array $students */
?>
<div class="mb-4">
    <h1 class="mb-0"><?= __('Enroll student') ?></h1>
</div>
<p class="text-muted"><?= htmlspecialchars($class['course_name'] . ' – ' . $class['code']) ?></p>
<div class="card">
    <div class="card-body">
        <form method="post" action="<?= url('enrollments/create') ?>">
            <input type="hidden" name="class_id" value="<?= (int) $class['id'] ?>">
            <div class="mb-3">
                <label for="student_id" class="form-label"><?= __('Student') ?></label>
                <select class="form-select" id="student_id" name="student_id" required>
                    <option value=""><?= __('Select student') ?></option>
                    <?php foreach ($students as $s): ?>
                        <option value="<?= (int) $s['id'] ?>"><?= htmlspecialchars($s['name'] . ' (' . $s['email'] . ')') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="approve" id="approve" value="1" checked>
                    <label class="form-check-label" for="approve"><?= __('Approve immediately') ?></label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><?= __('Enroll') ?></button>
            <a href="<?= url('enrollments', ['class_id' => $class['id']]) ?>" class="btn btn-secondary"><?= __('Cancel') ?></a>
        </form>
    </div>
</div>
