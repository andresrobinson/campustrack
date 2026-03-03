<?php
/** @var array $classes */
/** @var array|null $course */
/** @var int|null $courseId */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0"><?= __('classes') ?></h1>
    <a href="<?= url('classes/create') ?>" class="btn btn-primary"><?= __('Add class') ?></a>
</div>
<?php if ($course): ?>
    <p class="text-muted"><?= __('Course') ?>: <strong><?= htmlspecialchars($course['name']) ?></strong> (<?= htmlspecialchars($course['code']) ?>)</p>
<?php endif; ?>
<div class="card">
    <div class="card-body">
        <?php if (empty($classes)): ?>
            <p class="text-muted mb-0"><?= __('No classes yet.') ?></p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <?php if (!$courseId): ?>
                                <th><?= __('Course') ?></th>
                            <?php endif; ?>
                            <th><?= __('Code') ?></th>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Start') ?></th>
                            <th><?= __('End') ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classes as $cl): ?>
                            <tr>
                                <?php if (!$courseId): ?>
                                    <td><?= htmlspecialchars($cl['course_code'] . ' – ' . $cl['course_name']) ?></td>
                                <?php endif; ?>
                                <td><?= htmlspecialchars($cl['code']) ?></td>
                                <td><?= htmlspecialchars($cl['name']) ?></td>
                                <td><?= $cl['start_date'] ? htmlspecialchars($cl['start_date']) : '–' ?></td>
                                <td><?= $cl['end_date'] ? htmlspecialchars($cl['end_date']) : '–' ?></td>
                                <td class="text-end">
                                    <a href="<?= url('enrollments', ['class_id' => $cl['id']]) ?>" class="btn btn-sm btn-outline-info"><?= __('Enrollments') ?></a>
                                    <a href="<?= url('lectures', ['class_id' => $cl['id']]) ?>" class="btn btn-sm btn-outline-secondary"><?= __('lectures') ?></a>
                                    <a href="<?= url('files', ['class_id' => $cl['id']]) ?>" class="btn btn-sm btn-outline-dark"><?= __('Materials') ?></a>
                                    <a href="<?= url('assessments', ['class_id' => $cl['id']]) ?>" class="btn btn-sm btn-outline-warning"><?= __('Grading') ?></a>
                                    <a href="<?= url('reports/class', ['class_id' => $cl['id']]) ?>" class="btn btn-sm btn-outline-success"><?= __('Report') ?></a>
                                    <a href="<?= url('classes/edit?id=' . $cl['id']) ?>" class="btn btn-sm btn-outline-primary"><?= __('Edit') ?></a>
                                    <form action="<?= url('classes/delete') ?>" method="post" class="d-inline" onsubmit="return confirm('<?= __('Delete this class?') ?>');">
                                        <input type="hidden" name="id" value="<?= (int) $cl['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><?= __('Delete') ?></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php if ($courseId): ?>
    <a href="<?= url('courses') ?>" class="btn btn-link"><?= __('Back to courses') ?></a>
<?php endif; ?>
