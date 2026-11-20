<?php
/**
 * API: Placement Drives Resource (CRUD & Listing)
 */

require_once __DIR__ . '/../../config/auth.php';

$pdo = Database::getConnection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        $search = trim($_GET['search'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $branch = trim($_GET['branch'] ?? '');
        $minPackage = isset($_GET['min_package']) ? (float)$_GET['min_package'] : null;
        $studentId = isset($_GET['student_id']) ? (int)$_GET['student_id'] : null;

        // If current user is logged in as student, use their student ID automatically
        $currentUser = getCurrentUser();
        if ($currentUser && $currentUser['role'] === 'student' && !$studentId) {
            $studentId = (int)($currentUser['student_profile']['id'] ?? 0);
        }

        // Student details if checking eligibility
        $student = null;
        if ($studentId) {
            $sStmt = $pdo->prepare("SELECT * FROM students WHERE id = :sid LIMIT 1");
            $sStmt->execute(['sid' => $studentId]);
            $student = $sStmt->fetch();
        }

        if ($id) {
            $stmt = $pdo->prepare("
                SELECT d.*, c.company_name, c.industry, c.location AS company_location, c.website, c.logo
                FROM drives d
                JOIN companies c ON d.company_id = c.id
                WHERE d.id = :id LIMIT 1
            ");
            $stmt->execute(['id' => $id]);
            $drive = $stmt->fetch();
            if (!$drive) {
                sendError('Drive not found.', 404);
            }

            // Total applicants count
            $appCountStmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE drive_id = :did");
            $appCountStmt->execute(['did' => $id]);
            $drive['applicant_count'] = (int)$appCountStmt->fetchColumn();

            // Eligibility check if student is known
            if ($student) {
                $isEligible = true;
                $reasons = [];

                if ((float)$student['cgpa'] < (float)$drive['min_cgpa']) {
                    $isEligible = false;
                    $reasons[] = "CGPA {$student['cgpa']} is below required minimum {$drive['min_cgpa']}";
                }

                $eligibleBranches = array_map('trim', explode(',', $drive['eligible_branches']));
                $branchMatch = false;
                foreach ($eligibleBranches as $eb) {
                    if (strcasecmp($eb, 'All Branches') === 0 || stripos($student['branch'], $eb) !== false || stripos($eb, $student['branch']) !== false) {
                        $branchMatch = true;
                        break;
                    }
                }
                if (!$branchMatch) {
                    $isEligible = false;
                    $reasons[] = "Branch '{$student['branch']}' is not in eligible list ({$drive['eligible_branches']})";
                }

                if (strtotime($drive['deadline']) < strtotime(date('Y-m-d'))) {
                    $isEligible = false;
                    $reasons[] = "Application deadline has passed.";
                }

                $drive['is_eligible'] = $isEligible;
                $drive['eligibility_reasons'] = $reasons;

                // Also check if already applied
                $appliedStmt = $pdo->prepare("SELECT id, status, applied_date FROM applications WHERE student_id = :sid AND drive_id = :did LIMIT 1");
                $appliedStmt->execute(['sid' => $student['id'], 'did' => $id]);
                $drive['application'] = $appliedStmt->fetch() ?: null;
            }

            sendJson(['success' => true, 'data' => $drive]);
        }

        $sql = "
            SELECT d.*, c.company_name, c.industry, c.location AS company_location, c.website, c.logo,
                   (SELECT COUNT(*) FROM applications a WHERE a.drive_id = d.id) AS applicant_count
            FROM drives d
            JOIN companies c ON d.company_id = c.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (d.job_role LIKE :s OR c.company_name LIKE :s OR d.skills LIKE :s OR d.location LIKE :s)";
            $params['s'] = "%{$search}%";
        }
        if (!empty($status)) {
            $sql .= " AND d.status = :status";
            $params['status'] = $status;
        }
        if ($minPackage !== null && $minPackage > 0) {
            $sql .= " AND d.package >= :min_package";
            $params['min_package'] = $minPackage;
        }
        if (!empty($branch) && strcasecmp($branch, 'all') !== 0) {
            $sql .= " AND (d.eligible_branches LIKE :b OR d.eligible_branches LIKE '%All Branches%')";
            $params['b'] = "%{$branch}%";
        }

        $sql .= " ORDER BY d.drive_date ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $drives = $stmt->fetchAll();

        // Process student-specific eligibility and application state
        if ($student) {
            foreach ($drives as &$drv) {
                $isEligible = true;
                $reasons = [];

                if ((float)$student['cgpa'] < (float)$drv['min_cgpa']) {
                    $isEligible = false;
                    $reasons[] = "Minimum CGPA required is {$drv['min_cgpa']} (Yours: {$student['cgpa']})";
                }

                $eligibleBranches = array_map('trim', explode(',', $drv['eligible_branches']));
                $branchMatch = false;
                foreach ($eligibleBranches as $eb) {
                    if (strcasecmp($eb, 'All Branches') === 0 || stripos($student['branch'], $eb) !== false || stripos($eb, $student['branch']) !== false) {
                        $branchMatch = true;
                        break;
                    }
                }
                if (!$branchMatch) {
                    $isEligible = false;
                    $reasons[] = "Eligible branches: {$drv['eligible_branches']}";
                }

                $deadlinePassed = (strtotime($drv['deadline']) < strtotime(date('Y-m-d')));
                if ($deadlinePassed) {
                    $isEligible = false;
                    $reasons[] = "Application deadline has passed.";
                }

                $drv['is_eligible'] = $isEligible;
                $drv['eligibility_reasons'] = $reasons;

                // Check student's application status for this drive
                $appliedStmt = $pdo->prepare("SELECT id, status, applied_date FROM applications WHERE student_id = :sid AND drive_id = :did LIMIT 1");
                $appliedStmt->execute(['sid' => $student['id'], 'did' => $drv['id']]);
                $drv['application'] = $appliedStmt->fetch() ?: null;
            }
            unset($drv);
        }

        sendJson(['success' => true, 'data' => $drives, 'total' => count($drives)]);
        break;

    case 'POST':
        $user = requireRole(['admin', 'officer']);
        $data = getRequestData();

        $companyId = (int)($data['company_id'] ?? 0);
        $jobRole = trim($data['job_role'] ?? '');
        $package = (float)($data['package'] ?? 0);
        $location = trim($data['location'] ?? '');
        $driveDate = trim($data['drive_date'] ?? '');
        $deadline = trim($data['deadline'] ?? '');
        $eligibility = trim($data['eligibility_criteria'] ?? '');
        $skills = trim($data['skills'] ?? '');
        $vacancies = (int)($data['vacancies'] ?? 1);
        $minCgpa = (float)($data['min_cgpa'] ?? 0.0);
        $branches = trim($data['eligible_branches'] ?? 'All Branches');
        $status = trim($data['status'] ?? 'active');

        if ($companyId <= 0 || empty($jobRole) || empty($driveDate) || empty($deadline)) {
            sendError('Company, Job Role, Drive Date, and Deadline are required.');
        }

        $stmt = $pdo->prepare("
            INSERT INTO drives (company_id, job_role, package, location, drive_date, deadline, eligibility_criteria, skills, vacancies, min_cgpa, eligible_branches, status)
            VALUES (:cid, :role, :pkg, :loc, :ddate, :dline, :elig, :skills, :vac, :cgpa, :branches, :status)
        ");
        $stmt->execute([
            'cid' => $companyId,
            'role' => $jobRole,
            'pkg' => $package,
            'loc' => $location,
            'ddate' => $driveDate,
            'dline' => $deadline,
            'elig' => $eligibility,
            'skills' => $skills,
            'vac' => $vacancies,
            'cgpa' => $minCgpa,
            'branches' => $branches,
            'status' => $status
        ]);

        $newId = (int)$pdo->lastInsertId();
        sendJson(['success' => true, 'message' => 'Placement drive created successfully.', 'id' => $newId], 201);
        break;

    case 'PUT':
        $user = requireRole(['admin', 'officer']);
        $data = getRequestData();

        $id = (int)($data['id'] ?? 0);
        $companyId = (int)($data['company_id'] ?? 0);
        $jobRole = trim($data['job_role'] ?? '');
        $package = (float)($data['package'] ?? 0);
        $location = trim($data['location'] ?? '');
        $driveDate = trim($data['drive_date'] ?? '');
        $deadline = trim($data['deadline'] ?? '');
        $eligibility = trim($data['eligibility_criteria'] ?? '');
        $skills = trim($data['skills'] ?? '');
        $vacancies = (int)($data['vacancies'] ?? 1);
        $minCgpa = (float)($data['min_cgpa'] ?? 0.0);
        $branches = trim($data['eligible_branches'] ?? 'All Branches');
        $status = trim($data['status'] ?? 'active');

        if ($id <= 0 || $companyId <= 0 || empty($jobRole)) {
            sendError('Valid Drive ID, Company, and Job Role are required.');
        }

        $stmt = $pdo->prepare("
            UPDATE drives SET
                company_id = :cid, job_role = :role, package = :pkg, location = :loc,
                drive_date = :ddate, deadline = :dline, eligibility_criteria = :elig,
                skills = :skills, vacancies = :vac, min_cgpa = :cgpa,
                eligible_branches = :branches, status = :status
            WHERE id = :id
        ");
        $stmt->execute([
            'cid' => $companyId,
            'role' => $jobRole,
            'pkg' => $package,
            'loc' => $location,
            'ddate' => $driveDate,
            'dline' => $deadline,
            'elig' => $eligibility,
            'skills' => $skills,
            'vac' => $vacancies,
            'cgpa' => $minCgpa,
            'branches' => $branches,
            'status' => $status,
            'id' => $id
        ]);

        sendJson(['success' => true, 'message' => 'Placement drive updated successfully.']);
        break;

    case 'DELETE':
        $user = requireRole(['admin', 'officer']);
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            sendError('Valid Drive ID is required.');
        }

        $stmt = $pdo->prepare("DELETE FROM drives WHERE id = :id");
        $stmt->execute(['id' => $id]);

        sendJson(['success' => true, 'message' => 'Placement drive deleted successfully.']);
        break;

    default:
        sendError('Method Not Allowed', 405);
}
