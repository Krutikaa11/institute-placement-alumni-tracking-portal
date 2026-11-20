<?php
/**
 * API: Students Management (CRUD for Admin/Officer)
 */

require_once __DIR__ . '/../../config/auth.php';

$pdo = Database::getConnection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $user = requireRole(['admin', 'officer']);
        $search = trim($_GET['search'] ?? '');
        $branch = trim($_GET['branch'] ?? '');
        $minCgpa = isset($_GET['min_cgpa']) ? (float)$_GET['min_cgpa'] : null;

        $sql = "
            SELECT s.*, u.name, u.email, u.is_active,
                   (SELECT COUNT(*) FROM applications a WHERE a.student_id = s.id) AS applications_count,
                   (SELECT COUNT(*) FROM placements p WHERE p.student_id = s.id) AS placements_count,
                   (SELECT MAX(p.package) FROM placements p WHERE p.student_id = s.id) AS highest_offer
            FROM students s
            JOIN users u ON s.user_id = u.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (u.name LIKE :s OR u.email LIKE :s OR s.roll_no LIKE :s OR s.skills LIKE :s)";
            $params['s'] = "%{$search}%";
        }
        if (!empty($branch) && strcasecmp($branch, 'all') !== 0) {
            $sql .= " AND s.branch LIKE :b";
            $params['b'] = "%{$branch}%";
        }
        if ($minCgpa !== null && $minCgpa > 0) {
            $sql .= " AND s.cgpa >= :cgpa";
            $params['cgpa'] = $minCgpa;
        }

        $sql .= " ORDER BY s.roll_no ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $students = $stmt->fetchAll();

        sendJson(['success' => true, 'data' => $students, 'total' => count($students)]);
        break;

    case 'POST':
        $user = requireRole(['admin', 'officer']);
        $data = getRequestData();

        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? 'Student@123');
        $rollNo = trim($data['roll_no'] ?? '');
        $branch = trim($data['branch'] ?? 'Computer Science');
        $year = (int)($data['year'] ?? 4);
        $cgpa = (float)($data['cgpa'] ?? 0.0);
        $phone = trim($data['phone'] ?? '');
        $skills = trim($data['skills'] ?? '');

        if (empty($name) || empty($email) || empty($rollNo)) {
            sendError('Name, Email, and Roll Number are required.');
        }

        $pdo->beginTransaction();
        try {
            $hPass = password_hash($password, PASSWORD_BCRYPT);
            $uStmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (:n, :e, :p, 'student')");
            $uStmt->execute(['n' => $name, 'e' => $email, 'p' => $hPass]);
            $uid = (int)$pdo->lastInsertId();

            $sStmt = $pdo->prepare("INSERT INTO students (user_id, roll_no, branch, year, cgpa, phone, skills) VALUES (:u, :r, :b, :y, :c, :ph, :sk)");
            $sStmt->execute(['u' => $uid, 'r' => $rollNo, 'b' => $branch, 'y' => $year, 'c' => $cgpa, 'ph' => $phone, 'sk' => $skills]);

            $pdo->commit();
            sendJson(['success' => true, 'message' => 'Student added successfully.'], 201);
        } catch (Exception $e) {
            $pdo->rollBack();
            sendError('Failed to add student: ' . $e->getMessage(), 500);
        }
        break;

    case 'PUT':
        $user = requireRole(['admin', 'officer']);
        $data = getRequestData();

        $id = (int)($data['id'] ?? 0);
        $name = trim($data['name'] ?? '');
        $rollNo = trim($data['roll_no'] ?? '');
        $branch = trim($data['branch'] ?? '');
        $year = (int)($data['year'] ?? 4);
        $cgpa = (float)($data['cgpa'] ?? 0.0);
        $phone = trim($data['phone'] ?? '');
        $skills = trim($data['skills'] ?? '');

        if ($id <= 0) sendError('Valid student ID is required.');

        $sStmt = $pdo->prepare("SELECT user_id FROM students WHERE id = :id LIMIT 1");
        $sStmt->execute(['id' => $id]);
        $st = $sStmt->fetch();
        if (!$st) sendError('Student not found.', 404);

        $pdo->beginTransaction();
        try {
            if (!empty($name)) {
                $uStmt = $pdo->prepare("UPDATE users SET name = :n WHERE id = :uid");
                $uStmt->execute(['n' => $name, 'uid' => $st['user_id']]);
            }
            $upd = $pdo->prepare("UPDATE students SET roll_no = :r, branch = :b, year = :y, cgpa = :c, phone = :ph, skills = :sk WHERE id = :id");
            $upd->execute(['r' => $rollNo, 'b' => $branch, 'y' => $year, 'c' => $cgpa, 'ph' => $phone, 'sk' => $skills, 'id' => $id]);

            $pdo->commit();
            sendJson(['success' => true, 'message' => 'Student updated successfully.']);
        } catch (Exception $e) {
            $pdo->rollBack();
            sendError('Failed to update student: ' . $e->getMessage(), 500);
        }
        break;

    case 'DELETE':
        $user = requireRole('admin');
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) sendError('Valid student ID is required.');

        $sStmt = $pdo->prepare("SELECT user_id FROM students WHERE id = :id LIMIT 1");
        $sStmt->execute(['id' => $id]);
        $st = $sStmt->fetch();
        if ($st) {
            $del = $pdo->prepare("DELETE FROM users WHERE id = :uid");
            $del->execute(['uid' => $st['user_id']]);
        }

        sendJson(['success' => true, 'message' => 'Student deleted successfully.']);
        break;

    default:
        sendError('Method Not Allowed', 405);
}
