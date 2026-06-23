<?php
require_once 'config.php';
requireAdmin(); // Only admin can manage users
$pageTitle    = 'Manajemen User';
$pageSubtitle = 'Kelola Akun Pengguna';
include 'includes/header.php';
?>

<div class="section-header">
  <div>
    <h2><span class="sh-icon"><i data-lucide="users"></i></span> Manajemen User</h2>
    <p>Kelola akun pengguna sistem</p>
  </div>
  <div>
    <button class="btn btn-primary" onclick="openTambahUser()"><i data-lucide="plus"></i> Tambah User</button>
  </div>
</div>

<!-- TABLE -->
<div class="card">
  <div class="card-header">
    <h3><i data-lucide="clipboard-list"></i> Daftar Pengguna</h3>
    <span class="badge badge-primary" id="total-users">0 user</span>
  </div>
  <div class="table-wrapper">
    <table class="data-table" id="tbl-users">
      <thead>
        <tr>
          <th>No</th>
          <th>Username</th>
          <th>Nama Lengkap</th>
          <th>Role</th>
          <th>Status</th>
          <th>Terakhir Login</th>
          <th>Aksi</th>
          <th>PTK</th>
        </tr>
      </thead>
      <tbody id="tbody-users">
        <tr><td colspan="8" class="text-center" style="padding:40px"><div class="spinner"></div></td></tr>
      </tbody>
    </table>
  </div>
</div>

<!-- MODAL TAMBAH/EDIT -->
<div class="modal-overlay" id="modal-user">
  <div class="modal">
    <div class="modal-header">
      <h4 id="modal-user-title"><i data-lucide="plus"></i> Tambah User</h4>
      <button class="modal-close" onclick="TU.modal.close('modal-user')"><i data-lucide="x"></i></button>
    </div>
    <div class="modal-body">
      <form id="form-user" onsubmit="simpanUser(event)">
        <input type="hidden" id="user-id" name="id">
        <div class="form-group">
          <label class="form-label">Username <span class="req">*</span></label>
          <input type="text" name="username" id="f-username" class="form-control" placeholder="Username untuk login" required>
        </div>
        <div class="form-group">
          <label class="form-label">Nama Lengkap <span class="req">*</span></label>
          <input type="text" name="nama" id="f-nama-lengkap" class="form-control" placeholder="Nama lengkap" required>
        </div>
        <div class="form-group" id="password-group">
          <label class="form-label">Password <span class="req">*</span></label>
          <input type="password" name="password" id="f-password" class="form-control" placeholder="Password (min 6 karakter)">
          <p class="form-hint">Kosongkan saat edit jika tidak ingin mengubah password.</p>
        </div>
        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label class="form-label">Role <span class="req">*</span></label>
              <select name="role" id="f-role" class="form-select" required>
                <option value="guru">Guru</option>
                <option value="admin">Admin</option>
              </select>
            </div>
          </div>
          <div class="form-col">
            <div class="form-group">
              <label class="form-label">Status</label>
              <select name="aktif" id="f-status-user" class="form-select">
                <option value="1">Aktif</option>
                <option value="0">Non Aktif</option>
              </select>
            </div>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Rombel (untuk Guru)</label>
          <select name="rombel" id="f-rombel-user" class="form-select">
            <option value="">Pilih Rombel</option>
            <?php for($k=1;$k<=6;$k++): foreach(['A','B','C','D','E','F'] as $huruf): ?>
            <option value="<?= $k ?><?= $huruf ?>"><?= $k ?><?= $huruf ?></option>
            <?php endforeach; endfor; ?>
          </select>
          <p class="form-hint">Tentukan rombel yang diampu (khusus role Guru)</p>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-light" onclick="TU.modal.close('modal-user')">Batal</button>
      <button type="submit" form="form-user" class="btn btn-primary" id="btn-simpan-user"><i data-lucide="save"></i> Simpan</button>
    </div>
  </div>
</div>

