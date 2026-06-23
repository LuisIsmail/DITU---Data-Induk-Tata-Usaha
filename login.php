<?php
require_once 'config.php';
if (isLoggedIn()) { header('Location: index.php'); exit; }

$error = '';
$settings = getSettings();
$kop = getKopSekolah();
$schoolName = $kop['school_name'] ?? 'SD Negeri 001 Gunung Sari';
$appLogo = $settings['app_logo'] ?? '';

// Idle timeout message
if (isset($_GET['error']) && $_GET['error'] === 'timeout') {
    $error = 'Sesi telah berakhir karena tidak ada aktivitas. Silakan login kembali.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rate limiting check
    $rateCheck = checkRateLimit('login', 5, 300);
    if (!$rateCheck['allowed']) {
        $error = 'Terlalu banyak percobaan gagal. Coba lagi dalam ' . ceil($rateCheck['remaining'] / 60) . ' menit.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            $error = 'Username dan password wajib diisi.';
        } else {
            $user = findUser($username);
            if ($user && password_verify($password, $user['password'] ?? '') && ($user['aktif'] ?? '1') === '1') {
                // Maintenance mode: only admin can login
                if (isAppLocked() && $user['role'] !== ROLE_ADMIN) {
                    $error = 'Aplikasi sedang dalam pemeliharaan. Hanya admin yang dapat login.';
                } else {
                    resetRateLimit('login');
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['nama']    = $user['nama'];
                    $_SESSION['role']    = $user['role'];
                    $_SESSION['email']   = $user['email'] ?? '';
                    $_SESSION['foto']    = $user['foto'] ?? '';
                    $_SESSION['rombel']  = $user['rombel'] ?? '';
                    $_SESSION['nip']     = $user['nip'] ?? '';
                    $_SESSION['last_activity'] = time();
                    $conn = dbConnect();
                    $conn->query("UPDATE Users SET last_login=NOW() WHERE id='" . $conn->real_escape_string($user['id']) . "'");
                    logActivity('login', 'auth', 'Berhasil login ke sistem');
                    header('Location: index.php');
                    exit;
                }
            } else {
                recordRateLimitAttempt('login');
                logActivity('login_gagal', 'auth', 'Percobaan login gagal untuk username: ' . $username);
                $error = 'Username atau password salah.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — <?= htmlspecialchars($settings['app_name'] ?? 'TU') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div id="toast-container"></div>
<div class="login-page">
  <div class="login-card">
    <div class="login-logo">
      <div class="logo-icon">
        <?php if (!empty($appLogo)): ?>
          <img src="data/uploads/<?= htmlspecialchars($appLogo) ?>?t=<?= @filemtime(__DIR__ . '/data/uploads/' . $appLogo) ?>" alt="Logo" style="width:100%;height:100%;object-fit:contain;border-radius:0;">
        <?php else: ?>
          <i data-lucide="school"></i>
        <?php endif; ?>
      </div>
      <h2><?= htmlspecialchars($settings['app_name'] ?? 'TU') ?></h2>
      <p style="margin-top:4px;font-size:.72rem;color:var(--text-light)"><?= htmlspecialchars($schoolName) ?></p>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger">
      <span class="alert-icon"><i data-lucide="x-circle"></i></span>
      <span><?= htmlspecialchars($error) ?></span>
    </div>
    <?php endif; ?>

    <form method="POST" id="login-form">
      <?= csrfField() ?>
      <div class="form-group">
        <label class="form-label">Username <span class="req">*</span></label>
        <div class="input-group">
          <input type="text" name="username" class="form-control"
                 placeholder="Masukkan username"
                 value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autocomplete="username">
          <span class="ig-btn"><i data-lucide="user"></i></span>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Password <span class="req">*</span></label>
        <div class="input-group">
          <input type="password" name="password" id="pwd" class="form-control"
                 placeholder="Masukkan password" required autocomplete="current-password">
          <span class="ig-btn" onclick="togglePwd()" style="cursor:pointer" id="eye-btn"><i data-lucide="eye"></i></span>
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-block btn-lg" id="login-btn">
        <i data-lucide="log-in"></i> Masuk
      </button>
    </form>

   

    <div style="margin-top:24px;padding-top:20px;border-top:1px solid var(--border);text-align:center">
      <p style="font-size:.68rem;color:var(--text-light)">
        &#169; <?= date('Y') ?> TU v<?= APP_VERSION ?> — <?= htmlspecialchars($schoolName) ?>
      </p>
    </div>
  </div>
</div>

  <script src="assets/js/app.js"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>lucide.createIcons();</script>
<script>
function togglePwd() {
  const p = document.getElementById('pwd');
  const e = document.getElementById('eye-btn');
  if (p.type === 'password') { p.type = 'text'; e.innerHTML = '<i data-lucide="eye-off"></i>'; }
  else { p.type = 'password'; e.innerHTML = '<i data-lucide="eye"></i>'; }
  lucide.createIcons();
}
document.getElementById('login-form').addEventListener('submit', function() {
  const btn = document.getElementById('login-btn');
  TU.btnLoading(btn, true);
  lucide.createIcons();
});
</script>
</body>
</html>
