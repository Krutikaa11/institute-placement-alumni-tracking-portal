<?php
/**
 * API: Companies Resource (CRUD)
 */

require_once __DIR__ . '/../../config/auth.php';

$pdo = Database::getConnection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        $search = trim($_GET['search'] ?? '');

        if ($id) {
            $stmt = $pdo->prepare("SELECT c.*, (SELECT COUNT(*) FROM drives d WHERE d.company_id = c.id) AS active_drives_count FROM companies c WHERE c.id = :id LIMIT 1");
            $stmt->execute(['id' => $id]);
            $company = $stmt->fetch();
            if (!$company) {
                sendError('Company not found.', 404);
            }
            sendJson(['success' => true, 'data' => $company]);
        }

        $sql = "SELECT c.*, (SELECT COUNT(*) FROM drives d WHERE d.company_id = c.id) AS active_drives_count, (SELECT COUNT(*) FROM placements p WHERE p.company_id = c.id) AS placed_students_count FROM companies c WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (c.company_name LIKE :s OR c.industry LIKE :s OR c.location LIKE :s)";
            $params['s'] = "%{$search}%";
        }
        $sql .= " ORDER BY c.company_name ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $companies = $stmt->fetchAll();

        sendJson(['success' => true, 'data' => $companies, 'total' => count($companies)]);
        break;

    case 'POST':
        $user = requireRole(['admin', 'officer']);
        $data = getRequestData();

        $name = trim($data['company_name'] ?? '');
        $industry = trim($data['industry'] ?? '');
        $location = trim($data['location'] ?? '');
        $website = trim($data['website'] ?? '');
        $description = trim($data['description'] ?? '');

        if (empty($name)) {
            sendError('Company name is required.');
        }

        $stmt = $pdo->prepare("INSERT INTO companies (company_name, industry, location, website, description) VALUES (:name, :ind, :loc, :web, :descr)");
        $stmt->execute([
            'name' => $name,
            'ind' => $industry,
            'loc' => $location,
            'web' => $website,
            'descr' => $description
        ]);

        $newId = (int)$pdo->lastInsertId();
        sendJson(['success' => true, 'message' => 'Company added successfully.', 'id' => $newId], 201);
        break;

    case 'PUT':
        $user = requireRole(['admin', 'officer']);
        $data = getRequestData();

        $id = (int)($data['id'] ?? 0);
        $name = trim($data['company_name'] ?? '');
        $industry = trim($data['industry'] ?? '');
        $location = trim($data['location'] ?? '');
        $website = trim($data['website'] ?? '');
        $description = trim($data['description'] ?? '');

        if ($id <= 0 || empty($name)) {
            sendError('Valid Company ID and Name are required.');
        }

        $stmt = $pdo->prepare("UPDATE companies SET company_name = :name, industry = :ind, location = :loc, website = :web, description = :descr WHERE id = :id");
        $stmt->execute([
            'name' => $name,
            'ind' => $industry,
            'loc' => $location,
            'web' => $website,
            'descr' => $description,
            'id' => $id
        ]);

        sendJson(['success' => true, 'message' => 'Company updated successfully.']);
        break;

    case 'DELETE':
        $user = requireRole(['admin']);
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            sendError('Valid Company ID is required.');
        }

        $stmt = $pdo->prepare("DELETE FROM companies WHERE id = :id");
        $stmt->execute(['id' => $id]);

        sendJson(['success' => true, 'message' => 'Company deleted successfully.']);
        break;

    default:
        sendError('Method Not Allowed', 405);
}
