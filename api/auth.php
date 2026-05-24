<?php

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input') ?: '[]', true);
    if (!is_array($input)) {
        $input = [];
    }
    $merge = array_merge($_POST, $input);
    $action = $merge['action'] ?? $action;

    if ($action === 'login') {
        $email = trim((string) ($merge['email'] ?? ''));
        $password = (string) ($merge['password'] ?? '');
        if ($email === '' || $password === '') {
            json_response(['success' => false, 'message' => 'Email and password required.', 'data' => []], 422);
        }
        $stmt = db()->prepare('SELECT id, full_name, email, password_hash, role, status FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user || $user['status'] === 'suspended' || !password_verify($password, $user['password_hash'])) {
            json_response(['success' => false, 'message' => 'Invalid credentials or suspended account.', 'data' => []], 401);
        }
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        log_activity((int) $user['id'], 'login', 'API login', $_SERVER['REMOTE_ADDR'] ?? null);
        unset($_SESSION['post_login_redirect']);
        $redirect = public_home_url();
        json_response(['success' => true, 'message' => 'Logged in', 'data' => ['user' => ['id' => $user['id'], 'name' => $user['full_name'], 'role' => $user['role']], 'redirect' => $redirect]]);
    }

    if ($action === 'register') {
        $fullName = trim((string) ($merge['full_name'] ?? ''));
        $email = trim((string) ($merge['email'] ?? ''));
        $contact = trim((string) ($merge['contact_number'] ?? ''));
        $password = (string) ($merge['password'] ?? '');
        $confirm = (string) ($merge['confirm_password'] ?? '');
        $accountType = (string) ($merge['account_type'] ?? 'local_user');
        if ($fullName === '' || $email === '' || $password === '') {
            json_response(['success' => false, 'message' => 'Please complete required fields.', 'data' => []], 422);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            json_response(['success' => false, 'message' => 'Invalid email.', 'data' => []], 422);
        }
        if (strlen($password) < 8) {
            json_response(['success' => false, 'message' => 'Password must be at least 8 characters.', 'data' => []], 422);
        }
        if ($password !== $confirm) {
            json_response(['success' => false, 'message' => 'Passwords do not match.', 'data' => []], 422);
        }
        $role = $accountType === 'seller' ? 'seller' : 'local_user';
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            json_response(['success' => false, 'message' => 'Email already registered.', 'data' => []], 409);
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $status = $role === 'seller' ? 'active' : 'active';
        $stmt = db()->prepare(
            'INSERT INTO users (full_name, email, password_hash, contact_number, role, status, created_at, updated_at) VALUES (?,?,?,?,?,?,NOW(),NOW())'
        );
        $stmt->execute([$fullName, $email, $hash, $contact, $role, $status]);
        $id = (int) db()->lastInsertId();
        $_SESSION['user_id'] = $id;
        $_SESSION['user_name'] = $fullName;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = $role;
        $_SESSION['role'] = $role;
        log_activity($id, 'register', 'New account via API', $_SERVER['REMOTE_ADDR'] ?? null);
        $redirect = $role === 'seller' ? BASE_URL . 'register-business.php' : public_home_url();
        json_response(['success' => true, 'message' => 'Registered', 'data' => ['redirect' => $redirect]]);
    }

    if ($action === 'logout') {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        json_response(['success' => true, 'message' => 'Logged out', 'data' => ['redirect' => BASE_URL . 'index.php']]);
    }
}

json_response(['success' => false, 'message' => 'Invalid request', 'data' => []], 400);
