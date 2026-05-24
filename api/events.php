<?php

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

$validEventStatuses = ['published', 'draft'];

function api_valid_event_date(string $date): bool
{
    $dt = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    return $dt instanceof DateTimeImmutable && $dt->format('Y-m-d') === $date;
}

function api_valid_event_time(?string $time): bool
{
    if ($time === null || $time === '') {
        return true;
    }

    $dt = DateTimeImmutable::createFromFormat('!H:i', $time);
    return $dt instanceof DateTimeImmutable && $dt->format('H:i') === $time;
}

function api_event_by_id(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM events WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $event = $stmt->fetch();

    return $event ?: null;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $status = 'published';
    $stmt = db()->prepare(
        "SELECT * FROM events
         WHERE status = ? AND event_date >= CURDATE()
         ORDER BY event_date ASC, event_time IS NULL ASC, event_time ASC, id ASC"
    );
    $stmt->execute([$status]);
    json_response(['success' => true, 'message' => '', 'data' => $stmt->fetchAll()]);
}

if ($method === 'POST') {
    api_require_roles(['admin']);

    $rawJson = json_decode(file_get_contents('php://input') ?: '[]', true);
    $input = array_merge($_POST, is_array($rawJson) ? $rawJson : []);

    if (!verify_csrf($input['csrf_token'] ?? null)) {
        json_response(['success' => false, 'message' => 'Invalid CSRF token.', 'data' => []], 403);
    }

    $action = (string) ($input['action'] ?? '');
    $id = (int) ($input['id'] ?? $input['event_id'] ?? 0);

    if ($action === 'delete') {
        if (!api_event_by_id($id)) {
            json_response(['success' => false, 'message' => 'Event not found.', 'data' => []], 404);
        }

        db()->prepare('DELETE FROM events WHERE id = ?')->execute([$id]);
        json_response(['success' => true, 'message' => 'Deleted', 'data' => []]);
    }

    if ($action === 'toggle') {
        $event = api_event_by_id($id);
        if (!$event) {
            json_response(['success' => false, 'message' => 'Event not found.', 'data' => []], 404);
        }

        $nextStatus = ($event['status'] ?? '') === 'published' ? 'draft' : 'published';
        db()->prepare('UPDATE events SET status = ?, updated_at = NOW() WHERE id = ?')->execute([$nextStatus, $id]);
        json_response(['success' => true, 'message' => $nextStatus === 'published' ? 'Published' : 'Unpublished', 'data' => ['id' => $id, 'status' => $nextStatus]]);
    }

    if (!in_array($action, ['create', 'update'], true)) {
        json_response(['success' => false, 'message' => 'Unsupported action.', 'data' => []], 400);
    }

    $title = trim((string) ($input['title'] ?? ''));
    $description = trim((string) ($input['description'] ?? ''));
    $eventDate = trim((string) ($input['event_date'] ?? ''));
    $eventTime = trim((string) ($input['event_time'] ?? ''));
    $location = trim((string) ($input['location'] ?? ''));
    $status = in_array(($input['status'] ?? ''), $validEventStatuses, true) ? (string) $input['status'] : 'draft';
    $errors = [];

    if ($title === '' || strlen($title) > 200) {
        $errors[] = 'Event title is required and must be 200 characters or fewer.';
    }
    if (!api_valid_event_date($eventDate)) {
        $errors[] = 'A valid event date is required.';
    }
    if (!api_valid_event_time($eventTime)) {
        $errors[] = 'Please use a valid event time.';
    }
    if (strlen($location) > 255) {
        $errors[] = 'Location must be 255 characters or fewer.';
    }

    if ($errors) {
        json_response(['success' => false, 'message' => implode(' ', $errors), 'data' => []], 422);
    }

    $existing = $action === 'update' ? api_event_by_id($id) : null;
    if ($action === 'update' && !$existing) {
        json_response(['success' => false, 'message' => 'Event not found.', 'data' => []], 404);
    }

    $incomingImage = trim((string) ($input['image'] ?? ''));
    $image = $incomingImage !== '' ? $incomingImage : ($existing['image'] ?? null);
    $timeValue = $eventTime !== '' ? $eventTime : null;

    if ($action === 'create') {
        $stmt = db()->prepare(
            'INSERT INTO events (admin_id, title, description, event_date, event_time, location, image, status, created_at, updated_at)
             VALUES (?,?,?,?,?,?,?,?,NOW(),NOW())'
        );
        $stmt->execute([
            current_user_id(),
            $title,
            $description,
            $eventDate,
            $timeValue,
            $location,
            $image,
            $status,
        ]);
        json_response(['success' => true, 'message' => 'Created', 'data' => ['id' => (int) db()->lastInsertId()]]);
    }

    $stmt = db()->prepare(
        'UPDATE events
         SET title = ?, description = ?, event_date = ?, event_time = ?, location = ?, image = ?, status = ?, updated_at = NOW()
         WHERE id = ?'
    );
    $stmt->execute([
        $title,
        $description,
        $eventDate,
        $timeValue,
        $location,
        $image,
        $status,
        $id,
    ]);

    json_response(['success' => true, 'message' => 'Updated', 'data' => ['id' => $id]]);
}

json_response(['success' => false, 'message' => 'Method not allowed', 'data' => []], 405);
