<?php
/**
 * api/upload_ptk.php — Handle PTK document uploads
 * Accepts: image/* and PDF, max 1MB per file
 * Returns: { success: true, filename: "..." }
 */
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$MAX_SIZE = 1 * 1024 * 1024; // 1MB
$ALLOWED_TYPES = [
    'image/jpeg', 'image/png', 'image/gif', 'image/webp',
    'application/pdf'
];

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $errCode = $_FILES['file']['error'] ?? -1;
    echo json_encode(['error' => 'Upload gagal (kode: ' . $errCode . ')']);
    exit;
}

$file = $_FILES['file'];

if ($file['size'] > $MAX_SIZE) {
    echo json_encode(['error' => 'Ukuran file melebihi 1MB. Ukuran saat ini: ' . round($file['size'] / 1024 / 1024, 2) . 'MB']);
    exit;
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);

if (!in_array($mimeType, $ALLOWED_TYPES)) {
    echo json_encode(['error' => 'Tipe file tidak diizinkan: ' . $mimeType . '. Hanya JPG, PNG, GIF, WebP, dan PDF.']);
    exit;
}

// Create directory for PTK documents
$uploadDir = UPLOAD_DIR . 'ptk_docs/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Generate safe filename
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$safeName = uniqid('doc_') . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
$destPath = $uploadDir . $safeName;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    echo json_encode(['error' => 'Gagal menyimpan file ke server']);
    exit;
}

echo json_encode([
    'success'  => true,
    'filename' => $safeName,
    'path'     => 'data/uploads/ptk_docs/' . $safeName,
    'size'     => $file['size'],
    'type'     => $mimeType,
]);
