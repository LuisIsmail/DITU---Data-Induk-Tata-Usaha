<?php
// ============================================================
// TU - Kop Sekolah API (dedicated KopSekolah table)
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

// CSRF validation
if (in_array($action, ['save', 'uploadLogo'])) {
    $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!validateCSRFToken($csrfToken)) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid CSRF token. Please refresh the page.']);
        exit;
    }
}

switch ($action) {
    case 'get':
        $kop = getKopSekolah();
        echo json_encode(['success' => true, 'data' => $kop]);
        exit;

    case 'save':
        $data = $payload['data'] ?? [];
        if (empty($data)) {
            echo json_encode(['error' => 'No data provided']);
            exit;
        }
        // Whitelist allowed columns
        $allowed = [
            'school_name', 'school_short', 'npsn', 'nss', 'akreditasi',
            'alamat', 'kecamatan', 'kabupaten', 'provinsi', 'telp', 'email',
            'kepala_sekolah', 'nip_kepsek', 'dinas', 'instansi',
            'visi', 'misi', 'tahun_ajaran', 'semester'
        ];
        $filtered = [];
        foreach ($data as $k => $v) {
            if (in_array($k, $allowed)) {
                $filtered[$k] = $v;
            }
        }
        if (empty($filtered)) {
            echo json_encode(['error' => 'No valid fields']);
            exit;
        }
        $result = saveKopSekolah($filtered);
        logActivity('simpan', 'data', 'Menyimpan data profil sekolah');
        echo json_encode($result);
        exit;

    case 'uploadLogo':
        $base64 = $payload['data'] ?? '';
        $filename = $payload['filename'] ?? '';
        $key = $payload['key'] ?? '';
        if (empty($base64) || empty($filename) || empty($key)) {
            echo json_encode(['error' => 'Data tidak lengkap']);
            exit;
        }
        if (!in_array($key, ['logo_kiri', 'logo_kanan'])) {
            echo json_encode(['error' => 'Key logo tidak valid']);
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
            saveKopSekolah([$key => $safeFilename]);
            logActivity('upload', 'data', 'Mengupload logo sekolah: ' . $key);
            echo json_encode(['success' => true, 'filename' => $safeFilename]);
        } else {
            echo json_encode(['error' => 'Gagal menyimpan file']);
        }
        exit;

    default:
        echo json_encode(['error' => 'Unknown action: ' . $action]);
        exit;
}