<!-- MODAL RESET PASSWORD -->
<div class="modal-overlay" id="modal-reset-pw">
  <div class="modal">
    <div class="modal-header">
      <h4><i data-lucide="key"></i> Reset Password</h4>
      <button class="modal-close" onclick="TU.modal.close('modal-reset-pw')"><i data-lucide="x"></i></button>
    </div>
    <div class="modal-body">
      <p style="margin-bottom:12px">Atur ulang password untuk <strong id="reset-pw-nama"></strong></p>
      <input type="hidden" id="reset-pw-id">
      <div class="form-group">
        <label class="form-label">Password Baru <span class="req">*</span></label>
        <input type="password" id="f-new-password" class="form-control" placeholder="Password baru (min 6 karakter)" required>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-light" onclick="TU.modal.close('modal-reset-pw')">Batal</button>
      <button class="btn btn-primary" onclick="resetPassword()"><i data-lucide="key"></i> Reset Password</button>
    </div>
  </div>
</div>

<?php
$extraJs = <<<'JS'
<script>
let allUsers = [];

// User API helper — uses api/user.php with server-side bcrypt hashing
async function userAPI(action, payload = {}) {
  payload.action = action;
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const res = await fetch('api/user.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': csrfToken },
    body: JSON.stringify(payload)
  });
  return res.json();
}

function getRoleBadge(role) {
  return role === 'admin' ? 'badge-danger' : 'badge-primary';
}

async function loadUsers() {
  const tbody = document.getElementById('tbody-users');
  tbody.innerHTML = '<tr><td colspan="8" class="text-center" style="padding:30px"><div class="spinner"></div></td></tr>';
  const r = await GS.getData('Users');
  allUsers = r.data || [];
  // Fetch DataPTK to merge ptk_id into user records
  const ptkResult = await GS.getData('DataPTK');
  const ptkRows = ptkResult.data || [];
  const ptkMap = {};
  ptkRows.forEach(p => { if (p.user_id) ptkMap[p.user_id] = p.id; });
  allUsers.forEach(u => { u.ptk_id = ptkMap[u.id] || ''; });
  document.getElementById('total-users').textContent = allUsers.length + ' user';
  renderUsersTable(allUsers);
}

function renderUsersTable(rows) {
  const tbody = document.getElementById('tbody-users');
  if (!rows.length) {
    tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><div class="es-icon"><i data-lucide=\"users\"></i></div><h4>Belum ada user</h4></div></td></tr>';
    return;
  }
  tbody.innerHTML = rows.map((u, i) => `
    <tr>
      <td>${i+1}</td>
      <td><code>${u.username||'-'}</code></td>
      <td style="font-weight:600">${u.nama||'-'}</td>
      <td><span class="badge ${getRoleBadge(u.role)}">${(u.role||'').toUpperCase()}</span></td>
      <td><span class="badge ${u.aktif==='1'?'badge-success':'badge-warning'}">${u.aktif==='1'?'Aktif':'Non Aktif'}</span></td>
      <td style="font-size:.78rem;color:var(--text-muted)">${u.last_login||'Belum pernah'}</td>
      <td>
        <div style="display:flex;gap:4px">
          <button class="btn btn-sm btn-info" onclick="detailUser('${u.id}')"><i data-lucide=\"eye\"></i></button>
          <button class="btn btn-sm btn-warning" onclick="editUser('${u.id}')"><i data-lucide=\"pencil\"></i></button>
          <button class="btn btn-sm btn-danger" onclick="hapusUser('${u.id}','${(u.username||'').replace(/'/g,"\\'")}')"><i data-lucide=\"trash-2\"></i></button>
          <button class="btn btn-sm btn-outline" onclick="resetPasswordModal('${u.id}','${(u.nama||'').replace(/'/g,"\\'")}')"><i data-lucide=\"key\"></i></button>
        </div>
      </td>
      <td>${u.ptk_id ? '<span class="badge badge-success"><i data-lucide=\"check-circle\"></i> Terhubung</span>' : '<span class="badge badge-warning"><i data-lucide=\"clock\"></i> Belum</span>'}</td>
    </tr>`).join('');
  lucide.createIcons();
}

function openTambahUser() {
  document.getElementById('user-id').value = '';
  document.getElementById('modal-user-title').innerHTML = '<i data-lucide="plus"></i> Tambah User';
  lucide.createIcons();
  document.getElementById('form-user').reset();
  document.getElementById('password-group').style.display = '';
  document.getElementById('f-password').required = true;
  TU.modal.open('modal-user');
}

