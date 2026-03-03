<?php
/** @var array $class */
/** @var array|null $course */
/** @var array $students */
/** @var int $planned_minutes */

$plannedHours = $planned_minutes / 60;
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0"><?= __('reports') ?> - <?= __('classes') ?></h1>
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
<div class="card">
    <div class="card-body">
        <?php if (empty($students)): ?>
            <p class="text-muted mb-0"><?= __('No enrollments yet.') ?></p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th><?= __('Name') ?></th>
                            <th><?= __('email') ?></th>
                            <th><?= __('Credited hours') ?></th>
                            <th><?= __('Planned hours') ?></th>
                            <th><?= __('Attendance %') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $s): ?>
                            <?php
                            $creditedMin = (int) ($s['credited_minutes'] ?? 0);
                            $creditedHours = $creditedMin / 60;
                            $pct = $planned_minutes > 0 ? ($creditedMin / $planned_minutes * 100) : 0;
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($s['name']) ?></td>
                                <td><?= htmlspecialchars($s['email']) ?></td>
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
<a href="<?= url('classes', ['course_id' => $class['course_id']]) ?>" class="btn btn-link d-print-none"><?= __('Back to classes') ?></a>

