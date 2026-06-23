<?php
$pageTitle = 'Cetak Rapor';
$pageSubtitle = 'Cetak rapor siswa dengan Kop Surat';
require_once 'includes/header.php';
require_once 'includes/kop_helper.php';
requireLogin();
$conn = dbConnect();

// Get rombel list
$rombels = [];
$r = $conn->query("SELECT * FROM rombel ORDER BY id ASC");
if ($r) while ($row = $r->fetch_assoc()) $rombels[] = $row;

// Kop Sekolah (dedicated table)
$kop = getKopSekolah();
$schoolName = $kop['school_name'] ?? 'SD Negeri 001 Gunung Sari';
$instansi = $kop['instansi'] ?? 'Pemerintah Kabupaten Berau';
$dinasName = $kop['dinas'] ?? 'Dinas Pendidikan';
$logoKiri = $kop['logo_kiri'] ?? '';
$logoKanan = $kop['logo_kanan'] ?? '';
$alamat = $kop['alamat'] ?? '';
$kepsek = $kop['kepala_sekolah'] ?? '';
$nipKepsek = $kop['nip_kepsek'] ?? '';
$telepon = $kop['telp'] ?? '';
$email = $kop['email'] ?? '';
$website = $settings['website'] ?? '';

$userRombel = $_SESSION['rombel'] ?? '';
$userRole = $_SESSION['role'] ?? 'guru';
?>

