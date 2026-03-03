<?php
/** @var array $codes */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0"><?= __('Invite codes') ?></h1>
    <a href="<?= url('invites/create') ?>" class="btn btn-primary"><?= __('Create registration invite') ?></a>
</div>
<div class="card">
    <div class="card-body">
        <?php if (empty($codes)): ?>
            <p class="text-muted mb-0"><?= __('No invite codes yet.') ?></p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th><?= __('Code') ?></th>
                            <th><?= __('Scope') ?></th>
                            <th><?= __('Auto-approve') ?></th>
                            <th><?= __('Max uses') ?></th>
                            <th><?= __('Used') ?></th>
                            <th><?= __('Expires at') ?></th>
                            <th><?= __('Note') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($codes as $c): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($c['code']) ?></code></td>
                                <td>
                                    <?php if ($c['class_id']): ?>
                                        <?= __('Class') ?>: <?= htmlspecialchars(($c['course_code'] ?? '') . ' ' . ($c['class_code'] ?? '')) ?>
                                    <?php elseif ($c['course_id']): ?>
                                        <?= __('Course') ?>: <?= htmlspecialchars(($c['course_code'] ?? '') . ' ' . ($c['course_name'] ?? '')) ?>
                                    <?php else: ?>
                                        <?= __('Registration') ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= !empty($c['auto_approve']) ? __('Yes') : __('No') ?></td>
                                <td><?= $c['max_uses'] !== null ? (int) $c['max_uses'] : '∞' ?></td>
                                <td><?= (int) $c['used_count'] ?></td>
                                <td><?= $c['expires_at'] ? htmlspecialchars($c['expires_at']) : '–' ?></td>
                                <td><?= htmlspecialchars($c['note'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

