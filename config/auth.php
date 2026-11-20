<?php
/**
 * Authentication and API Helper Utility
 */

require_once __DIR__ . '/database.php';

function sendJson($data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function sendError(string $message, int $statusCode = 400, array $errors = []): void {
    $response = [
        'success' => false,
        'message' => $message
    ];
    if (!empty($errors)) {
        $response['errors'] = $errors;
    }
    sendJson($response, $statusCode);
}

function getRequestData(): array {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        return is_array($json) ? $json : [];
    }
    return $_POST;
}

function getCurrentUser(): ?array {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT id, name, email, role, avatar, is_active FROM users WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user || (int)$user['is_active'] !== 1) {
        unset($_SESSION['user_id'], $_SESSION['user_role']);
        return null;
    }

    // Attach role-specific IDs and profiles
    if ($user['role'] === 'student') {
        $stStmt = $pdo->prepare("SELECT * FROM students WHERE user_id = :uid LIMIT 1");
        $stStmt->execute(['uid' => $user['id']]);
        $user['student_profile'] = $stStmt->fetch() ?: null;
    } elseif ($user['role'] === 'alumni') {
        $alStmt = $pdo->prepare("SELECT * FROM alumni WHERE user_id = :uid LIMIT 1");
        $alStmt->execute(['uid' => $user['id']]);
        $user['alumni_profile'] = $alStmt->fetch() ?: null;
    }

    return $user;
}

function requireLogin(): array {
    $user = getCurrentUser();
    if (!$user) {
        sendError('Unauthorized. Please log in.', 401);
    }
    return $user;
}

function requireRole($roles): array {
    $user = requireLogin();
    $allowed = is_array($roles) ? $roles : [$roles];
    if (!in_array($user['role'], $allowed, true)) {
        sendError('Forbidden. Insufficient permissions for role: ' . $user['role'], 403);
    }
    return $user;
}

function sanitizeString(?string $str): string {
    if ($str === null) return '';
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}
