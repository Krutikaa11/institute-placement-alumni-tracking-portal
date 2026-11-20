<?php
/**
 * API: Get Current Authenticated User
 */

require_once __DIR__ . '/../../config/auth.php';

$user = getCurrentUser();

if (!$user) {
    sendJson([
        'success' => true,
        'authenticated' => false,
        'user' => null
    ]);
}

sendJson([
    'success' => true,
    'authenticated' => true,
    'user' => $user
]);
