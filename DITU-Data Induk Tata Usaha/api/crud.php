<?php
// ============================================================
// TU - Generic CRUD REST API (replaces Google Apps Script proxy)
// ============================================================
require_once '../config.php';
header('Content-Type: application/json; charset=utf-8');

// Session check for AJAX
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Read POST payload
$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
if (!$payload) {
    echo json_encode(['error' => 'Invalid JSON payload']);
    exit;
}

$action = $payload['action'] ?? '';
$table  = $payload['table']  ?? '';

// CSRF validation for mutating operations
if (in_array($action, ['addRow', 'updateRow', 'deleteRow', 'uploadFile'])) {
    $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!validateCSRFToken($csrfToken)) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid CSRF token. Please refresh the page.']);
        exit;
    }
}

// Whitelist tables
$allowedTables = ['DataSiswa', 'DataPTK', 'BankSoal', 'DaftarNilai', 'Users', 'Rombel', 'JadwalPelajaran', 'Settings', 'PaketSoal', 'ActivityLog', 'Absensi'];

// Map frontend table names to actual DB table names
$tableMap = [
    'DataSiswa' => 'datasiswa',
    'DataPTK' => 'dataptk',
    'BankSoal' => 'banksoal',
    'DaftarNilai' => 'nilai',
    'Users' => 'users',
    'Rombel' => 'rombel',
    'JadwalPelajaran' => 'jadwalpelajaran',
    'Settings' => 'settings',
    'PaketSoal' => 'paketsoal',
    'ActivityLog' => 'activitylog',
    'Absensi' => 'absensi',
];

function resolveTable($table, $tableMap) {
    return $tableMap[$table] ?? $table;
}

// Route actions
switch ($action) {
    case 'ping':
        $conn = dbConnect();
        if ($conn->ping()) {
            echo json_encode(['success' => true, 'message' => 'Database connected']);
        } else {
            echo json_encode(['error' => 'Database not responding']);
        }
        exit;

    case 'getData':
        if (!in_array($table, $allowedTables)) {
            echo json_encode(['error' => 'Invalid table: ' . $table]);
            exit;
        }
        handleGetData($table, $payload);
        exit;

    case 'addRow':
        if (!in_array($table, $allowedTables)) {
            echo json_encode(['error' => 'Invalid table: ' . $table]);
            exit;
        }
        handleAddRow($table, $payload);
        exit;

    case 'updateRow':
        if (!in_array($table, $allowedTables)) {
            echo json_encode(['error' => 'Invalid table: ' . $table]);
            exit;
        }
        handleUpdateRow($table, $payload);
        exit;

    case 'deleteRow':
        if (!in_array($table, $allowedTables)) {
            echo json_encode(['error' => 'Invalid table: ' . $table]);
            exit;
        }
        handleDeleteRow($table, $payload);
        exit;

    case 'getStats':
        handleGetStats();
        exit;

    case 'uploadFile':
        handleUploadFile($payload);
        exit;

    case 'findRow':
        if (!in_array($table, $allowedTables)) {
            echo json_encode(['error' => 'Invalid table: ' . $table]);
            exit;
        }
        handleFindRow($table, $payload);
        exit;

    default:
        echo json_encode(['error' => 'Unknown action: ' . $action]);
        exit;
}

// ============================================================
// Handler Functions
// ============================================================

function handleGetData($table, $payload) {
    global $tableMap;
    $conn = dbConnect();
    $filters = $payload['filters'] ?? [];
    $dbTable = resolveTable($table, $tableMap);
    
    $conditions = [];
    
    foreach ($filters as $key => $val) {
        if (!empty($val) && $key !== 'page' && $key !== 'per_page') {
            $key = $conn->real_escape_string($key);
            $val = $conn->real_escape_string($val);
            // Map common filter names to DB column names
            $conditions[] = "`$key` LIKE '%$val%'";
        }
    }
    
    // Role-based enforcement: guru can only see their rombel's data
    $rombelTables = ['datasiswa', 'nilai', 'jadwalpelajaran'];
    if (!isAdmin() && in_array($dbTable, $rombelTables)) {
        $userRombel = $_SESSION['rombel'] ?? '';
        if (!empty($userRombel)) {
            $conditions[] = "`rombel` = '" . $conn->real_escape_string($userRombel) . "'";
        }
    }

    $whereClause = '';
    if (!empty($conditions)) {
        $whereClause = " WHERE " . implode(' AND ', $conditions);
    }
    
    // Count total rows for pagination
    $countSql = "SELECT COUNT(*) as total FROM `$dbTable`" . $whereClause;
    $countResult = $conn->query($countSql);
    $total = 0;
    if ($countResult) {
        $total = (int)$countResult->fetch_assoc()['total'];
    }
    
    // Server-side pagination
    $page = max(1, (int)($payload['page'] ?? 1));
    $perPage = max(1, min(9999, (int)($payload['per_page'] ?? 9999)));
    $offset = ($page - 1) * $perPage;
    $totalPages = max(1, (int)ceil($total / $perPage));
    
    $sql = "SELECT * FROM `$dbTable`" . $whereClause . " ORDER BY id DESC LIMIT $perPage OFFSET $offset";
    
    $result = $conn->query($sql);
    if (!$result) {
        echo json_encode(['error' => $conn->error]);
        return;
    }
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    echo json_encode([
        'success' => true, 
        'data' => $data,
        'pagination' => [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => $totalPages
        ]
    ]);
}

