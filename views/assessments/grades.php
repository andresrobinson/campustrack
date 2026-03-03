<?php
/** @var array $assessment */
/** @var array $class */
/** @var array|null $course */
/** @var array $students */
/** @var array $grades */
/** @var bool $canEdit */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0"><?= __('Grades') ?> - <?= htmlspecialchars($assessment['name']) ?></h1>
    <button onclick="window.print()" class="btn btn-outline-secondary d-print-none"><?= __('Print') ?></button>
</div>
<p class="text-muted">
    <?= __('Course') ?>:
    <strong><?= htmlspecialchars($course['name'] ?? $class['course_name']) ?></strong>
    (<?= htmlspecialchars($course['code'] ?? $class['course_code']) ?>)
    <br>
    <?= __('classes') ?>:
    <strong><?= htmlspecialchars($class['name']) ?></strong>
    (<?= htmlspecialchars($class['code']) ?>)
</p>
<?php if (empty($students)): ?>
    <div class="alert alert-info"><?= __('No enrollments yet.') ?></div>
<?php else: ?>
    <form method="post" action="<?= url('assessments/grades/save') ?>" class="d-print-none">
        <input type="hidden" name="assessment_id" value="<?= (int) $assessment['id'] ?>">
        <div class="card mb-3">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th><?= __('Name') ?></th>
                                <th><?= __('email') ?></th>
                                <th><?= __('Score') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $s): ?>
                                <?php $g = $grades[$s['id']] ?? null; ?>
                                <tr>
                                    <td><?= htmlspecialchars($s['name']) ?></td>
                                    <td><?= htmlspecialchars($s['email']) ?></td>
                                    <td style="max-width:120px;">
                                        <?php if ($canEdit): ?>
                                            <input type="number" step="0.01" class="form-control form-control-sm" name="score[<?= (int) $s['id'] ?>]" value="<?= htmlspecialchars($g['score'] ?? '') ?>">
                                        <?php else: ?>
                                            <?= $g ? htmlspecialchars($g['score']) : '' ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($canEdit): ?>
                    <button type="submit" class="btn btn-primary"><?= __('Save') ?></button>
                <?php endif; ?>
                <a href="<?= url('assessments', ['class_id' => $class['id']]) ?>" class="btn btn-secondary"><?= __('Back to classes') ?></a>
            </div>
        </div>
    </form>
<?php endif; ?>

