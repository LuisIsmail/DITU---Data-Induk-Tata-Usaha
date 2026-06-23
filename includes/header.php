<?php
if (!defined('APP_NAME')) { require_once dirname(__DIR__) . '/config.php'; }
requireLogin();
$user = currentUser();
$settings = getSettings();
$kop      = getKopSekolah();
$schoolName  = $kop['school_name']  ?? 'SD Negeri 001 Gunung Sari';
$schoolShort = $kop['school_short'] ?? 'SDN 001 GUNUNG SARI';
$instansi    = $kop['instansi']     ?? 'Pemerintah Kabupaten Berau';
$dinasName   = $kop['dinas']        ?? 'Dinas Pendidikan';
$logoKiri    = $kop['logo_kiri']    ?? '';
$logoKanan   = $kop['logo_kanan']   ?? '';
$appLogo     = $settings['app_logo'] ?? '';
$appName     = $settings['app_name'] ?? 'TU';

$initials = strtoupper(substr($user['nama'] ?? 'U', 0, 1));
$fotoUrl  = '';
if (!empty($user['foto'])) {
    $fotoUrl = getUserUploadUrl($user['id'], $user['foto']) . '?t=' . @filemtime(getUserUploadDir($user['id']) . $user['foto']);
}

// Active page
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

$menuItems = [
    ['href' => 'index.php',     'icon' => 'layout-dashboard',  'label' => 'Dashboard',          'group' => 'main'],
    ['href' => 'siswa.php',     'icon' => 'graduation-cap',    'label' => 'Data Siswa',          'group' => 'akademik'],
    ['href' => 'ptk.php',       'icon' => 'briefcase',         'label' => 'Data PTK',            'group' => 'akademik', 'admin' => true],
    ['href' => 'jadwal.php',    'icon' => 'calendar-days',     'label' => 'Jadwal Pelajaran',    'group' => 'akademik'],
    ['href' => 'bank_soal.php', 'icon' => 'file-text',         'label' => 'Bank Soal',           'group' => 'akademik'],
    ['href' => 'buat_soal.php', 'icon' => 'pencil-line',       'label' => 'Buat Soal Ujian',    'group' => 'akademik'],
    ['href' => 'paket_soal.php','icon' => 'package',           'label' => 'Paket Soal',          'group' => 'akademik'],
    ['href' => 'nilai.php',     'icon' => 'clipboard-list',    'label' => 'Daftar Nilai',        'group' => 'akademik'],
    ['href' => 'absensi.php',   'icon' => 'check-square',      'label' => 'Absensi',             'group' => 'akademik'],
    ['href' => 'rapor.php',     'icon' => 'file-bar-chart',    'label' => 'Cetak Rapor',          'group' => 'akademik'],
    ['href' => 'kop.php',       'icon' => 'school',            'label' => 'Profil Sekolah',      'group' => 'pengaturan', 'admin' => true],
    ['href' => 'users.php',     'icon' => 'users',             'label' => 'Manajemen User',      'group' => 'pengaturan', 'admin' => true],
    ['href' => 'riwayat.php',   'icon' => 'history',           'label' => 'Riwayat Aktivitas', 'group' => 'pengaturan', 'admin' => true],
    ['href' => 'settings.php',  'icon' => 'settings',          'label' => 'Pengaturan',          'group' => 'pengaturan', 'admin' => true],
    ['href' => 'backup.php',    'icon' => 'shield-check',      'label' => 'Backup & Restore',     'group' => 'pengaturan', 'admin' => true],
    ['href' => 'import.php',    'icon' => 'upload',            'label' => 'Import Data',           'group' => 'pengaturan', 'admin' => true],
    ['href' => 'profil.php',    'icon' => 'user',              'label' => 'Profil Saya',         'group' => 'akun'],
];

