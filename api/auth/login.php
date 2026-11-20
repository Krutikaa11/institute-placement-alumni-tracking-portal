<?php
/**
 * API: User Login
 * POST: { email, password, role? }
 */

require_once __DIR__ . '/../../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method Not Allowed', 405);
}

$data = getRequestData();
$email = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');
$requestedRole = trim($data['role'] ?? '');

if (empty($email) || empty($password)) {
    sendError('Email and password are required.');
}

$pdo = Database::getConnection();
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    sendError('Invalid email or password.', 401);
}

if ((int)$user['is_active'] !== 1) {
    sendError('Your account has been deactivated. Please contact the administrator.', 403);
}

// If user specifically requested a role (e.g. from role-specific login selector), check it
if (!empty($requestedRole) && $requestedRole !== $user['role']) {
    sendError("Account exists, but role mismatch. Registered as: " . ucfirst($user['role']), 403);
}

// Session regeneration for security
session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_role'] = $user['role'];

// Fetch profile info
$profile = null;
if ($user['role'] === 'student') {
    $pStmt = $pdo->prepare("SELECT * FROM students WHERE user_id = :uid LIMIT 1");
    $pStmt->execute(['uid' => $user['id']]);
    $profile = $pStmt->fetch();
} elseif ($user['role'] === 'alumni') {
    $pStmt = $pdo->prepare("SELECT * FROM alumni WHERE user_id = :uid LIMIT 1");
    $pStmt->execute(['uid' => $user['id']]);
    $profile = $pStmt->fetch();
}

unset($user['password']);

sendJson([
    'success' => true,
    'message' => 'Login successful.',
    'user' => [
        'id' => (int)$user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
        'avatar' => $user['avatar'],
        'profile' => $profile
    ]
]);
