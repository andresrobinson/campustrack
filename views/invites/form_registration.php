<?php
/** @var array|null $invite */
?>
<div class="mb-4">
    <h1 class="mb-0"><?= __('Create registration invite') ?></h1>
</div>
<div class="card">
    <div class="card-body">
        <form method="post" action="<?= url('invites/create') ?>">
            <div class="mb-3">
                <label for="code" class="form-label"><?= __('Code') ?></label>
                <input type="text" class="form-control" id="code" name="code"
                       placeholder="<?= __('Leave blank to generate') ?>"
                       value="<?= htmlspecialchars($_POST['code'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="max_uses" class="form-label"><?= __('Max uses') ?></label>
                <input type="number" class="form-control" id="max_uses" name="max_uses" min="1"
                       placeholder="<?= __('Optional') ?>" value="<?= htmlspecialchars($_POST['max_uses'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="expires_at" class="form-label"><?= __('Expires at') ?></label>
                <input type="datetime-local" class="form-control" id="expires_at" name="expires_at"
                       value="<?= htmlspecialchars($_POST['expires_at'] ?? '') ?>">
                <small class="text-muted"><?= __('Optional') ?></small>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="auto_approve" name="auto_approve" value="1"
                    <?= !empty($_POST['auto_approve']) ? 'checked' : '' ?>>
                <label class="form-check-label" for="auto_approve"><?= __('Automatically approve accounts created with this invite') ?></label>
            </div>
            <div class="mb-3">
                <label for="note" class="form-label"><?= __('Note') ?></label>
                <input type="text" class="form-control" id="note" name="note"
                       value="<?= htmlspecialchars($_POST['note'] ?? '') ?>">
            </div>
            <button type="submit" class="btn btn-primary"><?= __('Create') ?></button>
            <a href="<?= url('invites') ?>" class="btn btn-secondary"><?= __('Cancel') ?></a>
        </form>
    </div>
</div>

