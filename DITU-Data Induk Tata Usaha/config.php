<?php
// ============================================================
// TU - Tata Usaha SD Negeri 001 Gunung Sari
// Config — MySQL Local Backend
// ============================================================

session_start();

// MySQL Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sik_sdn001gs');

// App Info
define('APP_NAME', 'TU');
define('APP_FULL_NAME', 'Tata Usaha SD Negeri 001 Gunung Sari');
define('APP_VERSION', '2.0.0');

// Roles
define('ROLE_ADMIN', 'admin');
define('ROLE_GURU',  'guru');

// Upload Directory
define('UPLOAD_DIR', __DIR__ . '/data/uploads/');

// ============================================================
// MySQL Connection
// ============================================================
function dbConnect() {
    static $conn = null;
    if ($conn !== null) return $conn;
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

// ============================================================
// Database Helper Functions
// ============================================================
function dbQuery($sql) {
    $conn = dbConnect();
    $result = $conn->query($sql);
    if (!$result) {
        return ['error' => $conn->error];
    }
    return $result;
}

function dbFetch($sql) {
    $result = dbQuery($sql);
    if (is_array($result)) return []; // error case
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

function dbFetchOne($sql) {
    $result = dbQuery($sql);
    if (is_array($result)) return null;
    return $result->fetch_assoc();
}

function dbInsert($table, $data) {
    $conn = dbConnect();
    $table = $conn->real_escape_string($table);
    $cols = array_keys($data);
    $vals = array_values($data);
    
    // Auto-generate ID if not provided
    if (!isset($data['id']) || empty($data['id'])) {
        $data['id'] = uniqid('');
        $cols = array_keys($data);
        $vals = array_values($data);
    }
    
    $colStr = '`' . implode('`,`', array_map([$conn, 'real_escape_string'], $cols)) . '`';
    $valStr = "'" . implode("','", array_map([$conn, 'real_escape_string'], $vals)) . "'";
    
    $sql = "INSERT INTO `$table` ($colStr) VALUES ($valStr)";
    $result = $conn->query($sql);
    if (!$result) {
        return ['error' => $conn->error, 'sql' => $sql];
    }
    return ['success' => true, 'insertId' => $data['id'], 'insertedId' => $data['id']];
}

function dbUpdate($table, $id, $data) {
    $conn = dbConnect();
    $table = $conn->real_escape_string($table);
    
    $sets = [];
    foreach ($data as $key => $val) {
        $key = $conn->real_escape_string($key);
        $val = $conn->real_escape_string($val);
        $sets[] = "`$key`='$val'";
    }
    
    $setStr = implode(',', $sets);
    $sql = "UPDATE `$table` SET $setStr WHERE `id`='" . $conn->real_escape_string($id) . "'";
    $result = $conn->query($sql);
    if (!$result) {
        return ['error' => $conn->error, 'sql' => $sql];
    }
    return ['success' => true, 'affected' => $conn->affected_rows];
}

function dbDelete($table, $id) {
    $conn = dbConnect();
    $table = $conn->real_escape_string($table);
    $sql = "DELETE FROM `$table` WHERE `id`='" . $conn->real_escape_string($id) . "'";
    $result = $conn->query($sql);
    if (!$result) {
        return ['error' => $conn->error];
    }
    return ['success' => true, 'affected' => $conn->affected_rows];
}

function dbCount($table, $where = '') {
    $conn = dbConnect();
    $table = $conn->real_escape_string($table);
    $sql = "SELECT COUNT(*) as cnt FROM `$table`";
    if (!empty($where)) {
        $sql .= " WHERE $where";
    }
    $result = $conn->query($sql);
    if (!$result) return 0;
    $row = $result->fetch_assoc();
    return (int)($row['cnt'] ?? 0);
}

// ============================================================
// Auth Helpers
// ============================================================
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function checkSessionTimeout() {
    if (!isLoggedIn()) return;
    $settings = getSettings();
    $timeout = (int)($settings['session_timeout'] ?? 15) * 60;
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
        session_unset();
        session_destroy();
        header('Location: login.php?error=timeout');
        exit;
    }
    $_SESSION['last_activity'] = time();
}

function isAppLocked() {
    $settings = getSettings();
    return ($settings['app_locked'] ?? '0') === '1';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
    checkSessionTimeout();
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === ROLE_ADMIN;
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: index.php?error=unauthorized');
        exit;
    }
}

function currentUser() {
    return [
        'id'       => $_SESSION['user_id']   ?? '',
        'nama'     => $_SESSION['nama']       ?? '',
        'role'     => $_SESSION['role']       ?? '',
        'email'    => $_SESSION['email']      ?? '',
        'foto'     => $_SESSION['foto']       ?? '',
        'rombel'   => $_SESSION['rombel']     ?? '',
        'nip'      => $_SESSION['nip']        ?? '',
    ];
}

// ============================================================
// Settings (MySQL)
// ============================================================
function getSettings() {
    $rows = dbFetch("SELECT key_name, setting_value FROM Settings");
    $settings = [];
    foreach ($rows as $row) {
        $settings[$row['key_name']] = $row['setting_value'];
    }
    return $settings;
}

