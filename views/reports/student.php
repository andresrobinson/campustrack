<?php
/** @var array $student */
/** @var array $classes */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0"><?= __('reports') ?> - <?= __('Student') ?></h1>
    <button onclick="window.print()" class="btn btn-outline-secondary d-print-none"><?= __('Print') ?></button>
</div>
<p class="text-muted">
    <?= __('Student') ?>:
    <strong><?= htmlspecialchars($student['name']) ?></strong>
    (<?= htmlspecialchars($student['email']) ?>)
</p>
<div class="card">
    <div class="card-body">
        <?php if (empty($classes)): ?>
            <p class="text-muted mb-0"><?= __('No enrollments yet.') ?></p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th><?= __('Course') ?></th>
                            <th><?= __('classes') ?></th>
                            <th><?= __('Credited hours') ?></th>
                            <th><?= __('Planned hours') ?></th>
                            <th><?= __('Attendance %') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classes as $cl): ?>
                            <?php
                            $creditedMin = (int) ($cl['credited_minutes'] ?? 0);
                            $plannedMin = (int) ($cl['planned_minutes'] ?? 0);
                            $creditedHours = $creditedMin / 60;
                            $plannedHours = $plannedMin / 60;
                            $pct = $plannedMin > 0 ? ($creditedMin / $plannedMin * 100) : 0;
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($cl['course_name']) ?></td>
                                <td><?= htmlspecialchars($cl['class_name'] . ' (' . $cl['class_code'] . ')') ?></td>
                                <td><?= number_format($creditedHours, 2) ?></td>
                                <td><?= number_format($plannedHours, 2) ?></td>
                                <td><?= number_format($pct, 1) ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<a href="<?= url('dashboard') ?>" class="btn btn-link d-print-none"><?= __('dashboard') ?></a>

