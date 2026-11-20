<?php
/**
 * API: Alumni Resource & Directory (Public Directory + Admin CRUD)
 */

require_once __DIR__ . '/../../config/auth.php';

$pdo = Database::getConnection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        $search = trim($_GET['search'] ?? '');
        $gradYear = isset($_GET['graduation_year']) && !empty($_GET['graduation_year']) ? (int)$_GET['graduation_year'] : null;
        $branch = trim($_GET['branch'] ?? '');
        $company = trim($_GET['company'] ?? '');
        $skills = trim($_GET['skills'] ?? '');
        $mentorshipOnly = isset($_GET['mentorship_only']) && $_GET['mentorship_only'] === 'true';

        if ($id) {
            $stmt = $pdo->prepare("
                SELECT a.*, u.name, u.email, u.avatar,
                       (SELECT COUNT(*) FROM mentorship_requests mr WHERE mr.alumni_id = a.id) AS total_mentees
                FROM alumni a
                JOIN users u ON a.user_id = u.id
                WHERE a.id = :id LIMIT 1
            ");
            $stmt->execute(['id' => $id]);
            $alum = $stmt->fetch();
            if (!$alum) sendError('Alumni not found.', 404);
            sendJson(['success' => true, 'data' => $alum]);
        }

        $sql = "
            SELECT a.*, u.name, u.email, u.avatar,
                   (SELECT COUNT(*) FROM mentorship_requests mr WHERE mr.alumni_id = a.id AND mr.status = 'Accepted') AS active_mentees_count
            FROM alumni a
            JOIN users u ON a.user_id = u.id
            WHERE u.is_active = 1
        ";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (u.name LIKE :s OR a.company LIKE :s OR a.job_role LIKE :s OR a.skills LIKE :s OR a.location LIKE :s)";
            $params['s'] = "%{$search}%";
        }
        if ($gradYear) {
            $sql .= " AND a.graduation_year = :gy";
            $params['gy'] = $gradYear;
        }
        if (!empty($branch) && strcasecmp($branch, 'all') !== 0) {
            $sql .= " AND a.branch LIKE :b";
            $params['b'] = "%{$branch}%";
        }
        if (!empty($company)) {
            $sql .= " AND a.company LIKE :comp";
            $params['comp'] = "%{$company}%";
        }
        if (!empty($skills)) {
            $sql .= " AND a.skills LIKE :sk";
            $params['sk'] = "%{$skills}%";
        }
        if ($mentorshipOnly) {
            $sql .= " AND a.available_for_mentorship = 1";
        }

        $sql .= " ORDER BY a.graduation_year DESC, u.name ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $alumni = $stmt->fetchAll();

        sendJson(['success' => true, 'data' => $alumni, 'total' => count($alumni)]);
        break;

    case 'POST':
        $user = requireRole('admin');
        $data = getRequestData();

        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? 'Alumni@123');
        $gradYear = (int)($data['graduation_year'] ?? date('Y'));
        $branch = trim($data['branch'] ?? 'Computer Science');
        $company = trim($data['company'] ?? '');
        $jobRole = trim($data['job_role'] ?? '');
        $location = trim($data['location'] ?? '');
        $skills = trim($data['skills'] ?? '');
        $linkedin = trim($data['linkedin'] ?? '');
        $bio = trim($data['bio'] ?? '');

        if (empty($name) || empty($email)) {
            sendError('Name and Email are required.');
        }

        $pdo->beginTransaction();
        try {
            $hPass = password_hash($password, PASSWORD_BCRYPT);
            $uStmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (:n, :e, :p, 'alumni')");
            $uStmt->execute(['n' => $name, 'e' => $email, 'p' => $hPass]);
            $uid = (int)$pdo->lastInsertId();

            $aStmt = $pdo->prepare("
                INSERT INTO alumni (user_id, graduation_year, branch, company, job_role, location, skills, linkedin, bio)
                VALUES (:u, :gy, :b, :c, :jr, :loc, :sk, :li, :bio)
            ");
            $aStmt->execute([
                'u' => $uid, 'gy' => $gradYear, 'b' => $branch, 'c' => $company,
                'jr' => $jobRole, 'loc' => $location, 'sk' => $skills, 'li' => $linkedin, 'bio' => $bio
            ]);

            $pdo->commit();
            sendJson(['success' => true, 'message' => 'Alumni profile created successfully.'], 201);
        } catch (Exception $e) {
            $pdo->rollBack();
            sendError('Failed to create alumni: ' . $e->getMessage(), 500);
        }
        break;

    case 'PUT':
        $user = requireRole('admin');
        $data = getRequestData();

        $id = (int)($data['id'] ?? 0);
        $name = trim($data['name'] ?? '');
        $gradYear = (int)($data['graduation_year'] ?? date('Y'));
        $branch = trim($data['branch'] ?? '');
        $company = trim($data['company'] ?? '');
        $jobRole = trim($data['job_role'] ?? '');
        $location = trim($data['location'] ?? '');
        $skills = trim($data['skills'] ?? '');
        $linkedin = trim($data['linkedin'] ?? '');
        $bio = trim($data['bio'] ?? '');

        if ($id <= 0) sendError('Valid alumni ID is required.');

        $aStmt = $pdo->prepare("SELECT user_id FROM alumni WHERE id = :id LIMIT 1");
        $aStmt->execute(['id' => $id]);
        $al = $aStmt->fetch();
        if (!$al) sendError('Alumni not found.', 404);

        $pdo->beginTransaction();
        try {
            if (!empty($name)) {
                $uStmt = $pdo->prepare("UPDATE users SET name = :n WHERE id = :uid");
                $uStmt->execute(['n' => $name, 'uid' => $al['user_id']]);
            }

            $upd = $pdo->prepare("
                UPDATE alumni SET graduation_year = :gy, branch = :b, company = :c, job_role = :jr,
                                  location = :loc, skills = :sk, linkedin = :li, bio = :bio
                WHERE id = :id
            ");
            $upd->execute([
                'gy' => $gradYear, 'b' => $branch, 'c' => $company, 'jr' => $jobRole,
                'loc' => $location, 'sk' => $skills, 'li' => $linkedin, 'bio' => $bio, 'id' => $id
            ]);

            $pdo->commit();
            sendJson(['success' => true, 'message' => 'Alumni record updated successfully.']);
        } catch (Exception $e) {
            $pdo->rollBack();
            sendError('Failed to update alumni: ' . $e->getMessage(), 500);
        }
        break;

    case 'DELETE':
        $user = requireRole('admin');
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) sendError('Valid alumni ID is required.');

        $aStmt = $pdo->prepare("SELECT user_id FROM alumni WHERE id = :id LIMIT 1");
        $aStmt->execute(['id' => $id]);
        $al = $aStmt->fetch();
        if ($al) {
            $del = $pdo->prepare("DELETE FROM users WHERE id = :uid");
            $del->execute(['uid' => $al['user_id']]);
        }

        sendJson(['success' => true, 'message' => 'Alumni record deleted successfully.']);
        break;

    default:
        sendError('Method Not Allowed', 405);
}
