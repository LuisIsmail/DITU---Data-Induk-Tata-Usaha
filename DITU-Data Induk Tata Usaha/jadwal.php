<?php
require_once 'config.php';
requireLogin();
$pageTitle    = 'Jadwal Pelajaran';
$pageSubtitle = 'Jadwal Mengajar & Pelajaran';
include 'includes/header.php';
?>

<div class="section-header">
  <div>
    <h2><span class="sh-icon"><i data-lucide="calendar-days"></i></span> Jadwal Pelajaran</h2>
    <p>Kelola jadwal mengajar per hari dan rombel</p>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap">
    <button class="btn btn-light btn-sm" onclick="exportJadwal()"><i data-lucide="download"></i> Export CSV</button>
    <button class="btn btn-primary" onclick="openTambahJadwal()"><i data-lucide="plus"></i> Tambah Jadwal</button>
  </div>
</div>

<!-- FILTER -->
<div class="card" style="margin-bottom:16px">
  <div class="card-body" style="padding:14px 18px">
    <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
      <div style="min-width:150px">
        <label class="form-label"><i data-lucide="calendar"></i> Hari</label>
        <select class="form-select" id="filter-hari" onchange="loadJadwal()">
          <option value="">Semua Hari</option>
          <option>Senin</option><option>Selasa</option><option>Rabu</option>
          <option>Kamis</option><option>Jumat</option><option>Sabtu</option>
        </select>
      </div>
      <div style="min-width:140px">
        <label class="form-label"><i data-lucide="school"></i> Rombel</label>
        <select class="form-select" id="filter-rombel-jadwal" onchange="loadJadwal()">
          <option value="">Semua Kelas</option>
        </select>
      </div>
      <button class="btn btn-outline-primary" onclick="loadJadwal()"><i data-lucide="refresh-cw"></i> Refresh</button>
    </div>
  </div>
</div>

<!-- TABLE -->
<div class="card">
  <div class="card-header">
    <h3><i data-lucide="clipboard-list"></i> Daftar Jadwal</h3>
    <span class="badge badge-primary" id="total-jadwal">0 jadwal</span>
  </div>
  <div class="table-wrapper">
    <table class="data-table" id="tbl-jadwal">
      <thead>
        <tr>
          <th>No</th>
          <th>Hari</th>
          <th>Jam</th>
          <th>Mata Pelajaran</th>
          <th>Rombel</th>
          <th>Guru</th>
          <th>Ruang</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody id="tbody-jadwal">
        <tr><td colspan="8" class="text-center" style="padding:40px"><div class="spinner"></div></td></tr>
      </tbody>
    </table>
  </div>
</div>

