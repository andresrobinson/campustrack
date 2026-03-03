<?php
/** @var array $courses */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0"><?= __('courses') ?></h1>
    <a href="<?= url('courses/create') ?>" class="btn btn-primary"><?= __('Add course') ?></a>
</div>
<div class="card">
    <div class="card-body">
        <?php if (empty($courses)): ?>
            <p class="text-muted mb-0"><?= __('No courses yet.') ?></p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th><?= __('Code') ?></th>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Attendance mode') ?></th>
                            <th><?= __('Public') ?></th>
                            <th><?= __('Duration (min)') ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $c): ?>
                            <tr>
                                <td><?= htmlspecialchars($c['code']) ?></td>
                                <td><?= htmlspecialchars($c['name']) ?></td>
                                <td><?= $c['attendance_mode'] === 'detailed' ? __('Detailed (Present/Late/Excused/Absent)') : __('Simple (Present/Absent)') ?></td>
                                <td><?= !empty($c['is_public']) ? __('Yes') : __('No') ?></td>
                                <td><?= $c['default_lecture_duration_minutes'] !== null ? (int) $c['default_lecture_duration_minutes'] : '–' ?></td>
                                <td class="text-end">
                                    <a href="<?= url('classes', ['course_id' => $c['id']]) ?>" class="btn btn-sm btn-outline-secondary"><?= __('classes') ?></a>
                                    <a href="<?= url('courses/edit?id=' . $c['id']) ?>" class="btn btn-sm btn-outline-primary"><?= __('Edit') ?></a>
                                    <form action="<?= url('courses/delete') ?>" method="post" class="d-inline" onsubmit="return confirm('<?= __('Delete this course?') ?>');">
                                        <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
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
