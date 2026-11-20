<?php
/**
 * Institute Placement & Alumni Tracking Portal
 * Configuration File
 */

// Application Constants
define('APP_NAME', 'Institute Placement & Alumni Tracking Portal');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://127.0.0.1:8000');

// Database Credentials
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'placement_alumni_portal');
define('DB_USER', 'root');
define('DB_PASS', '');

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads/resumes');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5 MB

// Session Configuration (Safely initialized when not in CLI or headers already sent)
if (php_sapi_name() !== 'cli' && session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}
