<?php

declare(strict_types=1);

$pageTitle = 'Events admin';
$activeAdmin = 'evt';
require_once __DIR__ . '/_init.php';
require_once BASE_PATH . '/middleware/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? null)) {
    $stmt = db()->prepare(
        'INSERT INTO events (admin_id, title, description, event_date, event_time, location, status, created_at, updated_at) VALUES (?,?,?,?,?,?,?,NOW(),NOW())'
    );
    $stmt->execute([
        current_user_id(),
        trim($_POST['title'] ?? ''),
        trim($_POST['description'] ?? ''),
        $_POST['event_date'] ?? date('Y-m-d'),
        $_POST['event_time'] ?: null,
        trim($_POST['location'] ?? ''),
        $_POST['status'] ?? 'published',
    ]);
    set_flash('success', 'Event saved');
    redirect(ADMIN_URL . 'events.php');
}

$list = db()->query('SELECT * FROM events ORDER BY event_date DESC')->fetchAll();
require BASE_PATH . '/includes/header.php';
require __DIR__ . '/partials/layout-start.php';
?>
<h1 class="h4 mb-3">Events</h1>
<?php if ($m = flash('success')): ?><div class="alert alert-success"><?= e($m) ?></div><?php endif; ?>
<div class="card card-lk mb-4"><div class="card-body">
<form method="post" class="row g-2">
<?= csrf_field() ?>
<div class="col-md-4"><input class="form-control" name="title" required placeholder="Title"></div>
<div class="col-md-2"><input type="date" class="form-control" name="event_date" required></div>
<div class="col-md-2"><input type="time" class="form-control" name="event_time"></div>
<div class="col-md-4"><input class="form-control" name="location" placeholder="Location"></div>
<div class="col-12"><textarea class="form-control" name="description" rows="2"></textarea></div>
<div class="col-md-2"><select class="form-select" name="status"><option value="published">Published</option><option value="draft">Draft</option></select></div>
<div class="col-12"><button class="btn btn-primary btn-sm" type="submit">Add event</button></div>
</form>
</div></div>
<table class="table table-sm"><thead><tr><th>Date</th><th>Title</th><th>Status</th></tr></thead><tbody>
<?php foreach ($list as $e): ?>
<tr><td><?= e($e['event_date']) ?></td><td><?= e($e['title']) ?></td><td><?= e($e['status']) ?></td></tr>
<?php endforeach; ?>
</tbody></table>
<?php require __DIR__ . '/partials/layout-end.php'; require BASE_PATH . '/includes/footer.php';
