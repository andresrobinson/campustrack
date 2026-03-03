<?php
/** @var array $file */
/** @var array $class */
/** @var array $logs */
?>
<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h1 class="mb-0"><?= __('Download history') ?></h1>
        <p class="text-muted mb-0">
            <?= __('Name') ?>: <strong><?= htmlspecialchars($file['title']) ?></strong><br>
            <?= __('Original file') ?>: <?= htmlspecialchars($file['original_name']) ?><br>
            <?= __('classes') ?>: <?= htmlspecialchars($class['name']) ?> (<?= htmlspecialchars($class['code']) ?>)
        </p>
    </div>
    <button onclick="window.print()" class="btn btn-outline-secondary d-print-none"><?= __('Print') ?></button>
</div>
<div class="card">
    <div class="card-body">
        <?php if (empty($logs)): ?>
            <p class="text-muted mb-0"><?= __('No downloads yet.') ?></p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <th><?= __('email') ?></th>
                            <th><?= __('Downloaded at') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= htmlspecialchars($log['name']) ?></td>
                                <td><?= htmlspecialchars($log['email']) ?></td>
                                <td><?= htmlspecialchars($log['downloaded_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<a href="<?= url('files', ['class_id' => $class['id']]) ?>" class="btn btn-link d-print-none mt-3"><?= __('Back to classes') ?></a>

