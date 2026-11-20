<?php
/**
 * API: Update Application Status
 * POST / PUT: { application_id, status, notes? }
 */

require_once __DIR__ . '/../../config/auth.php';

$user = requireRole(['admin', 'officer']);
$pdo = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
    sendError('Method Not Allowed', 405);
}

$data = getRequestData();
$appId = (int)($data['application_id'] ?? ($data['id'] ?? 0));
$status = trim($data['status'] ?? '');
$notes = trim($data['notes'] ?? '');

$allowedStatuses = ['Applied', 'Shortlisted', 'Interview', 'Selected', 'Rejected'];

if ($appId <= 0 || !in_array($status, $allowedStatuses, true)) {
    sendError('Valid application_id and status (Applied, Shortlisted, Interview, Selected, Rejected) are required.');
}

// Check application exists
$stmt = $pdo->prepare("SELECT a.*, d.company_id, d.package, d.job_role FROM applications a JOIN drives d ON a.drive_id = d.id WHERE a.id = :id LIMIT 1");
$stmt->execute(['id' => $appId]);
$app = $stmt->fetch();

if (!$app) {
    sendError('Application not found.', 404);
}

$pdo->beginTransaction();
try {
    $upd = $pdo->prepare("UPDATE applications SET status = :st, notes = :notes, updated_at = NOW() WHERE id = :id");
    $upd->execute([
        'st' => $status,
        'notes' => $notes ?: "Status updated to {$status} by " . $user['name'],
        'id' => $appId
    ]);

    // If marked Selected, check if already in placements
    if ($status === 'Selected') {
        $pCheck = $pdo->prepare("SELECT id FROM placements WHERE student_id = :sid AND company_id = :cid AND drive_id = :did LIMIT 1");
        $pCheck->execute([
            'sid' => $app['student_id'],
            'cid' => $app['company_id'],
            'did' => $app['drive_id']
        ]);
        if (!$pCheck->fetch()) {
            $pIns = $pdo->prepare("INSERT INTO placements (student_id, company_id, drive_id, package, placement_year) VALUES (:sid, :cid, :did, :pkg, :pyear)");
            $pIns->execute([
                'sid' => $app['student_id'],
                'cid' => $app['company_id'],
                'did' => $app['drive_id'],
                'pkg' => $app['package'],
                'pyear' => (int)date('Y')
            ]);
        }
    }

    $pdo->commit();

    sendJson([
        'success' => true,
        'message' => "Application status successfully updated to '{$status}'.",
        'application_id' => $appId,
        'status' => $status
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    sendError('Failed to update status: ' . $e->getMessage(), 500);
}