function handleAddRow($table, $payload) {
    global $tableMap;
    $data = $payload['data'] ?? [];
    if (empty($data)) {
        echo json_encode(['error' => 'No data provided']);
        return;
    }
    
    $result = dbInsert(resolveTable($table, $tableMap), $data);
    if (isset($result['success']) && $result['success']) {
        logActivity('tambah', 'data', 'Menambahkan data ke ' . $table);
    }
    echo json_encode($result);
}

function handleUpdateRow($table, $payload) {
    global $tableMap;
    $rowId = $payload['rowId'] ?? ($payload['id'] ?? '');
    $data  = $payload['data']  ?? [];
    
    if (empty($rowId)) {
        echo json_encode(['error' => 'No row ID provided']);
        return;
    }
    if (empty($data)) {
        echo json_encode(['error' => 'No data provided']);
        return;
    }
    
    $result = dbUpdate(resolveTable($table, $tableMap), $rowId, $data);
    if (isset($result['success']) && $result['success']) {
        logActivity('ubah', 'data', 'Mengubah data ' . $table . ' (ID: ' . $rowId . ')');
    }
    echo json_encode($result);
}

function handleDeleteRow($table, $payload) {
    global $tableMap;
    $rowId = $payload['rowId'] ?? ($payload['id'] ?? '');
    
    if (empty($rowId)) {
        echo json_encode(['error' => 'No row ID provided']);
        return;
    }
    
    $result = dbDelete(resolveTable($table, $tableMap), $rowId);
    if (isset($result['success']) && $result['success']) {
        logActivity('hapus', 'data', 'Menghapus data ' . $table . ' (ID: ' . $rowId . ')');
    }
    echo json_encode($result);
}

function handleFindRow($table, $payload) {
    global $tableMap;
    $conn = dbConnect();
    $key   = $payload['key']   ?? '';
    $value = $payload['value'] ?? '';
    
    if (empty($key) || empty($value)) {
        echo json_encode(['error' => 'Key and value required']);
        return;
    }
    
    $key   = $conn->real_escape_string($key);
    $value = $conn->real_escape_string($value);
    $dbTable = resolveTable($table, $tableMap);
    
    $sql = "SELECT * FROM `$dbTable` WHERE `$key`='$value' LIMIT 1";
    $result = $conn->query($sql);
    
    if (!$result || $result->num_rows === 0) {
        echo json_encode(['success' => false, 'data' => null]);
        return;
    }
    
    echo json_encode(['success' => true, 'data' => $result->fetch_assoc()]);
}

function handleGetStats() {
    $stats = [
        'siswa'  => dbCount('DataSiswa'),
        'ptk'    => dbCount('DataPTK'),
        'rombel' => dbCount('Rombel'),
        'soal'   => dbCount('BankSoal'),
    ];
    echo json_encode(['success' => true] + $stats);
}

function handleUploadFile($payload) {
    $fileData = $payload['fileData'] ?? '';
    $fileName = $payload['fileName'] ?? 'upload.jpg';
    $mimeType = $payload['mimeType'] ?? '';
    $userId   = $payload['userId'] ?? $_SESSION['user_id'] ?? '';
    
    if (empty($fileData)) {
        echo json_encode(['error' => 'No file data provided']);
        return;
    }
    
    // Use per-user upload directory
    $uploadDir = getUserUploadDir($userId);
    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
    $safeName = uniqid('f_') . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
    $path = $uploadDir . $safeName;
    
    if (is_string($fileData) && base64_decode($fileData, true) !== false) {
        $decoded = base64_decode($fileData);
        file_put_contents($path, $decoded);
        logActivity('upload', 'data', 'Mengupload dokumen: ' . $fileName);
        echo json_encode(['success' => true, 'filename' => $safeName, 'path' => 'data/uploads/' . $userId . '/' . $safeName]);
    } else {
        echo json_encode(['error' => 'Invalid file data']);
    }
}
