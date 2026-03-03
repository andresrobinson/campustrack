<?php
/** @var array $users */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0"><?= __('Users') ?></h1>
    <?php if (\Core\Auth::canManageSystem()): ?>
        <a href="<?= url('users/create') ?>" class="btn btn-primary"><?= __('Add user') ?></a>
    <?php endif; ?>
</div>
<div class="card">
    <div class="card-body">
        <?php if (empty($users)): ?>
            <p class="text-muted mb-0"><?= __('No users yet.') ?></p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <th><?= __('email') ?></th>
                            <th><?= __('role_label') ?></th>
                            <th><?= __('Status') ?></th>
                            <th><?= __('Actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?= htmlspecialchars($u['name']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><?= htmlspecialchars($u['role']) ?></td>
                                <td><?= htmlspecialchars($u['status']) ?></td>
                                <td>
                                    <?php if ($u['role'] === 'student'): ?>
                                        <div class="d-flex flex-wrap gap-1">
                                            <a href="<?= url('reports/student', ['student_id' => $u['id']]) ?>" class="btn btn-sm btn-outline-primary"><?= __('Grades') ?></a>
                                            <?php if ($u['status'] === 'pending'): ?>
                                                <form method="post" action="<?= url('users/approve') ?>" class="d-inline">
                                                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-success"><?= __('Approve') ?></button>
                                                </form>
                                                <form method="post" action="<?= url('users/reject') ?>" class="d-inline">
                                                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"><?= __('Reject') ?></button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
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
