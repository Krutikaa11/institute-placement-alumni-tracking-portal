<?php
/**
 * Institute Placement & Alumni Tracking Portal
 * Database Installer & Setup Script
 */

header('Content-Type: text/html; charset=utf-8');

$host = '127.0.0.1';
$port = '3306';
$user = 'root';
$pass = '';
$dbName = 'placement_alumni_portal';

$isCli = (php_sapi_name() === 'cli');

function outputMsg($msg, $type = 'info') {
    global $isCli;
    if ($isCli) {
        $prefix = match($type) {
            'success' => '[SUCCESS] ',
            'error' => '[ERROR] ',
            'warning' => '[WARNING] ',
            default => '[INFO] '
        };
        echo $prefix . strip_tags($msg) . PHP_EOL;
    } else {
        $color = match($type) {
            'success' => '#15803d',
            'error' => '#b91c1c',
            'warning' => '#b45309',
            default => '#1d4ed8'
        };
        echo "<div style='color: {$color}; margin: 6px 0; font-family: monospace;'>{$msg}</div>";
    }
}

if (!$isCli) {
    echo "<!DOCTYPE html><html><head><title>Portal Installer</title><style>body{font-family:Segoe UI,sans-serif;padding:30px;background:#f8fafc;color:#1e293b;max-width:800px;margin:auto;}.card{background:#fff;padding:24px;border-radius:12px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);}</style></head><body><div class='card'><h2>Institute Placement Portal Database Installer</h2><hr/>";
}

try {
    // 1. Connect to MySQL Server (multi-statements enabled)
    outputMsg("Connecting to MySQL server at {$host}:{$port} as '{$user}'...", 'info');
    $pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => true
    ]);
    outputMsg("Connected to MySQL server successfully.", 'success');

    // 2. Create Database if not exists
    outputMsg("Ensuring database '{$dbName}' exists...", 'info');
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $pdo->exec("USE `{$dbName}`;");
    outputMsg("Database '{$dbName}' selected.", 'success');

    // 3. Execute schema.sql
    $schemaFile = __DIR__ . '/database/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found at: {$schemaFile}");
    }
    outputMsg("Executing database/schema.sql...", 'info');
    $schemaSql = file_get_contents($schemaFile);
    $pdo->exec($schemaSql);
    // Flush multi-statement results
    while ($pdo->query("SELECT 1")->nextRowset()) {}
    outputMsg("Tables created successfully.", 'success');

    // 4. Execute sample_data.sql
    $dataFile = __DIR__ . '/database/sample_data.sql';
    if (!file_exists($dataFile)) {
        throw new Exception("Sample data file not found at: {$dataFile}");
    }
    outputMsg("Executing database/sample_data.sql...", 'info');
    $dataSql = file_get_contents($dataFile);
    $pdo->exec($dataSql);
    while ($pdo->query("SELECT 1")->nextRowset()) {}
    outputMsg("Sample data inserted successfully.", 'success');

    // 5. Verify Tables & Row Counts
    outputMsg("Verifying table records...", 'info');
    $tables = [
        'users' => 'Users (Admin, Officer, Students, Alumni)',
        'students' => 'Student Profiles',
        'alumni' => 'Alumni Records',
        'companies' => 'Partner Companies',
        'drives' => 'Placement Drives',
        'applications' => 'Student Applications',
        'placements' => 'Placement Offer Records',
        'mentorship_requests' => 'Mentorship Requests'
    ];

    $counts = [];
    foreach ($tables as $table => $label) {
        $stmt = $pdo->query("SELECT COUNT(*) AS total FROM `{$table}`");
        $count = $stmt->fetch()['total'];
        $counts[$table] = $count;
        outputMsg("✓ Table `{$table}`: {$count} records ({$label})", 'success');
    }

    // 6. Ensure Upload directory exists
    $uploadDir = __DIR__ . '/uploads/resumes';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    outputMsg("Upload directory verified at uploads/resumes/", 'success');

    outputMsg("<b>Installation completed successfully!</b>", 'success');
    outputMsg("Default credentials: Admin (admin@portal.edu / Admin@123), Officer (officer@portal.edu / Officer@123)", 'info');

} catch (Exception $e) {
    outputMsg("Installation failed: " . $e->getMessage(), 'error');
}

if (!$isCli) {
    echo "<br/><a href='index.html' style='display:inline-block;padding:10px 20px;background:#1d4ed8;color:#fff;text-decoration:none;border-radius:6px;font-weight:600;'>Go to Portal</a></div></body></html>";
}
