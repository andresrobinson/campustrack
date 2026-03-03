<?php
/** @var array $my */
/** @var array $available */
?>
<div class="mb-4">
    <h1 class="mb-0"><?= __('My classes') ?></h1>
</div>

<div class="card mb-4">
    <div class="card-body">
        <?php if (empty($my)): ?>
            <p class="text-muted mb-0"><?= __('No enrollments yet.') ?></p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th><?= __('Course') ?></th>
                            <th><?= __('Class') ?></th>
                            <th><?= __('Status') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($my as $e): ?>
                            <tr>
                                <td><?= htmlspecialchars($e['course_code'] . ' – ' . $e['course_name']) ?></td>
                                <td><?= htmlspecialchars($e['class_code'] . ' ' . $e['class_name']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $e['status'] === 'approved' ? 'success' : ($e['status'] === 'rejected' ? 'danger' : 'warning') ?>">
                                        <?= htmlspecialchars($e['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h2 class="h5"><?= __('Join class with invite code') ?></h2>
        <form method="post" action="<?= url('student/enroll-by-code') ?>" class="row g-2 align-items-end">
            <div class="col-sm-6 col-md-4">
                <label for="invite_code" class="form-label"><?= __('Invite code') ?></label>
                <input type="text" class="form-control" id="invite_code" name="invite_code" required
                       value="<?= htmlspecialchars($_POST['invite_code'] ?? '') ?>">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-primary mt-2 mt-sm-0"><?= __('Use code') ?></button>
            </div>
        </form>
    </div>
</div>

<div class="mb-3">
    <h2 class="h4 mb-0"><?= __('Available classes') ?></h2>
    <p class="text-muted small mb-0"><?= __('You can request enrollment in classes of public courses. A teacher or manager must approve your request.') ?></p>
</div>
<div class="card">
    <div class="card-body">
        <?php if (empty($available)): ?>
            <p class="text-muted mb-0"><?= __('No available classes at the moment.') ?></p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th><?= __('Course') ?></th>
                            <th><?= __('Code') ?></th>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Start') ?></th>
                            <th><?= __('End') ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($available as $cl): ?>
                            <tr>
                                <td><?= htmlspecialchars($cl['course_code'] . ' – ' . $cl['course_name']) ?></td>
                                <td><?= htmlspecialchars($cl['code']) ?></td>
                                <td><?= htmlspecialchars($cl['name']) ?></td>
                                <td><?= $cl['start_date'] ? htmlspecialchars($cl['start_date']) : '–' ?></td>
                                <td><?= $cl['end_date'] ? htmlspecialchars($cl['end_date']) : '–' ?></td>
                                <td class="text-end">
                                    <form method="post" action="<?= url('student/enroll') ?>" class="d-inline">
                                        <input type="hidden" name="class_id" value="<?= (int) $cl['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-primary"><?= __('Request enrollment') ?></button>
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