function editUser(id) {
  const u = allUsers.find(x => x.id == id);
  if (!u) return;
  document.getElementById('user-id').value = u.id;
  document.getElementById('modal-user-title').innerHTML = '<i data-lucide="pencil"></i> Edit User';
  document.getElementById('f-username').value = u.username || '';
  document.getElementById('f-nama-lengkap').value = u.nama || '';
  document.getElementById('f-role').value = u.role || 'guru';
  document.getElementById('f-status-user').value = u.aktif || '1';
  document.getElementById('f-rombel-user').value = u.rombel || '';
  // Password optional on edit
  document.getElementById('password-group').style.display = '';
  document.getElementById('f-password').required = false;
  document.getElementById('f-password').value = '';
  TU.modal.open('modal-user');
}

function detailUser(id) {
  const u = allUsers.find(x => x.id == id);
  if (!u) return;
  editUser(id);
  document.getElementById('modal-user-title').innerHTML = '<i data-lucide="eye"></i> Detail User';
  document.querySelectorAll('#form-user input, #form-user select').forEach(el => el.disabled = true);
  document.getElementById('password-group').style.display = 'none';
  const footer = document.querySelector('#modal-user .modal-footer');
  footer.innerHTML = '<button type="button" class="btn btn-outline" onclick="TU.modal.close(\'modal-user\'); document.querySelectorAll(\'#form-user input, #form-user select\').forEach(el => el.disabled = false);"><i data-lucide=\"x\"></i> Tutup</button>';
}

async function simpanUser(e) {
  e.preventDefault();
  const btn = document.getElementById('btn-simpan-user');
  TU.btnLoading(btn, true);

  const id = document.getElementById('user-id').value;
  const formData = new FormData(document.getElementById('form-user'));
  const data = Object.fromEntries(formData.entries());
  delete data.id;

  // Remove empty password on edit
  if (id && !data.password) delete data.password;

  let r;
  if (id) {
    // Update: use api/user.php with server-side bcrypt
    r = await userAPI('updateUser', { id, data });
  } else {
    // Add: use api/user.php with server-side bcrypt
    if (!data.password) {
      TU.toast('Password wajib diisi untuk user baru', 'error');
      TU.btnLoading(btn, false);
      return;
    }
    r = await userAPI('addUser', { data });
  }

  TU.btnLoading(btn, false);
  if (r.success) {
    TU.toast(id ? 'User diperbarui' : 'User berhasil ditambahkan', 'success');
    TU.modal.close('modal-user');
    loadUsers();
  } else TU.toast(r.error || 'Gagal menyimpan', 'error');
}

function hapusUser(id, username) {
  if (username === 'admin') {
    TU.toast('Tidak bisa menghapus akun admin utama!', 'error');
    return;
  }
  TU.confirm(`Hapus user <strong>${username}</strong>?`, async () => {
    const r = await userAPI('deleteUser', { id });
    if (r.success) { TU.toast('User dihapus', 'success'); loadUsers(); }
    else TU.toast(r.error || 'Gagal', 'error');
  });
}

function resetPasswordModal(id, nama) {
  document.getElementById('reset-pw-id').value = id;
  document.getElementById('reset-pw-nama').textContent = nama;
  document.getElementById('f-new-password').value = '';
  TU.modal.open('modal-reset-pw');
}

async function resetPassword() {
  const id = document.getElementById('reset-pw-id').value;
  const pw = document.getElementById('f-new-password').value;
  if (!pw || pw.length < 6) {
    TU.toast('Password minimal 6 karakter', 'warning');
    return;
  }
  const r = await userAPI('resetPassword', { id, password: pw });
  if (r.success) {
    TU.toast('Password berhasil direset', 'success');
    TU.modal.close('modal-reset-pw');
  } else TU.toast(r.error || 'Gagal', 'error');
}

document.addEventListener('DOMContentLoaded', () => { loadUsers(); });
</script>
JS;
?>

<?php include 'includes/footer.php'; ?>
