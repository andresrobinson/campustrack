<?php
/** @var array $class */
/** @var array $enrollments */
/** @var array $pending */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0"><?= __('Enrollments') ?></h1>
    <div class="d-flex gap-2">
        <a href="<?= url('invites/class', ['class_id' => $class['id']]) ?>" class="btn btn-outline-secondary btn-sm"><?= __('Create class invite') ?></a>
        <a href="<?= url('enrollments/create', ['class_id' => $class['id']]) ?>" class="btn btn-primary"><?= __('Enroll student') ?></a>
    </div>
</div>
<p class="text-muted"><?= htmlspecialchars($class['course_name'] . ' – ' . $class['code'] . ' ' . $class['name']) ?></p>
<?php if (!empty($pending)): ?>
    <div class="card mb-3 border-warning">
        <div class="card-header bg-warning bg-opacity-25"><?= __('Pending requests') ?></div>
        <div class="card-body">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th><?= __('Name') ?></th>
                        <th><?= __('email') ?></th>
                        <th><?= __('Requested at') ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending as $e): ?>
                        <tr>
                            <td><?= htmlspecialchars($e['student_name']) ?></td>
                            <td><?= htmlspecialchars($e['student_email']) ?></td>
                            <td><?= htmlspecialchars($e['requested_at']) ?></td>
                            <td>
                                <form action="<?= url('enrollments/approve') ?>" method="post" class="d-inline">
                                    <input type="hidden" name="id" value="<?= (int) $e['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-success"><?= __('Approve') ?></button>
                                </form>
                                <form action="<?= url('enrollments/reject') ?>" method="post" class="d-inline">
                                    <input type="hidden" name="id" value="<?= (int) $e['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><?= __('Reject') ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
<div class="card">
    <div class="card-body">
        <h5 class="card-title"><?= __('Enrolled students') ?></h5>
        <?php if (empty($enrollments)): ?>
            <p class="text-muted mb-0"><?= __('No enrollments yet.') ?></p>
        <?php else: ?>
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th><?= __('Name') ?></th>
                        <th><?= __('email') ?></th>
                        <th><?= __('Status') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($enrollments as $e): ?>
                        <tr>
                            <td><?= htmlspecialchars($e['student_name']) ?></td>
                            <td><?= htmlspecialchars($e['student_email']) ?></td>
                            <td>
                                <span class="badge bg-<?= $e['status'] === 'approved' ? 'success' : ($e['status'] === 'rejected' ? 'danger' : 'warning') ?>"><?= htmlspecialchars($e['status']) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
<a href="<?= url('classes', ['course_id' => $class['course_id']]) ?>" class="btn btn-link"><?= __('Back to classes') ?></a>
