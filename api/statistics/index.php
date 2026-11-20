<?php
/**
 * API: Placement Statistics & Analytics
 * GET: Aggregates overall KPIs, charts data by year, branch, company, package ranges, and placed vs unplaced.
 */

require_once __DIR__ . '/../../config/auth.php';

$pdo = Database::getConnection();

// 1. Core KPIs
$totalStudents = (int)$pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$placedStudents = (int)$pdo->query("SELECT COUNT(DISTINCT student_id) FROM placements")->fetchColumn();
$unplacedStudents = max(0, $totalStudents - $placedStudents);
$placementPercentage = ($totalStudents > 0) ? round(($placedStudents / $totalStudents) * 100, 1) : 0;

$totalCompanies = (int)$pdo->query("SELECT COUNT(*) FROM companies")->fetchColumn();
$activeDrives = (int)$pdo->query("SELECT COUNT(*) FROM drives WHERE status = 'active'")->fetchColumn();
$totalAlumni = (int)$pdo->query("SELECT COUNT(*) FROM alumni")->fetchColumn();

// Salary Stats
$salaryStats = $pdo->query("
    SELECT
        COALESCE(MAX(package), 0) AS highest_package,
        COALESCE(ROUND(AVG(package), 2), 0) AS average_package,
        COALESCE(MIN(package), 0) AS lowest_package
    FROM placements
")->fetch();

// 2. Placements by Year (e.g. 2023, 2024, 2025, 2026)
$byYearStmt = $pdo->query("
    SELECT placement_year, COUNT(*) AS count, ROUND(AVG(package), 2) AS avg_package, MAX(package) AS max_package
    FROM placements
    GROUP BY placement_year
    ORDER BY placement_year ASC
");
$placementsByYear = $byYearStmt->fetchAll();

// 3. Placements by Branch
$byBranchStmt = $pdo->query("
    SELECT s.branch,
           COUNT(DISTINCT s.id) AS total_students,
           COUNT(DISTINCT p.student_id) AS placed_students,
           ROUND((COUNT(DISTINCT p.student_id) / COUNT(DISTINCT s.id)) * 100, 1) AS placement_pct
    FROM students s
    LEFT JOIN placements p ON s.id = p.student_id
    GROUP BY s.branch
    ORDER BY placed_students DESC
");
$placementsByBranch = $byBranchStmt->fetchAll();

// 4. Company-wise Placements
$byCompanyStmt = $pdo->query("
    SELECT c.company_name, COUNT(p.id) AS placed_count, MAX(p.package) AS top_package
    FROM placements p
    JOIN companies c ON p.company_id = c.id
    GROUP BY c.id, c.company_name
    ORDER BY placed_count DESC, top_package DESC
    LIMIT 10
");
$companyPlacements = $byCompanyStmt->fetchAll();

// 5. Package Range Distribution
$ranges = [
    'Below 6 LPA' => 0,
    '6 - 10 LPA' => 0,
    '10 - 15 LPA' => 0,
    '15 - 20 LPA' => 0,
    'Above 20 LPA' => 0
];
$allPackagesStmt = $pdo->query("SELECT package FROM placements");
while ($row = $allPackagesStmt->fetch()) {
    $pkg = (float)$row['package'];
    if ($pkg < 6) {
        $ranges['Below 6 LPA']++;
    } elseif ($pkg <= 10) {
        $ranges['6 - 10 LPA']++;
    } elseif ($pkg <= 15) {
        $ranges['10 - 15 LPA']++;
    } elseif ($pkg <= 20) {
        $ranges['15 - 20 LPA']++;
    } else {
        $ranges['Above 20 LPA']++;
    }
}

sendJson([
    'success' => true,
    'kpis' => [
        'total_students' => $totalStudents,
        'placed_students' => $placedStudents,
        'unplaced_students' => $unplacedStudents,
        'placement_percentage' => $placementPercentage,
        'total_companies' => $totalCompanies,
        'active_drives' => $activeDrives,
        'total_alumni' => $totalAlumni,
        'highest_package' => (float)$salaryStats['highest_package'],
        'average_package' => (float)$salaryStats['average_package'],
        'lowest_package' => (float)$salaryStats['lowest_package']
    ],
    'charts' => [
        'by_year' => $placementsByYear,
        'by_branch' => $placementsByBranch,
        'company_wise' => $companyPlacements,
        'package_ranges' => $ranges,
        'placed_vs_unplaced' => [
            'placed' => $placedStudents,
            'unplaced' => $unplacedStudents
        ]
    ]
]);
