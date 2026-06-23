<?php
require_once 'config.php';
requireAdmin();
$pageTitle    = 'Pengaturan';
$pageSubtitle = 'Konfigurasi Aplikasi';

$settings = getSettings();

// Known fields that get special treatment
$logoKeys = ['app_logo'];
$textFields = ['app_name', 'footer_text'];
// Everything else falls into "Pengaturan Tambahan"

include 'includes/header.php';
?>

<div class="section-header">
  <div>
    <h2><span class="sh-icon"><i data-lucide="settings"></i></span> Pengaturan</h2>
    <p>Semua pengaturan aplikasi dari database — ubah dan simpan langsung</p>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start">
  <!-- Left: All Text Settings -->
  <div style="display:flex;flex-direction:column;gap:20px">
    <!-- Pengaturan Aplikasi -->
    <div class="card">
      <div class="card-header">
        <h3><i data-lucide="monitor"></i> Pengaturan Aplikasi</h3>
      </div>
      <div class="card-body" style="padding:20px 24px">
        <form id="form-settings" onsubmit="simpanSettings(event)">
          <?php foreach ($settings as $key => $value): ?>
            <?php if (in_array($key, $logoKeys)) continue; ?>
            <?php if ($key === 'app_locked'): ?>
              <div class="form-group" style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)">
                <label class="form-label" style="margin:0"><i data-lucide="lock"></i> Kunci Aplikasi (Maintenance Mode)</label>
                <input type="hidden" name="app_locked" value="0">
                <label class="toggle-switch">
                  <input type="checkbox" name="app_locked" value="1" <?= ($value ?? '0') === '1' ? 'checked' : '' ?> onchange="document.getElementById('lock-status').textContent=this.checked?'ON — Hanya admin bisa login':'OFF'">
                  <span class="toggle-slider"></span>
                </label>
                <span id="lock-status" style="font-size:.78rem;color:var(--text-muted);min-width:160px;text-align:right"><?= ($value ?? '0') === '1' ? 'ON — Hanya admin bisa login' : 'OFF' ?></span>
              </div>
            <?php elseif ($key === 'session_timeout'): ?>
              <div class="form-group">
                <label class="form-label"><i data-lucide="clock"></i> Batas Waktu Idle (menit)</label>
                <input type="number" name="session_timeout" class="form-control" value="<?= htmlspecialchars($value) ?>" min="5" max="120" style="max-width:200px">
                <small style="color:var(--text-light)">Default: 15 menit. User akan logout otomatis jika tidak ada aktivitas.</small>
              </div>
            <?php else: ?>
              <div class="form-group">
                <label class="form-label"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $key))) ?></label>
                <input type="text" name="<?= htmlspecialchars($key) ?>" class="form-control" value="<?= htmlspecialchars($value) ?>">
              </div>
            <?php endif; ?>
          <?php endforeach; ?>

          <?php if (empty($settings)): ?>
            <p style="color:var(--text-muted);font-size:.85rem">Belum ada pengaturan di database.</p>
          <?php endif; ?>

          <div style="margin-top:16px">
            <button type="submit" class="btn btn-primary" id="btn-save-settings"><i data-lucide="save"></i> Simpan Semua Pengaturan</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Right: Logo Upload -->
  <div style="display:flex;flex-direction:column;gap:20px">
    <div class="card">
      <div class="card-header">
        <h3><i data-lucide="image"></i> Logo Aplikasi</h3>
      </div>
      <div class="card-body" style="padding:20px;text-align:center">
        <div style="width:100px;height:100px;border:2px dashed var(--border);border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;cursor:pointer;overflow:hidden;background:var(--bg-hover)" onclick="document.getElementById('input-logo-app').click()">
          <img id="preview-logo-app" src="data/uploads/<?= htmlspecialchars($settings['app_logo'] ?? '') ?>" alt="Logo App" style="max-width:100%;max-height:100%;object-fit:contain;display:<?= !empty($settings['app_logo']) ? 'block' : 'none' ?>">
          <span id="placeholder-logo-app" style="font-size:2.5rem;display:<?= empty($settings['app_logo']) ? 'block' : 'none' ?>"><i data-lucide="school"></i></span>
        </div>
        <input type="file" id="input-logo-app" accept="image/*" style="display:none" onchange="uploadLogoApp(this)">
        <button type="button" class="btn btn-outline" onclick="document.getElementById('input-logo-app').click()" style="font-size:.8rem"><i data-lucide="upload"></i> Upload Logo</button>
        <p style="margin-top:8px;font-size:.72rem;color:var(--text-light)">Max 2MB • Format: JPG, PNG, SVG</p>
      </div>
    </div>

    <!-- Info Card -->
    <div class="card">
      <div class="card-header">
        <h3><i data-lucide="info"></i> Informasi</h3>
      </div>
      <div class="card-body" style="padding:16px 20px">
        <p style="font-size:.82rem;color:var(--text-muted);line-height:1.6">
          Semua pengaturan di atas disimpan di tabel <code>Settings</code> sebagai key-value pairs.
          Ubah nilai sesuai kebutuhan, lalu klik <strong>Simpan</strong>.
        </p>
        <p style="font-size:.82rem;color:var(--text-muted);line-height:1.6;margin-top:8px">
          Total: <strong><?= count($settings) ?></strong> pengaturan aktif
        </p>
      </div>
    </div>
  </div>
</div>

<?php
$extraJs = <<<'JS'
<script>
async function simpanSettings(e) {
  e.preventDefault();
  const form = e.target;
  const btn = form.querySelector('[type="submit"]');
  TU.btnLoading(btn, true);

  const formData = new FormData(form);
  const data = Object.fromEntries(formData.entries());

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const res = await fetch('api/settings.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': csrfToken },
    body: JSON.stringify({ action: 'save', data })
  });
  const r = await res.json();

  TU.btnLoading(btn, false);
  if (r.success) {
    TU.toast('Semua pengaturan berhasil disimpan', 'success');
  } else {
    TU.toast(r.error || 'Gagal menyimpan pengaturan', 'error');
  }
}

async function uploadLogoApp(input) {
  const file = input.files[0];
  if (!file) return;
  if (file.size > 2 * 1024 * 1024) { TU.toast('Ukuran max 2MB', 'error'); return; }

  TU.toast('Mengunggah logo...', 'info');
  const reader = new FileReader();
  reader.onload = async function(ev) {
    const base64 = ev.target.result.split(',')[1];
    const ext = file.name.split('.').pop();
    const filename = 'app_logo_' + Date.now() + '.' + ext;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const res = await fetch('api/settings.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': csrfToken },
      body: JSON.stringify({ action: 'uploadLogo', data: base64, filename: filename, key: 'app_logo' })
    });
    const r = await res.json();

    if (r.success) {
      const preview = document.getElementById('preview-logo-app');
      const placeholder = document.getElementById('placeholder-logo-app');
      if (preview) { preview.src = ev.target.result; preview.style.display = 'block'; }
      if (placeholder) placeholder.style.display = 'none';
      TU.toast('Logo aplikasi berhasil diunggah', 'success');
    } else {
      TU.toast(r.error || 'Gagal mengunggah logo', 'error');
    }
  };
  reader.readAsDataURL(file);
}
</script>
JS;
?>

<?php include 'includes/footer.php'; ?>