function saveSettings($data) {
    $conn = dbConnect();
    foreach ($data as $key => $val) {
        $key = $conn->real_escape_string($key);
        $val = $conn->real_escape_string($val);
        $conn->query("INSERT INTO Settings (key_name, setting_value) VALUES ('$key', '$val') ON DUPLICATE KEY UPDATE setting_value='$val'");
    }
    return ['success' => true];
}

// ============================================================
// Kop Sekolah (dedicated table)
// ============================================================
function getKopSekolah() {
    $row = dbFetchOne("SELECT * FROM KopSekolah WHERE id=1");
    if (!$row) {
        // Auto-create row if missing
        $conn = dbConnect();
        $conn->query("INSERT IGNORE INTO KopSekolah (id) VALUES (1)");
        $row = dbFetchOne("SELECT * FROM KopSekolah WHERE id=1");
    }
    return $row ?: [];
}

function saveKopSekolah($data) {
    $conn = dbConnect();
    $sets = [];
    foreach ($data as $key => $val) {
        $key = $conn->real_escape_string($key);
        $val = $conn->real_escape_string($val);
        $sets[] = "`$key`='$val'";
    }
    if (empty($sets)) return ['success' => true];
    $setStr = implode(',', $sets);
    $conn->query("UPDATE KopSekolah SET $setStr WHERE id=1");
    return ['success' => true];
}

// ============================================================
// User Lookup (MySQL)
// ============================================================
function findUser($username) {
    $conn = dbConnect();
    $username = $conn->real_escape_string($username);
    $row = dbFetchOne("SELECT * FROM Users WHERE username='$username' LIMIT 1");
    return $row;
}

// Get per-user upload directory
function getUserUploadDir($userId) {
    $dir = UPLOAD_DIR . $userId . '/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    return $dir;
}

function getUserUploadUrl($userId, $filename) {
    return 'data/uploads/' . $userId . '/' . $filename;
}

// ============================================================
// Local File Upload
// ============================================================
function uploadToLocal($fileData, $fileName, $mimeType = '') {
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
    
    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
    $safeName = uniqid('f_') . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
    $path = UPLOAD_DIR . $safeName;
    
    if (is_string($fileData) && base64_decode($fileData, true) !== false) {
        // Base64 data
        $decoded = base64_decode($fileData);
        file_put_contents($path, $decoded);
    } else {
        return ['error' => 'Invalid file data'];
    }
    
    return ['success' => true, 'filename' => $safeName, 'path' => 'data/uploads/' . $safeName];
}

// ============================================================
// Get Upload URL for display
// ============================================================
function getUploadUrl($filename) {
    if (empty($filename)) return '';
    if (strpos($filename, 'http') === 0) return $filename;
    if (strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
        return $filename;
    }
    return 'data/uploads/' . $filename;
}

// ============================================================
// CSRF Protection
// ============================================================
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}

function requireCSRF() {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!validateCSRFToken($token)) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid CSRF token']);
        exit;
    }
}

// ============================================================
// Rate Limiting (in-memory for session)
// ============================================================
function checkRateLimit($action, $maxAttempts = 5, $lockoutSeconds = 300) {
    $key = 'rate_' . $action;
    $now = time();
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['attempts' => 0, 'last_attempt' => 0];
    }
    
    $rate = &$_SESSION[$key];
    
    // Reset if lockout period has passed
    if ($rate['attempts'] >= $maxAttempts && ($now - $rate['last_attempt']) > $lockoutSeconds) {
        $rate['attempts'] = 0;
    }
    
    if ($rate['attempts'] >= $maxAttempts) {
        $remaining = $lockoutSeconds - ($now - $rate['last_attempt']);
        return ['allowed' => false, 'remaining' => $remaining];
    }
    
    return ['allowed' => true];
}

function recordRateLimitAttempt($action) {
    $key = 'rate_' . $action;
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['attempts' => 0, 'last_attempt' => 0];
    }
    $_SESSION[$key]['attempts']++;
    $_SESSION[$key]['last_attempt'] = time();
}

function resetRateLimit($action) {
    unset($_SESSION['rate_' . $action]);
}

function logActivity($action, $category = 'umum', $description = '') {
    if (!isLoggedIn()) return;
    try {
        $conn = dbConnect();
        // Check if ActivityLog table exists
        $check = $conn->query("SHOW TABLES LIKE 'ActivityLog'");
        if (!$check || $check->num_rows === 0) return;
        
        $userId    = $conn->real_escape_string($_SESSION['user_id'] ?? '');
        $userName  = $conn->real_escape_string($_SESSION['nama'] ?? '');
        $userRole  = $conn->real_escape_string($_SESSION['role'] ?? '');
        $action    = $conn->real_escape_string($action);
        $category  = $conn->real_escape_string($category);
        $desc      = $conn->real_escape_string($description);
        $ip        = $conn->real_escape_string($_SERVER['REMOTE_ADDR'] ?? '');
        
        $logId     = 'LOG-' . uniqid('');
        $sql = "INSERT INTO `ActivityLog` (`id`, `user_id`, `user_name`, `user_role`, `action`, `category`, `description`, `ip_address`)
                VALUES ('$logId', '$userId', '$userName', '$userRole', '$action', '$category', '$desc', '$ip')";
        $conn->query($sql);
    } catch (Exception $e) {
        // Silently fail - logging should never break the app
    }
}
