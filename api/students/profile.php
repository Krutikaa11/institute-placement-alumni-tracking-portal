<?php
/**
 * API: Student Profile (Get & Update)
 */

require_once __DIR__ . '/../../config/auth.php';

$user = requireLogin();
$pdo = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $studentId = isset($_GET['id']) ? (int)$_GET['id'] : null;

    if ($user['role'] === 'student') {
        $studentId = (int)($user['student_profile']['id'] ?? 0);
    }

    if (!$studentId) {
        sendError('Student ID is required.', 400);
    }

    $stmt = $pdo->prepare("
        SELECT s.*, u.name, u.email, u.avatar,
               (SELECT COUNT(*) FROM applications a WHERE a.student_id = s.id) AS applications_count,
               (SELECT COUNT(*) FROM placements p WHERE p.student_id = s.id) AS placements_count
        FROM students s
        JOIN users u ON s.user_id = u.id
        WHERE s.id = :id LIMIT 1
    ");
    $stmt->execute(['id' => $studentId]);
    $student = $stmt->fetch();

    if (!$student) {
        sendError('Student profile not found.', 404);
    }

    // Profile completion calculation
    $fields = ['roll_no', 'branch', 'year', 'cgpa', 'phone', 'resume', 'skills'];
    $filled = 0;
    foreach ($fields as $f) {
        if (!empty($student[$f])) $filled++;
    }
    $student['profile_completion'] = round(($filled / count($fields)) * 100);

    sendJson(['success' => true, 'data' => $student]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = getRequestData();
    $targetStudentId = ($user['role'] === 'student')
        ? (int)($user['student_profile']['id'] ?? 0)
        : (int)($data['id'] ?? 0);

    if ($targetStudentId <= 0) {
        sendError('Invalid student ID.', 400);
    }

    $name = trim($data['name'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $skills = trim($data['skills'] ?? '');
    $branch = trim($data['branch'] ?? '');
    $cgpa = isset($data['cgpa']) ? (float)$data['cgpa'] : null;

    $sStmt = $pdo->prepare("SELECT user_id FROM students WHERE id = :sid LIMIT 1");
    $sStmt->execute(['sid' => $targetStudentId]);
    $st = $sStmt->fetch();

    if (!$st) sendError('Student not found.', 404);

    $pdo->beginTransaction();
    try {
        if (!empty($name)) {
            $uStmt = $pdo->prepare("UPDATE users SET name = :n WHERE id = :uid");
            $uStmt->execute(['n' => $name, 'uid' => $st['user_id']]);
        }

        $sql = "UPDATE students SET phone = :ph, skills = :sk";
        $params = ['ph' => $phone, 'sk' => $skills, 'sid' => $targetStudentId];

        if (!empty($branch)) {
            $sql .= ", branch = :br";
            $params['br'] = $branch;
        }
        if ($cgpa !== null && $cgpa > 0) {
            $sql .= ", cgpa = :cgpa";
            $params['cgpa'] = $cgpa;
        }

        $sql .= " WHERE id = :sid";
        $upd = $pdo->prepare($sql);
        $upd->execute($params);

        $pdo->commit();
        sendJson(['success' => true, 'message' => 'Profile updated successfully.']);
    } catch (Exception $e) {
        $pdo->rollBack();
        sendError('Failed to update profile: ' . $e->getMessage(), 500);
    }
}

sendError('Method Not Allowed', 405);
