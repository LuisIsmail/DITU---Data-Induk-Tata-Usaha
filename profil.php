<?php
require_once 'config.php';
requireLogin();
$pageTitle = 'Profil Pengguna';
include 'includes/header.php';

$conn = dbConnect();
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT u.id, u.username, u.nama, u.role, u.email, u.foto, u.nip, u.rombel, u.aktif, u.last_login,
    p.id as ptk_id, p.jk, p.nik, p.tempat_lahir, p.tgl_lahir, p.agama, p.status_nikah, p.jml_anak,
    p.nip as ptk_nip, p.nuptk, p.jenis_ptk, p.status_kepeg, p.golongan, p.sertifikasi,
    p.pendidikan, p.jurusan, p.perguruan_tinggi, p.tahun_lulus,
    p.alamat, p.hp as ptk_hp, p.email as ptk_email,
    p.mapel_diampu, p.rombel_diampu, p.tugas_tambahan,
    p.foto as ptk_foto, p.catatan as ptk_catatan
    FROM users u LEFT JOIN dataptk p ON u.id = p.user_id WHERE u.id = ?");
$stmt->bind_param("s", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$fotoTs = !empty($user['foto']) ? @filemtime(getUserUploadDir($userId) . $user['foto']) : time();
$fotoUrl = !empty($user['foto']) ? getUserUploadUrl($userId, $user['foto']) . '?t=' . $fotoTs : '';
$lastLogin = $user['last_login'] ? date('d M Y H:i', strtotime($user['last_login'])) : '-';
$hasPTK = !empty($user['ptk_id']);
?>

<div class="section-header">
  <h2><i data-lucide="user"></i> Profil Pengguna</h2>
  <p style="font-size:.82rem;color:var(--text-muted)">Kelola informasi profil dan keamanan akun Anda</p>
</div>

<div style="display:grid;grid-template-columns:350px 1fr;gap:20px;align-items:start">
  <!-- Profile Card -->
  <div class="card" style="text-align:center">
    <div class="card-body" style="padding:30px 20px">
      <div id="profile-foto-container" style="width:120px;height:120px;border-radius:50%;overflow:hidden;margin:0 auto 16px;border:3px solid var(--primary);cursor:pointer;position:relative" onclick="document.getElementById('input-foto').click()">
        <img id="profile-foto" src="<?= $fotoUrl ?>" alt="Foto Profil" style="width:100%;height:100%;object-fit:cover;display:<?= $fotoUrl ? 'block' : 'none' ?>">
        <div id="profile-placeholder" style="width:100%;height:100%;display:<?= $fotoUrl ? 'none' : 'flex' ?>;align-items:center;justify-content:center;background:transparent;font-size:2.5rem;color:var(--text)">
          <?= strtoupper(substr($user['nama'], 0, 1)) ?>
        </div>
        <div style="position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,.5);color:#fff;font-size:.65rem;padding:4px 0;text-align:center"><i data-lucide="camera"></i> Ganti Foto</div>
      </div>
      <input type="file" id="input-foto" accept="image/*" style="display:none" onchange="uploadFoto(this)">
      <h3 style="margin:0 0 4px;font-size:1.1rem"><?= htmlspecialchars($user['nama']) ?></h3>
      <p style="color:var(--text-muted);font-size:.82rem;margin:0 0 12px">@<?= htmlspecialchars($user['username']) ?></p>
      <span class="badge badge-<?= $user['role'] === 'admin' ? 'success' : 'info' ?>" style="font-size:.75rem;padding:4px 12px;border-radius:12px">
        <?= $user['role'] === 'admin' ? '<i data-lucide="crown"></i> Administrator' : '<i data-lucide="briefcase"></i> Guru' ?>
      </span>
      <div style="margin-top:16px;text-align:left;border-top:1px solid var(--border);padding-top:16px">
        <div style="display:flex;justify-content:space-between;font-size:.78rem;margin-bottom:8px">
          <span style="color:var(--text-muted)">Email</span>
          <span style="font-weight:500"><?= htmlspecialchars($user['email'] ?: '-') ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:.78rem;margin-bottom:8px">
          <span style="color:var(--text-muted)">NIP</span>
          <span style="font-weight:500"><?= htmlspecialchars($user['nip'] ?: '-') ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:.78rem;margin-bottom:8px">
          <span style="color:var(--text-muted)">Rombel</span>
          <span style="font-weight:500"><?= htmlspecialchars($user['rombel'] ?: '-') ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:.78rem;margin-bottom:8px">
          <span style="color:var(--text-muted)">Status</span>
          <span class="badge badge-<?= $user['aktif'] === '1' ? 'success' : 'danger' ?>" style="font-size:.7rem;padding:2px 8px;border-radius:8px">
            <?= $user['aktif'] === '1' ? '<i data-lucide="check-circle"></i> Aktif' : '<i data-lucide="x-circle"></i> Nonaktif' ?>
          </span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:.78rem">
          <span style="color:var(--text-muted)">Login Terakhir</span>
          <span style="font-weight:500"><?= $lastLogin ?></span>
        </div>
      </div>
    </div>
  </div>

  <!-- Right Column: Forms -->
  <div style="display:flex;flex-direction:column;gap:20px">
    <!-- Edit Profile Form -->
    <div class="card">
      <div class="card-header">
        <h3><i data-lucide="pencil"></i> Edit Profil</h3>
      </div>
      <div class="card-body" style="padding:20px">
        <form id="form-edit-profil" onsubmit="simpanProfil(event)">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <div class="form-group">
              <label class="form-label">Nama Lengkap</label>
              <input type="text" class="form-control" id="edit-nama" value="<?= htmlspecialchars($user['nama']) ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Username</label>
              <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled style="opacity:.6">
              <small style="color:var(--text-muted)">Username tidak dapat diubah</small>
            </div>
            <div class="form-group">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" id="edit-email" value="<?= htmlspecialchars($user['email']) ?>">
            </div>
            <div class="form-group">
              <label class="form-label">NIP</label>
              <input type="text" class="form-control" id="edit-nip" value="<?= htmlspecialchars($user['nip']) ?>">
            </div>
          </div>
          <div style="margin-top:16px;text-align:right">
            <button type="submit" class="btn btn-primary" id="btn-simpan-profil"><i data-lucide="save"></i> Simpan Profil</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Change Password Form -->
    <div class="card">
      <div class="card-header">
        <h3><i data-lucide="lock"></i> Ganti Password</h3>
      </div>
      <div class="card-body" style="padding:20px">
        <form id="form-ganti-password" onsubmit="gantiPassword(event)">
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px">
            <div class="form-group">
              <label class="form-label">Password Lama</label>
              <input type="password" class="form-control" id="pw-lama" placeholder="Masukkan password lama" required>
            </div>
            <div class="form-group">
              <label class="form-label">Password Baru</label>
              <input type="password" class="form-control" id="pw-baru" placeholder="Minimal 6 karakter" required minlength="6">
            </div>
            <div class="form-group">
              <label class="form-label">Konfirmasi Password Baru</label>
              <input type="password" class="form-control" id="pw-konfirmasi" placeholder="Ulangi password baru" required minlength="6">
            </div>
          </div>
          <div style="margin-top:16px;text-align:right">
            <button type="submit" class="btn btn-warning" id="btn-ganti-pw"><i data-lucide="key"></i> Ganti Password</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Data PTK Section (shown if user has PTK record) -->
<?php if ($hasPTK): ?>
<div style="margin-top:24px">
  <div class="section-header">
    <div style="display:flex;justify-content:space-between;align-items:center;width:100%">
      <div>
        <h2><span class="sh-icon"><i data-lucide="briefcase"></i></span> Data PTK (Pendidik & Tenaga Kependidikan)</h2>
        <p>Biodata kepegawaian yang terintegrasi dengan akun pengguna</p>
      </div>
      <button class="btn btn-info" id="btn-edit-ptk" onclick="togglePTKEdit()"><i data-lucide="pencil"></i> Edit Data PTK</button>
    </div>
  </div>

  <!-- Read-Only View -->
  <div id="ptk-view">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
      <div class="card">
        <div class="card-header"><h3><i data-lucide="clipboard-list"></i> Informasi Kepegawaian</h3></div>
        <div class="card-body" style="padding:16px 20px">
          <?php
          $ptkRows = [
            'NIP'           => $user['ptk_nip'] ?? $user['nip'] ?? '-',
            'NUPTK'         => $user['nuptk'] ?? '-',
            'Jenis PTK'     => $user['jenis_ptk'] ?? '-',
            'Status Kepeg.' => $user['status_kepeg'] ?? '-',
            'Golongan'      => $user['golongan'] ?? '-',
            'Sertifikasi'   => $user['sertifikasi'] ?? '-',
            'Status Aktif'  => $user['status_aktif'] ?? '-',
          ];
          foreach ($ptkRows as $k => $v):
          ?>
          <div style="display:flex;gap:10px;padding:7px 0;border-bottom:1px solid var(--border);font-size:.82rem">
            <span style="color:var(--text-muted);width:130px;flex-shrink:0"><?= $k ?></span>
            <span style="font-weight:500"><?= htmlspecialchars($v) ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="card">
        <div class="card-header"><h3><i data-lucide="user"></i> Informasi Pribadi</h3></div>
        <div class="card-body" style="padding:16px 20px">
          <?php
          $pribadiRows = [
            'Jenis Kelamin'  => $user['jk'] ?? '-',
            'NIK'            => $user['nik'] ?? '-',
            'Tempat Lahir'   => $user['tempat_lahir'] ?? '-',
            'Tanggal Lahir'  => $user['tgl_lahir'] ? date('d M Y', strtotime($user['tgl_lahir'])) : '-',
            'Agama'          => $user['agama'] ?? '-',
            'Status Nikah'   => $user['status_nikah'] ?? '-',
            'Jumlah Anak'    => $user['jml_anak'] ?? '-',
            'Alamat'         => $user['alamat'] ?? '-',
            'HP'             => $user['ptk_hp'] ?? '-',
          ];
          foreach ($pribadiRows as $k => $v):
          ?>
          <div style="display:flex;gap:10px;padding:7px 0;border-bottom:1px solid var(--border);font-size:.82rem">
            <span style="color:var(--text-muted);width:130px;flex-shrink:0"><?= $k ?></span>
            <span style="font-weight:500"><?= htmlspecialchars($v) ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="card">
        <div class="card-header"><h3><i data-lucide="graduation-cap"></i> Pendidikan</h3></div>
        <div class="card-body" style="padding:16px 20px">
          <?php
          $pendRows = [
            'Pendidikan'         => $user['pendidikan'] ?? '-',
            'Jurusan'            => $user['jurusan'] ?? '-',
            'Perguruan Tinggi'   => $user['perguruan_tinggi'] ?? '-',
            'Tahun Lulus'        => $user['tahun_lulus'] ?? '-',
          ];
          foreach ($pendRows as $k => $v):
          ?>
          <div style="display:flex;gap:10px;padding:7px 0;border-bottom:1px solid var(--border);font-size:.82rem">
            <span style="color:var(--text-muted);width:140px;flex-shrink:0"><?= $k ?></span>
            <span style="font-weight:500"><?= htmlspecialchars($v) ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="card">
        <div class="card-header"><h3><i data-lucide="book-open"></i> Tugas Mengajar</h3></div>
        <div class="card-body" style="padding:16px 20px">
          <?php
          $tugasRows = [
            'Mapel Diampu'      => $user['mapel_diampu'] ?? '-',
            'Rombel Diampu'     => $user['rombel_diampu'] ?? '-',
            'Tugas Tambahan'    => $user['tugas_tambahan'] ?? '-',
          ];
          foreach ($tugasRows as $k => $v):
          ?>
          <div style="display:flex;gap:10px;padding:7px 0;border-bottom:1px solid var(--border);font-size:.82rem">
            <span style="color:var(--text-muted);width:140px;flex-shrink:0"><?= $k ?></span>
            <span style="font-weight:500"><?= htmlspecialchars($v) ?></span>
          </div>
          <?php endforeach; ?>
          <?php if (!empty($user['ptk_catatan'])): ?>
          <div style="margin-top:12px;padding:10px;background:var(--bg-hover);border-radius:8px;font-size:.8rem">
            <strong style="color:var(--text-muted)"><i data-lucide="pencil-line"></i> Catatan:</strong><br>
            <?= nl2br(htmlspecialchars($user['ptk_catatan'])) ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Form (hidden by default) -->
  <div id="ptk-edit" style="display:none">
    <form id="form-edit-ptk" onsubmit="simpanPTKSelf(event)">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
        <!-- Informasi Pribadi -->
        <div class="card">
          <div class="card-header"><h3><i data-lucide="user"></i> Informasi Pribadi</h3></div>
          <div class="card-body" style="padding:16px 20px">
            <div class="form-group">
              <label class="form-label">NIK</label>
              <input type="text" class="form-control" name="nik" value="<?= htmlspecialchars($user['nik'] ?? '') ?>" maxlength="16" placeholder="16 digit NIK">
            </div>
            <div class="form-group">
              <label class="form-label">Tempat Lahir</label>
              <input type="text" class="form-control" name="tempat_lahir" value="<?= htmlspecialchars($user['tempat_lahir'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Tanggal Lahir</label>
              <input type="date" class="form-control" name="tgl_lahir" value="<?= $user['tgl_lahir'] ?? '' ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Agama</label>
              <select class="form-select" name="agama">
                <option value="">Pilih</option>
                <?php foreach (['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu'] as $ag): ?>
                <option <?= ($user['agama'] ?? '') === $ag ? 'selected' : '' ?>><?= $ag ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Status Nikah</label>
              <select class="form-select" name="status_nikah">
                <option value="">Pilih</option>
                <?php foreach (['Belum Menikah','Menikah','Cerai Hidup','Cerai Mati'] as $sn): ?>
                <option <?= ($user['status_nikah'] ?? '') === $sn ? 'selected' : '' ?>><?= $sn ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Jumlah Anak</label>
              <input type="number" class="form-control" name="jml_anak" value="<?= $user['jml_anak'] ?? '' ?>" min="0">
            </div>
            <div class="form-group">
              <label class="form-label">Alamat</label>
              <textarea class="form-control" name="alamat" rows="2"><?= htmlspecialchars($user['alamat'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
              <label class="form-label">HP</label>
              <input type="text" class="form-control" name="hp" value="<?= htmlspecialchars($user['ptk_hp'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['ptk_email'] ?? '') ?>">
            </div>
          </div>
        </div>
        <!-- Tugas Mengajar -->
        <div class="card">
          <div class="card-header"><h3><i data-lucide="book-open"></i> Tugas Mengajar & Catatan</h3></div>
          <div class="card-body" style="padding:16px 20px">
            <div class="form-group">
              <label class="form-label">Mapel Diampu</label>
              <input type="text" class="form-control" name="mapel_diampu" value="<?= htmlspecialchars($user['mapel_diampu'] ?? '') ?>" placeholder="Contoh: Matematika, IPA">
            </div>
            <div class="form-group">
              <label class="form-label">Rombel Diampu</label>
              <select class="form-select" name="rombel_diampu">
                <option value="">Pilih Rombel</option>
                <?php for($k=1;$k<=6;$k++): foreach(['A','B','C','D','E','F'] as $huruf): ?>
                <option value="<?= $k ?><?= $huruf ?>" <?= ($user['rombel_diampu'] ?? '') === $k.$huruf ? 'selected' : '' ?>><?= $k ?><?= $huruf ?></option>
                <?php endforeach; endfor; ?>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Tugas Tambahan</label>
              <input type="text" class="form-control" name="tugas_tambahan" value="<?= htmlspecialchars($user['tugas_tambahan'] ?? '') ?>" placeholder="Contoh: Wali Kelas 1A">
            </div>
            <div class="form-group">
              <label class="form-label">Catatan</label>
              <textarea class="form-control" name="catatan" rows="4" placeholder="Catatan tambahan..."><?= htmlspecialchars($user['ptk_catatan'] ?? '') ?></textarea>
            </div>
          </div>
        </div>
      </div>
      <!-- Dokumen Upload -->
      <div class="card" style="margin-top:20px">
        <div class="card-header"><h3><i data-lucide="file-text"></i> Dokumen Pendukung</h3></div>
        <div class="card-body" style="padding:16px 20px">
          <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:16px">Upload dokumen pendukung. Format: JPG, PNG, PDF. <strong>Maks 1MB per file.</strong></p>
          <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px">
            <?php
            $docFields = [
              'dokumen_ijazah_sd' => '<i data-lucide="file-text"></i> Ijazah SD',
              'dokumen_ijazah_smp' => '<i data-lucide="file-text"></i> Ijazah SMP',
              'dokumen_ijazah_sma' => '<i data-lucide="file-text"></i> Ijazah SMA',
              'dokumen_ijazah_s1' => '<i data-lucide="file-text"></i> Ijazah S1',
              'dokumen_kk' => '<i data-lucide="credit-card"></i> Kartu Keluarga',
              'dokumen_ktp' => '<i data-lucide="credit-card"></i> KTP',
              'dokumen_npwp' => '<i data-lucide="credit-card"></i> NPWP',
              'dokumen_sertif_1' => '<i data-lucide="award"></i> Sertifikat 1',
              'dokumen_sertif_2' => '<i data-lucide="award"></i> Sertifikat 2',
            ];
            foreach ($docFields as $field => $label):
              $existing = $user[$field] ?? '';
            ?>
            <div style="display:flex;flex-direction:column;gap:6px">
              <label style="font-size:.8rem;font-weight:600"><?= $label ?></label>
              <?php if ($existing): ?>
              <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px">
                <a href="data/uploads/ptk_docs/<?= htmlspecialchars($existing) ?>" target="_blank" style="font-size:.75rem;color:var(--primary);text-decoration:underline"><i data-lucide="paperclip"></i> <?= htmlspecialchars($existing) ?></a>
                <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove();uploadDocField('<?= $field ?>', this)" title="Ganti file"><i data-lucide="refresh-cw"></i></button>
              </div>
              <?php endif; ?>
              <input type="file" class="form-control" accept="image/*,.pdf" style="font-size:.78rem;padding:6px" onchange="handleDocUpload(this, '<?= $field ?>')">
              <input type="hidden" name="<?= $field ?>" value="<?= htmlspecialchars($existing) ?>">
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <div style="margin-top:16px;text-align:right;display:flex;gap:8px;justify-content:flex-end">
        <button type="button" class="btn btn-light" onclick="togglePTKEdit()"><i data-lucide="x"></i> Batal</button>
        <button type="submit" class="btn btn-primary" id="btn-simpan-ptk-self"><i data-lucide="save"></i> Simpan Data PTK</button>
      </div>
    </form>
  </div>
</div>
<?php else: ?>
<!-- No PTK record -->
<div style="margin-top:24px">
  <div class="card">
    <div class="card-body" style="padding:30px;text-align:center">
      <div style="font-size:2rem;margin-bottom:12px"><i data-lucide="clipboard-list"></i></div>
      <h4 style="margin-bottom:8px;color:var(--text-muted)">Belum Ada Data PTK</h4>
      <p style="font-size:.82rem;color:var(--text-muted)">Data PTK akan otomatis dibuat saat akun ini didaftarkan oleh admin. Hubungi administrator untuk informasi lebih lanjut.</p>
    </div>
  </div>
</div>
<?php endif; ?>

<?php
$extraJs = <<<'JS'
<script>
// ===== Profile API Helper =====
async function profileApi(action, data = {}) {
  data.action = action;
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const res = await fetch('api/profile.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': csrfToken },
    body: JSON.stringify(data)
  });
  return res.json();
}

// ===== Save Profile =====
async function simpanProfil(e) {
  e.preventDefault();
  const btn = document.getElementById('btn-simpan-profil');
  TU.btnLoading(btn, true);
  const data = {
    nama: document.getElementById('edit-nama').value.trim(),
    email: document.getElementById('edit-email').value.trim(),
    nip: document.getElementById('edit-nip').value.trim()
  };
  const r = await profileApi('updateProfile', { data });
  TU.btnLoading(btn, false);
  if (r.success) {
    TU.toast('Profil berhasil diperbarui', 'success');
    // Update header nama if visible
    const headerNama = document.querySelector('.user-name');
    if (headerNama) headerNama.textContent = data.nama;
  } else {
    TU.toast(r.error || 'Gagal memperbarui profil', 'error');
  }
}

// ===== Upload Foto =====
async function uploadFoto(input) {
  const file = input.files[0];
  if (!file) return;
  if (file.size > 2 * 1024 * 1024) {
    TU.toast('Ukuran foto max 2MB', 'error');
    return;
  }
  if (!file.type.startsWith('image/')) {
    TU.toast('File harus berupa gambar', 'error');
    return;
  }

  TU.toast('Mengunggah foto...', 'info');

  // Read as base64
  const reader = new FileReader();
  reader.onload = async function(ev) {
    const base64 = ev.target.result.split(',')[1];
    const ext = file.name.split('.').pop();
    const filename = 'foto_' + Date.now() + '.' + ext;

    const r = await profileApi('uploadFoto', { foto: base64, filename });
    if (r.success) {
      // Update preview
      const img = document.getElementById('profile-foto');
      const placeholder = document.getElementById('profile-placeholder');
      img.src = 'data/uploads/' + r.user_id + '/' + r.filename + '?t=' + Date.now();
      img.style.display = 'block';
      placeholder.style.display = 'none';
      TU.toast('Foto profil berhasil diunggah', 'success');
    } else {
      TU.toast(r.error || 'Gagal mengunggah foto', 'error');
    }
  };
  reader.readAsDataURL(file);
}

// ===== Change Password =====
async function gantiPassword(e) {
  e.preventDefault();
  const pwLama = document.getElementById('pw-lama').value;
  const pwBaru = document.getElementById('pw-baru').value;
  const pwKonfirmasi = document.getElementById('pw-konfirmasi').value;

  if (pwBaru !== pwKonfirmasi) {
    TU.toast('Konfirmasi password tidak cocok', 'error');
    return;
  }
  if (pwBaru.length < 6) {
    TU.toast('Password baru minimal 6 karakter', 'error');
    return;
  }

  const btn = document.getElementById('btn-ganti-pw');
  TU.btnLoading(btn, true);
  const r = await profileApi('changePassword', { current_password: pwLama, new_password: pwBaru });
  TU.btnLoading(btn, false);

  if (r.success) {
    TU.toast('Password berhasil diganti', 'success');
    document.getElementById('form-ganti-password').reset();
  } else {
    TU.toast(r.error || 'Gagal mengganti password', 'error');
  }
}

// ===== Document Upload =====
async function handleDocUpload(input, field) {
  const file = input.files[0];
  if (!file) return;
  if (file.size > 1048576) { // 1MB
    TU.toast('Ukuran file maksimal 1MB!', 'error');
    input.value = '';
    return;
  }
  if (!file.type.match(/^image\/|application\/pdf$/)) {
    TU.toast('Format file harus JPG, PNG, atau PDF', 'error');
    input.value = '';
    return;
  }
  TU.toast('Mengupload dokumen...', 'info');
  const reader = new FileReader();
  reader.onload = async function(e) {
    const base64 = e.target.result.split(',')[1];
    const r = await profileApi('uploadPTKDoc', { field, data: base64, name: file.name });
    if (r.success) {
      // Update hidden field
      const hidden = input.parentElement.querySelector('input[type="hidden"]');
      if (hidden) hidden.value = r.filename;
      TU.toast('Dokumen berhasil diupload', 'success');
    } else {
      TU.toast(r.error || 'Gagal upload dokumen', 'error');
    }
  };
  reader.readAsDataURL(file);
}

// ===== PTK Self-Edit =====
function togglePTKEdit() {
  const view = document.getElementById('ptk-view');
  const edit = document.getElementById('ptk-edit');
  const btn = document.getElementById('btn-edit-ptk');
  if (edit.style.display === 'none') {
    view.style.display = 'none';
    edit.style.display = 'block';
    btn.innerHTML = '<i data-lucide="eye"></i> Lihat Data PTK';
    btn.classList.remove('btn-info');
    btn.classList.add('btn-light');
  } else {
    view.style.display = 'block';
    edit.style.display = 'none';
    btn.innerHTML = '<i data-lucide="pencil"></i> Edit Data PTK';
    btn.classList.remove('btn-light');
    btn.classList.add('btn-info');
  }
  lucide.createIcons();
}

async function simpanPTKSelf(e) {
  e.preventDefault();
  const btn = document.getElementById('btn-simpan-ptk-self');
  TU.btnLoading(btn, true);
  const form = document.getElementById('form-edit-ptk');
  const formData = new FormData(form);
  const data = Object.fromEntries(formData.entries());
  const r = await profileApi('updatePTK', { data });
  TU.btnLoading(btn, false);
  if (r.success) {
    TU.toast('Data PTK berhasil diperbarui', 'success');
    togglePTKEdit(); // Switch back to view mode
    setTimeout(() => location.reload(), 800); // Reload to show updated values
  } else {
    TU.toast(r.error || 'Gagal memperbarui data PTK', 'error');
  }
}
</script>
JS;
?>

<?php include 'includes/footer.php'; ?>
