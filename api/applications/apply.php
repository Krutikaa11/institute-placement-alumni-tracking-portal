<?php
/**
 * API: Student Drive Application Submission
 * POST: { drive_id, notes?, resume_file? }
 */

require_once __DIR__ . '/../../config/auth.php';

$user = requireRole('student');
$pdo = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method Not Allowed', 405);
}

$studentProfile = $user['student_profile'] ?? null;
if (!$studentProfile) {
    sendError('Student profile not found. Please complete your profile first.', 400);
}

$studentId = (int)$studentProfile['id'];
$driveId = (int)($_POST['drive_id'] ?? ($_GET['drive_id'] ?? 0));
$notes = trim($_POST['notes'] ?? '');

if ($driveId <= 0) {
    $data = getRequestData();
    $driveId = (int)($data['drive_id'] ?? 0);
    $notes = trim($data['notes'] ?? '');
}

if ($driveId <= 0) {
    sendError('Valid drive_id is required.');
}

// 1. Fetch Drive
$dStmt = $pdo->prepare("SELECT * FROM drives WHERE id = :did LIMIT 1");
$dStmt->execute(['did' => $driveId]);
$drive = $dStmt->fetch();

if (!$drive) {
    sendError('Placement drive not found.', 404);
}

if ($drive['status'] !== 'active') {
    sendError('This placement drive is currently not accepting applications.', 400);
}

if (strtotime($drive['deadline']) < strtotime(date('Y-m-d'))) {
    sendError('Application deadline for this drive has elapsed.', 400);
}

// 2. Prevent Duplicate Application
$chkStmt = $pdo->prepare("SELECT id, status, applied_date FROM applications WHERE student_id = :sid AND drive_id = :did LIMIT 1");
$chkStmt->execute(['sid' => $studentId, 'did' => $driveId]);
$existing = $chkStmt->fetch();
if ($existing) {
    sendError("You have already applied for this drive on {$existing['applied_date']}. Current status: {$existing['status']}", 409);
}

// 3. Eligibility Verification
if ((float)$studentProfile['cgpa'] < (float)$drive['min_cgpa']) {
    sendError("Your CGPA ({$studentProfile['cgpa']}) is below the required minimum of {$drive['min_cgpa']}.", 403);
}

$eligibleBranches = array_map('trim', explode(',', $drive['eligible_branches']));
$branchMatch = false;
foreach ($eligibleBranches as $eb) {
    if (strcasecmp($eb, 'All Branches') === 0 || stripos($studentProfile['branch'], $eb) !== false || stripos($eb, $studentProfile['branch']) !== false) {
        $branchMatch = true;
        break;
    }
}
if (!$branchMatch) {
    sendError("Your branch ({$studentProfile['branch']}) is not eligible for this drive ({$drive['eligible_branches']}).", 403);
}

// 4. Handle Resume (Uploaded or Profile Resume)
$resumePath = $studentProfile['resume'] ? 'uploads/resumes/' . $studentProfile['resume'] : null;

if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['resume'];
    $allowedMime = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    $allowedExt = ['pdf', 'doc', 'docx'];

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($ext, $allowedExt, true) || !in_array($mime, $allowedMime, true)) {
        sendError('Invalid resume file format. Allowed formats: PDF, DOC, DOCX.', 422);
    }

    if ($file['size'] > MAX_UPLOAD_SIZE) {
        sendError('Resume file size exceeds maximum limit of 5MB.', 422);
    }

    $filename = 'resume_' . $studentId . '_' . time() . '.' . $ext;
    $target = UPLOAD_PATH . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        sendError('Failed to save uploaded resume file.', 500);
    }

    $resumePath = 'uploads/resumes/' . $filename;

    // Also update student profile default resume
    $updRes = $pdo->prepare("UPDATE students SET resume = :res WHERE id = :sid");
    $updRes->execute(['res' => $filename, 'sid' => $studentId]);
}

// 5. Create Application
$stmt = $pdo->prepare("
    INSERT INTO applications (student_id, drive_id, applied_date, status, notes, resume_path)
    VALUES (:sid, :did, NOW(), 'Applied', :notes, :rpath)
");
$stmt->execute([
    'sid' => $studentId,
    'did' => $driveId,
    'notes' => $notes ?: 'Application submitted via student portal.',
    'rpath' => $resumePath
]);

$appId = (int)$pdo->lastInsertId();

sendJson([
    'success' => true,
    'message' => 'Your application has been submitted successfully!',
    'application_id' => $appId,
    'drive_id' => $driveId,
    'status' => 'Applied'
], 201);