<!-- MODAL TAMBAH/EDIT -->
<div class="modal-overlay" id="modal-jadwal">
  <div class="modal">
    <div class="modal-header">
      <h4 id="modal-jadwal-title"><i data-lucide="plus"></i> Tambah Jadwal</h4>
      <button class="modal-close" onclick="TU.modal.close('modal-jadwal')">✕</button>
    </div>
    <div class="modal-body">
      <form id="form-jadwal" onsubmit="simpanJadwal(event)">
        <input type="hidden" id="jadwal-id" name="id">
        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label class="form-label">Hari <span class="req">*</span></label>
              <select name="hari" id="f-hari" class="form-select" required>
                <option value="">Pilih Hari</option>
                <option>Senin</option><option>Selasa</option><option>Rabu</option>
                <option>Kamis</option><option>Jumat</option><option>Sabtu</option>
              </select>
            </div>
          </div>
          <div class="form-col">
            <div class="form-group">
              <label class="form-label">Rombel <span class="req">*</span></label>
              <select name="rombel" id="f-rombel-jadwal" class="form-select" required>
                <option value="">Pilih Rombel</option>
              </select>
            </div>
          </div>
        </div>
        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label class="form-label">Jam Mulai <span class="req">*</span></label>
              <input type="time" name="jam_mulai" id="f-jam-mulai" class="form-control" required>
            </div>
          </div>
          <div class="form-col">
            <div class="form-group">
              <label class="form-label">Jam Selesai <span class="req">*</span></label>
              <input type="time" name="jam_selesai" id="f-jam-selesai" class="form-control" required>
            </div>
          </div>
        </div>
        <div class="form-row">
          <div class="form-col" style="flex:2">
            <div class="form-group">
              <label class="form-label">Mata Pelajaran <span class="req">*</span></label>
              <input type="text" name="mapel" id="f-mapel" class="form-control" placeholder="Matematika, IPA, dll" required>
            </div>
          </div>
          <div class="form-col">
            <div class="form-group">
              <label class="form-label">Ruang</label>
              <input type="text" name="ruang" id="f-ruang" class="form-control" placeholder="R.01">
            </div>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Guru Pengampu <span class="req">*</span></label>
          <input type="text" name="guru" id="f-guru" class="form-control" placeholder="Nama Guru" required>
        </div>
        <div class="form-group">
          <label class="form-label">Keterangan</label>
          <textarea name="keterangan" id="f-keterangan" class="form-control" rows="2" placeholder="Catatan tambahan"></textarea>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-light" onclick="TU.modal.close('modal-jadwal')">Batal</button>
      <button type="submit" form="form-jadwal" class="btn btn-primary" id="btn-simpan-jadwal"><i data-lucide="save"></i> Simpan</button>
    </div>
  </div>
</div>

<?php
$extraJs = <<<'JS'
<script>
let allJadwal = [];
const hariOrder = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];

async function loadRombelJadwal() {
  const r = await GS.getData('Rombel');
  const data = r.data || [];
  ['#filter-rombel-jadwal','#f-rombel-jadwal'].forEach(sel => {
    const s = document.querySelector(sel);
    const cur = s.value;
    while (s.options.length > 1) s.remove(1);
    data.forEach(row => s.add(new Option(row.nama_rombel, row.nama_rombel)));
    s.value = cur;
  });
}

async function loadJadwal() {
  const tbody = document.getElementById('tbody-jadwal');
  tbody.innerHTML = '<tr><td colspan="8" class="text-center" style="padding:30px"><div class="spinner"></div></td></tr>';
  const filters = {
    hari: document.getElementById('filter-hari').value,
    rombel: document.getElementById('filter-rombel-jadwal').value,
  };
  const r = await GS.getData('JadwalPelajaran', filters);
  allJadwal = r.data || [];
  allJadwal.sort((a, b) => {
    const ha = hariOrder.indexOf(a.hari), hb = hariOrder.indexOf(b.hari);
    if (ha !== hb) return ha - hb;
    return (a.jam_mulai||'').localeCompare(b.jam_mulai||'');
  });
  document.getElementById('total-jadwal').textContent = allJadwal.length + ' jadwal';
  renderJadwalTable(allJadwal);
}

function renderJadwalTable(rows) {
  const tbody = document.getElementById('tbody-jadwal');
  if (!rows.length) {
    tbody.innerHTML = '<tr><td colspan="8"><div class="empty-state"><div class="es-icon"><i data-lucide=\"calendar-days\"></i></div><h4>Belum ada jadwal</h4><p>Tambah jadwal pelajaran baru</p></div></td></tr>';
    return;
  }
  tbody.innerHTML = rows.map((j, i) => `
    <tr>
      <td>${i+1}</td>
      <td><span class="badge badge-primary">${j.hari||'-'}</span></td>
      <td>${j.jam_mulai||'?'} - ${j.jam_selesai||'?'}</td>
      <td style="font-weight:600">${j.mapel||'-'}</td>
      <td><span class="badge badge-info">${j.rombel||'-'}</span></td>
      <td style="font-size:.8rem">${j.guru||'-'}</td>
      <td>${j.ruang||'-'}</td>
      <td>
        <div style="display:flex;gap:4px">
          <button class="btn btn-sm btn-info" onclick="detailJadwal('${j.id}')"><i data-lucide="eye"></i></button>
          <button class="btn btn-sm btn-warning" onclick="editJadwal('${j.id}')"><i data-lucide="pencil"></i></button>
          <button class="btn btn-sm btn-danger" onclick="hapusJadwal('${j.id}','${j.mapel}')"><i data-lucide="trash-2"></i></button>
        </div>
      </td>
    </tr>`).join('');
}

