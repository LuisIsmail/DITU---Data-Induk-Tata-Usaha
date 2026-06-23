<?php
$pageTitle = 'Paket Soal';
$pageSubtitle = 'Kelola paket soal yang sudah tersimpan';
require_once 'includes/header.php';
require_once 'includes/kop_helper.php';
requireLogin();

// Kop Sekolah (dedicated table)
$kop = getKopSekolah();
$schoolName = $kop['school_name'] ?? 'SD Negeri 001 Gunung Sari';
$instansi = $kop['instansi'] ?? 'Pemerintah Kabupaten Berau';
$dinasName = $kop['dinas'] ?? 'Dinas Pendidikan';
$logoKiri = $kop['logo_kiri'] ?? '';
$logoKanan = $kop['logo_kanan'] ?? '';
$kepsek = $kop['kepala_sekolah'] ?? '';
$nipKepsek = $kop['nip_kepsek'] ?? '';

// Fetch all paket soal via API
$pakets = [];
$conn = dbConnect();
$result = $conn->query("SELECT * FROM paketsoal ORDER BY dibuat_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $pakets[] = $row;
    }
}
?>

<style>
/* Print styles — flexible A4 / F4 (Folio) */
@page { size: auto; margin: 0; }
@media print {
  body * { visibility: hidden; }
  .print-area, .print-area * { visibility: visible !important; }
  .print-area { 
    position: absolute; left: 0; top: 0; width: 100%; 
    padding: 15mm 20mm; background: white; font-family: 'Times New Roman', serif;
  }
  .no-print { display: none !important; }
  .print-area .kop-header { text-align: center; margin-bottom: 12px; }
  .print-area .kop-header h3 { margin: 0; font-size: 13pt; text-transform: uppercase; letter-spacing: .5px; }
  .print-area .kop-header p { margin: 1px 0; font-size: 11pt; }
  .print-area .kop-line { border-top: 2px solid #000; margin: 8px 0; }
  .print-area .soal-item { margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #e5e7eb; page-break-inside: avoid; }
  .print-area .soal-item:last-child { border-bottom: none; }
  .print-area .soal-num { font-weight: 700; color: #1e293b; }
  .print-area .soal-pilihan { margin-left: 24px; margin-top: 4px; }
  .print-area .soal-pilihan p { margin: 3px 0; }
  .print-area .kunci-text { color: #2563eb; font-weight: 600; }
  .print-area .pembahasan { color: #6b7280; font-style: italic; font-size: 10pt; margin-top: 4px; }
}

/* Stats pill badges */
.stats-pills { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
.stats-pills .stat-pill {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 10px 18px; border-radius: 50px;
  background: var(--bg-card); border: 1px solid var(--border);
  font-size: .85rem; font-weight: 600; color: var(--text);
  transition: all .2s ease; white-space: nowrap;
}
.stats-pills .stat-pill:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,.06); }
.stats-pills .stat-pill .pill-icon { font-size: 1.1rem; }
.stats-pills .stat-pill .pill-num { font-size: 1.15rem; font-weight: 800; }
.stats-pills .stat-pill.pill-total .pill-num { color: #0d9488; }
.stats-pills .stat-pill.pill-pg .pill-num { color: #2563eb; }
.stats-pills .stat-pill.pill-isian .pill-num { color: #059669; }
.stats-pills .stat-pill.pill-uraian .pill-num { color: #ea580c; }

/* Modern table */
#table-paket { border-collapse: separate; border-spacing: 0; width: 100%; }
#table-paket thead tr { background: linear-gradient(135deg, var(--primary), var(--accent)); }
#table-paket thead th { padding: 12px 14px; color: #fff; font-weight: 600; font-size: .8rem; text-transform: uppercase; letter-spacing: .3px; white-space: nowrap; }
#table-paket tbody tr { transition: all .2s; border-bottom: 1px solid var(--border); }
#table-paket tbody tr:hover { background: var(--primary-light); transform: scale(1.002); }
#table-paket tbody td { padding: 10px 14px; font-size: .85rem; vertical-align: middle; }
#table-paket tbody td:first-child { border-radius: 8px 0 0 8px; }
#table-paket tbody td:last-child { border-radius: 0 8px 8px 0; }

/* Action buttons */
.action-btns { display: flex; gap: 6px; }
.action-btns .btn { transition: all .2s; }
.action-btns .btn:hover { transform: translateY(-1px); }

/* Preview modal improvements */
.modal-lg { max-width: 800px; }
#preview-content { font-family: 'Times New Roman', serif; line-height: 1.6; }
#preview-content h2 { font-size: 14pt; margin-bottom: 2px; text-transform: uppercase; }
#preview-content h3 { font-size: 12pt; margin-top: 10px; }

/* Dark mode overrides */
[data-theme="dark"] .stat-pill.pill-total .pill-num { color: #2dd4bf; }
[data-theme="dark"] .stat-pill.pill-pg .pill-num { color: #60a5fa; }
[data-theme="dark"] .stat-pill.pill-isian .pill-num { color: #34d399; }
[data-theme="dark"] .stat-pill.pill-uraian .pill-num { color: #fb923c; }
[data-theme="dark"] #preview-content { color: var(--text); }

/* Print override — kop surat always black on white */
@media print {
  .kop-print-area { color: #000 !important; }
  .kop-print-area * { color: inherit !important; }
}
</style>

<div class="content-wrapper">
  <!-- Header Section -->
  <div class="content-header">
    <div>
      <h2><i data-lucide="package"></i> Manajemen Paket Soal</h2>
      <p class="text-muted">Lihat, cetak, dan hapus paket soal ujian yang sudah tersimpan</p>
    </div>
    <div class="header-actions">
      <a href="buat_soal.php" class="btn btn-primary">
        <span class="btn-icon"><i data-lucide="plus"></i></span> Buat Paket Baru
      </a>
    </div>
  </div>

  <!-- Stats Pills -->
  <div class="stats-pills" id="stats-row">
    <div class="stat-pill pill-total">
      <span class="pill-icon"><i data-lucide="package"></i></span>
      <span class="pill-num" id="stat-total">0</span>
      <span>Total</span>
    </div>
    <div class="stat-pill pill-pg">
      <span class="pill-icon"><i data-lucide="pencil-line"></i></span>
      <span class="pill-num" id="stat-pg">0</span>
      <span>PG</span>
    </div>
    <div class="stat-pill pill-isian">
      <span class="pill-icon"><i data-lucide="file-text"></i></span>
      <span class="pill-num" id="stat-isian">0</span>
      <span>Isian</span>
    </div>
    <div class="stat-pill pill-uraian">
      <span class="pill-icon"><i data-lucide="clipboard-list"></i></span>
      <span class="pill-num" id="stat-uraian">0</span>
      <span>Uraian</span>
    </div>
  </div>

  <!-- Filter Section -->
  <div class="card" style="margin-bottom:20px">
    <div class="card-body" style="display:flex;gap:12px;flex-wrap:wrap;align-items:end">
      <div class="form-group" style="flex:1;min-width:150px;margin:0">
        <label class="form-label"><i data-lucide="search"></i> Filter</label>
        <input type="text" id="filter-search" class="form-control" placeholder="Cari judul/mapel..." oninput="filterPakets()">
      </div>
      <div class="form-group" style="min-width:120px;margin:0">
        <label class="form-label">Semester</label>
        <select id="filter-semester" class="form-control" onchange="filterPakets()">
          <option value="">Semua</option>
          <option value="1">Ganjil (1)</option>
          <option value="2">Genap (2)</option>
        </select>
      </div>
      <div class="form-group" style="min-width:120px;margin:0">
        <label class="form-label">Kelas</label>
        <select id="filter-kelas" class="form-control" onchange="filterPakets()">
          <option value="">Semua</option>
          <?php for ($i = 1; $i <= 6; $i++): ?>
            <option value="<?= $i ?>"><?= $i ?></option>
          <?php endfor; ?>
        </select>
      </div>
    </div>
  </div>

  <!-- Paket Soal Table -->
  <div class="card">
    <div class="card-body table-responsive">
      <table class="table" id="table-paket">
        <thead>
          <tr>
            <th style="width:40px">No</th>
            <th>Judul</th>
            <th>Mapel</th>
            <th>Kelas</th>
            <th>Semester</th>
            <th>Tahun Ajaran</th>
            <th>Jumlah Soal</th>
            <th>Waktu (mnt)</th>
            <th>Dibuat Oleh</th>
            <th>Tanggal</th>
            <th style="width:150px">Aksi</th>
          </tr>
        </thead>
        <tbody id="tbody-paket">
          <!-- Rendered by JS -->
        </tbody>
      </table>
      <div id="empty-state" class="empty-state" style="display:none">
        <div class="empty-icon"><i data-lucide="package"></i></div>
        <h3>Belum Ada Paket Soal</h3>
        <p>Buat paket soal baru dari halaman <a href="buat_soal.php">Buat Soal Ujian</a></p>
      </div>
    </div>
  </div>
</div>

<!-- Preview Modal -->
<div class="modal-overlay" id="modal-preview">
  <div class="modal modal-lg">
    <div class="modal-header">
      <h4 id="preview-title"><i data-lucide="eye"></i> Preview Paket Soal</h4>
      <button class="modal-close" onclick="TU.modal.close('modal-preview')">✕</button>
    </div>
    <div class="modal-body">
      <div class="no-print" style="display:flex;gap:10px;margin-bottom:15px;flex-wrap:wrap">
        <button class="btn btn-outline-primary" id="btn-toggle-answer" onclick="toggleAnswer()"><i data-lucide="eye"></i> Tampilkan Jawaban</button>
        <button class="btn btn-primary" onclick="printPaket('soal')"><i data-lucide="printer"></i> Cetak Soal</button>
        <button class="btn btn-success" onclick="printPaket('kunci')"><i data-lucide="key"></i> Cetak Kunci Jawaban</button>
      </div>
      <div id="preview-content" class="print-area"></div>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="modal-delete">
  <div class="modal modal-sm">
    <div class="modal-header">
      <h4><i data-lucide="alert-triangle"></i> Konfirmasi Hapus</h4>
      <button class="modal-close" onclick="TU.modal.close('modal-delete')">✕</button>
    </div>
    <div class="modal-body">
      <p>Yakin ingin menghapus paket soal <strong id="delete-name"></strong>?</p>
      <p class="text-muted" style="font-size:.82rem">Tindakan ini tidak dapat dibatalkan.</p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="TU.modal.close('modal-delete')">Batal</button>
      <button class="btn btn-danger" id="btn-confirm-delete" onclick="confirmDelete()"><i data-lucide="trash-2"></i> Hapus</button>
    </div>
  </div>
</div>

<div id="toast-container"></div>

<?php
$jsonPakets = json_encode($pakets);
$schoolNameJs = addslashes($schoolName);
$instansiJs = addslashes($instansi);
$dinasNameJs = addslashes($dinasName);
$kopHtmlJs = getKopSuratJsHtml($kop);

$extraJs = <<<JS
<script>
// ===== DATA =====
let allPakets = {$jsonPakets};
let currentPreviewId = null;
let deleteId = null;
let currentSoalData = [];
let previewShowAnswers = false;
const schoolName = '{$schoolNameJs}';
const instansi = '{$instansiJs}';
const dinasName = '{$dinasNameJs}';
const kopSuratHtml = {$kopHtmlJs};

// ===== INIT =====
document.addEventListener('DOMContentLoaded', () => {
  updateStats();
  renderTable(allPakets);
  // Event delegation for preview and delete buttons
  document.getElementById('tbody-paket').addEventListener('click', function(e) {
    const btn = e.target.closest('[data-action]');
    if (!btn) return;
    const action = btn.dataset.action;
    const id = btn.dataset.id;
    if (action === 'preview') previewPaket(id);
    if (action === 'delete') deletePaket(id, btn.dataset.judul || '');
  });
});

// ===== STATS =====
function updateStats() {
  document.getElementById('stat-total').textContent = allPakets.length;
  document.getElementById('stat-pg').textContent = allPakets.filter(p => p.mapel?.includes('PG') || p.jumlah_soal > 0).length;
  document.getElementById('stat-isian').textContent = allPakets.filter(p => p.waktu >= 30).length;
  document.getElementById('stat-uraian').textContent = allPakets.filter(p => p.waktu < 30).length;
}

// ===== RENDER TABLE =====
function renderTable(data) {
  const tbody = document.getElementById('tbody-paket');
  const empty = document.getElementById('empty-state');
  
  if (data.length === 0) {
    tbody.innerHTML = '';
    empty.style.display = 'block';
    return;
  }
  empty.style.display = 'none';
  
  tbody.innerHTML = data.map((p, i) => {
    const date = p.dibuat_at ? new Date(p.dibuat_at).toLocaleDateString('id-ID', {day:'2-digit',month:'short',year:'numeric'}) : '-';
    const escapedJudul = (p.judul || '').replace(/'/g, "\\'");
    return '<tr>' +
      '<td>' + (i + 1) + '</td>' +
      '<td><strong>' + (p.judul || '-') + '</strong></td>' +
      '<td>' + (p.mapel || '-') + '</td>' +
      '<td><span class="badge badge-primary">' + (p.kelas || '-') + '</span></td>' +
      '<td>' + (p.semester || '-') + '</td>' +
      '<td>' + (p.tapel || p.tahun_ajaran || '-') + '</td>' +
      '<td><span class="badge badge-success">' + (p.jumlah_soal || 0) + ' soal</span></td>' +
      '<td>' + (p.waktu || '-') + '</td>' +
      '<td>' + (p.dibuat_oleh || '-') + '</td>' +
      '<td>' + date + '</td>' +
      '<td>' +
        '<button class="btn btn-sm btn-outline" data-action="preview" data-id="' + p.id + '"><i data-lucide="eye"></i></button> ' +
        '<button class="btn btn-sm btn-danger" data-action="delete" data-id="' + p.id + '" data-judul="' + escapedJudul + '"><i data-lucide="trash-2"></i></button> ' +
      '</td>' +
    '</tr>';
  }).join('');
  lucide.createIcons();
}

// ===== FILTER =====
function filterPakets() {
  const search = document.getElementById('filter-search').value.toLowerCase();
  const semester = document.getElementById('filter-semester').value;
  const kelas = document.getElementById('filter-kelas').value;
  
  let filtered = allPakets.filter(p => {
    const matchSearch = !search || (p.judul || '').toLowerCase().includes(search) || (p.mapel || '').toLowerCase().includes(search);
    const matchSemester = !semester || p.semester == semester;
    const matchKelas = !kelas || (p.kelas || '').toString().includes(kelas);
    return matchSearch && matchSemester && matchKelas;
  });
  
  renderTable(filtered);
}

// ===== PREVIEW =====
async function previewPaket(id) {
  const p = allPakets.find(x => x.id === id);
  if (!p) return;
  currentPreviewId = id;
  previewShowAnswers = false;
  
  document.getElementById('preview-title').innerHTML = '<i data-lucide="eye"></i> ' + (p.judul || 'Preview Paket Soal');
  
  const soalIds = (p.soal_ids || '').split(',').filter(Boolean);
  currentSoalData = [];
  
  if (soalIds.length > 0) {
    const result = await GS.call('getData', { table: 'BankSoal', filters: {} });
    if (result.success) {
      let allSoal = result.data;
      currentSoalData = allSoal.filter(s => soalIds.includes(s.id));
      currentSoalData.sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0));
    }
  }
  
  renderPreviewContent(p, false);
  TU.modal.open('modal-preview');
  lucide.createIcons();
}

function renderPreviewContent(p, showAnswers) {
  previewShowAnswers = showAnswers;
  let soalHtml = '';
  
  currentSoalData.forEach((s, i) => {
    soalHtml += '<div class="soal-item">';
    soalHtml += '<p class="soal-num">' + (i + 1) + '. ' + (s.pertanyaan || '') + '</p>';
    if (s.gambar) {
      soalHtml += '<img src="data/uploads/' + s.gambar + '?t=' + Date.now() + '" style="max-width:300px;border-radius:8px;margin:8px 0">';
    }
    if (s.tipe === 'pg' || s.tipe === 'PG') {
      soalHtml += '<div class="soal-pilihan">';
      ['A', 'B', 'C', 'D', 'E'].forEach(opt => {
        const key = 'pilihan_' + opt.toLowerCase();
        if (s[key]) {
          const isCorrect = (s.jawaban_benar || '').toUpperCase() === opt;
          if (showAnswers && isCorrect) {
            soalHtml += '<p style="color:#059669;font-weight:600">' + opt + '. ' + s[key] + ' ✓</p>';
          } else {
            soalHtml += '<p>' + opt + '. ' + s[key] + '</p>';
          }
        }
      });
      soalHtml += '</div>';
    } else if (s.tipe === 'isian') {
      if (showAnswers) {
        soalHtml += '<p class="kunci-text" style="margin-left:20px">Jawaban: ' + (s.kunci_jawaban || '-') + '</p>';
      } else {
        soalHtml += '<div style="border-bottom:1px solid #999;margin:6px 20px;width:50%;height:20px"></div>';
      }
    } else {
      if (showAnswers) {
        soalHtml += '<p class="kunci-text" style="margin-left:20px">Jawaban: ' + (s.kunci_jawaban || '-') + '</p>';
      } else {
        soalHtml += '<div style="border:1px solid #ccc;border-radius:4px;height:80px;margin:6px 20px"></div>';
      }
    }
    if (showAnswers && s.pembahasan) {
      soalHtml += '<p class="pembahasan" style="margin-left:20px">💡 ' + s.pembahasan + '</p>';
    }
    soalHtml += '</div>';
  });
  
  const toggleLabel = showAnswers ? '<i data-lucide="eye-off"></i> Sembunyikan Jawaban' : '<i data-lucide="eye"></i> Tampilkan Jawaban';
  
  document.getElementById('preview-content').innerHTML =
    kopSuratHtml +
    '<div style="text-align:center;margin-bottom:20px">' +
      '<h3 style="font-size:12pt;margin-bottom:8px">PAKET SOAL UJIAN</h3>' +
      '<p style="margin:2px 0;font-size:10pt"><strong>Judul:</strong> ' + (p.judul || '-') + '</p>' +
      '<p style="margin:2px 0;font-size:10pt"><strong>Mata Pelajaran:</strong> ' + (p.mapel || '-') + ' | <strong>Kelas:</strong> ' + (p.kelas || '-') + '</p>' +
      '<p style="margin:2px 0;font-size:10pt"><strong>Semester:</strong> ' + (p.semester || '-') + ' | <strong>Tahun Ajaran:</strong> ' + (p.tapel || p.tahun_ajaran || '-') + '</p>' +
      '<p style="margin:2px 0;font-size:10pt"><strong>Waktu:</strong> ' + (p.waktu || '-') + ' menit | <strong>Jumlah Soal:</strong> ' + currentSoalData.length + ' butir</p>' +
    '</div>' +
    '<div>' + (soalHtml || '<p class="text-muted" style="text-align:center">Tidak ada soal ditemukan</p>') + '</div>';
  
  document.getElementById('btn-toggle-answer').innerHTML = toggleLabel;
  lucide.createIcons();
}

function toggleAnswer() {
  const p = allPakets.find(x => x.id === currentPreviewId);
  if (!p) return;
  renderPreviewContent(p, !previewShowAnswers);
}

// ===== PRINT =====
function buildPrintSoalHtml(p) {
  let html = '';
  html += kopSuratHtml;
  html += '<div style="text-align:center;margin-bottom:16px">';
  html += '<h3 style="font-size:11pt;margin:10px 0 2px">' + (p.judul || '').toUpperCase() + '</h3>';
  html += '<p style="font-size:9pt;margin:2px 0">Mata Pelajaran: <strong>' + (p.mapel||'-') + '</strong> | Kelas: <strong>' + (p.kelas||'-') + '</strong> | Semester: <strong>' + (p.semester||'-') + '</strong> | T.P: <strong>' + (p.tapel||p.tahun_ajaran||'-') + '</strong> | Waktu: <strong>' + (p.waktu||'-') + ' menit</strong></p>';
  html += '</div>';
  html += '<div style="display:flex;gap:20px;margin-bottom:20px;font-size:.82rem">';
  html += '<div>Nama : ___________________________</div>';
  html += '<div>Kelas : ______________</div>';
  html += '<div>No. Absen : _________</div>';
  html += '<div>Nilai : _________</div>';
  html += '</div>';
  
  let nomor = 0;
  const pgSoal = currentSoalData.filter(s => (s.tipe||'').toLowerCase() === 'pg');
  const isianSoal = currentSoalData.filter(s => (s.tipe||'').toLowerCase() === 'isian');
  const uraianSoal = currentSoalData.filter(s => (s.tipe||'').toLowerCase() === 'uraian');
  
  if (pgSoal.length) {
    html += '<div style="font-weight:700;margin-bottom:12px;font-size:.9rem">I. Pilihan Ganda</div>';
    html += '<p style="font-size:.78rem;margin-bottom:12px;font-style:italic">Pilih jawaban yang paling tepat!</p>';
    pgSoal.forEach(s => {
      nomor++;
      html += '<div style="margin-bottom:14px;page-break-inside:avoid">';
      html += '<div style="display:flex;gap:10px"><span style="font-weight:700;min-width:24px">' + nomor + '.</span>';
      html += '<div style="flex:1"><div style="margin-bottom:4px">' + (s.pertanyaan||'') + '</div>';
      if (s.gambar) html += '<img src="data/uploads/' + s.gambar + '" style="max-width:200px;margin:4px 0"><br>';
      ['a','b','c','d','e'].forEach(o => {
        const key = 'pilihan_' + o;
        if (s[key]) html += '<div style="padding:2px 0">' + o.toUpperCase() + '. ' + s[key] + '</div>';
      });
      html += '</div></div></div>';
    });
  }
  if (isianSoal.length) {
    html += '<div style="font-weight:700;margin:20px 0 12px;font-size:.9rem">II. Isian Singkat</div>';
    html += '<p style="font-size:.78rem;margin-bottom:12px;font-style:italic">Isilah titik-titik berikut dengan jawaban yang tepat!</p>';
    isianSoal.forEach(s => {
      nomor++;
      html += '<div style="margin-bottom:14px;page-break-inside:avoid">';
      html += '<div style="display:flex;gap:10px"><span style="font-weight:700;min-width:24px">' + nomor + '.</span>';
      html += '<div style="flex:1"><div>' + (s.pertanyaan||'') + '</div>';
      html += '<div style="border-bottom:1px solid #333;margin:6px 0;width:50%;height:22px"></div>';
      html += '</div></div></div>';
    });
  }
  if (uraianSoal.length) {
    html += '<div style="font-weight:700;margin:20px 0 12px;font-size:.9rem">III. Uraian</div>';
    html += '<p style="font-size:.78rem;margin-bottom:12px;font-style:italic">Jawablah pertanyaan berikut dengan benar dan lengkap!</p>';
    uraianSoal.forEach(s => {
      nomor++;
      html += '<div style="margin-bottom:14px;page-break-inside:avoid">';
      html += '<div style="display:flex;gap:10px"><span style="font-weight:700;min-width:24px">' + nomor + '.</span>';
      html += '<div style="flex:1"><div>' + (s.pertanyaan||'') + '</div>';
      html += '<div style="border:1px solid #ccc;border-radius:4px;height:80px;margin-top:6px"></div>';
      html += '</div></div></div>';
    });
  }
  
  html += '<div style="margin-top:30px;text-align:right;font-size:.78rem">';
  html += '<p>Dibuat: ' + (p.dibuat_at ? new Date(p.dibuat_at).toLocaleString('id-ID') : '-') + '</p>';
  html += '<p>Oleh: ' + (p.dibuat_oleh || '-') + '</p>';
  html += '</div>';
  return html;
}

function buildPrintKunciHtml(p) {
  let html = '';
  html += kopSuratHtml;
  html += '<div style="text-align:center;margin-bottom:20px">';
  html += '<h3 style="font-size:12pt;margin-bottom:8px">KUNCI JAWABAN — ' + (p.judul || '').toUpperCase() + '</h3>';
  html += '<p style="font-size:9pt;margin:2px 0">Mata Pelajaran: <strong>' + (p.mapel||'-') + '</strong> | Kelas: <strong>' + (p.kelas||'-') + '</strong> | Semester: <strong>' + (p.semester||'-') + '</strong> | T.P: <strong>' + (p.tapel||p.tahun_ajaran||'-') + '</strong></p>';
  html += '</div>';
  
  let nomor = 0;
  const pgSoal = currentSoalData.filter(s => (s.tipe||'').toLowerCase() === 'pg');
  const isianSoal = currentSoalData.filter(s => (s.tipe||'').toLowerCase() === 'isian');
  const uraianSoal = currentSoalData.filter(s => (s.tipe||'').toLowerCase() === 'uraian');
  
  // Compact table for PG
  if (pgSoal.length) {
    html += '<div style="font-weight:700;margin-bottom:8px;font-size:.9rem">I. Pilihan Ganda</div>';
    html += '<table style="width:100%;border-collapse:collapse;font-size:9pt;margin-bottom:16px">';
    html += '<tr style="background:#f1f5f9"><th style="padding:6px 8px;border:1px solid #cbd5e1;text-align:center;width:40px">No</th><th style="padding:6px 8px;border:1px solid #cbd5e1;text-align:left">Pertanyaan</th><th style="padding:6px 8px;border:1px solid #cbd5e1;text-align:center;width:60px">Jawaban</th></tr>';
    pgSoal.forEach(s => {
      nomor++;
      const qShort = (s.pertanyaan||'').substring(0, 80) + ((s.pertanyaan||'').length > 80 ? '...' : '');
      html += '<tr><td style="padding:5px 8px;border:1px solid #e2e8f0;text-align:center;font-weight:600">' + nomor + '</td>';
      html += '<td style="padding:5px 8px;border:1px solid #e2e8f0">' + qShort + '</td>';
      html += '<td style="padding:5px 8px;border:1px solid #e2e8f0;text-align:center;font-weight:700;color:#2563eb;font-size:11pt">' + (s.jawaban_benar||'-').toUpperCase() + '</td></tr>';
    });
    html += '</table>';
  }
  
  // Isian
  if (isianSoal.length) {
    html += '<div style="font-weight:700;margin:16px 0 8px;font-size:.9rem">II. Isian Singkat</div>';
    html += '<table style="width:100%;border-collapse:collapse;font-size:9pt;margin-bottom:16px">';
    html += '<tr style="background:#f1f5f9"><th style="padding:6px 8px;border:1px solid #cbd5e1;text-align:center;width:40px">No</th><th style="padding:6px 8px;border:1px solid #cbd5e1;text-align:left">Pertanyaan</th><th style="padding:6px 8px;border:1px solid #cbd5e1;text-align:left">Kunci Jawaban</th></tr>';
    isianSoal.forEach(s => {
      nomor++;
      const qShort = (s.pertanyaan||'').substring(0, 60) + ((s.pertanyaan||'').length > 60 ? '...' : '');
      html += '<tr><td style="padding:5px 8px;border:1px solid #e2e8f0;text-align:center;font-weight:600">' + nomor + '</td>';
      html += '<td style="padding:5px 8px;border:1px solid #e2e8f0">' + qShort + '</td>';
      html += '<td style="padding:5px 8px;border:1px solid #e2e8f0;font-weight:700;color:#2563eb">' + (s.kunci_jawaban||'-') + '</td></tr>';
    });
    html += '</table>';
  }
  
  // Uraian
  if (uraianSoal.length) {
    html += '<div style="font-weight:700;margin:16px 0 8px;font-size:.9rem">III. Uraian</div>';
    html += '<table style="width:100%;border-collapse:collapse;font-size:9pt;margin-bottom:16px">';
    html += '<tr style="background:#f1f5f9"><th style="padding:6px 8px;border:1px solid #cbd5e1;text-align:center;width:40px">No</th><th style="padding:6px 8px;border:1px solid #cbd5e1;text-align:left">Pertanyaan</th><th style="padding:6px 8px;border:1px solid #cbd5e1;text-align:left">Kunci Jawaban</th><th style="padding:6px 8px;border:1px solid #cbd5e1;text-align:left">Pembahasan</th></tr>';
    uraianSoal.forEach(s => {
      nomor++;
      const qShort = (s.pertanyaan||'').substring(0, 50) + ((s.pertanyaan||'').length > 50 ? '...' : '');
      html += '<tr><td style="padding:5px 8px;border:1px solid #e2e8f0;text-align:center;font-weight:600">' + nomor + '</td>';
      html += '<td style="padding:5px 8px;border:1px solid #e2e8f0">' + qShort + '</td>';
      html += '<td style="padding:5px 8px;border:1px solid #e2e8f0;font-weight:700;color:#2563eb">' + (s.kunci_jawaban||'-') + '</td>';
      html += '<td style="padding:5px 8px;border:1px solid #e2e8f0;font-style:italic;color:#6b7280">' + (s.pembahasan||'-') + '</td></tr>';
    });
    html += '</table>';
  }
  
  html += '<div style="margin-top:20px;text-align:right;font-size:.78rem">';
  html += '<p>Dibuat: ' + (p.dibuat_at ? new Date(p.dibuat_at).toLocaleString('id-ID') : '-') + '</p>';
  html += '<p>Oleh: ' + (p.dibuat_oleh || '-') + '</p>';
  html += '</div>';
  return html;
}

function printPaket(mode) {
  const p = allPakets.find(x => x.id === currentPreviewId);
  if (!p) return;
  
  fetch('api/log.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'cetak', category: 'umum', description: mode === 'kunci' ? 'Mencetak kunci jawaban paket soal' : 'Mencetak soal paket soal' }) });
  
  const printWindow = window.open('', '_blank');
  const content = mode === 'kunci' ? buildPrintKunciHtml(p) : buildPrintSoalHtml(p);
  const title = mode === 'kunci' ? 'Kunci Jawaban — ' + (p.judul||'') : 'Soal Ujian — ' + (p.judul||'');
  
  printWindow.document.write('<html><head><title>' + title + '</title>');
  printWindow.document.write('<style>');
  printWindow.document.write('body{font-family:"Times New Roman",serif;padding:20mm;font-size:12pt;line-height:1.6}');
  printWindow.document.write('h2,h3{text-align:center}');
  printWindow.document.write('img{max-width:200px}');
  printWindow.document.write('table{font-size:10pt}');
  printWindow.document.write('th,td{padding:4px 8px}');
  printWindow.document.write('</style>');
  printWindow.document.write('</head><body>');
  printWindow.document.write(content);
  printWindow.document.write('</body></html>');
  printWindow.document.close();
  setTimeout(() => printWindow.print(), 300);
}

// ===== DELETE =====
function deletePaket(id, name) {
  deleteId = id;
  document.getElementById('delete-name').textContent = name;
  TU.modal.open('modal-delete');
}

async function confirmDelete() {
  if (!deleteId) return;
  const btn = document.getElementById('btn-confirm-delete');
  TU.btnLoading(btn, true);
  
  const result = await GS.call('deleteRow', { table: 'PaketSoal', rowId: deleteId });
  TU.btnLoading(btn, false);
  
  if (result.success) {
    allPakets = allPakets.filter(p => p.id !== deleteId);
    renderTable(allPakets);
    updateStats();
    TU.toast('Paket soal berhasil dihapus', 'success');
    TU.modal.close('modal-delete');
  } else {
    TU.toast(result.error || 'Gagal menghapus paket soal', 'error');
  }
}

// ===== MODAL HELPERS =====
// Using TU.modal.open() and TU.modal.close() from app.js
// (custom openModal/closeModal removed — now aligned with design system)
</script>
JS;
?>

<?php require_once 'includes/footer.php'; ?>
