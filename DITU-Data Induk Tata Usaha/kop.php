<?php
require_once 'config.php';
require_once 'includes/kop_helper.php';
requireLogin();
if (!isAdmin()) { header('Location: index.php'); exit; }

$pageTitle = 'Kop Surat';
include 'includes/header.php';
$kop = getKopSekolah();
$s = $kop;
?>

<div class="section-header">
  <h2><i data-lucide="file-text"></i> Pengaturan Kop Surat</h2>
  <p style="font-size:.82rem;color:var(--text-muted)">Atur data kop surat sekolah — ditampilkan pada cetakan rapor dan surat-menyurat</p>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start">
  <!-- Left: Form -->
  <div style="display:flex;flex-direction:column;gap:20px">
    <!-- Info Sekolah -->
    <div class="card">
      <div class="card-header"><h3><i data-lucide="school"></i> Informasi Sekolah</h3></div>
      <div class="card-body" style="padding:20px">
        <form id="form-kop" onsubmit="simpanKop(event)">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
            <div class="form-group" style="grid-column:span 2">
              <label class="form-label">Nama Sekolah (Lengkap)</label>
              <input type="text" class="form-control" id="kop-school_name" value="<?= htmlspecialchars($s['school_name'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Nama Singkat</label>
              <input type="text" class="form-control" id="kop-school_short" value="<?= htmlspecialchars($s['school_short'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">NPSN</label>
              <input type="text" class="form-control" id="kop-npsn" value="<?= htmlspecialchars($s['npsn'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">NSS</label>
              <input type="text" class="form-control" id="kop-nss" value="<?= htmlspecialchars($s['nss'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Akreditasi</label>
              <input type="text" class="form-control" id="kop-akreditasi" value="<?= htmlspecialchars($s['akreditasi'] ?? '') ?>" maxlength="2">
            </div>
            <div class="form-group" style="grid-column:span 2">
              <label class="form-label">Alamat Sekolah</label>
              <input type="text" class="form-control" id="kop-alamat" value="<?= htmlspecialchars($s['alamat'] ?? $s['alamat_sekolah'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Kecamatan</label>
              <input type="text" class="form-control" id="kop-kecamatan" value="<?= htmlspecialchars($s['kecamatan'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Kabupaten</label>
              <input type="text" class="form-control" id="kop-kabupaten" value="<?= htmlspecialchars($s['kabupaten'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Provinsi</label>
              <input type="text" class="form-control" id="kop-provinsi" value="<?= htmlspecialchars($s['provinsi'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Telepon</label>
              <input type="text" class="form-control" id="kop-telp" value="<?= htmlspecialchars($s['telp'] ?? '') ?>">
            </div>
            <div class="form-group" style="grid-column:span 2">
              <label class="form-label">Email Sekolah</label>
              <input type="email" class="form-control" id="kop-email" value="<?= htmlspecialchars($s['email'] ?? $s['email_sekolah'] ?? '') ?>">
            </div>
          </div>

          <!-- Kepala Sekolah -->
          <h4 style="margin:20px 0 12px;font-size:.9rem;color:var(--text-muted);border-top:1px solid var(--border);padding-top:16px">Kepala Sekolah</h4>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
            <div class="form-group">
              <label class="form-label">Nama Kepala Sekolah</label>
              <input type="text" class="form-control" id="kop-kepsek" value="<?= htmlspecialchars($s['kepala_sekolah'] ?? $s['kepsek'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">NIP Kepsek</label>
              <input type="text" class="form-control" id="kop-nip_kepsek" value="<?= htmlspecialchars($s['nip_kepsek'] ?? '') ?>">
            </div>
          </div>

          <!-- Dinas & Instansi -->
          <h4 style="margin:20px 0 12px;font-size:.9rem;color:var(--text-muted);border-top:1px solid var(--border);padding-top:16px">Dinas & Instansi</h4>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
            <div class="form-group">
              <label class="form-label">Dinas</label>
              <input type="text" class="form-control" id="kop-dinas" value="<?= htmlspecialchars($s['dinas'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Instansi</label>
              <input type="text" class="form-control" id="kop-instansi" value="<?= htmlspecialchars($s['instansi'] ?? '') ?>">
            </div>
          </div>

          <!-- Visi & Misi -->
          <h4 style="margin:20px 0 12px;font-size:.9rem;color:var(--text-muted);border-top:1px solid var(--border);padding-top:16px">Visi & Misi</h4>
          <div class="form-group">
            <label class="form-label">Visi</label>
            <textarea class="form-control" id="kop-visi" rows="2"><?= htmlspecialchars($s['visi'] ?? '') ?></textarea>
          </div>
          <div class="form-group">
            <label class="form-label">Misi</label>
            <textarea class="form-control" id="kop-misi" rows="3"><?= htmlspecialchars($s['misi'] ?? '') ?></textarea>
          </div>

          <!-- Tahun Ajaran -->
          <h4 style="margin:20px 0 12px;font-size:.9rem;color:var(--text-muted);border-top:1px solid var(--border);padding-top:16px">Tahun Ajaran</h4>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
            <div class="form-group">
              <label class="form-label">Tahun Ajaran</label>
              <input type="text" class="form-control" id="kop-tahun_ajaran" value="<?= htmlspecialchars($s['tahun_ajaran'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Semester</label>
              <input type="number" class="form-control" id="kop-semester" value="<?= htmlspecialchars($s['semester'] ?? '1') ?>" min="1" max="2">
            </div>
          </div>

          <div style="margin-top:20px;text-align:right;border-top:1px solid var(--border);padding-top:16px">
            <button type="submit" class="btn btn-primary" id="btn-simpan-kop"><i data-lucide="save"></i> Simpan Pengaturan</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Right: Logo Upload + Preview -->
  <div style="display:flex;flex-direction:column;gap:20px">
    <!-- Logo Upload -->
    <div class="card">
      <div class="card-header"><h3><i data-lucide="image"></i> Logo Sekolah</h3></div>
      <div class="card-body" style="padding:20px">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;text-align:center">
          <!-- Logo Kiri -->
          <div>
            <div style="width:90px;height:90px;border:2px dashed var(--border);border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 8px;cursor:pointer;overflow:hidden;background:var(--bg-secondary)" onclick="document.getElementById('input-logo-kiri').click()">
              <img id="preview-logo-kiri" src="data/uploads/<?= htmlspecialchars($s['logo_kiri'] ?? '') ?>" alt="Logo Kiri" style="max-width:100%;max-height:100%;object-fit:contain;display:<?= !empty($s['logo_kiri']) ? 'block' : 'none' ?>">
              <span id="placeholder-logo-kiri" style="font-size:2rem;display:<?= empty($s['logo_kiri']) ? 'block' : 'none' ?>"><i data-lucide="landmark"></i></span>
            </div>
            <input type="file" id="input-logo-kiri" accept="image/*" style="display:none" onchange="uploadLogo('logo_kiri', this)">
            <small style="font-weight:600;color:var(--text-muted)">Logo Kiri</small>
            <br><small style="color:var(--text-light)">(Pemda/Dinas)</small>
          </div>
          <!-- Logo Kanan -->
          <div>
            <div style="width:90px;height:90px;border:2px dashed var(--border);border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 8px;cursor:pointer;overflow:hidden;background:var(--bg-secondary)" onclick="document.getElementById('input-logo-kanan').click()">
              <img id="preview-logo-kanan" src="data/uploads/<?= htmlspecialchars($s['logo_kanan'] ?? '') ?>" alt="Logo Kanan" style="max-width:100%;max-height:100%;object-fit:contain;display:<?= !empty($s['logo_kanan']) ? 'block' : 'none' ?>">
              <span id="placeholder-logo-kanan" style="font-size:2rem;display:<?= empty($s['logo_kanan']) ? 'block' : 'none' ?>"><i data-lucide="graduation-cap"></i></span>
            </div>
            <input type="file" id="input-logo-kanan" accept="image/*" style="display:none" onchange="uploadLogo('logo_kanan', this)">
            <small style="font-weight:600;color:var(--text-muted)">Logo Kanan</small>
            <br><small style="color:var(--text-light)">(Tut Wuri Handayani)</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Preview Kop Surat -->
    <div class="card">
      <div class="card-header"><h3><i data-lucide="eye"></i> Preview Kop Surat</h3></div>
      <div class="card-body" style="padding:20px">
        <div id="preview-kop" style="border:1px solid var(--border);border-radius:8px;padding:24px 20px;background:#fff">
          <?= getKopSuratHtml($kop) ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
