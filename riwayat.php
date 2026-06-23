<?php
require_once 'config.php';
requireLogin();
$pageTitle = 'Riwayat Aktivitas';
$pageSubtitle = 'Log aktivitas pengguna sistem';
$user = currentUser();
$isAdmin = isAdmin();
include 'includes/header.php';

$selectedUser = $_GET['user_id'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$actionFilter = $_GET['action'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 25;
$offset = ($page - 1) * $perPage;

$conn = dbConnect();

// Get total count
$where = [];
$params = [];
if (!$isAdmin) {
    $where[] = '`user_id` = ' . $conn->real_escape_string($user['id']);
} elseif ($selectedUser) {
    $where[] = '`user_id` = \'' . $conn->real_escape_string($selectedUser) . '\'';
}
if ($dateFrom) { $where[] = '`created_at` >= ' . $conn->real_escape_string($dateFrom . ' 00:00:00'); }
if ($dateTo) { $where[] = '`created_at` <= ' . $conn->real_escape_string($dateTo . ' 23:59:59'); }
if ($actionFilter) { $where[] = '`action` = ' . $conn->real_escape_string($actionFilter); }
$categoryFilter = $_GET['category'] ?? '';
if ($categoryFilter) { $where[] = '`category` = ' . $conn->real_escape_string($categoryFilter); }

$whereSQL = count($where) ? ' WHERE ' . implode(' AND ', $where) : '';

$countResult = $conn->query("SELECT COUNT(*) as cnt FROM `ActivityLog` $whereSQL");
$totalRows = $countResult ? (int)$countResult->fetch_assoc()['cnt'] : 0;
$totalPages = max(1, ceil($totalRows / $perPage));

$sql = "SELECT * FROM `ActivityLog` $whereSQL ORDER BY `created_at` DESC LIMIT $perPage OFFSET $offset";
$result = $conn->query($sql);
$logs = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
}

$allUsers = [];
if ($isAdmin) {
    $userResult = $conn->query("SELECT `id`, `nama` FROM `users` WHERE `aktif`='1' ORDER BY `nama`");
    if ($userResult) {
        while ($row = $userResult->fetch_assoc()) {
            $allUsers[] = $row;
        }
    }
}

$actionIcons = [
    'login' => '<i data-lucide="lock"></i>', 'logout' => '<i data-lucide="log-out"></i>', 'login_gagal' => '<i data-lucide="x-circle"></i>',
    'tambah' => '<i data-lucide="plus"></i>', 'ubah' => '<i data-lucide="pencil"></i>', 'edit' => '<i data-lucide="pencil"></i>', 'hapus' => '<i data-lucide="trash-2"></i>',
    'simpan' => '<i data-lucide="save"></i>', 'generate' => '<i data-lucide="dice-5"></i>', 'cetak' => '<i data-lucide="printer"></i>',
    'upload' => '<i data-lucide="upload"></i>', 'export' => '<i data-lucide="download"></i>',
];
$actionColors = [
    'login' => 'badge-success', 'logout' => 'badge-info', 'login_gagal' => 'badge-danger',
    'tambah' => 'badge-primary', 'ubah' => 'badge-warning', 'edit' => 'badge-warning', 'hapus' => 'badge-danger',
    'simpan' => 'badge-success', 'generate' => 'badge-teal', 'cetak' => 'badge-info',
    'upload' => 'badge-orange', 'export' => 'badge-info',
];
?>

<div class="section-header">
  <div>
    <h2><span class="sh-icon"><i data-lucide="history"></i></span> Riwayat Aktivitas</h2>
    <p>Log seluruh aktivitas pengguna sistem <?= $isAdmin ? '(Semua User)' : '(Saya Saja)' ?></p>
  </div>
</div>

<!-- FILTER -->
<div class="card" style="margin-bottom:16px">
  <div class="card-body" style="padding:14px 18px">
    <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
      <?php if ($isAdmin): ?>
      <div style="min-width:180px">
        <label class="form-label"><i data-lucide="user"></i> User</label>
        <select name="user_id" class="form-select">
          <option value="">Semua User</option>
          <?php foreach ($allUsers as $u): ?>
            <option value="<?= htmlspecialchars($u['id']) ?>" <?= $selectedUser == $u['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['nama']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>
      <div style="min-width:150px">
        <label class="form-label"><i data-lucide="calendar-days"></i> Dari Tanggal</label>
        <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($dateFrom) ?>">
      </div>
      <div style="min-width:150px">
        <label class="form-label"><i data-lucide="calendar-days"></i> Sampai Tanggal</label>
        <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($dateTo) ?>">
      </div>
      <div style="min-width:140px">
        <label class="form-label"><i data-lucide="list-checks"></i> Aksi</label>
        <select name="action" class="form-select">
          <option value="">Semua Aksi</option>
           <option value="login" <?= $actionFilter==='login'?'selected':'' ?>><i data-lucide="lock"></i> Login</option>
           <option value="logout" <?= $actionFilter==='logout'?'selected':'' ?>><i data-lucide="log-out"></i> Logout</option>
           <option value="login_gagal" <?= $actionFilter==='login_gagal'?'selected':'' ?>><i data-lucide="x-circle"></i> Login Gagal</option>
           <option value="tambah" <?= $actionFilter==='tambah'?'selected':'' ?>><i data-lucide="plus"></i> Tambah</option>
           <option value="ubah" <?= $actionFilter==='ubah'?'selected':'' ?>><i data-lucide="pencil"></i> Ubah</option>
           <option value="hapus" <?= $actionFilter==='hapus'?'selected':'' ?>><i data-lucide="trash-2"></i> Hapus</option>
           <option value="simpan" <?= $actionFilter==='simpan'?'selected':'' ?>><i data-lucide="save"></i> Simpan</option>
           <option value="generate" <?= $actionFilter==='generate'?'selected':'' ?>><i data-lucide="dice-5"></i> Generate</option>
           <option value="cetak" <?= $actionFilter==='cetak'?'selected':'' ?>><i data-lucide="printer"></i> Cetak</option>
           <option value="upload" <?= $actionFilter==='upload'?'selected':'' ?>><i data-lucide="upload"></i> Upload</option>
        </select>
      </div>
      <div style="min-width:140px">
        <label class="form-label"><i data-lucide="folder"></i> Kategori</label>
        <select name="category" class="form-select">
          <option value="">Semua Kategori</option>
           <option value="auth" <?= $categoryFilter==='auth'?'selected':'' ?>><i data-lucide="lock"></i> Autentikasi</option>
           <option value="data" <?= $categoryFilter==='data'?'selected':'' ?>><i data-lucide="bar-chart-2"></i> Data</option>
           <option value="user" <?= $categoryFilter==='user'?'selected':'' ?>><i data-lucide="user"></i> User</option>
           <option value="profil" <?= $categoryFilter==='profil'?'selected':'' ?>><i data-lucide="clipboard-list"></i> Profil</option>
           <option value="umum" <?= $categoryFilter==='umum'?'selected':'' ?>><i data-lucide="pin"></i> Umum</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary"><i data-lucide="search"></i> Filter</button>
      <a href="riwayat.php" class="btn btn-light"><i data-lucide="refresh-cw"></i> Reset</a>
    </form>
  </div>
</div>

<!-- LOG TABLE -->
<div class="card">
  <div class="card-header">
    <h3><i data-lucide="clipboard-list"></i> Log Aktivitas</h3>
    <span class="badge badge-info"><?= $totalRows ?> aktivitas</span>
  </div>
  <div class="table-wrapper">
    <table class="data-table">
      <thead>
        <tr>
          <th style="width:50px">No</th>
          <th style="width:160px">Waktu</th>
          <th style="width:140px">Pengguna</th>
          <th style="width:80px">Role</th>
          <th style="width:100px">Aksi</th>
          <th style="width:100px">Kategori</th>
          <th>Deskripsi</th>
          <th style="width:100px">IP</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($logs)): ?>
          <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-muted)">
            <div style="font-size:2.5rem;margin-bottom:10px"><i data-lucide="history"></i></div>
            <h4>Tidak ada aktivitas</h4>
            <p style="font-size:.85rem">Belum ada log aktivitas yang tercatat</p>
          </td></tr>
        <?php else: ?>
          <?php foreach ($logs as $i => $log): ?>
            <tr>
              <td><?= $offset + $i + 1 ?></td>
              <td style="font-size:.8rem">
                <div style="font-weight:600"><?= date('d M Y', strtotime($log['created_at'])) ?></div>
                <div style="color:var(--text-muted)"><?= date('H:i:s', strtotime($log['created_at'])) ?></div>
              </td>
              <td style="font-weight:600;font-size:.85rem"><?= htmlspecialchars($log['user_name']) ?></td>
              <td><span class="badge <?= ($log['user_role']??'')==='admin'?'badge-danger':(($log['user_role']??'')==='guru'?'badge-primary':'badge-info') ?>"><?= ($log['user_role']??'')==='admin'?'Admin':(($log['user_role']??'')==='guru'?'Guru':'System') ?></span></td>
              <td>
                <?php $icon = $actionIcons[$log['action']] ?? '<i data-lucide="pin"></i>'; $color = $actionColors[$log['action']] ?? 'badge-info'; ?>
                <span class="badge <?= $color ?>"><?= $icon ?> <?= ucfirst($log['action']) ?></span>
              </td>
              <td style="font-size:.82rem;color:var(--text-muted)"><?= ucfirst($log['category']) ?></td>
              <td style="font-size:.82rem"><?= htmlspecialchars($log['description']) ?></td>
              <td style="font-size:.75rem;color:var(--text-muted)"><code><?= htmlspecialchars($log['ip_address']) ?></code></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($totalPages > 1): ?>
  <div class="card-footer">
    <div class="pagination" id="pagination-log"></div>
  </div>
  <?php endif; ?>
</div>

<?php if ($totalPages > 1): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
  TU.renderPagination(document.getElementById('pagination-log'), <?= $page ?>, <?= $totalPages ?>,
    'function(pg){window.location.href="?page="+pg+"&user_id=<?= urlencode($selectedUser) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>&action=<?= urlencode($actionFilter) ?>"}'
  );
});
</script>
<?php endif; ?>
<?php include 'includes/footer.php'; ?>
