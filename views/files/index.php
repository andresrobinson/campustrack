<?php
/** @var array $class */
/** @var array|null $course */
/** @var array $courseFiles */
/** @var array $classFiles */
/** @var bool $canManage */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0"><?= __('Materials') ?> - <?= htmlspecialchars($class['name']) ?></h1>
</div>
<p class="text-muted">
    <?= __('Course') ?>:
    <strong><?= htmlspecialchars($course['name'] ?? $class['course_name']) ?></strong>
    (<?= htmlspecialchars($course['code'] ?? $class['course_code']) ?>)
    <br>
    <?= __('classes') ?>:
    <strong><?= htmlspecialchars($class['name']) ?></strong>
    (<?= htmlspecialchars($class['code']) ?>)
</p>
<?php if ($canManage): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title"><?= __('Upload file') ?></h5>
            <form method="post" action="<?= url('files/upload') ?>" enctype="multipart/form-data" class="row g-3">
                <input type="hidden" name="class_id" value="<?= (int) $class['id'] ?>">
                <div class="col-md-4">
                    <label for="title" class="form-label"><?= __('Name') ?></label>
                    <input type="text" class="form-control" id="title" name="title" required>
                </div>
                <div class="col-md-8">
                    <label for="description" class="form-label"><?= __('Description') ?></label>
                    <input type="text" class="form-control" id="description" name="description">
                </div>
                <div class="col-md-4">
                    <label for="file" class="form-label"><?= __('File') ?></label>
                    <input type="file" class="form-control" id="file" name="file" required>
                </div>
                <div class="col-md-4">
                    <label for="available_from" class="form-label"><?= __('Available from') ?></label>
                    <input type="datetime-local" class="form-control" id="available_from" name="available_from">
                </div>
                <div class="col-md-4">
                    <label for="available_until" class="form-label"><?= __('Available until') ?></label>
                    <input type="datetime-local" class="form-control" id="available_until" name="available_until">
                </div>
                <div class="col-md-6">
                    <label class="form-label"><?= __('Scope') ?></label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="scope" id="scope_class" value="class" checked>
                        <label class="form-check-label" for="scope_class"><?= __('Only this class') ?></label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="scope" id="scope_course" value="course">
                        <label class="form-check-label" for="scope_course"><?= __('All classes of this course') ?></label>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary"><?= __('Upload') ?></button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>
<div class="card mb-3">
    <div class="card-body">
        <h5 class="card-title"><?= __('Course materials') ?></h5>
        <?php if (empty($courseFiles)): ?>
            <p class="text-muted mb-0"><?= __('No files yet.') ?></p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Description') ?></th>
                            <th><?= __('Available from') ?></th>
                            <th><?= __('Available until') ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courseFiles as $f): ?>
                            <tr>
                                <td><?= htmlspecialchars($f['title']) ?></td>
                                <td><?= htmlspecialchars($f['description'] ?? '') ?></td>
                                <td><?= $f['available_from'] ? htmlspecialchars($f['available_from']) : '–' ?></td>
                                <td><?= $f['available_until'] ? htmlspecialchars($f['available_until']) : '–' ?></td>
                                <td class="text-end">
                                    <a href="<?= url('files/download', ['id' => $f['id']]) ?>" class="btn btn-sm btn-outline-primary"><?= __('Download') ?></a>
                                    <?php if ($canManage): ?>
                                        <a href="<?= url('files/edit?id=' . $f['id']) ?>" class="btn btn-sm btn-outline-secondary"><?= __('Edit') ?></a>
                                        <a href="<?= url('files/logs', ['id' => $f['id']]) ?>" class="btn btn-sm btn-outline-info"><?= __('Logs') ?></a>
                                        <form action="<?= url('files/delete') ?>" method="post" class="d-inline" onsubmit="return confirm('<?= __('Delete this file?') ?>');">
                                            <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><?= __('Delete') ?></button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
></div>
<div class="card">
    <div class="card-body">
        <h5 class="card-title"><?= __('Class materials') ?></h5>
        <?php if (empty($classFiles)): ?>
            <p class="text-muted mb-0"><?= __('No files yet.') ?></p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Description') ?></th>
                            <th><?= __('Available from') ?></th>
                            <th><?= __('Available until') ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classFiles as $f): ?>
                            <tr>
                                <td><?= htmlspecialchars($f['title']) ?></td>
                                <td><?= htmlspecialchars($f['description'] ?? '') ?></td>
                                <td><?= $f['available_from'] ? htmlspecialchars($f['available_from']) : '–' ?></td>
                                <td><?= $f['available_until'] ? htmlspecialchars($f['available_until']) : '–' ?></td>
                                <td class="text-end">
                                    <a href="<?= url('files/download', ['id' => $f['id']]) ?>" class="btn btn-sm btn-outline-primary"><?= __('Download') ?></a>
                                    <?php if ($canManage): ?>
                                        <a href="<?= url('files/edit?id=' . $f['id']) ?>" class="btn btn-sm btn-outline-secondary"><?= __('Edit') ?></a>
                                        <a href="<?= url('files/logs', ['id' => $f['id']]) ?>" class="btn btn-sm btn-outline-info"><?= __('Logs') ?></a>
                                        <form action="<?= url('files/delete') ?>" method="post" class="d-inline" onsubmit="return confirm('<?= __('Delete this file?') ?>');">
                                            <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><?= __('Delete') ?></button>
                                        </form>
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
<a href="<?= url('classes', ['course_id' => $class['course_id']]) ?>" class="btn btn-link mt-3"><?= __('Back to classes') ?></a>