$extraJs = <<<'JS'
<script>
// Map form IDs to KopSekolah column names
const kopFields = [
  'school_name', 'school_short', 'npsn', 'nss', 'akreditasi',
  'alamat', 'kecamatan', 'kabupaten', 'provinsi', 'telp', 'email',
  'kepala_sekolah', 'nip_kepsek', 'dinas', 'instansi',
  'visi', 'misi', 'tahun_ajaran', 'semester'
];

// Save Kop settings
async function simpanKop(e) {
  e.preventDefault();
  const btn = document.getElementById('btn-simpan-kop');
  TU.btnLoading(btn, true);
  const data = {};
  kopFields.forEach(f => {
    const el = document.getElementById('kop-' + f);
    if (el) data[f] = el.value.trim();
  });

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const res = await fetch('api/kop.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': csrfToken },
    body: JSON.stringify({ action: 'save', data })
  });
  const r = await res.json();
  TU.btnLoading(btn, false);
  if (r.success) {
    TU.toast('Profil sekolah berhasil disimpan', 'success');
    updatePreview();
  } else {
    TU.toast(r.error || 'Gagal menyimpan', 'error');
  }
}

// Upload Logo
async function uploadLogo(key, input) {
  const file = input.files[0];
  if (!file) return;
  if (file.size > 2 * 1024 * 1024) { TU.toast('Ukuran max 2MB', 'error'); return; }

  TU.toast('Mengunggah logo...', 'info');
  const reader = new FileReader();
  reader.onload = async function(ev) {
    const base64 = ev.target.result.split(',')[1];
    const ext = file.name.split('.').pop();
    const filename = key + '_' + Date.now() + '.' + ext;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const res = await fetch('api/kop.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': csrfToken },
      body: JSON.stringify({ action: 'uploadLogo', data: base64, filename: filename, key: key })
    });
    const r = await res.json();

    if (r.success) {
      const preview = document.getElementById('preview-' + key);
      const placeholder = document.getElementById('placeholder-' + key);
      if (preview) {
        preview.src = ev.target.result;
        preview.style.display = 'block';
        if (placeholder) placeholder.style.display = 'none';
      }
      // Also update kop preview
      const kopPreview = document.getElementById('preview-kop-' + key);
      if (kopPreview) { kopPreview.src = ev.target.result; kopPreview.style.display = 'block'; }
      TU.toast('Logo berhasil diunggah', 'success');
    } else {
      TU.toast(r.error || 'Gagal mengunggah logo', 'error');
    }
  };
  reader.readAsDataURL(file);
}

