<?php
$pageTitle = 'Absensi';
$pageSubtitle = 'Daftar hadir siswa harian';
require_once 'includes/header.php';
requireLogin();
$conn = dbConnect();

// Get rombel list
$rombels = [];
$r = $conn->query("SELECT * FROM rombel ORDER BY id ASC");
if ($r) while ($row = $r->fetch_assoc()) $rombels[] = $row;

// Get today's date
$today = date('Y-m-d');

// Get current user rombel (for guru filtering)
$userRombel = $_SESSION['rombel'] ?? '';
$userRole = $_SESSION['role'] ?? 'guru';
?>

<style>
@page { size: auto; margin: 0; }
@media print {
  body * { visibility: hidden; }
  .print-area, .print-area * { visibility: visible !important; }
  .print-area { position: absolute; left: 0; top: 0; width: 100%; padding: 15mm 20mm; background: white; }
  .no-print { display: none !important; }
}
.absence-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px; }
.absence-card { padding: 12px 16px; border-radius: 10px; background: var(--bg-card); border: 1px solid var(--border); display: flex; align-items: center; gap: 12px; transition: all .2s; cursor: default; }
.absence-card:hover { border-color: var(--primary); }
.absence-card .avatar-sm { width: 36px; height: 36px; border-radius: 50%; background: var(--primary-light); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .75rem; color: var(--primary); flex-shrink: 0; }
.absence-card .siswa-info { flex: 1; }
.absence-card .siswa-name { font-weight: 600; font-size: .88rem; }
.absence-card .siswa-nipd { font-size: .72rem; color: var(--text-light); }
.absence-card .status-btns { display: flex; gap: 4px; }
.absence-card .status-btns button { padding: 4px 8px; border-radius: 6px; border: 1.5px solid var(--border); background: transparent; cursor: pointer; font-size: .72rem; font-weight: 600; transition: all .15s; }
.absence-card .status-btns button.active-hadir { background: #d1fae5; border-color: #10b981; color: #065f46; }
.absence-card .status-btns button.active-sakit { background: #fef3c7; border-color: #f59e0b; color: #92400e; }
.absence-card .status-btns button.active-ijin { background: #dbeafe; border-color: #3b82f6; color: #1e40af; }
.absence-card .status-btns button.active-alpha { background: #fee2e2; border-color: #ef4444; color: #991b1b; }
.summary-bar { display: flex; gap: 16px; flex-wrap: wrap; padding: 12px 16px; background: var(--bg-card); border: 1px solid var(--border); border-radius: 10px; margin-bottom: 20px; }
.summary-bar .sum-item { display: flex; align-items: center; gap: 6px; font-size: .85rem; }
.summary-bar .sum-dot { width: 10px; height: 10px; border-radius: 50%; }
</style>

<div class="content-wrapper">
  <div class="content-header">
    <div>
      <h2><i data-lucide="check-circle"></i> Absensi Siswa</h2>
      <p class="text-muted">Input daftar hadir siswa per rombel per tanggal</p>
    </div>
    <div class="header-actions">
      <button class="btn btn-success" onclick="exportAbsensi()" id="btn-export"><i data-lucide="file-text"></i> Export CSV</button>
    </div>
  </div>

  <!-- Controls -->
  <div class="card" style="margin-bottom:20px">
    <div class="card-body" style="display:flex;gap:12px;flex-wrap:wrap;align-items:end">
      <div class="form-group" style="min-width:150px;margin:0">
        <label class="form-label"><i data-lucide="calendar"></i> Tanggal</label>
        <input type="date" id="absen-tanggal" class="form-control" value="<?= $today ?>">
      </div>
      <div class="form-group" style="min-width:140px;margin:0">
        <label class="form-label"><i data-lucide="school"></i> Rombel</label>
        <select id="absen-rombel" class="form-control" onchange="loadSiswa()">
          <option value="">Pilih Rombel</option>
          <?php foreach ($rombels as $rb): ?>
            <?php if (!empty($userRombel) && $userRole !== 'admin' && $rb['nama_rombel'] !== $userRombel) continue; ?>
            <option value="<?= htmlspecialchars($rb['nama_rombel']) ?>" <?= ($rb['nama_rombel'] === $userRombel && $userRole !== 'admin') ? 'selected' : '' ?>><?= htmlspecialchars($rb['nama_rombel']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button class="btn btn-primary" onclick="loadSiswa()"><i data-lucide="refresh-cw"></i> Muat Data</button>
    </div>
  </div>

  <!-- Summary -->
  <div class="summary-bar" id="summary-bar" style="display:none">
    <div class="sum-item"><span class="sum-dot" style="background:#10b981"></span> Hadir: <strong id="sum-hadir">0</strong></div>
    <div class="sum-item"><span class="sum-dot" style="background:#f59e0b"></span> Sakit: <strong id="sum-sakit">0</strong></div>
    <div class="sum-item"><span class="sum-dot" style="background:#3b82f6"></span> Ijin: <strong id="sum-ijin">0</strong></div>
    <div class="sum-item"><span class="sum-dot" style="background:#ef4444"></span> Alpha: <strong id="sum-alpha">0</strong></div>
    <div class="sum-item" style="margin-left:auto;font-weight:700">Total: <strong id="sum-total">0</strong></div>
  </div>

  <!-- Absence Cards -->
  <div class="absence-grid" id="absence-grid">
    <div class="empty-state" id="empty-absen" style="grid-column:1/-1">
      <div class="empty-icon"><i data-lucide="clipboard-list"></i></div>
      <h3>Pilih Rombel dan Tanggal</h3>
      <p>Pilih rombel dan tanggal terlebih dahulu untuk memuat daftar siswa</p>
    </div>
  </div>

  <div style="text-align:center;margin-top:20px;display:none" id="save-section">
    <button class="btn btn-primary btn-lg" onclick="saveAbsensi()" id="btn-save">
      <i data-lucide="save"></i> Simpan Absensi
    </button>
  </div>
</div>

<div id="toast-container"></div>

<?php
$todayJs = addslashes($today);

$extraJs = <<<JS
<script>
let siswaList = [];
let absenceData = {};

document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('absen-tanggal').value = '$todayJs';
});

async function loadSiswa() {
  const rombel = document.getElementById('absen-rombel').value;
  const tanggal = document.getElementById('absen-tanggal').value;
  if (!rombel) { TU.toast('Pilih rombel terlebih dahulu', 'error'); return; }
  
  const grid = document.getElementById('absence-grid');
  grid.innerHTML = '<div style="text-align:center;grid-column:1/-1;padding:30px">⏳ Memuat data...</div>';
  
  const result = await GS.getData('DataSiswa', { rombel });
  if (!result.success) { TU.toast('Gagal memuat data siswa', 'error'); return; }
  
  siswaList = result.data;
  absenceData = {};
  
  // Load existing absence data
  const existResult = await GS.getData('Absensi', { rombel, tanggal });
  if (existResult.success && existResult.data) {
    existResult.data.forEach(a => {
      absenceData[a.siswa_id] = a.status;
    });
  }
  
  renderAbsenceCards();
}

function renderAbsenceCards() {
  const grid = document.getElementById('absence-grid');
  const statuses = ['hadir', 'sakit', 'ijin', 'alpha'];
  
  if (siswaList.length === 0) {
    grid.innerHTML = '<div class="empty-state" style="grid-column:1/-1"><div class="empty-icon"><i data-lucide=\"graduation-cap\"></i></div><h3>Tidak Ada Siswa</h3><p>Tidak ditemukan siswa di rombel ini</p></div>';
    document.getElementById('summary-bar').style.display = 'none';
    document.getElementById('save-section').style.display = 'none';
    return;
  }
  
  grid.innerHTML = siswaList.map(s => {
    const initials = (s.nama || 'S').substring(0, 1).toUpperCase();
    const current = absenceData[s.id] || 'hadir';
    const escNama = (s.nama || '').replace(/'/g, "\\'");
    
    return '<div class="absence-card" data-id="' + s.id + '">' +
      '<div class="avatar-sm">' + initials + '</div>' +
      '<div class="siswa-info">' +
        '<div class="siswa-name">' + escNama + '</div>' +
        '<div class="siswa-nipd">NIPD: ' + (s.nipd || '-') + '</div>' +
      '</div>' +
      '<div class="status-btns">' +
        statuses.map(st => 
          '<button class="active-' + (current === st ? st : '') + '" data-status="' + st + '" onclick="setAbsence(\'' + s.id + '\', \'' + st + '\')">' + 
            (st === 'hadir' ? '<i data-lucide=\"check-circle\"></i>' : st === 'sakit' ? '<i data-lucide=\"thermometer\"></i>' : st === 'ijin' ? '<i data-lucide=\"file-text\"></i>' : '<i data-lucide=\"x-circle\"></i>') + 
          '</button>'
        ).join('') +
      '</div>' +
    '</div>';
  }).join('');
  
  document.getElementById('summary-bar').style.display = 'flex';
  document.getElementById('save-section').style.display = 'block';
  updateSummary();
  lucide.createIcons();
}

function setAbsence(siswaId, status) {
  absenceData[siswaId] = status;
  const card = document.querySelector('.absence-card[data-id="' + siswaId + '"]');
  if (card) {
    card.querySelectorAll('.status-btns button').forEach(btn => {
      const st = btn.getAttribute('data-status');
      btn.className = 'active-' + (st === status ? status : '');
    });
  }
  updateSummary();
}

function updateSummary() {
  const counts = { hadir: 0, sakit: 0, ijin: 0, alpha: 0 };
  siswaList.forEach(s => { counts[absenceData[s.id] || 'hadir']++; });
  document.getElementById('sum-hadir').textContent = counts.hadir;
  document.getElementById('sum-sakit').textContent = counts.sakit;
  document.getElementById('sum-ijin').textContent = counts.ijin;
  document.getElementById('sum-alpha').textContent = counts.alpha;
  document.getElementById('sum-total').textContent = siswaList.length;
}

async function saveAbsensi() {
  const rombel = document.getElementById('absen-rombel').value;
  const tanggal = document.getElementById('absen-tanggal').value;
  if (!rombel || siswaList.length === 0) { TU.toast('Tidak ada data untuk disimpan', 'error'); return; }
  
  const btn = document.getElementById('btn-save');
  TU.btnLoading(btn, true);
  
  let saved = 0;
  for (const s of siswaList) {
    const status = absenceData[s.id] || 'hadir';
    const result = await GS.call('addRow', {
      table: 'Absensi',
      data: {
        siswa_id: s.id,
        nama_siswa: s.nama || '',
        rombel: rombel,
        tanggal: tanggal,
        status: status,
        keterangan: ''
      }
    });
    if (result.success) saved++;
  }
  
  TU.btnLoading(btn, false);
  TU.toast(saved + ' absensi berhasil disimpan', 'success');
}

function exportAbsensi() {
  if (siswaList.length === 0) { TU.toast('Tidak ada data untuk di-export', 'error'); return; }
  const rombel = document.getElementById('absen-rombel').value;
  const tanggal = document.getElementById('absen-tanggal').value;
  
  let csv = 'No,Nama,NIPD,Rombel,Tanggal,Status\n';
  siswaList.forEach((s, i) => {
    const status = absenceData[s.id] || 'hadir';
    csv += (i+1) + ',"' + (s.nama||'') + '",' + (s.nipd||'') + ',' + rombel + ',' + tanggal + ',' + status + '\\n';
  });
  
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = 'absensi_' + rombel + '_' + tanggal + '.csv';
  a.click();
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
  fetch('api/log.php', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken }, body: JSON.stringify({ action: 'export', category: 'data', description: 'Export data Absensi ke CSV' }) });
}
</script>
JS;
?>

<?php require_once 'includes/footer.php'; ?>
