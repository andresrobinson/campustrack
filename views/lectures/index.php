<?php
/** @var array $lectures */
/** @var array $class */
/** @var bool $canEdit */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0"><?= __('lectures') ?></h1>
    <?php if ($canEdit): ?>
        <div>
            <a href="<?= url('lectures/create', ['class_id' => $class['id']]) ?>" class="btn btn-primary"><?= __('Add lecture') ?></a>
        </div>
    <?php endif; ?>
</div>
<p class="text-muted"><?= htmlspecialchars($class['course_name'] . ' – ' . $class['code'] . ' ' . $class['name']) ?></p>
<?php if ($canEdit && (!empty($class['start_date']) || !empty($class['auto_lectures_count']))): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title"><?= __('Generate recurring lectures') ?></h6>
            <form method="post" action="<?= url('lectures/generate') ?>" class="row g-2 align-items-end">
                <input type="hidden" name="class_id" value="<?= (int) $class['id'] ?>">
                <div class="col-auto">
                    <label for="start_date" class="form-label small"><?= __('Start date') ?></label>
                    <input type="date" class="form-control form-control-sm" id="start_date" name="start_date" value="<?= htmlspecialchars($class['start_date'] ?? '') ?>">
                </div>
                <div class="col-auto">
                    <label for="gen_count" class="form-label small"><?= __('Number of lectures') ?></label>
                    <input type="number" class="form-control form-control-sm" id="gen_count" name="count" min="1" value="<?= (int)($class['auto_lectures_count'] ?? 10) ?>">
                </div>
                <div class="col-auto">
                    <label for="start_time" class="form-label small"><?= __('Time') ?></label>
                    <input type="time" class="form-control form-control-sm" id="start_time" name="start_time" value="09:00">
                </div>
                <div class="col-auto">
                    <label for="location" class="form-label small"><?= __('Location') ?></label>
                    <input type="text" class="form-control form-control-sm" id="location" name="location" placeholder="<?= __('Room') ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-outline-primary btn-sm"><?= __('Generate') ?></button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>
<div class="card">
    <div class="card-body">
        <?php if (empty($lectures)): ?>
            <p class="text-muted mb-0"><?= __('No lectures yet.') ?></p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th><?= __('Date') ?></th>
                            <th><?= __('Time') ?></th>
                            <th><?= __('Duration') ?></th>
                            <th><?= __('Location') ?></th>
                            <th><?= __('Extra') ?></th>
                            <?php if ($canEdit): ?>
                                <th></th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lectures as $l): ?>
                            <tr>
                                <td><?= htmlspecialchars($l['lecture_date']) ?></td>
                                <td><?= $l['start_time'] ? htmlspecialchars(substr($l['start_time'], 0, 5)) : '–' ?></td>
                                <td><?= (int) $l['duration_minutes'] ?> min</td>
                                <td><?= $l['location'] ? htmlspecialchars($l['location']) : '–' ?></td>
                                <td><?= !empty($l['is_extra']) ? __('Yes') : '–' ?></td>
                                <?php if ($canEdit): ?>
                                    <td class="text-end">
                                        <a href="<?= url('attendance', ['lecture_id' => $l['id']]) ?>" class="btn btn-sm btn-outline-success"><?= __('attendance') ?></a>
                                        <a href="<?= url('lectures/edit?id=' . $l['id']) ?>" class="btn btn-sm btn-outline-primary"><?= __('Edit') ?></a>
                                        <form action="<?= url('lectures/delete') ?>" method="post" class="d-inline" onsubmit="return confirm('<?= __('Delete this lecture?') ?>');">
                                            <input type="hidden" name="id" value="<?= (int) $l['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><?= __('Delete') ?></button>
                                        </form>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<a href="<?= url('classes', ['course_id' => $class['course_id']]) ?>" class="btn btn-link"><?= __('Back to classes') ?></a>
