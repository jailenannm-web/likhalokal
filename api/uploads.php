<?php

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

api_require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'POST only', 'data' => []], 405);
}

if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    json_response(['success' => false, 'message' => 'Invalid CSRF', 'data' => []], 403);
}

if (empty($_FILES['file'])) {
    json_response(['success' => false, 'message' => 'No file', 'data' => []], 422);
}

$sub = preg_replace('/[^a-z0-9_-]/i', '', (string) ($_POST['folder'] ?? 'general')) ?: 'general';
$path = save_upload($_FILES['file'], $sub);
if (!$path) {
    json_response(['success' => false, 'message' => 'Invalid file type or size (max 5MB, JPG/PNG/WEBP)', 'data' => []], 422);
}

json_response(['success' => true, 'message' => 'Uploaded', 'data' => ['path' => $path, 'url' => asset_url($path)]]);
