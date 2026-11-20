<?php
/**
 * API: Detailed Eligibility Verification for Drive & Student
 * GET: ?drive_id=...&student_id=...
 */

require_once __DIR__ . '/../../config/auth.php';

$pdo = Database::getConnection();
$driveId = (int)($_GET['drive_id'] ?? 0);
$studentId = (int)($_GET['student_id'] ?? 0);

$user = getCurrentUser();
if ($user && $user['role'] === 'student' && !$studentId) {
    $studentId = (int)($user['student_profile']['id'] ?? 0);
}

if ($driveId <= 0 || $studentId <= 0) {
    sendError('Valid drive_id and student_id are required.');
}

$dStmt = $pdo->prepare("SELECT d.*, c.company_name FROM drives d JOIN companies c ON d.company_id = c.id WHERE d.id = :did LIMIT 1");
$dStmt->execute(['did' => $driveId]);
$drive = $dStmt->fetch();
if (!$drive) sendError('Drive not found.', 404);

$sStmt = $pdo->prepare("SELECT s.*, u.name, u.email FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = :sid LIMIT 1");
$sStmt->execute(['sid' => $studentId]);
$student = $sStmt->fetch();
if (!$student) sendError('Student not found.', 404);

// Criteria Breakdown
$checks = [];

// 1. CGPA Check
$cgpaPass = ((float)$student['cgpa'] >= (float)$drive['min_cgpa']);
$checks[] = [
    'criterion' => 'Minimum CGPA',
    'required' => (float)$drive['min_cgpa'],
    'actual' => (float)$student['cgpa'],
    'passed' => $cgpaPass,
    'message' => $cgpaPass ? 'CGPA requirement satisfied.' : "Your CGPA ({$student['cgpa']}) is below required minimum ({$drive['min_cgpa']})."
];

// 2. Branch Check
$eligibleBranches = array_map('trim', explode(',', $drive['eligible_branches']));
$branchPass = false;
foreach ($eligibleBranches as $eb) {
    if (strcasecmp($eb, 'All Branches') === 0 || stripos($student['branch'], $eb) !== false || stripos($eb, $student['branch']) !== false) {
        $branchPass = true;
        break;
    }
}
$checks[] = [
    'criterion' => 'Eligible Engineering Branches',
    'required' => $drive['eligible_branches'],
    'actual' => $student['branch'],
    'passed' => $branchPass,
    'message' => $branchPass ? 'Branch requirement satisfied.' : "Your branch ({$student['branch']}) is not listed in eligible branches."
];

// 3. Deadline Check
$deadlinePass = (strtotime($drive['deadline']) >= strtotime(date('Y-m-d')));
$checks[] = [
    'criterion' => 'Application Deadline',
    'required' => $drive['deadline'],
    'actual' => date('Y-m-d'),
    'passed' => $deadlinePass,
    'message' => $deadlinePass ? 'Application deadline is open.' : 'The deadline for this drive has elapsed.'
];

// 4. Drive Status Check
$statusPass = ($drive['status'] === 'active');
$checks[] = [
    'criterion' => 'Drive Status',
    'required' => 'active',
    'actual' => $drive['status'],
    'passed' => $statusPass,
    'message' => $statusPass ? 'Drive is actively accepting applications.' : "Drive status is {$drive['status']}."
];

// Check if already applied
$appStmt = $pdo->prepare("SELECT * FROM applications WHERE student_id = :sid AND drive_id = :did LIMIT 1");
$appStmt->execute(['sid' => $studentId, 'did' => $driveId]);
$existingApp = $appStmt->fetch();

$overallEligible = ($cgpaPass && $branchPass && $deadlinePass && $statusPass);

sendJson([
    'success' => true,
    'eligible' => $overallEligible,
    'already_applied' => ($existingApp !== false),
    'application' => $existingApp ?: null,
    'checks' => $checks,
    'drive' => [
        'id' => $drive['id'],
        'company_name' => $drive['company_name'],
        'job_role' => $drive['job_role'],
        'package' => $drive['package']
    ],
    'student' => [
        'id' => $student['id'],
        'name' => $student['name'],
        'branch' => $student['branch'],
        'cgpa' => $student['cgpa']
    ]
]);
