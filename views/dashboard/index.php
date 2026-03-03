<?php
ob_start();
?>
<h1 class="mb-4"><?= __('dashboard') ?></h1>
<p class="lead"><?= __("welcome", ['name' => \Core\Auth::user()['name']]) ?></p>
<p class="text-muted"><?= __('role_label') ?>: <?= htmlspecialchars(\Core\Auth::user()['role']) ?></p>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
