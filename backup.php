<?php
$pageTitle = 'Backup & Restore';
$pageSubtitle = 'Cadangkan dan pulihkan database aplikasi';
require_once 'includes/header.php';
requireLogin();
if (!isAdmin()) { header('Location: index.php'); exit; }
?>

<style>
.backup-hero { text-align: center; padding: 40px 20px; background: linear-gradient(135deg, var(--primary-light), #ede9fe); border-radius: 16px; margin-bottom: 30px; }
.backup-hero h2 { font-size: 1.8rem; margin-bottom: 8px; }
.backup-hero p { color: var(--text-light); max-width: 500px; margin: 0 auto; }
.backup-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
@media (max-width: 768px) { .backup-grid { grid-template-columns: 1fr; } }
.backup-card { border-radius: 16px; overflow: hidden; border: 1.5px solid var(--border); background: var(--bg-card); }
.backup-card-header { padding: 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 12px; }
.backup-card-header .icon-lg { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
.backup-card-body { padding: 20px; }
.backup-list { list-style: none; padding: 0; margin: 0; max-height: 300px; overflow-y: auto; }
.backup-list li { padding: 10px 14px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; font-size: .85rem; transition: background .15s; }
.backup-list li:last-child { border-bottom: none; }
.backup-list li:hover { background: var(--bg-hover); }
.backup-list .file-info { display: flex; flex-direction: column; gap: 2px; }
.backup-list .file-size { font-size: .72rem; color: var(--text-light); }
.restore-zone { border: 2px dashed var(--border); border-radius: 12px; padding: 30px; text-align: center; cursor: pointer; transition: all .2s; }
.restore-zone:hover, .restore-zone.dragover { border-color: var(--primary); background: var(--primary-light); }
.restore-zone p { margin: 8px 0 0; color: var(--text-light); font-size: .85rem; }
.restore-progress { margin-top: 16px; }
.progress-bar { width: 100%; height: 8px; background: var(--bg-secondary); border-radius: 4px; overflow: hidden; margin-top: 8px; }
.progress-fill { height: 100%; background: var(--primary); border-radius: 4px; transition: width .3s; width: 0%; }
</style>

<div class="content-wrapper">
  <div class="backup-hero">
    <div style="font-size:3rem;margin-bottom:10px"><i data-lucide="shield"></i></div>
    <h2>Backup & Restore Database</h2>
    <p>Buat cadangan data secara berkala dan pulihkan saat diperlukan. Disarankan backup minimal 1x seminggu.</p>
  </div>

  <div class="backup-grid">
    <!-- BACKUP SECTION -->
    <div class="backup-card">
      <div class="backup-card-header">
        <div class="icon-lg" style="background:#d1fae5;color:#059669"><i data-lucide="save"></i></div>
        <div>
          <h3 style="margin:0">Backup Database</h3>
          <p style="margin:2px 0 0;font-size:.82rem;color:var(--text-light)">Download file .sql cadangan</p>
        </div>
      </div>
      <div class="backup-card-body">
        <button class="btn btn-primary btn-lg" style="width:100%;margin-bottom:16px" onclick="doBackup()" id="btn-backup">
          <i data-lucide="save"></i> Buat Backup Sekarang
        </button>
        <p style="font-size:.78rem;color:var(--text-light);text-align:center;margin-bottom:16px">
          Backup akan mengunduh file SQL berisi seluruh data database
        </p>
        
        <h4 style="margin-bottom:10px;font-size:.88rem"><i data-lucide="folder"></i> Riwayat Backup Lokal</h4>
        <ul class="backup-list" id="backup-list">
          <li style="text-align:center;color:var(--text-light);padding:20px">Memuat...</li>
        </ul>
      </div>
    </div>

    <!-- RESTORE SECTION -->
    <div class="backup-card">
      <div class="backup-card-header">
        <div class="icon-lg" style="background:#fef3c7;color:#d97706"><i data-lucide="refresh-cw"></i></div>
        <div>
          <h3 style="margin:0">Restore Database</h3>
          <p style="margin:2px 0 0;font-size:.82rem;color:var(--text-light)">Pulihkan dari file backup .sql</p>
        </div>
      </div>
      <div class="backup-card-body">
        <div class="restore-zone" id="restore-zone" onclick="document.getElementById('restore-file').click()" 
             ondragover="event.preventDefault();this.classList.add('dragover')" 
             ondragleave="this.classList.remove('dragover')" 
             ondrop="handleDrop(event)">
          <div style="font-size:2.5rem"><i data-lucide="folder-open"></i></div>
          <p><strong>Klik atau seret file .sql ke sini</strong></p>
          <p>Format: .sql (maks 50MB)</p>
        </div>
        <input type="file" id="restore-file" accept=".sql" style="display:none" onchange="handleFileSelect(this)">
        
        <div id="restore-info" style="display:none;margin-top:16px;padding:12px;border-radius:10px;background:var(--bg-secondary)">
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
            <span style="font-size:1.3rem"><i data-lucide="file-text"></i></span>
            <div>
              <div style="font-weight:600" id="restore-filename">-</div>
              <div style="font-size:.75rem;color:var(--text-light)" id="restore-filesize">-</div>
            </div>
          </div>
          <button class="btn btn-warning" style="width:100%" onclick="doRestore()" id="btn-restore">
            <i data-lucide="refresh-cw"></i> Restore Sekarang
          </button>
        </div>
        
        <div class="restore-progress" id="restore-progress" style="display:none">
          <div style="display:flex;justify-content:space-between;font-size:.82rem">
            <span>Proses restore...</span>
            <span id="restore-status">0%</span>
          </div>
          <div class="progress-bar"><div class="progress-fill" id="restore-fill"></div></div>
        </div>
        
        <div id="restore-result" style="display:none;margin-top:16px;padding:12px;border-radius:10px;font-size:.85rem"></div>
      </div>
    </div>
  </div>
</div>

<div id="toast-container"></div>

<?php
$backupApiUrl = 'api/backup.php';
$csrfToken = generateCSRFToken();

$extraJs = <<<JS
<script>
const backupApi = '{$backupApiUrl}';
const csrfToken = '{$csrfToken}';

document.addEventListener('DOMContentLoaded', () => {
  loadBackupList();
});

// ===== BACKUP =====
async function doBackup() {
  const btn = document.getElementById('btn-backup');
  TU.btnLoading(btn, true);
  btn.innerHTML = '<i data-lucide="loader"></i> Membuat backup...';
  
  try {
    const response = await fetch(backupApi + '?action=backup&csrf_token=' + encodeURIComponent(csrfToken), {
      headers: { 'X-CSRF-Token': csrfToken }
    });
    
    if (response.ok) {
      const blob = await response.blob();
      const filename = response.headers.get('Content-Disposition')?.match(/filename="(.+)"/)?.[1] || 'backup.sql';
      
      // Download
      const a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = filename;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(a.href);
      
      TU.toast('Backup berhasil diunduh', 'success');
      loadBackupList(); // Refresh list
    } else {
      const err = await response.json();
      TU.toast(err.error || 'Gagal membuat backup', 'error');
    }
  } catch (e) {
    TU.toast('Error: ' + e.message, 'error');
  }
  
  TU.btnLoading(btn, false);
  btn.innerHTML = '<i data-lucide="save"></i> Buat Backup Sekarang';
  lucide.createIcons();
}

// ===== LIST BACKUPS =====
async function loadBackupList() {
  const list = document.getElementById('backup-list');
  try {
    const resp = await fetch(backupApi + '?action=listBackups', {
      headers: { 'X-CSRF-Token': csrfToken }
    });
    const result = await resp.json();
    
    if (result.success && result.backups.length > 0) {
      list.innerHTML = result.backups.map(b => {
        const size = (b.size / 1024).toFixed(1);
        const sizeLabel = size > 1024 ? (size / 1024).toFixed(1) + ' MB' : size + ' KB';
        return '<li>' +
          '<div class="file-info"><span><i data-lucide=\"file-text\"></i> ' + b.filename + '</span><span class="file-size">' + sizeLabel + ' • ' + b.date + '</span></div>' +
          '<button class="btn btn-sm btn-outline" onclick="downloadBackup(\'' + b.filename + '\')"><i data-lucide=\"download\"></i></button>' +
        '</li>';
      }).join('');
      lucide.createIcons();
    } else {
      list.innerHTML = '<li style="text-align:center;color:var(--text-light);padding:20px">Belum ada backup tersimpan</li>';
    }
  } catch (e) {
    list.innerHTML = '<li style="text-align:center;color:#ef4444">Gagal memuat daftar backup</li>';
  }
}

function downloadBackup(filename) {
  window.location.href = backupApi + '?action=backup&file=' + encodeURIComponent(filename);
}

// ===== RESTORE =====
let selectedFile = null;

function handleFileSelect(input) {
  if (input.files.length > 0) {
    showFileInfo(input.files[0]);
  }
}

function handleDrop(event) {
  event.preventDefault();
  event.currentTarget.classList.remove('dragover');
  if (event.dataTransfer.files.length > 0) {
    showFileInfo(event.dataTransfer.files[0]);
  }
}

function showFileInfo(file) {
  if (!file.name.endsWith('.sql')) {
    TU.toast('Hanya file .sql yang didukung', 'error');
    return;
  }
  if (file.size > 50 * 1024 * 1024) {
    TU.toast('Ukuran file terlalu besar (maks 50MB)', 'error');
    return;
  }
  
  selectedFile = file;
  const size = (file.size / 1024).toFixed(1);
  const sizeLabel = size > 1024 ? (size / 1024).toFixed(1) + ' MB' : size + ' KB';
  
  document.getElementById('restore-filename').textContent = file.name;
  document.getElementById('restore-filesize').textContent = sizeLabel;
  document.getElementById('restore-info').style.display = 'block';
  document.getElementById('restore-result').style.display = 'none';
}

async function doRestore() {
  if (!selectedFile) { TU.toast('Pilih file terlebih dahulu', 'error'); return; }
  
  if (!confirm('<i data-lucide=\"alert-triangle\"></i> PERINGATAN: Restore akan menimpa data yang ada. Pastikan sudah backup data terbaru. Lanjutkan?')) return;
  
  const btn = document.getElementById('btn-restore');
  const progress = document.getElementById('restore-progress');
  const fill = document.getElementById('restore-fill');
  const statusText = document.getElementById('restore-status');
  
  TU.btnLoading(btn, true);
  progress.style.display = 'block';
  fill.style.width = '30%';
  statusText.textContent = 'Mengunggah file...';
  
  const formData = new FormData();
  formData.append('sql_file', selectedFile);
  formData.append('action', 'restore');
  
  try {
    fill.style.width = '60%';
    statusText.textContent = 'Menjalankan restore...';
    
    const resp = await fetch(backupApi, {
      method: 'POST',
      headers: { 'X-CSRF-Token': csrfToken },
      body: formData
    });
    
    fill.style.width = '90%';
    const result = await resp.json();
    
    fill.style.width = '100%';
    
    if (result.success) {
      const resultDiv = document.getElementById('restore-result');
      resultDiv.style.display = 'block';
      resultDiv.style.background = '#d1fae5';
      resultDiv.style.color = '#065f46';
      resultDiv.innerHTML = '<i data-lucide=\"check-circle\"></i> ' + result.message + (result.error_count > 0 ? '<br><small>Error: ' + (result.errors || []).join('; ') + '</small>' : '');
      TU.toast('Restore berhasil!', 'success');
    } else {
      TU.toast(result.error || 'Restore gagal', 'error');
    }
  } catch (e) {
    TU.toast('Error: ' + e.message, 'error');
  }
  
  TU.btnLoading(btn, false);
  setTimeout(() => { progress.style.display = 'none'; fill.style.width = '0%'; }, 1000);
}
</script>
JS;
?>

<?php require_once 'includes/footer.php'; ?>
