<?php
/** @var array $class */
/** @var array|null $course */
/** @var array $assessments */
/** @var bool $canEdit */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0"><?= __('Grading') ?> - <?= htmlspecialchars($class['name']) ?></h1>
    <?php if ($canEdit): ?>
        <a href="<?= url('assessments/create', ['class_id' => $class['id']]) ?>" class="btn btn-primary"><?= __('Add assessment') ?></a>
    <?php endif; ?>
</div>
<p class="text-muted">
    <?= __('Course') ?>:
    <strong><?= htmlspecialchars($course['name'] ?? $class['course_name']) ?></strong>
    (<?= htmlspecialchars($course['code'] ?? $class['course_code']) ?>)
</p>
<div class="card">
    <div class="card-body">
        <?php if (empty($assessments)): ?>
            <p class="text-muted mb-0"><?= __('No assessments yet.') ?></p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Description') ?></th>
                            <th><?= __('Max score') ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assessments as $a): ?>
                            <tr>
                                <td><?= (int) $a['position'] ?></td>
                                <td><?= htmlspecialchars($a['name']) ?></td>
                                <td><?= htmlspecialchars($a['description'] ?? '') ?></td>
                                <td><?= $a['max_score'] !== null ? (float) $a['max_score'] : '' ?></td>
                                <td class="text-end">
                                    <a href="<?= url('assessments/grades', ['assessment_id' => $a['id']]) ?>" class="btn btn-sm btn-outline-success"><?= __('Grades') ?></a>
                                    <?php if ($canEdit): ?>
                                        <a href="<?= url('assessments/edit?id=' . $a['id']) ?>" class="btn btn-sm btn-outline-primary"><?= __('Edit') ?></a>
                                        <form action="<?= url('assessments/delete') ?>" method="post" class="d-inline" onsubmit="return confirm('<?= __('Delete this assessment?') ?>');">
                                            <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><?= __('Delete') ?></button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<a href="<?= url('classes', ['course_id' => $class['course_id']]) ?>" class="btn btn-link"><?= __('Back to classes') ?></a>

