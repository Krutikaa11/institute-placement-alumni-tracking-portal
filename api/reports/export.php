<?php
/**
 * API: Report Generation & Export
 * GET: ?type=placements|students|drives|applications&format=json|xml|csv
 */

require_once __DIR__ . '/../../config/auth.php';

$user = requireRole(['admin', 'officer']);
$pdo = Database::getConnection();

$type = trim($_GET['type'] ?? 'placements');
$format = strtolower(trim($_GET['format'] ?? 'csv'));

$filename = "report_{$type}_" . date('Ymd_His');

$data = [];
$columns = [];

switch ($type) {
    case 'placements':
        $stmt = $pdo->query("
            SELECT p.id, u.name AS student_name, s.roll_no, s.branch,
                   c.company_name, p.package, p.placement_year, p.created_at
            FROM placements p
            JOIN students s ON p.student_id = s.id
            JOIN users u ON s.user_id = u.id
            JOIN companies c ON p.company_id = c.id
            ORDER BY p.placement_year DESC, p.package DESC
        ");
        $data = $stmt->fetchAll();
        $columns = ['id', 'student_name', 'roll_no', 'branch', 'company_name', 'package', 'placement_year', 'created_at'];
        break;

    case 'students':
        $stmt = $pdo->query("
            SELECT s.id, u.name, u.email, s.roll_no, s.branch, s.year, s.cgpa, s.phone, s.skills
            FROM students s
            JOIN users u ON s.user_id = u.id
            ORDER BY s.roll_no ASC
        ");
        $data = $stmt->fetchAll();
        $columns = ['id', 'name', 'email', 'roll_no', 'branch', 'year', 'cgpa', 'phone', 'skills'];
        break;

    case 'drives':
        $stmt = $pdo->query("
            SELECT d.id, c.company_name, d.job_role, d.package, d.location, d.drive_date,
                   d.deadline, d.min_cgpa, d.eligible_branches, d.vacancies, d.status
            FROM drives d
            JOIN companies c ON d.company_id = c.id
            ORDER BY d.drive_date DESC
        ");
        $data = $stmt->fetchAll();
        $columns = ['id', 'company_name', 'job_role', 'package', 'location', 'drive_date', 'deadline', 'min_cgpa', 'eligible_branches', 'vacancies', 'status'];
        break;

    case 'applications':
        $stmt = $pdo->query("
            SELECT a.id, u.name AS student_name, s.roll_no, s.branch, s.cgpa,
                   c.company_name, d.job_role, a.applied_date, a.status, a.notes
            FROM applications a
            JOIN students s ON a.student_id = s.id
            JOIN users u ON s.user_id = u.id
            JOIN drives d ON a.drive_id = d.id
            JOIN companies c ON d.company_id = c.id
            ORDER BY a.applied_date DESC
        ");
        $data = $stmt->fetchAll();
        $columns = ['id', 'student_name', 'roll_no', 'branch', 'cgpa', 'company_name', 'job_role', 'applied_date', 'status', 'notes'];
        break;

    default:
        sendError("Invalid report type: {$type}. Supported: placements, students, drives, applications");
}

if ($format === 'json') {
    header('Content-Type: application/json; charset=utf-8');
    header("Content-Disposition: attachment; filename=\"{$filename}.json\"");
    echo json_encode([
        'report_type' => $type,
        'generated_at' => date('Y-m-d H:i:s'),
        'total_records' => count($data),
        'records' => $data
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

if ($format === 'xml') {
    header('Content-Type: application/xml; charset=utf-8');
    header("Content-Disposition: attachment; filename=\"{$filename}.xml\"");

    $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><report></report>");
    $xml->addAttribute('type', $type);
    $xml->addAttribute('generated_at', date('Y-m-d H:i:s'));
    $xml->addAttribute('total_records', (string)count($data));

    foreach ($data as $row) {
        $item = $xml->addChild('record');
        foreach ($row as $key => $val) {
            $item->addChild($key, htmlspecialchars((string)$val));
        }
    }

    echo $xml->asXML();
    exit;
}

// Default format: CSV
header('Content-Type: text/csv; charset=utf-8');
header("Content-Disposition: attachment; filename=\"{$filename}.csv\"");
$out = fopen('php://output', 'w');
fputcsv($out, $columns);
foreach ($data as $row) {
    fputcsv($out, array_values($row));
}
fclose($out);
exit;
