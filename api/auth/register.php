<?php
/**
 * API: User Registration (Student or Alumni)
 * POST: { name, email, password, role, ...profile_fields }
 */

require_once __DIR__ . '/../../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method Not Allowed', 405);
}

$data = getRequestData();
$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');
$role = trim($data['role'] ?? '');

$errors = [];
if (empty($name)) $errors[] = 'Name is required.';
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
if (empty($password) || strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
if (!in_array($role, ['student', 'alumni'], true)) $errors[] = 'Role must be student or alumni.';

if (!empty($errors)) {
    sendError('Validation failed.', 422, $errors);
}

$pdo = Database::getConnection();

// Check if email is already taken
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
$stmt->execute(['email' => $email]);
if ($stmt->fetch()) {
    sendError('An account with this email already exists.', 409);
}

$pdo->beginTransaction();
try {
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $uStmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)");
    $uStmt->execute([
        'name' => $name,
        'email' => $email,
        'password' => $hashedPassword,
        'role' => $role
    ]);
    $userId = (int)$pdo->lastInsertId();

    if ($role === 'student') {
        $rollNo = trim($data['roll_no'] ?? ('STU' . time()));
        $branch = trim($data['branch'] ?? 'Computer Science');
        $year = (int)($data['year'] ?? 4);
        $cgpa = (float)($data['cgpa'] ?? 0.0);
        $phone = trim($data['phone'] ?? '');
        $skills = trim($data['skills'] ?? '');

        // Check unique roll_no
        $chk = $pdo->prepare("SELECT id FROM students WHERE roll_no = :roll LIMIT 1");
        $chk->execute(['roll' => $rollNo]);
        if ($chk->fetch()) {
            $rollNo .= '_' . rand(100, 999);
        }

        $sStmt = $pdo->prepare("INSERT INTO students (user_id, roll_no, branch, year, cgpa, phone, skills) VALUES (:uid, :roll, :branch, :year, :cgpa, :phone, :skills)");
        $sStmt->execute([
            'uid' => $userId,
            'roll' => $rollNo,
            'branch' => $branch,
            'year' => $year,
            'cgpa' => $cgpa,
            'phone' => $phone,
            'skills' => $skills
        ]);
    } elseif ($role === 'alumni') {
        $gradYear = (int)($data['graduation_year'] ?? date('Y'));
        $branch = trim($data['branch'] ?? 'Computer Science');
        $company = trim($data['company'] ?? '');
        $jobRole = trim($data['job_role'] ?? '');
        $location = trim($data['location'] ?? '');
        $skills = trim($data['skills'] ?? '');
        $linkedin = trim($data['linkedin'] ?? '');
        $bio = trim($data['bio'] ?? '');

        $aStmt = $pdo->prepare("INSERT INTO alumni (user_id, graduation_year, branch, company, job_role, location, skills, linkedin, bio) VALUES (:uid, :gyear, :branch, :comp, :jrole, :loc, :skills, :linkedin, :bio)");
        $aStmt->execute([
            'uid' => $userId,
            'gyear' => $gradYear,
            'branch' => $branch,
            'comp' => $company,
            'jrole' => $jobRole,
            'loc' => $location,
            'skills' => $skills,
            'linkedin' => $linkedin,
            'bio' => $bio
        ]);
    }

    $pdo->commit();

    // Auto log in after registration
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_role'] = $role;

    sendJson([
        'success' => true,
        'message' => 'Registration successful! Welcome to the portal.',
        'user' => [
            'id' => $userId,
            'name' => $name,
            'email' => $email,
            'role' => $role
        ]
    ], 201);

} catch (Exception $e) {
    $pdo->rollBack();
    sendError('Registration failed: ' . $e->getMessage(), 500);
}