// Update preview from form values
function updatePreview() {
  const instansi = document.getElementById('kop-instansi')?.value || 'PEMERINTAH KABUPATEN BERAU';
  const dinas = document.getElementById('kop-dinas')?.value || 'DINAS PENDIDIKAN';
  const name = document.getElementById('kop-school_name')?.value || 'SD NEGERI 001 GUNUNG SARI';
  const alamat = document.getElementById('kop-alamat')?.value || '';
  const kecamatan = document.getElementById('kop-kecamatan')?.value || '';
  const kabupaten = document.getElementById('kop-kabupaten')?.value || '';
  const provinsi = document.getElementById('kop-provinsi')?.value || '';
  const email = document.getElementById('kop-email')?.value || '';
  const npsn = document.getElementById('kop-npsn')?.value || '';
  const nss = document.getElementById('kop-nss')?.value || '';
  const akreditasi = document.getElementById('kop-akreditasi')?.value || '';

  document.getElementById('preview-instansi').textContent = instansi.toUpperCase();
  document.getElementById('preview-dinas').textContent = dinas.toUpperCase();
  const previewName = document.getElementById('preview-nama-sekolah');
  if (previewName) previewName.textContent = name.toUpperCase();

  // Build alamat line
  let alamatParts = [alamat];
  if (kecamatan) alamatParts.push('Kec. ' + kecamatan);
  if (kabupaten) alamatParts.push('Kab. ' + kabupaten);
  if (provinsi) alamatParts.push('Prov. ' + provinsi);
  const previewAlamat = document.getElementById('preview-alamat');
  if (previewAlamat) previewAlamat.textContent = alamatParts.join(', ');

  const previewNpsn = document.getElementById('preview-npsn');
  if (previewNpsn) previewNpsn.textContent = npsn;
  const previewEmail = document.getElementById('preview-email');
  if (previewEmail) previewEmail.textContent = email;
  const previewNss = document.getElementById('preview-nss');
  if (previewNss) previewNss.textContent = nss;
  const previewAkreditasi = document.getElementById('preview-akreditasi');
  if (previewAkreditasi) previewAkreditasi.textContent = 'TERAKREDITASI : ' + akreditasi;
}

// Live update preview on form change
document.querySelectorAll('[id^="kop-"]').forEach(el => {
  el.addEventListener('input', updatePreview);
});
</script>
JS;
?>

<?php include 'includes/footer.php'; ?>
