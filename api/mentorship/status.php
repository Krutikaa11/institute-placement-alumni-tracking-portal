<?php
/**
 * API: Update Mentorship Request Status (Accept / Reject / Complete)
 * POST / PUT: { request_id, status, response_message? }
 */

require_once __DIR__ . '/../../config/auth.php';

$user = requireRole(['alumni', 'admin']);
$pdo = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
    sendError('Method Not Allowed', 405);
}

$data = getRequestData();
$requestId = (int)($data['request_id'] ?? ($data['id'] ?? 0));
$status = trim($data['status'] ?? '');
$responseMessage = trim($data['response_message'] ?? '');

$validStatuses = ['Pending', 'Accepted', 'Rejected', 'Completed'];

if ($requestId <= 0 || !in_array($status, $validStatuses, true)) {
    sendError('Valid request_id and status (Pending, Accepted, Rejected, Completed) are required.');
}

// Fetch request
$stmt = $pdo->prepare("SELECT * FROM mentorship_requests WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $requestId]);
$req = $stmt->fetch();

if (!$req) {
    sendError('Mentorship request not found.', 404);
}

// If alumni, verify ownership
if ($user['role'] === 'alumni') {
    $alumniId = (int)($user['alumni_profile']['id'] ?? 0);
    if ((int)$req['alumni_id'] !== $alumniId) {
        sendError('You do not have permission to manage this mentorship request.', 403);
    }
}

$upd = $pdo->prepare("
    UPDATE mentorship_requests SET
        status = :st,
        response_message = :resp,
        updated_at = NOW()
    WHERE id = :id
");
$upd->execute([
    'st' => $status,
    'resp' => $responseMessage ?: "Status updated to {$status}.",
    'id' => $requestId
]);

sendJson([
    'success' => true,
    'message' => "Mentorship request status updated to '{$status}'.",
    'request_id' => $requestId,
    'status' => $status
]);
