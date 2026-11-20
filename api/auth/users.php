<?php
/**
 * API: System Users Management (Admin Only)
 */

require_once __DIR__ . '/../../config/auth.php';

$user = requireRole('admin');
$pdo = Database::getConnection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $role = trim($_GET['role'] ?? '');
        $search = trim($_GET['search'] ?? '');

        $sql = "SELECT id, name, email, role, avatar, is_active, created_at FROM users WHERE 1=1";
        $params = [];

        if (!empty($role) && strcasecmp($role, 'all') !== 0) {
            $sql .= " AND role = :r";
            $params['r'] = $role;
        }
        if (!empty($search)) {
            $sql .= " AND (name LIKE :s OR email LIKE :s)";
            $params['s'] = "%{$search}%";
        }

        $sql .= " ORDER BY id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll();

        sendJson(['success' => true, 'data' => $users, 'total' => count($users)]);
        break;

    case 'PUT':
        $data = getRequestData();
        $targetId = (int)($data['id'] ?? 0);
        $action = trim($data['action'] ?? '');

        if ($targetId <= 0) sendError('Valid user ID is required.');

        if ($action === 'toggle_status') {
            $stmt = $pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE id = :id");
            $stmt->execute(['id' => $targetId]);
            sendJson(['success' => true, 'message' => 'User status toggled successfully.']);
        } elseif ($action === 'reset_password') {
            $newPass = trim($data['password'] ?? 'Welcome@123');
            $hPass = password_hash($newPass, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET password = :p WHERE id = :id");
            $stmt->execute(['p' => $hPass, 'id' => $targetId]);
            sendJson(['success' => true, 'message' => "Password reset successfully to '{$newPass}'."]);
        } elseif ($action === 'change_role') {
            $newRole = trim($data['role'] ?? '');
            if (!in_array($newRole, ['admin', 'officer', 'student', 'alumni'], true)) {
                sendError('Invalid role.');
            }
            $stmt = $pdo->prepare("UPDATE users SET role = :r WHERE id = :id");
            $stmt->execute(['r' => $newRole, 'id' => $targetId]);
            sendJson(['success' => true, 'message' => "User role changed to '{$newRole}'."]);
        }

        sendError('Invalid action specified.');
        break;

    case 'DELETE':
        $targetId = (int)($_GET['id'] ?? 0);
        if ($targetId <= 0) sendError('Valid user ID is required.');
        if ($targetId === (int)$user['id']) sendError('You cannot delete your own admin account.', 400);

        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => $targetId]);

        sendJson(['success' => true, 'message' => 'User deleted successfully.']);
        break;

    default:
        sendError('Method Not Allowed', 405);
}
