<?php
// ============================================================
// TU - User Management API (password hashing via server-side)
// ============================================================
require_once '../config.php';
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden - Admin only']);
    exit;
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
$action = $payload['action'] ?? '';

// CSRF validation for mutating operations
$csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!validateCSRFToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token. Please refresh the page.']);
    exit;
}

switch ($action) {
    case 'addUser':
        $data = $payload['data'] ?? [];
        if (empty($data['username']) || empty($data['password'])) {
            echo json_encode(['error' => 'Username dan password wajib diisi']);
            exit;
        }
        // Hash password with bcrypt
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        if (empty($data['aktif'])) $data['aktif'] = '1';
        $result = dbInsert('Users', $data);
        // Auto-create DataPTK record linked to this user
        if (isset($result['success']) && $result['success']) {
            $newUserId = $result['insertedId'] ?? $result['insertId'] ?? uniqid('USR-');
            $ptkId = 'PTK-' . substr(md5($newUserId), 0, 12);
            $ptkData = [
                'id'              => $ptkId,
                'user_id'         => $newUserId,
                'nama'            => $data['nama'] ?? '',
                'username_sistem' => $data['username'] ?? '',
                'jenis_ptk'       => ($data['role'] ?? 'guru') === 'admin' ? 'Operator Sekolah' : 'Guru Kelas',
                'status_kepeg'    => 'Honorer',
                'status_aktif'    => 'Aktif',
                'rombel_diampu'   => $data['rombel'] ?? '',
            ];
            $ptkResult = dbInsert('DataPTK', $ptkData);
            logActivity('tambah', 'user', 'Menambahkan user baru: ' . ($data['username'] ?? ''));
        }
        echo json_encode($result);
        exit;

    case 'updateUser':
        $id = $payload['id'] ?? '';
        $data = $payload['data'] ?? [];
        if (empty($id)) {
            echo json_encode(['error' => 'ID tidak valid']);
            exit;
        }
        // Hash password if provided, otherwise remove from update data
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }
        $result = dbUpdate('Users', $id, $data);
        if (isset($result['success']) && $result['success']) {
            $conn = dbConnect();
            // Sync rombel to DataPTK if provided
            if (isset($data['rombel'])) {
                $conn->query("UPDATE dataptk SET rombel_diampu='" . $conn->real_escape_string($data['rombel']) . "' WHERE user_id='" . $conn->real_escape_string($id) . "'");
            }
            // Sync nama to DataPTK if provided
            if (isset($data['nama'])) {
                $conn->query("UPDATE dataptk SET nama='" . $conn->real_escape_string($data['nama']) . "' WHERE user_id='" . $conn->real_escape_string($id) . "'");
            }
            // Sync nip to DataPTK if provided
            if (isset($data['nip'])) {
                $conn->query("UPDATE dataptk SET nip='" . $conn->real_escape_string($data['nip']) . "' WHERE user_id='" . $conn->real_escape_string($id) . "'");
            }
            logActivity('ubah', 'user', 'Mengubah data user (ID: ' . $id . ')');
        }
        echo json_encode($result);
        exit;

    case 'resetPassword':
        $id = $payload['id'] ?? '';
        $newPassword = $payload['password'] ?? '';
        if (empty($id) || empty($newPassword)) {
            echo json_encode(['error' => 'ID dan password baru wajib diisi']);
            exit;
        }
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $result = dbUpdate('Users', $id, ['password' => $hashed]);
        if (isset($result['success']) && $result['success']) {
            logActivity('ubah', 'user', 'Reset password user (ID: ' . $id . ')');
        }
        echo json_encode($result);
        exit;

    case 'deleteUser':
        $id = $payload['id'] ?? '';
        if (empty($id)) {
            echo json_encode(['error' => 'ID tidak valid']);
            exit;
        }
        // Prevent deleting the main admin account
        $conn = dbConnect();
        $row = dbFetchOne("SELECT username FROM Users WHERE id='" . $conn->real_escape_string($id) . "' LIMIT 1");
        if ($row && $row['username'] === 'admin') {
            echo json_encode(['error' => 'Tidak bisa menghapus akun admin utama']);
            exit;
        }
        $result = dbDelete('Users', $id);
        if (isset($result['success']) && $result['success']) {
            // Delete associated DataPTK record
            $conn->query("DELETE FROM dataptk WHERE user_id='" . $conn->real_escape_string($id) . "'");
            logActivity('hapus', 'user', 'Menghapus user (ID: ' . $id . ')');
        }
        echo json_encode($result);
        exit;

    default:
        echo json_encode(['error' => 'Unknown action: ' . $action]);
        exit;
}
