<?php
// ============================================================
// TU - File Upload Handler (Local)
// ============================================================
require_once '../config.php';
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'POST required']);
    exit;
}

if (!isset($_FILES['file'])) {
    echo json_encode(['error' => 'No file uploaded']);
    exit;
}

$file = $_FILES['file'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'Upload error: ' . $file['error']]);
    exit;
}

// Limit 5MB
if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['error' => 'File too large (max 5MB)']);
    exit;
}

// Validate MIME type
$allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowed)) {
    echo json_encode(['error' => 'File type not allowed: ' . $mimeType]);
    exit;
}

// Save file
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$safeName = uniqid('f_') . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
$dest = UPLOAD_DIR . $safeName;

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    echo json_encode(['error' => 'Failed to save file']);
    exit;
}

echo json_encode([
    'success'  => true,
    'filename' => $safeName,
    'path'     => 'data/uploads/' . $safeName,
    'url'      => 'data/uploads/' . $safeName,
    'mimeType' => $mimeType,
    'size'     => $file['size'],
]);
