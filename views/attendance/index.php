<?php
/** @var array $lecture */
/** @var array $class */
/** @var array $students */
/** @var array $attendanceMap */
/** @var string $attendanceMode */
/** @var int $durationMinutes */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0"><?= __('attendance') ?></h1>
    <a href="<?= url('lectures', ['class_id' => $lecture['class_id']]) ?>" class="btn btn-outline-secondary"><?= __('Back to lectures') ?></a>
</div>
<p class="text-muted"><?= htmlspecialchars($class['course_name'] . ' – ' . $class['code']) ?> · <?= htmlspecialchars($lecture['lecture_date']) ?> <?= $lecture['start_time'] ? substr($lecture['start_time'], 0, 5) : '' ?></p>
<?php if (empty($students)): ?>
    <div class="alert alert-info"><?= __('No approved students in this class. Enroll students first.') ?></div>
<?php else: ?>
    <form method="post" action="<?= url('attendance/save') ?>">
        <input type="hidden" name="lecture_id" value="<?= (int) $lecture['id'] ?>">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th><?= __('Name') ?></th>
                                <th><?= __('email') ?></th>
                                <th><?= __('Status') ?></th>
                                <?php if ($attendanceMode === 'detailed'): ?>
                                    <th><?= __('Credited (min)') ?></th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $s): ?>
                                <?php
                                $current = $attendanceMap[$s['id']] ?? null;
                                $currentStatus = $current['status'] ?? '';
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($s['name']) ?></td>
                                    <td><?= htmlspecialchars($s['email']) ?></td>
                                    <td>
                                        <select name="attendance[<?= (int) $s['id'] ?>]" class="form-select form-select-sm" style="width: auto;">
                                            <option value="present" <?= $currentStatus === 'present' ? 'selected' : '' ?>><?= __('Present') ?></option>
                                            <option value="absent" <?= $currentStatus === 'absent' || $currentStatus === '' ? 'selected' : '' ?>><?= __('Absent') ?></option>
                                            <?php if ($attendanceMode === 'detailed'): ?>
                                                <option value="late" <?= $currentStatus === 'late' ? 'selected' : '' ?>><?= __('Late') ?></option>
                                                <option value="excused" <?= $currentStatus === 'excused' ? 'selected' : '' ?>><?= __('Excused') ?></option>
                                            <?php endif; ?>
                                        </select>
                                    </td>
                                    <?php if ($attendanceMode === 'detailed'): ?>
                                        <td><?= $current ? (int) $current['credited_minutes'] : 0 ?></td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="btn btn-primary"><?= __('Save attendance') ?></button>
            </div>
        </div>
    </form>
<?php endif; ?>
