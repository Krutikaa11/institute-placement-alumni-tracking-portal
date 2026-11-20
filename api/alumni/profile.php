<?php
/**
 * API: Alumni Profile Management
 */

require_once __DIR__ . '/../../config/auth.php';

$user = requireLogin();
$pdo = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $alumniId = isset($_GET['id']) ? (int)$_GET['id'] : null;

    if ($user['role'] === 'alumni') {
        $alumniId = (int)($user['alumni_profile']['id'] ?? 0);
    }

    if (!$alumniId) sendError('Alumni ID is required.', 400);

    $stmt = $pdo->prepare("
        SELECT a.*, u.name, u.email, u.avatar,
               (SELECT COUNT(*) FROM mentorship_requests mr WHERE mr.alumni_id = a.id) AS total_requests_count,
               (SELECT COUNT(*) FROM mentorship_requests mr WHERE mr.alumni_id = a.id AND mr.status = 'Accepted') AS accepted_mentees_count
        FROM alumni a
        JOIN users u ON a.user_id = u.id
        WHERE a.id = :id LIMIT 1
    ");
    $stmt->execute(['id' => $alumniId]);
    $alum = $stmt->fetch();

    if (!$alum) sendError('Alumni profile not found.', 404);

    sendJson(['success' => true, 'data' => $alum]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = getRequestData();
    $targetAlumniId = ($user['role'] === 'alumni')
        ? (int)($user['alumni_profile']['id'] ?? 0)
        : (int)($data['id'] ?? 0);

    if ($targetAlumniId <= 0) sendError('Invalid alumni ID.', 400);

    $name = trim($data['name'] ?? '');
    $company = trim($data['company'] ?? '');
    $jobRole = trim($data['job_role'] ?? '');
    $location = trim($data['location'] ?? '');
    $skills = trim($data['skills'] ?? '');
    $linkedin = trim($data['linkedin'] ?? '');
    $bio = trim($data['bio'] ?? '');
    $available = isset($data['available_for_mentorship']) ? (int)$data['available_for_mentorship'] : 1;

    $aStmt = $pdo->prepare("SELECT user_id FROM alumni WHERE id = :aid LIMIT 1");
    $aStmt->execute(['aid' => $targetAlumniId]);
    $al = $aStmt->fetch();
    if (!$al) sendError('Alumni not found.', 404);

    $pdo->beginTransaction();
    try {
        if (!empty($name)) {
            $uStmt = $pdo->prepare("UPDATE users SET name = :n WHERE id = :uid");
            $uStmt->execute(['n' => $name, 'uid' => $al['user_id']]);
        }

        $upd = $pdo->prepare("
            UPDATE alumni SET company = :c, job_role = :jr, location = :loc,
                              skills = :sk, linkedin = :li, bio = :bio,
                              available_for_mentorship = :av
            WHERE id = :id
        ");
        $upd->execute([
            'c' => $company, 'jr' => $jobRole, 'loc' => $location,
            'sk' => $skills, 'li' => $linkedin, 'bio' => $bio,
            'av' => $available, 'id' => $targetAlumniId
        ]);

        $pdo->commit();
        sendJson(['success' => true, 'message' => 'Alumni profile updated successfully.']);
    } catch (Exception $e) {
        $pdo->rollBack();
        sendError('Failed to update alumni profile: ' . $e->getMessage(), 500);
    }
}

sendError('Method Not Allowed', 405);
