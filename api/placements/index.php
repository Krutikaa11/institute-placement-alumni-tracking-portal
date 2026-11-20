<?php
/**
 * API: Placements Resource (CRUD)
 */

require_once __DIR__ . '/../../config/auth.php';

$pdo = Database::getConnection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $search = trim($_GET['search'] ?? '');
        $year = isset($_GET['year']) ? (int)$_GET['year'] : null;
        $branch = trim($_GET['branch'] ?? '');

        $sql = "
            SELECT p.*,
                   s.roll_no, s.branch, s.cgpa,
                   u.name AS student_name, u.email AS student_email,
                   c.company_name, c.industry, c.logo AS company_logo,
                   d.job_role
            FROM placements p
            JOIN students s ON p.student_id = s.id
            JOIN users u ON s.user_id = u.id
            JOIN companies c ON p.company_id = c.id
            LEFT JOIN drives d ON p.drive_id = d.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (u.name LIKE :s OR s.roll_no LIKE :s OR c.company_name LIKE :s OR d.job_role LIKE :s)";
            $params['s'] = "%{$search}%";
        }
        if ($year) {
            $sql .= " AND p.placement_year = :yr";
            $params['yr'] = $year;
        }
        if (!empty($branch) && strcasecmp($branch, 'all') !== 0) {
            $sql .= " AND s.branch LIKE :b";
            $params['b'] = "%{$branch}%";
        }

        $sql .= " ORDER BY p.placement_year DESC, p.package DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $placements = $stmt->fetchAll();

        sendJson(['success' => true, 'data' => $placements, 'total' => count($placements)]);
        break;

    case 'POST':
        $user = requireRole(['admin', 'officer']);
        $data = getRequestData();

        $studentId = (int)($data['student_id'] ?? 0);
        $companyId = (int)($data['company_id'] ?? 0);
        $driveId = isset($data['drive_id']) && (int)$data['drive_id'] > 0 ? (int)$data['drive_id'] : null;
        $package = (float)($data['package'] ?? 0);
        $placementYear = (int)($data['placement_year'] ?? date('Y'));
        $offerLetter = trim($data['offer_letter'] ?? '');

        if ($studentId <= 0 || $companyId <= 0 || $package <= 0) {
            sendError('Student, Company, and valid Salary Package are required.');
        }

        $stmt = $pdo->prepare("
            INSERT INTO placements (student_id, company_id, drive_id, package, placement_year, offer_letter)
            VALUES (:sid, :cid, :did, :pkg, :pyear, :letter)
        ");
        $stmt->execute([
            'sid' => $studentId,
            'cid' => $companyId,
            'did' => $driveId,
            'pkg' => $package,
            'pyear' => $placementYear,
            'letter' => $offerLetter
        ]);

        sendJson(['success' => true, 'message' => 'Placement recorded successfully.', 'id' => (int)$pdo->lastInsertId()], 201);
        break;

    case 'PUT':
        $user = requireRole(['admin', 'officer']);
        $data = getRequestData();

        $id = (int)($data['id'] ?? 0);
        $package = (float)($data['package'] ?? 0);
        $placementYear = (int)($data['placement_year'] ?? date('Y'));

        if ($id <= 0 || $package <= 0) {
            sendError('Valid Placement ID and Package are required.');
        }

        $stmt = $pdo->prepare("UPDATE placements SET package = :pkg, placement_year = :pyear WHERE id = :id");
        $stmt->execute(['pkg' => $package, 'pyear' => $placementYear, 'id' => $id]);

        sendJson(['success' => true, 'message' => 'Placement record updated successfully.']);
        break;

    case 'DELETE':
        $user = requireRole(['admin', 'officer']);
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) sendError('Valid placement ID is required.');

        $stmt = $pdo->prepare("DELETE FROM placements WHERE id = :id");
        $stmt->execute(['id' => $id]);

        sendJson(['success' => true, 'message' => 'Placement record deleted successfully.']);
        break;

    default:
        sendError('Method Not Allowed', 405);
}