<style>
@page { size: auto; margin: 0; }
@media print {
  body * { visibility: hidden; }
  .print-area, .print-area * { visibility: visible !important; }
  .print-area { position: absolute; left: 0; top: 0; width: 100%; padding: 10mm 15mm; background: white; }
  .no-print { display: none !important; }
}
.rapor-header { text-align: center; margin-bottom: 20px; }
.rapor-header img { height: 60px; }
.rapor-header .kop-text { font-size: .82rem; }
.rapor-table { width: 100%; border-collapse: collapse; font-size: .82rem; margin-bottom: 20px; }
.rapor-table th, .rapor-table td { border: 1px solid #333; padding: 6px 10px; }
.rapor-table th { background: #f3f4f6; text-align: left; }
.rapor-table td.center, .rapor-table th.center { text-align: center; }
.rapor-table tr.avg-row { background: #e0f2fe; font-weight: 700; }
.rapor-table tr.rank-row { background: #dbeafe; font-weight: 700; }
.siswa-select-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; margin-bottom: 20px; }
.siswa-chip { padding: 10px 14px; border: 1.5px solid var(--border); border-radius: 10px; cursor: pointer; transition: all .15s; background: var(--bg-card); }
.siswa-chip:hover { border-color: var(--primary); }
.siswa-chip.active { border-color: var(--primary); background: var(--primary-light); color: var(--primary); }
.siswa-chip .chip-name { font-weight: 600; font-size: .85rem; }
.siswa-chip .chip-nipd { font-size: .72rem; color: var(--text-light); }
</style>

<div class="content-wrapper">
  <div class="content-header no-print">
    <div>
      <h2><i data-lucide="bar-chart-3"></i> Cetak Rapor</h2>
      <p class="text-muted">Pilih rombel dan siswa untuk mencetak rapor</p>
    </div>
  </div>

  <!-- Controls -->
  <div class="card no-print" style="margin-bottom:20px">
    <div class="card-body" style="display:flex;gap:12px;flex-wrap:wrap;align-items:end">
      <div class="form-group" style="min-width:140px;margin:0">
        <label class="form-label"><i data-lucide="school"></i> Rombel</label>
        <select id="rapor-rombel" class="form-control" onchange="loadSiswaRapor()">
          <option value="">Pilih Rombel</option>
          <?php foreach ($rombels as $rb): ?>
            <?php if (!empty($userRombel) && $userRole !== 'admin' && $rb['nama_rombel'] !== $userRombel) continue; ?>
            <option value="<?= htmlspecialchars($rb['nama_rombel']) ?>" <?= ($rb['nama_rombel'] === $userRombel && $userRole !== 'admin') ? 'selected' : '' ?>><?= htmlspecialchars($rb['nama_rombel']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group" style="min-width:120px;margin:0">
        <label class="form-label">Semester</label>
        <select id="rapor-semester" class="form-control">
          <option value="1">Ganjil (1)</option>
          <option value="2">Genap (2)</option>
        </select>
      </div>
      <div class="form-group" style="min-width:140px;margin:0">
        <label class="form-label">Tahun Ajaran</label>
        <input type="text" id="rapor-tapel" class="form-control" value="<?= date('Y') ?>/<?= date('Y')+1 ?>" placeholder="2025/2026">
      </div>
      <button class="btn btn-primary" onclick="loadSiswaRapor()"><i data-lucide="refresh-cw"></i> Muat Siswa</button>
      <button class="btn btn-success" onclick="printAllRapor()"><i data-lucide="printer"></i> Cetak Semua</button>
    </div>
  </div>

  <!-- Student chips -->
  <div class="siswa-select-grid" id="siswa-grid">
    <div class="empty-state" style="grid-column:1/-1">
      <div class="empty-icon"><i data-lucide="graduation-cap"></i></div>
      <h3>Pilih Rombel terlebih dahulu</h3>
    </div>
  </div>

  <!-- Rapor Preview -->
  <div class="card" id="rapor-card" style="display:none">
    <div class="card-body no-print" style="text-align:center;padding:10px">
      <button class="btn btn-primary" onclick="printRapor()"><i data-lucide="printer"></i> Cetak Rapor</button>
      <button class="btn btn-secondary" onclick="closeRapor()">✕ Tutup</button>
    </div>
    <div class="card-body print-area" id="rapor-content"></div>
  </div>
</div>

<div id="toast-container"></div>

<?php
$logoKiriUrl = !empty($logoKiri) ? 'data/uploads/' . $logoKiri . '?t=' . @filemtime(__DIR__ . '/data/uploads/' . $logoKiri) : '';
$logoKananUrl = !empty($logoKanan) ? 'data/uploads/' . $logoKanan . '?t=' . @filemtime(__DIR__ . '/data/uploads/' . $logoKanan) : '';

$schoolNameJs = addslashes($schoolName);
$instansiJs = addslashes($instansi);
$dinasNameJs = addslashes($dinasName);
$alamatJs = addslashes($alamat);
$kepsekJs = addslashes($kepsek);
$nipKepsekJs = addslashes($nipKepsek);
$teleponJs = addslashes($telepon);
$emailJs = addslashes($email);
$websiteJs = addslashes($website);
$logoKiriJs = addslashes($logoKiriUrl);
$logoKananJs = addslashes($logoKananUrl);
$kopHtmlJs = getKopSuratJsHtml($kop);

$extraJs = <<<JS
<script>
let allSiswa = [];
let allNilai = [];
let selectedSiswaId = null;

const schoolName = '{$schoolNameJs}';
const instansi = '{$instansiJs}';
const dinasName = '{$dinasNameJs}';
const alamat = '{$alamatJs}';
const kepsek = '{$kepsekJs}';
const nipKepsek = '{$nipKepsekJs}';
const telepon = '{$teleponJs}';
const emailR = '{$emailJs}';
const website = '{$websiteJs}';
const logoKiriUrl = '{$logoKiriJs}';
const logoKananUrl = '{$logoKananJs}';
const kopSuratHtml = {$kopHtmlJs};

const KOMPETENSI_MAP = {
  'Pendidikan Agama dan Budi Pekerti': 'PABP',
  'Pendidikan Agama': 'PABP',
  'Pendidikan Pancasila': 'PPn',
  'PPKn': 'PPn',
  'Bahasa Indonesia': 'B.Ina',
  'Matematika': 'MTK',
  'Seni Budaya': 'SBud',
  'SBdP': 'SBud',
  'PJOK': 'PJOK',
  'IPAS': 'IPAS',
  'IPA': 'IPAS',
  'IPS': 'IPAS',
  'Bahasa Inggris': 'B.Ing',
  'Muatan Lokal': 'ML'
};

async function loadSiswaRapor() {
  const rombel = document.getElementById('rapor-rombel').value;
  if (!rombel) { TU.toast('Pilih rombel', 'error'); return; }
  
  const result = await GS.getData('DataSiswa', { rombel });
  if (!result.success) { TU.toast('Gagal memuat data', 'error'); return; }
  
  allSiswa = result.data;
  renderSiswaChips();
  
  // Preload all nilai for this rombel
  const nilaiResult = await GS.getData('DaftarNilai', { rombel });
  allNilai = nilaiResult.success ? nilaiResult.data : [];
}

function renderSiswaChips() {
  const grid = document.getElementById('siswa-grid');
  if (allSiswa.length === 0) {
    grid.innerHTML = '<div class="empty-state" style="grid-column:1/-1"><h3>Tidak Ada Siswa</h3></div>';
    return;
  }
  grid.innerHTML = allSiswa.map(s =>
    '<div class="siswa-chip' + (selectedSiswaId === s.id ? ' active' : '') + '" onclick="selectSiswa(\\'' + s.id + '\\')">' +
      '<div class="chip-name">' + (s.nama || '-') + '</div>' +
      '<div class="chip-nipd">NIPD: ' + (s.nipd || '-') + '</div>' +
    '</div>'
  ).join('');
}

function selectSiswa(id) {
  selectedSiswaId = id;
  renderSiswaChips();
  renderRapor(id);
}

function renderRapor(siswaId) {
  const siswa = allSiswa.find(s => s.id === siswaId);
  if (!siswa) return;
  
  const rombel = document.getElementById('rapor-rombel').value;
  const semester = document.getElementById('rapor-semester').value;
  const tapel = document.getElementById('rapor-tapel').value;
  
  // Filter nilai for this siswa
  const nilaiSiswa = allNilai.filter(n => 
    n.nama_siswa === siswa.nama && 
    n.rombel === rombel
  );
  
  // Build table rows
  let tableRows = '';
  let totalNilai = 0;
  let countNilai = 0;
  const mapelScores = {};
  
  nilaiSiswa.forEach(n => {
    const score = parseFloat(n.nilai) || 0;
    const mapel = n.mapel || 'Lainnya';
    if (!mapelScores[mapel]) mapelScores[mapel] = [];
    mapelScores[mapel].push(score);
  });
  
  let rowIdx = 1;
  for (const [mapel, scores] of Object.entries(mapelScores)) {
    const avg = scores.reduce((a, b) => a + b, 0) / scores.length;
    totalNilai += avg;
    countNilai++;
    const predikat = avg >= 85 ? 'Sangat Baik' : avg >= 75 ? 'Baik' : avg >= 65 ? 'Cukup' : 'Kurang';
    const color = avg >= 85 ? '#059669' : avg >= 75 ? '#2563eb' : avg >= 65 ? '#d97706' : '#dc2626';
    
    tableRows += '<tr>' +
      '<td class="center">' + rowIdx + '</td>' +
      '<td>' + mapel + '</td>' +
      '<td class="center" style="font-weight:700;color:' + color + '">' + avg.toFixed(1) + '</td>' +
      '<td class="center">' + predikat + '</td>' +
    '</tr>';
    rowIdx++;
  }
  
  const rataRata = countNilai > 0 ? (totalNilai / countNilai) : 0;
  const predikatAkhir = rataRata >= 85 ? 'Sangat Baik' : rataRata >= 75 ? 'Baik' : rataRata >= 65 ? 'Cukup' : 'Kurang';
  
  // Rank in class
  const siswaRanks = allSiswa.map(s => {
    const sn = allNilai.filter(n => n.nama_siswa === s.nama && n.rombel === rombel);
    let tot = 0, cnt = 0;
    sn.forEach(n => { tot += parseFloat(n.nilai) || 0; cnt++; });
    return { id: s.id, avg: cnt > 0 ? tot / cnt : 0 };
  }).sort((a, b) => b.avg - a.avg);
  
  const rank = siswaRanks.findIndex(x => x.id === siswaId) + 1;
  
  const html = `
    <div class="rapor-header">
      \${kopSuratHtml}
      <hr style="margin:5px 0;border:1.5px solid #333">
      <h3 style="margin:10px 0 5px">RAPOR PENILAIAN</h3>
      <p style="margin:2px 0;font-size:.82rem">Semester \${semester} — Tahun Ajaran \${tapel}</p>
    </div>
    
    <table style="width:100%;margin-bottom:15px;font-size:.85rem;border-collapse:collapse">
      <tr>
        <td style="width:120px;padding:3px 8px;font-weight:600">Nama Siswa</td>
        <td style="padding:3px 8px">: \\${siswa.nama || '-'}</td>
        <td style="width:100px;padding:3px 8px;font-weight:600">Rombel</td>
        <td style="padding:3px 8px">: \\${rombel}</td>
      </tr>
      <tr>
        <td style="padding:3px 8px;font-weight:600">NIPD</td>
        <td style="padding:3px 8px">: \\${siswa.nipd || '-'}</td>
        <td style="padding:3px 8px;font-weight:600">NISN</td>
        <td style="padding:3px 8px">: \\${siswa.nisn || '-'}</td>
      </tr>
    </table>
    
    <table class="rapor-table">
      <thead>
        <tr>
          <th class="center" style="width:40px">No</th>
          <th>Mata Pelajaran</th>
          <th class="center" style="width:80px">Nilai</th>
          <th class="center" style="width:100px">Predikat</th>
        </tr>
      </thead>
      <tbody>
        \\${tableRows || '<tr><td colspan="4" class="center text-muted">Belum ada data nilai</td></tr>'}
        <tr class="avg-row">
          <td class="center">-</td>
          <td><strong>Rata-rata</strong></td>
          <td class="center"><strong>\\${rataRata.toFixed(1)}</strong></td>
          <td class="center"><strong>\\${predikatAkhir}</strong></td>
        </tr>
        <tr class="rank-row">
          <td class="center">-</td>
          <td><strong>Ranking dalam Kelas</strong></td>
          <td class="center" colspan="2"><strong>\\${rank > 0 ? rank + ' / ' + allSiswa.length : '-'}</strong></td>
        </tr>
      </tbody>
    </table>
    
    <table style="width:100%;margin-top:40px;font-size:.82rem">
      <tr>
        <td style="width:50%;vertical-align:top">
          <p>Mengetahui,</p>
          <p style="margin-top:35px;font-weight:600;text-decoration:underline">\\${kepsek || 'Kepala Sekolah'}</p>
          <p style="font-size:.75rem">NIP. \\${nipKepsek || '-'}</p>
        </td>
        <td style="width:50%;vertical-align:top;text-align:right">
          <p>\\${rombel}, <?= date('d/m/Y') ?></p>
          <p style="margin-top:35px;font-weight:600;text-decoration:underline">Wali Kelas</p>
          <p style="font-size:.75rem">NIP. -</p>
        </td>
      </tr>
    </table>
  `;
  
  document.getElementById('rapor-content').innerHTML = html;
  document.getElementById('rapor-card').style.display = 'block';
  document.getElementById('rapor-card').scrollIntoView({ behavior: 'smooth' });
  lucide.createIcons();
}

function printRapor() {
  const content = document.getElementById('rapor-content').innerHTML;
  fetch('api/log.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'cetak', category: 'umum', description: 'Mencetak rapor siswa' }) });
  const printWindow = window.open('', '_blank');
  printWindow.document.write('<html><head><title>Cetak Rapor</title>');
  printWindow.document.write('<style>body{font-family:serif;padding:0;margin:0;font-size:11pt}.rapor-header{text-align:center;margin-bottom:15px}.rapor-header img{height:55px}.rapor-table{width:100%;border-collapse:collapse;font-size:.82rem;margin:10px 0}.rapor-table th,.rapor-table td{border:1px solid #333;padding:5px 8px}.rapor-table th{background:#f3f4f6;text-align:left}.center{text-align:center}tr.avg-row{background:#e0f2fe;font-weight:700}tr.rank-row{background:#dbeafe;font-weight:700}hr{margin:5px 0;border:1.5px solid #333}</style>');
  printWindow.document.write('</head><body>');
  printWindow.document.write(content);
  printWindow.document.write('</body></html>');
  printWindow.document.close();
  printWindow.print();
}

function printAllRapor() {
  if (allSiswa.length === 0) { MU.toast('Muat data siswa dulu', 'error'); return; }
  // Print each siswa sequentially
  let idx = 0;
  function printNext() {
    if (idx >= allSiswa.length) return;
    selectedSiswaId = allSiswa[idx].id;
    renderRapor(allSiswa[idx].id);
    setTimeout(() => { printRapor(); idx++; setTimeout(printNext, 1000); }, 500);
  }
  printNext();
}

function closeRapor() {
  document.getElementById('rapor-card').style.display = 'none';
  selectedSiswaId = null;
  renderSiswaChips();
}
</script>
JS;
?>

<?php require_once 'includes/footer.php'; ?>
