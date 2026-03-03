<?php
/** @var array $matrix */
/** @var array $courses */
/** @var int|null $selectedCourseId */

$classes = $matrix['classes'] ?? [];
$students = $matrix['students'] ?? [];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0"><?= __('reports') ?> - <?= __('Attendance matrix') ?></h1>
    <button onclick="window.print()" class="btn btn-outline-secondary d-print-none"><?= __('Print') ?></button>
</div>
<form method="get" class="row g-2 align-items-end mb-3 d-print-none">
    <input type="hidden" name="route" value="reports/matrix">
    <div class="col-auto">
        <label for="courseFilter" class="form-label small"><?= __('Course') ?></label>
        <select id="courseFilter" name="course_id" class="form-select form-select-sm">
            <option value=""><?= __('All courses') ?></option>
            <?php foreach ($courses as $c): ?>
                <option value="<?= (int) $c['id'] ?>" <?= $selectedCourseId === (int) $c['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['code'] . ' – ' . $c['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary btn-sm"><?= __('Filter') ?></button>
    </div>
</form>
<div class="card">
    <div class="card-body">
        <?php if (empty($students) || empty($classes)): ?>
            <p class="text-muted mb-0"><?= __('No data to display.') ?></p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th><?= __('Student') ?></th>
                            <?php foreach ($classes as $cl): ?>
                                <th>
                                    <?= htmlspecialchars($cl['code']) ?><br>
                                    <small class="text-muted"><?= htmlspecialchars($cl['class_name']) ?></small>
                                </th>
                            <?php endforeach; ?>
                            <th><?= __('Total hours') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $s): ?>
                            <?php $rowTotalMin = 0; ?>
                            <tr>
                                <td><?= htmlspecialchars($s['name']) ?></td>
                                <?php foreach ($classes as $cl): ?>
                                    <?php
                                    $cid = (int) $cl['id'];
                                    $mins = (int) ($s['classes'][$cid] ?? 0);
                                    $rowTotalMin += $mins;
                                    ?>
                                    <td><?= $mins > 0 ? number_format($mins / 60, 2) : '' ?></td>
                                <?php endforeach; ?>
                                <td><?= number_format($rowTotalMin / 60, 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

