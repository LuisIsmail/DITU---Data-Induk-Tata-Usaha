<?php
$pageTitle = 'Import Data';
$pageSubtitle = 'Import data siswa atau PTK dari file CSV';
require_once 'includes/header.php';
requireLogin();
if (!isAdmin()) { header('Location: index.php'); exit; }
$conn = dbConnect();

// Get count
$siswaCount = 0;
$ptkCount = 0;
$r = $conn->query("SELECT COUNT(*) as cnt FROM datasiswa");
if ($r) $siswaCount = $r->fetch_assoc()['cnt'];
$r = $conn->query("SELECT COUNT(*) as cnt FROM dataptk");
if ($r) $ptkCount = $r->fetch_assoc()['cnt'];
?>

<style>
.import-hero { text-align: center; padding: 30px 20px; background: linear-gradient(135deg, #dbeafe, #ede9fe); border-radius: 16px; margin-bottom: 30px; }
.import-hero h2 { font-size: 1.6rem; margin-bottom: 6px; }
.import-hero p { color: var(--text-light); max-width: 520px; margin: 0 auto; font-size: .88rem; }
.import-tabs { display: flex; gap: 10px; margin-bottom: 20px; }
.import-tab { flex: 1; padding: 14px 20px; border: 1.5px solid var(--border); border-radius: 12px; cursor: pointer; text-align: center; transition: all .2s; background: var(--bg-card); }
.import-tab:hover { border-color: var(--primary); }
.import-tab.active { border-color: var(--primary); background: var(--primary-light); color: var(--primary); font-weight: 600; }
.import-zone { border: 2px dashed var(--border); border-radius: 16px; padding: 40px; text-align: center; cursor: pointer; transition: all .2s; margin-bottom: 20px; }
.import-zone:hover, .import-zone.dragover { border-color: var(--primary); background: var(--primary-light); }
.import-zone p { margin: 8px 0 0; color: var(--text-light); font-size: .85rem; }
.preview-table { width: 100%; border-collapse: collapse; font-size: .78rem; margin-top: 16px; }
.preview-table th, .preview-table td { border: 1px solid var(--border); padding: 5px 8px; text-align: left; }
.preview-table th { background: var(--bg-secondary); font-weight: 600; position: sticky; top: 0; }
.preview-table tr:nth-child(even) { background: var(--bg-hover); }
.map-row { display: flex; gap: 10px; align-items: center; margin-bottom: 8px; flex-wrap: wrap; }
.map-row label { font-size: .82rem; font-weight: 600; min-width: 120px; }
.map-row select { flex: 1; min-width: 150px; }
.result-card { padding: 20px; border-radius: 12px; text-align: center; margin-top: 20px; }
.result-card.success { background: #d1fae5; border: 1.5px solid #10b981; color: #065f46; }
.result-card.error { background: #fee2e2; border: 1.5px solid #ef4444; color: #991b1b; }
</style>

<div class="content-wrapper">
  <div class="import-hero">
    <div style="font-size:2.5rem;margin-bottom:8px"><i data-lucide="upload"></i></div>
    <h2>Import Data dari CSV</h2>
    <p>Upload file CSV untuk menambahkan data siswa atau PTK secara massal. Pastikan format kolom sesuai template.</p>
  </div>

  <!-- Table Tabs -->
  <div class="import-tabs">
    <div class="import-tab active" id="tab-siswa" onclick="switchTab('siswa')">
      <i data-lucide="graduation-cap"></i> Import Data Siswa<br>
      <small style="font-weight:400;color:var(--text-light)">Tabel DataSiswa (<?= $siswaCount ?> record)</small>
    </div>
    <div class="import-tab" id="tab-ptk" onclick="switchTab('ptk')">
      <i data-lucide="briefcase"></i> Import Data PTK<br>
      <small style="font-weight:400;color:var(--text-light)">Tabel DataPTK (<?= $ptkCount ?> record)</small>
    </div>
  </div>

  <!-- Template Download -->
  <div class="card" style="margin-bottom:20px">
    <div class="card-body" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
      <div>
        <strong><i data-lucide="clipboard-list"></i> Download Template CSV</strong>
        <p style="margin:4px 0 0;font-size:.82rem;color:var(--text-light)">Download template CSV kosong untuk memastikan format benar sebelum import</p>
      </div>
      <div style="display:flex;gap:8px">
        <button class="btn btn-outline" onclick="downloadTemplate('siswa')"><i data-lucide="download"></i> Template Siswa</button>
        <button class="btn btn-outline" onclick="downloadTemplate('ptk')"><i data-lucide="download"></i> Template PTK</button>
      </div>
    </div>
  </div>

  <!-- Import Zone -->
  <div class="card" id="import-card">
    <div class="card-body">
      <div class="import-zone" id="import-zone" onclick="document.getElementById('csv-file').click()"
           ondragover="event.preventDefault();this.classList.add('dragover')"
           ondragleave="this.classList.remove('dragover')"
           ondrop="handleCSVDrop(event)">
        <div style="font-size:3rem"><i data-lucide="file-text"></i></div>
        <p><strong>Klik atau seret file CSV ke sini</strong></p>
        <p>Format: .csv (UTF-8, max 5MB)</p>
      </div>
      <input type="file" id="csv-file" accept=".csv" style="display:none" onchange="handleCSVSelect(this)">
      
      <div id="csv-info" style="display:none">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;padding:10px;border-radius:8px;background:var(--bg-secondary)">
          <span style="font-size:1.2rem"><i data-lucide="file-text"></i></span>
          <div>
            <strong id="csv-filename">-</strong>
            <span style="font-size:.78rem;color:var(--text-light);margin-left:8px" id="csv-filesize">-</span>
          </div>
          <button class="btn btn-sm btn-outline" style="margin-left:auto" onclick="resetCSV()"><i data-lucide="x"></i> Ganti File</button>
        </div>
        
        <!-- Column Mapping -->
        <div id="mapping-section" style="display:none">
          <h4 style="margin-bottom:12px"><i data-lucide="link"></i> Mapping Kolom CSV → Database</h4>
          <div id="mapping-container"></div>
          <button class="btn btn-primary btn-lg" style="width:100%;margin-top:16px" onclick="doImport()" id="btn-import">
            <i data-lucide="upload"></i> Import Data
          </button>
        </div>
        
        <!-- Preview -->
        <div id="preview-section" style="display:none;margin-top:16px">
          <h4 style="margin-bottom:10px"><i data-lucide="eye"></i> Preview Data (10 baris pertama)</h4>
          <div style="max-height:300px;overflow:auto;border:1px solid var(--border);border-radius:8px">
            <table class="preview-table" id="preview-table"></table>
          </div>
        </div>
      </div>
      
      <!-- Progress -->
      <div id="import-progress" style="display:none;margin-top:16px">
        <div style="display:flex;justify-content:space-between;font-size:.85rem;margin-bottom:6px">
          <span>Proses import...</span>
          <span id="import-status">0%</span>
        </div>
        <div style="width:100%;height:8px;background:var(--bg-secondary);border-radius:4px;overflow:hidden">
          <div style="height:100%;background:var(--primary);border-radius:4px;transition:width .3s;width:0%" id="import-fill"></div>
        </div>
      </div>
      
      <!-- Result -->
      <div id="import-result" style="display:none"></div>
    </div>
  </div>
</div>

<div id="toast-container"></div>

<?php
$importApi = 'api/import.php';
$csrfToken = generateCSRFToken();

// Siswa columns
$siswaCols = [
    'id' => 'ID (auto)', 'nipd' => 'NIPD', 'nisn' => 'NISN', 'nama' => 'Nama Lengkap',
    'jk' => 'Jenis Kelamin (L/P)', 'tmplahir' => 'Tempat Lahir', 'tgllahir' => 'Tanggal Lahir (YYYY-MM-DD)',
    'rombel' => 'Rombel', 'agama' => 'Agama', 'alamat' => 'Alamat', 'telp' => 'Telepon',
    'nama_ayah' => 'Nama Ayah', 'nama_ibu' => 'Nama Ibu', 'foto' => 'Foto',
    'kewarganegaraan' => 'Kewarganegaraan', 'anak_ke' => 'Anak Ke', 'status_anak' => 'Status Anak',
    'bahasa' => 'Bahasa di Rumah', 'tinggi_badan' => 'Tinggi Badan', 'berat_badan' => 'Berat Badan'
];
$ptkCols = [
    'id' => 'ID (auto)', 'nip' => 'NIP', 'nama' => 'Nama Lengkap',
    'jk' => 'Jenis Kelamin (L/P)', 'tmplahir' => 'Tempat Lahir', 'tgllahir' => 'Tanggal Lahir (YYYY-MM-DD)',
    'agama' => 'Agama', 'jabatan' => 'Jabatan', 'alamat' => 'Alamat',
    'telp' => 'Telepon', 'status_kepegawaian' => 'Status Kepegawaian'
];

$siswaColsJson = json_encode($siswaCols);
$ptkColsJson = json_encode($ptkCols);

$extraJs = <<<JS
<script>
const importApi = '{$importApi}';
const csrfToken = '{$csrfToken}';
const siswaCols = {$siswaColsJson};
const ptkCols = {$ptkColsJson};

let currentTab = 'siswa';
let csvData = null;
let csvHeaders = [];

function switchTab(tab) {
  currentTab = tab;
  document.getElementById('tab-siswa').classList.toggle('active', tab === 'siswa');
  document.getElementById('tab-ptk').classList.toggle('active', tab === 'ptk');
  resetCSV();
}

// ===== TEMPLATE DOWNLOAD =====
function downloadTemplate(type) {
  let csv = '';
  if (type === 'siswa') {
    csv = 'nipd,nisn,nama,jk,tmplahir,tgllahir,rombel,agama,alamat,telp,nama_ayah,nama_ibu\\n';
    csv += '0011234001,0081234567,Ananda Putra,L,Samarinda,2015-03-15,1A,Islam,Jl. Merdeka No.1,081234567890,Budi Santoso,Siti Rahayu\\n';
    csv += '0011234002,0081234568,Rina Wati,P,Surabaya,2015-07-20,1A,Hindu,Jl. Sudirman No.5,085678901234,Andi Wati,Lisa Hartono\\n';
  } else {
    csv = 'nip,nama,jk,tmplahir,tgllahir,agama,jabatan,alamat,telp,status_kepegawaian\\n';
    csv += '198501012010011001,Suharto, L,Palangkaraya,1985-01-01,Islam,Guru SD,Jl. Pahlawan No.2,082123456789,PNS\\n';
  }
  
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = 'template_import_' + type + '.csv';
  a.click();
  TU.toast('Template ' + type + ' diunduh', 'success');
}

// ===== FILE HANDLING =====
function handleCSVSelect(input) { if (input.files.length) parseCSV(input.files[0]); }
function handleCSVDrop(e) {
  e.preventDefault();
  e.currentTarget.classList.remove('dragover');
  if (e.dataTransfer.files.length) parseCSV(e.dataTransfer.files[0]);
}

function parseCSV(file) {
  if (!file.name.endsWith('.csv')) { TU.toast('Hanya file .csv', 'error'); return; }
  if (file.size > 5*1024*1024) { TU.toast('Max 5MB', 'error'); return; }
  
  document.getElementById('csv-filename').textContent = file.name;
  document.getElementById('csv-filesize').textContent = (file.size/1024).toFixed(1) + ' KB';
  document.getElementById('csv-info').style.display = 'block';
  
  const reader = new FileReader();
  reader.onload = (e) => {
    const text = e.target.result;
    const lines = text.split(/\\r?\\n/).filter(l => l.trim());
    if (lines.length < 2) { TU.toast('CSV minimal 2 baris (header + data)', 'error'); return; }
    
    csvHeaders = lines[0].split(',').map(h => h.trim().replace(/^"|"$/g, ''));
    csvData = lines.slice(1).map(line => {
      const vals = line.split(',').map(v => v.trim().replace(/^"|"$/g, ''));
      const obj = {};
      csvHeaders.forEach((h, i) => obj[h] = vals[i] || '');
      return obj;
    });
    
    buildMapping();
    showPreview();
  };
  reader.readAsText(file);
}

function resetCSV() {
  csvData = null; csvHeaders = [];
  document.getElementById('csv-info').style.display = 'none';
  document.getElementById('import-result').style.display = 'none';
  document.getElementById('import-progress').style.display = 'none';
  document.getElementById('csv-file').value = '';
}

// ===== MAPPING =====
function buildMapping() {
  const cols = currentTab === 'siswa' ? siswaCols : ptkCols;
  const container = document.getElementById('mapping-container');
  const skipCols = ['id'];
  
  container.innerHTML = Object.entries(cols)
    .filter(([k]) => !skipCols.includes(k))
    .map(([field, label]) => {
      // Auto-match by name
      const match = csvHeaders.find(h => h.toLowerCase().replace(/\\s/g,'_') === field || h.toLowerCase() === field || h.toLowerCase() === label.toLowerCase());
      const options = csvHeaders.map(h => 
        '<option value="' + h + '"' + (h === match ? ' selected' : '') + '>' + h + '</option>'
      ).join('');
      
      return '<div class="map-row">' +
        '<label>' + label + '</label>' +
        '<select data-field="' + field + '" class="form-control">' +
          '<option value="">— Lewati —</option>' + options +
        '</select>' +
      '</div>';
    }).join('');
  
  document.getElementById('mapping-section').style.display = 'block';
}

// ===== PREVIEW =====
function showPreview() {
  const table = document.getElementById('preview-table');
  const previewData = csvData.slice(0, 10);
  
  let html = '<thead><tr>' + csvHeaders.map(h => '<th>' + h + '</th>').join('') + '</tr></thead>';
  html += '<tbody>' + previewData.map(row => 
    '<tr>' + csvHeaders.map(h => '<td>' + (row[h] || '') + '</td>').join('') + '</tr>'
  ).join('') + '</tbody>';
  
  table.innerHTML = html;
  document.getElementById('preview-section').style.display = 'block';
}

// ===== IMPORT =====
async function doImport() {
  if (!csvData || csvData.length === 0) { TU.toast('Tidak ada data', 'error'); return; }
  
  // Build mapping
  const mapping = {};
  document.querySelectorAll('#mapping-container select').forEach(sel => {
    const field = sel.dataset.field;
    const csvCol = sel.value;
    if (csvCol) mapping[csvCol] = field;
  });
  
  if (Object.keys(mapping).length === 0) { TU.toast('Mapping minimal 1 kolom', 'error'); return; }
  
  const btn = document.getElementById('btn-import');
  const progress = document.getElementById('import-progress');
  const fill = document.getElementById('import-fill');
  const status = document.getElementById('import-status');
  
  TU.btnLoading(btn, true);
  progress.style.display = 'block';
  
  let success = 0, fail = 0, errors = [];
  
  for (let i = 0; i < csvData.length; i++) {
    const row = csvData[i];
    const data = {};
    
    for (const [csvCol, dbField] of Object.entries(mapping)) {
      if (row[csvCol]) data[dbField] = row[csvCol];
    }
    
    const table = currentTab === 'siswa' ? 'DataSiswa' : 'DataPTK';
    const result = await GS.addRow(table, data);
    
    if (result.success) {
      success++;
    } else {
      fail++;
      if (errors.length < 5) errors.push(data.nama || row[csvHeaders[0]] || 'Unknown');
    }
    
    const pct = Math.round(((i + 1) / csvData.length) * 100);
    fill.style.width = pct + '%';
    status.textContent = pct + '% (' + (i + 1) + '/' + csvData.length + ')';
  }
  
  TU.btnLoading(btn, false);
  
  const resultDiv = document.getElementById('import-result');
  resultDiv.style.display = 'block';
  resultDiv.className = fail > 0 ? 'result-card error' : 'result-card success';
  resultDiv.innerHTML = fail > 0 
    ? '<i data-lucide="alert-triangle"></i> Import selesai: <strong>' + success + ' berhasil</strong>, <strong>' + fail + ' gagal</strong>' + 
      (errors.length ? '<br><small>Gagal pada: ' + errors.join(', ') + '...</small>' : '')
    : '<i data-lucide="check-circle"></i> Semua <strong>' + success + ' data berhasil</strong> diimport!';
  lucide.createIcons();
  
  TU.toast('Import selesai: ' + success + ' berhasil, ' + fail + ' gagal', fail > 0 ? 'warning' : 'success');
}
</script>
JS;
?>

<?php require_once 'includes/footer.php'; ?>
