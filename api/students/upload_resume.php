<?php
/**
 * API: Resume Upload Endpoint
 * POST: file upload (multipart/form-data)
 */

require_once __DIR__ . '/../../config/auth.php';

$user = requireLogin();
$pdo = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method Not Allowed', 405);
}

$studentId = 0;
if ($user['role'] === 'student') {
    $studentId = (int)($user['student_profile']['id'] ?? 0);
} elseif (in_array($user['role'], ['admin', 'officer'])) {
    $studentId = (int)($_POST['student_id'] ?? 0);
}

if ($studentId <= 0) {
    sendError('Student profile not found.', 400);
}

if (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
    $errCode = $_FILES['resume']['error'] ?? 'None';
    sendError("No file uploaded or upload error code: {$errCode}", 400);
}

$file = $_FILES['resume'];
$allowedExt = ['pdf', 'doc', 'docx'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExt, true)) {
    sendError('Invalid file format. Only PDF, DOC, and DOCX files are allowed.', 422);
}

if ($file['size'] > MAX_UPLOAD_SIZE) {
    sendError('File size exceeds the 5MB limit.', 422);
}

if (!is_dir(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0777, true);
}

$filename = 'resume_' . $studentId . '_' . time() . '.' . $ext;
$dest = UPLOAD_PATH . '/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    sendError('Failed to save file on server.', 500);
}

$stmt = $pdo->prepare("UPDATE students SET resume = :r WHERE id = :id");
$stmt->execute(['r' => $filename, 'id' => $studentId]);

sendJson([
    'success' => true,
    'message' => 'Resume uploaded successfully!',
    'filename' => $filename,
    'path' => 'uploads/resumes/' . $filename
]);
