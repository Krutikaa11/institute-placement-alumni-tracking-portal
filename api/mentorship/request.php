<?php
/**
 * API: Submit Mentorship Request
 * POST: { alumni_id, message }
 */

require_once __DIR__ . '/../../config/auth.php';

$user = requireRole('student');
$pdo = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method Not Allowed', 405);
}

$data = getRequestData();
$alumniId = (int)($data['alumni_id'] ?? 0);
$message = trim($data['message'] ?? '');

if ($alumniId <= 0 || empty($message)) {
    sendError('Alumni ID and a personalized request message are required.');
}

$studentProfile = $user['student_profile'] ?? null;
if (!$studentProfile) {
    sendError('Student profile not found. Please complete your profile first.', 400);
}

$studentId = (int)$studentProfile['id'];

// Check alumni exists and is accepting mentorship
$aStmt = $pdo->prepare("SELECT a.*, u.name FROM alumni a JOIN users u ON a.user_id = u.id WHERE a.id = :aid LIMIT 1");
$aStmt->execute(['aid' => $alumniId]);
$alumni = $aStmt->fetch();

if (!$alumni) {
    sendError('Alumni not found.', 404);
}

if ((int)$alumni['available_for_mentorship'] !== 1) {
    sendError('This alumni is currently not accepting new mentorship requests.', 400);
}

// Check for existing pending request
$chk = $pdo->prepare("SELECT id FROM mentorship_requests WHERE student_id = :sid AND alumni_id = :aid AND status = 'Pending' LIMIT 1");
$chk->execute(['sid' => $studentId, 'aid' => $alumniId]);
if ($chk->fetch()) {
    sendError('You already have a pending mentorship request with this alumni.', 409);
}

$ins = $pdo->prepare("INSERT INTO mentorship_requests (student_id, alumni_id, message, status) VALUES (:sid, :aid, :msg, 'Pending')");
$ins->execute([
    'sid' => $studentId,
    'aid' => $alumniId,
    'msg' => $message
]);

$reqId = (int)$pdo->lastInsertId();

sendJson([
    'success' => true,
    'message' => 'Mentorship request sent successfully! You will be notified once the alumni responds.',
    'request_id' => $reqId
], 201);
