<?php

declare(strict_types=1);

$pageTitle = 'Events admin';
$activeAdmin = 'evt';
require_once __DIR__ . '/_init.php';
require_once BASE_PATH . '/middleware/csrf.php';

$validStatuses = ['published', 'draft'];
$emptyEvent = [
    'id' => 0,
    'title' => '',
    'description' => '',
    'event_date' => '',
    'event_time' => '',
    'location' => '',
    'image' => '',
    'status' => 'published',
];

function valid_event_date(string $date): bool
{
    $dt = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    return $dt instanceof DateTimeImmutable && $dt->format('Y-m-d') === $date;
}

function valid_event_time(?string $time): bool
{
    if ($time === null || $time === '') {
        return true;
    }
    $dt = DateTimeImmutable::createFromFormat('!H:i', $time);
    return $dt instanceof DateTimeImmutable && $dt->format('H:i') === $time;
}

function admin_event_by_id(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM events WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $event = $stmt->fetch();
    return $event ?: null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Invalid token. Please try again.');
        redirect(ADMIN_URL . 'events.php');
    }

    $action = (string) ($_POST['action'] ?? 'create');
    $eventId = (int) ($_POST['event_id'] ?? 0);

    if ($action === 'delete') {
        if (admin_event_by_id($eventId)) {
            db()->prepare('DELETE FROM events WHERE id = ?')->execute([$eventId]);
            set_flash('success', 'Event deleted.');
        } else {
            set_flash('error', 'Event not found.');
        }
        redirect(ADMIN_URL . 'events.php');
    }

    if ($action === 'toggle') {
        $event = admin_event_by_id($eventId);
        if ($event) {
            $next = ($event['status'] ?? '') === 'published' ? 'draft' : 'published';
            db()->prepare('UPDATE events SET status = ?, updated_at = NOW() WHERE id = ?')->execute([$next, $eventId]);
            set_flash('success', $next === 'published' ? 'Event published.' : 'Event unpublished.');
        }
        redirect(ADMIN_URL . 'events.php');
    }

    $title = trim((string) ($_POST['title'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $eventDate = trim((string) ($_POST['event_date'] ?? ''));
    $eventTime = trim((string) ($_POST['event_time'] ?? ''));
    $location = trim((string) ($_POST['location'] ?? ''));
    $status = in_array(($_POST['status'] ?? ''), $validStatuses, true) ? (string) $_POST['status'] : 'draft';
    $errors = [];

    if ($title === '' || strlen($title) > 200) {
        $errors[] = 'Event title is required and must be 200 characters or fewer.';
    }
    if (!valid_event_date($eventDate)) {
        $errors[] = 'A valid event date is required.';
    }
    if (!valid_event_time($eventTime)) {
        $errors[] = 'Please use a valid event time.';
    }
    if (strlen($location) > 255) {
        $errors[] = 'Location must be 255 characters or fewer.';
    }

    $existing = $eventId > 0 ? admin_event_by_id($eventId) : null;
    $image = $existing['image'] ?? null;
    if (!empty($_FILES['image']['tmp_name'])) {
        $upload = save_upload($_FILES['image'], 'events');
        if ($upload) {
            $image = $upload;
        } else {
            $errors[] = 'Event image must be a JPG, PNG, or WEBP file under the upload limit.';
        }
    } elseif (isset($_FILES['image']) && (int) ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Event image upload failed.';
    }

    if (!empty($errors)) {
        set_flash('error', implode(' ', $errors));
        $target = $eventId > 0 ? ADMIN_URL . 'events.php?edit=' . $eventId : ADMIN_URL . 'events.php';
        redirect($target);
    }

    if ($action === 'update' && $existing) {
        $stmt = db()->prepare(
            'UPDATE events
             SET title = ?, description = ?, event_date = ?, event_time = ?, location = ?, image = ?, status = ?, updated_at = NOW()
             WHERE id = ?'
        );
        $stmt->execute([
            $title,
            $description,
            $eventDate,
            $eventTime !== '' ? $eventTime : null,
            $location,
            $image,
            $status,
            $eventId,
        ]);
        set_flash('success', 'Event updated.');
    } else {
        $stmt = db()->prepare(
            'INSERT INTO events (admin_id, title, description, event_date, event_time, location, image, status, created_at, updated_at)
             VALUES (?,?,?,?,?,?,?,?,NOW(),NOW())'
        );
        $stmt->execute([
            current_user_id(),
            $title,
            $description,
            $eventDate,
            $eventTime !== '' ? $eventTime : null,
            $location,
            $image,
            $status,
        ]);
        set_flash('success', 'Event created.');
    }
    redirect(ADMIN_URL . 'events.php');
}

$editId = (int) ($_GET['edit'] ?? 0);
$editingEvent = $editId > 0 ? admin_event_by_id($editId) : null;
$formEvent = $editingEvent ?: $emptyEvent;
$list = db()->query('SELECT * FROM events ORDER BY event_date >= CURDATE() DESC, event_date ASC, event_time IS NULL ASC, event_time ASC, id DESC')->fetchAll();

require __DIR__ . '/partials/layout-start.php';
require BASE_PATH . '/includes/partials/dash-flash.php';
?>
<div class="lk-dash-inner-head d-flex flex-wrap justify-content-between align-items-start gap-2">
    <div>
        <h1 class="lk-dash-page-title mb-1">Events</h1>
        <p class="lk-dash-page-lead text-muted mb-0">Create, schedule, publish, and maintain public upcoming events.</p>
    </div>
    <?php if ($editingEvent): ?>
        <a href="<?= e(ADMIN_URL) ?>events.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg me-1"></i> Cancel edit</a>
    <?php endif; ?>
</div>

<div class="lk-panel mb-4">
    <div class="lk-panel-header">
        <h2><i class="bi <?= $editingEvent ? 'bi-pencil-square' : 'bi-plus-circle' ?> me-2 text-warning"></i><?= $editingEvent ? 'Edit event' : 'Add event' ?></h2>
    </div>
    <div class="lk-panel-body">
        <form method="post" enctype="multipart/form-data" class="row g-3">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="<?= $editingEvent ? 'update' : 'create' ?>">
            <input type="hidden" name="event_id" value="<?= (int) ($formEvent['id'] ?? 0) ?>">
            <div class="col-md-6">
                <label class="form-label">Event title</label>
                <input class="form-control" name="title" required maxlength="200" value="<?= e((string) $formEvent['title']) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Date</label>
                <input type="date" class="form-control" name="event_date" required value="<?= e((string) $formEvent['event_date']) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Time</label>
                <input type="time" class="form-control" name="event_time" value="<?= e(substr((string) ($formEvent['event_time'] ?? ''), 0, 5)) ?>">
            </div>
            <div class="col-md-8">
                <label class="form-label">Location</label>
                <input class="form-control" name="location" maxlength="255" value="<?= e((string) $formEvent['location']) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <?php foreach ($validStatuses as $status): ?>
                        <option value="<?= e($status) ?>" <?= ($formEvent['status'] ?? '') === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="3"><?= e((string) $formEvent['description']) ?></textarea>
            </div>
            <div class="col-md-8">
                <label class="form-label">Event image</label>
                <input class="form-control" type="file" name="image" accept="image/jpeg,image/png,image/webp">
                <?php if (!empty($formEvent['image'])): ?>
                    <div class="form-text">Current image will be kept if no new image is uploaded.</div>
                <?php endif; ?>
            </div>
            <?php if (!empty($formEvent['image'])): ?>
                <div class="col-md-4">
                    <img src="<?= e(media_url($formEvent['image'])) ?>" alt="" class="rounded shadow-sm w-100" style="height:110px;object-fit:cover;">
                </div>
            <?php endif; ?>
            <div class="col-12">
                <button class="btn btn-lk-orange" type="submit"><i class="bi bi-save me-1"></i><?= $editingEvent ? ' Save changes' : ' Add event' ?></button>
            </div>
        </form>
    </div>
</div>

<div class="lk-panel">
    <div class="lk-panel-header">
        <h2>All events</h2>
        <span class="badge bg-light text-dark"><?= count($list) ?> event(s)</span>
    </div>
    <div class="lk-dash-table-wrap">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Date</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($list as $event): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <img src="<?= e(media_url($event['image'] ?? null, asset_url('images/tacboanfes1.png'))) ?>" alt="" class="rounded" style="width:70px;height:54px;object-fit:cover;">
                            <div>
                                <strong class="d-block"><?= e($event['title']) ?></strong>
                                <span class="small text-muted"><?= e(str_limit($event['description'] ?? '', 80)) ?></span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <strong><?= e(date('M j, Y', strtotime((string) $event['event_date']))) ?></strong>
                        <?php if (!empty($event['event_time'])): ?>
                            <span class="small text-muted d-block"><?= e(date('g:i A', strtotime((string) $event['event_time']))) ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= e($event['location'] ?: 'Location not provided') ?></td>
                    <td><span class="badge bg-<?= ($event['status'] ?? '') === 'published' ? 'success' : 'secondary' ?>"><?= e(ucfirst((string) $event['status'])) ?></span></td>
                    <td class="text-end">
                        <a href="<?= e(ADMIN_URL) ?>events.php?edit=<?= (int) $event['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                        <form method="post" class="d-inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="event_id" value="<?= (int) $event['id'] ?>">
                            <button class="btn btn-sm btn-outline-primary" type="submit">
                                <i class="bi <?= ($event['status'] ?? '') === 'published' ? 'bi-eye-slash' : 'bi-eye' ?>"></i>
                            </button>
                        </form>
                        <form method="post" class="d-inline" onsubmit="return confirm('Delete this event?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="event_id" value="<?= (int) $event['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($list)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No events yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/partials/layout-end.php'; require BASE_PATH . '/includes/footer.php'; ?>
