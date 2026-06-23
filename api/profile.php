<?php
// ============================================================
// TU - Profile API (update自己的 profile, change password)
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
$action = $payload['action'] ?? '';
$userId = $_SESSION['user_id'];

// CSRF validation for mutating operations
if (in_array($action, ['updateProfile', 'uploadFoto', 'changePassword', 'updatePTK', 'uploadPTKDoc'])) {
    $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!validateCSRFToken($csrfToken)) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid CSRF token. Please refresh the page.']);
        exit;
    }
}

switch ($action) {
    case 'getProfile':
        $conn = dbConnect();
        $stmt = $conn->prepare("SELECT u.id, u.username, u.nama, u.role, u.email, u.foto, u.nip, u.rombel, u.aktif, u.last_login,
            p.jk, p.nik, p.tempat_lahir, p.tgl_lahir, p.agama, p.status_nikah, p.jml_anak,
            p.nip as ptk_nip, p.nuptk, p.jenis_ptk, p.status_kepeg, p.golongan, p.sertifikasi,
            p.pendidikan, p.jurusan, p.perguruan_tinggi, p.tahun_lulus,
            p.alamat, p.hp as ptk_hp, p.email as ptk_email,
            p.mapel_diampu, p.rombel_diampu, p.tugas_tambahan,
            p.foto as ptk_foto, p.catatan as ptk_catatan
            FROM users u LEFT JOIN dataptk p ON u.id = p.user_id WHERE u.id = ?");
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        if ($user) {
            echo json_encode(['success' => true, 'data' => $user]);
        } else {
            echo json_encode(['error' => 'User tidak ditemukan']);
        }
        exit;

    case 'updateProfile':
        $data = $payload['data'] ?? [];
        if (empty($data)) {
            echo json_encode(['error' => 'Tidak ada data yang diperbarui']);
            exit;
        }
        $allowedFields = ['nama', 'email', 'nip'];
        $updateData = [];
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }
        if (empty($updateData)) {
            echo json_encode(['error' => 'Tidak ada field yang valid untuk diperbarui']);
            exit;
        }
        $result = dbUpdate('users', $userId, $updateData);
        // Also sync fields to DataPTK if changed
        $conn = dbConnect();
        if (isset($updateData['nama'])) {
            $conn->query("UPDATE dataptk SET nama='" . $conn->real_escape_string($updateData['nama']) . "' WHERE user_id='" . $conn->real_escape_string($userId) . "'");
        }
        if (isset($updateData['nip'])) {
            $conn->query("UPDATE dataptk SET nip='" . $conn->real_escape_string($updateData['nip']) . "' WHERE user_id='" . $conn->real_escape_string($userId) . "'");
        }
        if (isset($result['success']) && $result['success']) {
            if (isset($updateData['nama'])) $_SESSION['nama'] = $updateData['nama'];
            if (isset($updateData['email'])) $_SESSION['email'] = $updateData['email'];
            logActivity('ubah', 'profil', 'Memperbarui profil sendiri');
        }
        echo json_encode($result);
        exit;

    case 'uploadFoto':
        $foto = $payload['foto'] ?? '';
        $filename = $payload['filename'] ?? '';
        if (empty($foto) || empty($filename)) {
            echo json_encode(['error' => 'Data foto tidak valid']);
            exit;
        }
        // Decode base64
        $imageData = base64_decode($foto);
        if ($imageData === false) {
            echo json_encode(['error' => 'Data foto tidak valid (base64 decode gagal)']);
            exit;
        }
        // Create uploads directory if not exists
        $uploadDir = getUserUploadDir($userId);
        $safeFilename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        $fullPath = $uploadDir . $safeFilename;
        if (file_put_contents($fullPath, $imageData)) {
            // Delete old foto if exists
            $conn = dbConnect();
            $oldStmt = $conn->prepare("SELECT foto FROM users WHERE id = ?");
            $oldStmt->bind_param("s", $userId);
            $oldStmt->execute();
            $oldResult = $oldStmt->get_result();
            $oldRow = $oldResult->fetch_assoc();
            if (!empty($oldRow['foto']) && $oldRow['foto'] !== $safeFilename) {
                $oldFile = $uploadDir . $oldRow['foto'];
                if (file_exists($oldFile)) unlink($oldFile);
            }
            // Update DB
            $result = dbUpdate('users', $userId, ['foto' => $safeFilename]);
            $_SESSION['foto'] = $safeFilename;
            logActivity('upload', 'profil', 'Mengganti foto profil');
            // Sync foto to dataptk so admin sees the new photo
            $fotoPath = 'data/uploads/' . $userId . '/' . $safeFilename;
            $conn->query("UPDATE dataptk SET foto='" . $conn->real_escape_string($fotoPath) . "' WHERE user_id='" . $conn->real_escape_string($userId) . "'");
            echo json_encode(['success' => true, 'filename' => $safeFilename, 'user_id' => $userId]);
        } else {
            echo json_encode(['error' => 'Gagal menyimpan foto']);
        }
        exit;

    case 'changePassword':
        $currentPassword = $payload['current_password'] ?? '';
        $newPassword = $payload['new_password'] ?? '';
        if (empty($currentPassword) || empty($newPassword)) {
            echo json_encode(['error' => 'Password lama dan baru wajib diisi']);
            exit;
        }
        if (strlen($newPassword) < 6) {
            echo json_encode(['error' => 'Password baru minimal 6 karakter']);
            exit;
        }
        $conn = dbConnect();
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if (!$row || !password_verify($currentPassword, $row['password'])) {
            echo json_encode(['error' => 'Password lama salah']);
            exit;
        }
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $updateResult = dbUpdate('users', $userId, ['password' => $hashed]);
        logActivity('ubah', 'profil', 'Mengubah password');
        echo json_encode($updateResult);
        exit;

    case 'updatePTK':
        $data = $payload['data'] ?? [];
        if (empty($data)) {
            echo json_encode(['error' => 'Tidak ada data yang diperbarui']);
            exit;
        }
        // Only allow user-editable fields
        $allowedPTKFields = [
            'nik', 'tempat_lahir', 'tgl_lahir', 'agama', 'status_nikah', 'jml_anak',
            'alamat', 'hp', 'email',
            'mapel_diampu', 'rombel_diampu', 'tugas_tambahan', 'catatan'
        ];
        $updateData = [];
        foreach ($allowedPTKFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }
        if (empty($updateData)) {
            echo json_encode(['error' => 'Tidak ada field yang valid untuk diperbarui']);
            exit;
        }
        // Verify user owns a PTK record
        $conn = dbConnect();
        $stmt = $conn->prepare("SELECT id FROM dataptk WHERE user_id = ?");
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $ptkRow = $stmt->get_result()->fetch_assoc();
        if (!$ptkRow) {
            echo json_encode(['error' => 'Data PTK tidak ditemukan untuk akun ini']);
            exit;
        }
        $result = dbUpdate('dataptk', $ptkRow['id'], $updateData);
        if (isset($result['success']) && $result['success']) {
            // Reverse sync rombel_diampu → users.rombel
            if (isset($updateData['rombel_diampu'])) {
                $conn->query("UPDATE users SET rombel='" . $conn->real_escape_string($updateData['rombel_diampu']) . "' WHERE id='" . $conn->real_escape_string($userId) . "'");
            }
            logActivity('ubah', 'profil', 'Memperbarui data PTK sendiri');
        }
        echo json_encode($result);
        exit;

    case 'uploadPTKDoc':
        $field = $payload['field'] ?? '';
        $docData = $payload['data'] ?? '';
        $docName = $payload['name'] ?? '';
        if (empty($field) || empty($docData) || empty($docName)) {
            echo json_encode(['error' => 'Data dokumen tidak valid']);
            exit;
        }
        // Decode base64
        $decoded = base64_decode($docData);
        if ($decoded === false) {
            echo json_encode(['error' => 'Data dokumen tidak valid (base64 decode gagal)']);
            exit;
        }
        // Save to ptk_docs directory
        $docDir = __DIR__ . '/../data/uploads/ptk_docs/';
        if (!is_dir($docDir)) mkdir($docDir, 0755, true);
        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $docName);
        $fullPath = $docDir . $safeName;
        if (file_put_contents($fullPath, $decoded)) {
            // Update dataptk field
            $conn = dbConnect();
            $stmt = $conn->prepare("SELECT id FROM dataptk WHERE user_id = ?");
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $ptkRow = $stmt->get_result()->fetch_assoc();
            if ($ptkRow) {
                $safeField = $conn->real_escape_string($field);
                $conn->query("UPDATE dataptk SET `$safeField`='$safeName' WHERE id='" . $conn->real_escape_string($ptkRow['id']) . "'");
            }
            echo json_encode(['success' => true, 'field' => $field, 'filename' => $safeName]);
        } else {
            echo json_encode(['error' => 'Gagal menyimpan dokumen']);
        }
        exit;

    default:
        echo json_encode(['error' => 'Unknown action: ' . $action]);
        exit;
}
?>