function openTambahJadwal() {
  document.getElementById('jadwal-id').value = '';
  document.getElementById('modal-jadwal-title').innerHTML = '<i data-lucide="plus"></i> Tambah Jadwal';
  lucide.createIcons();
  document.getElementById('form-jadwal').reset();
  TU.modal.open('modal-jadwal');
}

function editJadwal(id) {
  const j = allJadwal.find(x => x.id == id);
  if (!j) return;
  document.getElementById('jadwal-id').value = j.id;
  document.getElementById('modal-jadwal-title').innerHTML = '<i data-lucide="pencil"></i> Edit Jadwal';
  lucide.createIcons();
  ['hari','jam_mulai','jam_selesai','mapel','rombel','guru','ruang','keterangan'].forEach(f => {
    const el = document.querySelector(`[name="${f}"]`);
    if (el) el.value = j[f] || '';
  });
  TU.modal.open('modal-jadwal');
}

function detailJadwal(id) {
  const j = allJadwal.find(x => x.id == id);
  if (!j) return;
  editJadwal(id);
  document.getElementById('modal-jadwal-title').innerHTML = '<i data-lucide="eye"></i> Detail Jadwal';
  lucide.createIcons();
  document.querySelectorAll('#form-jadwal input, #form-jadwal select').forEach(el => el.disabled = true);
  const footer = document.querySelector('#modal-jadwal .modal-footer');
  footer.innerHTML = '<button type="button" class="btn btn-outline" onclick="TU.modal.close(\'modal-jadwal\'); document.querySelectorAll(\'#form-jadwal input, #form-jadwal select\').forEach(el => el.disabled = false);"><i data-lucide="x"></i> Tutup</button>';
  lucide.createIcons();
}

async function simpanJadwal(e) {
  e.preventDefault();
  const btn = document.getElementById('btn-simpan-jadwal');
  TU.btnLoading(btn, true);
  const formData = new FormData(document.getElementById('form-jadwal'));
  const data = Object.fromEntries(formData.entries());
  const id = data.id; delete data.id;
  let r;
  if (id) { r = await GS.updateRow('JadwalPelajaran', id, data); }
  else { data.id = Date.now().toString(); r = await GS.addRow('JadwalPelajaran', data); }
  TU.btnLoading(btn, false);
  if (r.success) {
    TU.toast(id ? 'Jadwal diperbarui' : 'Jadwal berhasil ditambahkan', 'success');
    TU.modal.close('modal-jadwal');
    loadJadwal();
  } else TU.toast(r.error || 'Gagal menyimpan', 'error');
}

function hapusJadwal(id, mapel) {
  TU.confirm(`Hapus jadwal <strong>${mapel}</strong>?`, async () => {
    const r = await GS.deleteRow('JadwalPelajaran', id);
    if (r.success) { TU.toast('Jadwal dihapus', 'success'); loadJadwal(); }
    else TU.toast(r.error || 'Gagal', 'error');
  });
}

function exportJadwal() {
  TU.exportCSV(allJadwal, 'jadwal_pelajaran_'+new Date().toISOString().slice(0,10)+'.csv');
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
  fetch('api/log.php', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken }, body: JSON.stringify({ action: 'export', category: 'data', description: 'Export data Jadwal Pelajaran ke CSV' }) });
}

document.addEventListener('DOMContentLoaded', () => { loadRombelJadwal(); loadJadwal(); });
</script>
JS;
?>

<?php include 'includes/footer.php'; ?>