$groups = ['main' => 'Menu Utama', 'akademik' => 'Akademik', 'pengaturan' => 'Pengaturan', 'akun' => 'Akun'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?? 'Dashboard' ?> — <?= htmlspecialchars($appName) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <meta name="csrf-token" content="<?= generateCSRFToken() ?>">
  <link rel="stylesheet" href="assets/css/style.css">
  <script src="https://unpkg.com/lucide@latest"></script>
  <?php if (isset($extraCss)) echo $extraCss; ?>
</head>
<body>
<div id="toast-container"></div>
<div class="app-layout">

  <!-- SIDEBAR -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <div class="brand-icon">
        <?php if (!empty($appLogo)):
          $logoFullPath = dirname(__DIR__) . '/data/uploads/' . $appLogo;
          $logoModTime = file_exists($logoFullPath) ? filemtime($logoFullPath) : time();
        ?>
          <img src="data/uploads/<?= htmlspecialchars($appLogo) ?>?t=<?= $logoModTime ?>" alt="Logo" style="width:100%;height:100%;object-fit:contain">
        <?php else: ?>
          <i data-lucide="school"></i>
        <?php endif; ?>
      </div>
      <div class="brand-text">
        <h1><?= htmlspecialchars($appName) ?></h1>
        <p><?= APP_FULL_NAME ?></p>
      </div>
    </div>
    <nav class="sidebar-menu">
      <?php
      $prevGroup = null;
      foreach ($menuItems as $item):
        if (!empty($item['admin']) && !isAdmin()) continue;
        if ($item['group'] !== $prevGroup):
          if ($prevGroup !== null) echo '</ul></div>';
          echo '<div class="menu-group"><div class="menu-group-label">' . ($groups[$item['group']] ?? '') . '</div><ul>';
          $prevGroup = $item['group'];
        endif;
        $active = (basename($item['href'], '.php') === $currentPage) ? 'active' : '';
      ?>
      <li>
        <a href="<?= $item['href'] ?>" class="menu-item <?= $active ?>">
          <span class="menu-icon"><i data-lucide="<?= $item['icon'] ?>"></i></span>
          <?= $item['label'] ?>
        </a>
      </li>
      <?php endforeach; ?>
      <?php if ($prevGroup) echo '</ul></div>'; ?>
    </nav>
    <div class="sidebar-footer">
      <a href="logout.php" class="sidebar-user" onclick="return confirm('Yakin ingin keluar?')">
        <div class="avatar">
          <?php if ($fotoUrl): ?>
            <img src="<?= $fotoUrl ?>" alt="foto">
          <?php else: ?>
            <?= $initials ?>
          <?php endif; ?>
        </div>
        <div class="user-info">
          <div class="uname"><?= htmlspecialchars($user['nama']) ?></div>
          <div class="urole"><?= $user['role'] === 'admin' ? '<i data-lucide="shield"></i> Admin' : '<i data-lucide="book-open"></i> Guru' ?></div>
        </div>
        <span class="logout-btn"><i data-lucide="log-out"></i></span>
      </a>
    </div>
  </aside>

  <!-- MAIN -->
  <div class="main-content">
    <!-- TOPBAR -->
    <header class="topbar">
      <button class="topbar-menu-btn" id="menu-toggle"><i data-lucide="menu"></i></button>
      <div class="topbar-title">
        <?= $pageTitle ?? 'Dashboard' ?>
        <small><?= $pageSubtitle ?? $schoolShort ?></small>
      </div>
      <div class="topbar-actions">
        <button class="theme-toggle-btn" id="theme-toggle" onclick="toggleTheme()" title="Toggle Dark/Light Mode"><i data-lucide="moon"></i></button>
        <a href="profil.php" class="topbar-profile">
          <div class="tp-avatar">
            <?php if ($fotoUrl): ?>
              <img src="<?= $fotoUrl ?>" alt="foto">
            <?php else: ?>
              <?= $initials ?>
            <?php endif; ?>
          </div>
        </a>
      </div>
    </header>

    <!-- PAGE CONTENT -->
    <main class="page-content">
