<?php
// ============================================================
// TU - Activity Log API (for JS-side logging)
// ============================================================
require_once '../config.php';
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);

$action    = $payload['action']    ?? '';
$category  = $payload['category']  ?? 'umum';
$desc      = $payload['description'] ?? '';

// CSRF validation
$csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!validateCSRFToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token. Please refresh the page.']);
    exit;
}

if (empty($action)) {
    echo json_encode(['error' => 'Action required']);
    exit;
}

logActivity($action, $category, $desc);
echo json_encode(['success' => true]);
