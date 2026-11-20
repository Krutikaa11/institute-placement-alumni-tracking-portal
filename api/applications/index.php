<?php
/**
 * API: Applications Listing & Management
 */

require_once __DIR__ . '/../../config/auth.php';

$user = requireLogin();
$pdo = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $driveId = isset($_GET['drive_id']) ? (int)$_GET['drive_id'] : null;
    $status = trim($_GET['status'] ?? '');
    $search = trim($_GET['search'] ?? '');

    if ($user['role'] === 'student') {
        $studentId = (int)($user['student_profile']['id'] ?? 0);
        $sql = "
            SELECT a.*, d.job_role, d.package, d.location, d.drive_date, d.deadline,
                   c.company_name, c.logo, c.website
            FROM applications a
            JOIN drives d ON a.drive_id = d.id
            JOIN companies c ON d.company_id = c.id
            WHERE a.student_id = :sid
        ";
        $params = ['sid' => $studentId];

        if (!empty($status)) {
            $sql .= " AND a.status = :st";
            $params['st'] = $status;
        }

        $sql .= " ORDER BY a.applied_date DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $applications = $stmt->fetchAll();

        sendJson(['success' => true, 'data' => $applications, 'total' => count($applications)]);
    } else {
        // Officer or Admin view
        $sql = "
            SELECT a.*, d.job_role, d.package, d.drive_date,
                   c.company_name,
                   s.roll_no, s.branch, s.cgpa, s.phone, s.resume AS student_resume,
                   u.name AS student_name, u.email AS student_email
            FROM applications a
            JOIN drives d ON a.drive_id = d.id
            JOIN companies c ON d.company_id = c.id
            JOIN students s ON a.student_id = s.id
            JOIN users u ON s.user_id = u.id
            WHERE 1=1
        ";
        $params = [];

        if ($driveId) {
            $sql .= " AND a.drive_id = :did";
            $params['did'] = $driveId;
        }
        if (!empty($status)) {
            $sql .= " AND a.status = :st";
            $params['st'] = $status;
        }
        if (!empty($search)) {
            $sql .= " AND (u.name LIKE :s OR s.roll_no LIKE :s OR c.company_name LIKE :s OR d.job_role LIKE :s)";
            $params['s'] = "%{$search}%";
        }

        $sql .= " ORDER BY a.applied_date DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $applications = $stmt->fetchAll();

        sendJson(['success' => true, 'data' => $applications, 'total' => count($applications)]);
    }
}

sendError('Method Not Allowed', 405);
