<?php
// ============================================================
// TU - Settings API (key-value store)
// ============================================================
require_once '../config.php';
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
$action = $payload['action'] ?? '';

// CSRF validation for mutating operations
if (in_array($action, ['save', 'uploadLogo', 'deleteFile'])) {
    $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!validateCSRFToken($csrfToken)) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid CSRF token. Please refresh the page.']);
        exit;
    }
}

switch ($action) {
    case 'get':
        // Get all settings as key-value object
        $settings = getSettings();
        echo json_encode(['success' => true, 'data' => $settings]);
        exit;

    case 'save':
        // Save settings (key-value pairs)
        $data = $payload['data'] ?? [];
        if (empty($data)) {
            echo json_encode(['error' => 'No data provided']);
            exit;
        }
        $result = saveSettings($data);
        if (isset($result['success']) && $result['success']) {
            logActivity('simpan', 'data', 'Menyimpan pengaturan sistem');
        }
        echo json_encode($result);
        exit;

    case 'uploadLogo':
        // Upload logo file (base64)
        $base64 = $payload['data'] ?? '';
        $filename = $payload['filename'] ?? '';
        $key = $payload['key'] ?? '';
        if (empty($base64) || empty($filename) || empty($key)) {
            echo json_encode(['error' => 'Data tidak lengkap']);
            exit;
        }
        $imageData = base64_decode($base64);
        if ($imageData === false) {
            echo json_encode(['error' => 'Data base64 tidak valid']);
            exit;
        }
        $uploadDir = UPLOAD_DIR;
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $safeFilename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        $fullPath = $uploadDir . $safeFilename;
        if (file_put_contents($fullPath, $imageData)) {
            $result = saveSettings([$key => $safeFilename]);
            logActivity('upload', 'data', 'Mengupload logo/gambar: ' . $key);
            echo json_encode(['success' => true, 'filename' => $safeFilename]);
        } else {
            echo json_encode(['error' => 'Gagal menyimpan file']);
        }
        exit;

    case 'deleteFile':
        $key = $payload['key'] ?? '';
        if (empty($key)) {
            echo json_encode(['error' => 'Key tidak valid']);
            exit;
        }
        $settings = getSettings();
        $file = $settings[$key] ?? '';
        if (!empty($file)) {
            $filePath = UPLOAD_DIR . $file;
            if (file_exists($filePath)) unlink($filePath);
            saveSettings([$key => '']);
        }
        echo json_encode(['success' => true]);
        exit;

    default:
        echo json_encode(['error' => 'Unknown action: ' . $action]);
        exit;
}
