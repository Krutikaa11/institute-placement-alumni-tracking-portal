<?php
/**
 * API: Mentorship Requests Listing
 */

require_once __DIR__ . '/../../config/auth.php';

$user = requireLogin();
$pdo = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $status = trim($_GET['status'] ?? '');

    if ($user['role'] === 'student') {
        $studentId = (int)($user['student_profile']['id'] ?? 0);
        $sql = "
            SELECT mr.*, a.company, a.job_role, a.graduation_year, a.linkedin,
                   u.name AS alumni_name, u.email AS alumni_email
            FROM mentorship_requests mr
            JOIN alumni a ON mr.alumni_id = a.id
            JOIN users u ON a.user_id = u.id
            WHERE mr.student_id = :sid
        ";
        $params = ['sid' => $studentId];

        if (!empty($status)) {
            $sql .= " AND mr.status = :st";
            $params['st'] = $status;
        }

        $sql .= " ORDER BY mr.request_date DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $requests = $stmt->fetchAll();

        sendJson(['success' => true, 'data' => $requests, 'total' => count($requests)]);
    } elseif ($user['role'] === 'alumni') {
        $alumniId = (int)($user['alumni_profile']['id'] ?? 0);
        $sql = "
            SELECT mr.*, s.roll_no, s.branch, s.cgpa, s.phone, s.resume,
                   u.name AS student_name, u.email AS student_email
            FROM mentorship_requests mr
            JOIN students s ON mr.student_id = s.id
            JOIN users u ON s.user_id = u.id
            WHERE mr.alumni_id = :aid
        ";
        $params = ['aid' => $alumniId];

        if (!empty($status)) {
            $sql .= " AND mr.status = :st";
            $params['st'] = $status;
        }

        $sql .= " ORDER BY mr.request_date DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $requests = $stmt->fetchAll();

        sendJson(['success' => true, 'data' => $requests, 'total' => count($requests)]);
    } else {
        // Admin or Officer view
        $sql = "
            SELECT mr.*,
                   su.name AS student_name, s.roll_no, s.branch,
                   au.name AS alumni_name, a.company, a.job_role
            FROM mentorship_requests mr
            JOIN students s ON mr.student_id = s.id
            JOIN users su ON s.user_id = su.id
            JOIN alumni a ON mr.alumni_id = a.id
            JOIN users au ON a.user_id = au.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($status)) {
            $sql .= " AND mr.status = :st";
            $params['st'] = $status;
        }

        $sql .= " ORDER BY mr.request_date DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $requests = $stmt->fetchAll();

        sendJson(['success' => true, 'data' => $requests, 'total' => count($requests)]);
    }
}

sendError('Method Not Allowed', 405);
