<?php
require_once 'config.php';
requireLogin();
$pageTitle    = 'Nilai Siswa';
$pageSubtitle = 'Pengolahan & Rekapitulasi Nilai';
include 'includes/header.php';
?>

<div class="section-header">
  <div>
    <h2><span class="sh-icon"><i data-lucide="clipboard"></i></span> Nilai Siswa</h2>
    <p>Kelola nilai ujian dan tugas siswa</p>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap">
    <button class="btn btn-light btn-sm" onclick="exportNilai()"><i data-lucide="download"></i> Export CSV</button>
    <button class="btn btn-primary" onclick="openTambahNilai()"><i data-lucide="plus"></i> Input Nilai</button>
  </div>
</div>

<!-- FILTER -->
<div class="card" style="margin-bottom:16px">
  <div class="card-body" style="padding:14px 18px">
    <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
      <div style="min-width:140px">
        <label class="form-label"><i data-lucide="school"></i> Rombel</label>
        <select class="form-select" id="filter-rombel-nilai" onchange="loadNilai()">
          <option value="">Semua Kelas</option>
        </select>
      </div>
      <div style="min-width:150px">
        <label class="form-label"><i data-lucide="book-open"></i> Mata Pelajaran</label>
        <input type="text" id="filter-mapel-nilai" class="form-control" placeholder="Cari mapel..." oninput="loadNilai()">
      </div>
      <div style="min-width:130px">
        <label class="form-label"><i data-lucide="clipboard-list"></i> Tipe Ujian</label>
        <select class="form-select" id="filter-tipe-nilai" onchange="loadNilai()">
          <option value="">Semua</option>
          <option>UTS</option><option>UAS</option><option>ULangan</option>
          <option>Tugas</option><option>Praktik</option><option>Responsi</option>
        </select>
      </div>
      <button class="btn btn-outline-primary" onclick="loadNilai()"><i data-lucide="refresh-cw"></i> Refresh</button>
    </div>
  </div>
</div>

<!-- TABLE -->
<div class="card">
  <div class="card-header">
    <h3><i data-lucide="clipboard-list"></i> Daftar Nilai</h3>
    <span class="badge badge-primary" id="total-nilai">0 data</span>
  </div>
  <div class="table-wrapper">
    <table class="data-table" id="tbl-nilai">
      <thead>
        <tr>
          <th>No</th>
          <th>Nama Siswa</th>
          <th>Rombel</th>
          <th>Mata Pelajaran</th>
          <th>Tipe Ujian</th>
          <th>Nilai</th>
          <th>Keterangan</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody id="tbody-nilai">
        <tr><td colspan="8" class="text-center" style="padding:40px"><div class="spinner"></div></td></tr>
      </tbody>
    </table>
  </div>
</div>

<!-- MODAL TAMBAH/EDIT -->
<div class="modal-overlay" id="modal-nilai">
  <div class="modal">
    <div class="modal-header">
      <h4 id="modal-nilai-title"><i data-lucide="plus"></i> Input Nilai</h4>
      <button class="modal-close" onclick="TU.modal.close('modal-nilai')">✕</button>
    </div>
    <div class="modal-body">
      <form id="form-nilai" onsubmit="simpanNilai(event)">
        <input type="hidden" id="nilai-id" name="id">
        <div class="form-row">
          <div class="form-col" style="flex:2">
            <div class="form-group">
              <label class="form-label">Nama Siswa <span class="req">*</span></label>
              <select name="nama_siswa" id="f-nama-siswa-nilai" class="form-select" required>
                <option value="">Pilih Siswa</option>
              </select>
            </div>
          </div>
          <div class="form-col">
            <div class="form-group">
              <label class="form-label">Rombel</label>
              <input type="text" id="f-rombel-nilai-auto" class="form-control" readonly placeholder="Otomatis">
            </div>
          </div>
        </div>
        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label class="form-label">Mata Pelajaran <span class="req">*</span></label>
              <input type="text" name="mapel" id="f-mapel-nilai" class="form-control" placeholder="Matematika, IPA, dll" required>
            </div>
          </div>
          <div class="form-col">
            <div class="form-group">
              <label class="form-label">Tipe Ujian <span class="req">*</span></label>
              <select name="jenis" id="f-tipe-nilai" class="form-select" required>
                <option value="">Pilih</option>
                <option>UTS</option><option>UAS</option><option>ULangan</option>
                <option>Tugas</option><option>Praktik</option><option>Responsi</option>
              </select>
            </div>
          </div>
        </div>
        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label class="form-label">Nilai <span class="req">*</span></label>
              <input type="number" name="nilai" id="f-nilai-angka" class="form-control" min="0" max="100" step="0.01" required>
            </div>
          </div>
          <div class="form-col">
            <div class="form-group">
              <label class="form-label">Tahun Ajaran</label>
              <input type="text" name="tahun_ajaran" id="f-tahun-ajaran" class="form-control" placeholder="2024/2025">
            </div>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Keterangan</label>
          <textarea name="keterangan" id="f-keterangan-nilai" class="form-control" rows="2" placeholder="Catatan tentang nilai"></textarea>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-light" onclick="TU.modal.close('modal-nilai')">Batal</button>
      <button type="submit" form="form-nilai" class="btn btn-primary" id="btn-simpan-nilai"><i data-lucide="save"></i> Simpan Nilai</button>
    </div>
  </div>
</div>

<?php
$extraJs = <<<'JS'
<script>
let allNilai = [];
let allSiswaCache = [];

function getNilaiBadge(val) {
  const n = parseFloat(val);
  if (isNaN(n)) return 'badge-info';
  if (n >= 90) return 'badge-success';
  if (n >= 75) return 'badge-primary';
  if (n >= 60) return 'badge-warning';
  return 'badge-danger';
}

async function loadSiswaForNilai() {
  const r = await GS.getData('DataSiswa');
  allSiswaCache = r.data || [];
  const sel = document.getElementById('f-nama-siswa-nilai');
  const cur = sel.value;
  while (sel.options.length > 1) sel.remove(1);
  allSiswaCache.forEach(s => {
    sel.add(new Option(`${s.nama} (${s.rombel||'?'})`, s.nama));
  });
  sel.value = cur;
}

async function loadRombelNilai() {
  const r = await GS.getData('Rombel');
  const data = r.data || [];
  const sel = document.getElementById('filter-rombel-nilai');
  const cur = sel.value;
  while (sel.options.length > 1) sel.remove(1);
  data.forEach(row => sel.add(new Option(row.nama_rombel, row.nama_rombel)));
  sel.value = cur;
}

async function loadNilai() {
  const tbody = document.getElementById('tbody-nilai');
  tbody.innerHTML = '<tr><td colspan="8" class="text-center" style="padding:30px"><div class="spinner"></div></td></tr>';
  const filters = {
    rombel: document.getElementById('filter-rombel-nilai').value,
    jenis: document.getElementById('filter-tipe-nilai').value,
  };
  const r = await GS.getData('DaftarNilai', filters);
  allNilai = r.data || [];

  // Client-side filter for mapel (text search)
  const q = document.getElementById('filter-mapel-nilai').value.toLowerCase();
  let filtered = allNilai;
  if (q) filtered = allNilai.filter(n => (n.mapel||'').toLowerCase().includes(q) || (n.nama_siswa||'').toLowerCase().includes(q));

  document.getElementById('total-nilai').textContent = filtered.length + ' data';
  renderNilaiTable(filtered);
}

function renderNilaiTable(rows) {
  const tbody = document.getElementById('tbody-nilai');
  if (!rows.length) {
    tbody.innerHTML = '<tr><td colspan="8"><div class="empty-state"><div class="es-icon"><i data-lucide=\"clipboard\"></i></div><h4>Belum ada data nilai</h4><p>Input nilai siswa dengan tombol di atas</p></div></td></tr>';
    return;
  }
  tbody.innerHTML = rows.map((n, i) => `
    <tr>
      <td>${i+1}</td>
      <td style="font-weight:600">${n.nama||'-'}</td>
      <td><span class="badge badge-info">${n.rombel||'-'}</span></td>
      <td>${n.mapel||'-'}</td>
      <td><span class="badge badge-teal">${n.jenis||'-'}</span></td>
      <td><span class="badge ${getNilaiBadge(n.nilai)}" style="font-size:.9rem;font-weight:700">${n.nilai||'-'}</span></td>
      <td style="font-size:.78rem;color:var(--text-muted)">${n.keterangan||'-'}</td>
      <td>
        <div style="display:flex;gap:4px">
          <button class="btn btn-sm btn-info" onclick="detailNilai('${n.id}')"><i data-lucide="eye"></i></button>
          <button class="btn btn-sm btn-warning" onclick="editNilai('${n.id}')"><i data-lucide="pencil"></i></button>
          <button class="btn btn-sm btn-danger" onclick="hapusNilai('${n.id}','${n.nama}')"><i data-lucide="trash-2"></i></button>
        </div>
      </td>
    </tr>`).join('');
}

// Auto-fill rombel when selecting siswa
document.getElementById('f-nama-siswa-nilai').addEventListener('change', function() {
  const s = allSiswaCache.find(x => x.nama === this.value);
  document.getElementById('f-rombel-nilai-auto').value = s ? (s.rombel||'') : '';
});

function detailNilai(id) {
  const n = allNilai.find(x => x.id == id);
  if (!n) return;
  editNilai(id);
  document.getElementById('modal-nilai-title').innerHTML = '<i data-lucide="eye"></i> Detail Nilai';
  lucide.createIcons();
  document.querySelectorAll('#form-nilai input, #form-nilai select, #form-nilai textarea').forEach(el => el.disabled = true);
  const footer = document.querySelector('#modal-nilai .modal-footer');
  footer.innerHTML = '<button type="button" class="btn btn-outline" onclick="TU.modal.close(\'modal-nilai\'); document.querySelectorAll(\'#form-nilai input, #form-nilai select, #form-nilai textarea\').forEach(el => el.disabled = false);"><i data-lucide="x"></i> Tutup</button>';
  lucide.createIcons();
}

function openTambahNilai() {
  document.getElementById('nilai-id').value = '';
  document.getElementById('modal-nilai-title').innerHTML = '<i data-lucide="plus"></i> Input Nilai';
  lucide.createIcons();
  document.getElementById('form-nilai').reset();
  document.getElementById('f-rombel-nilai-auto').value = '';
  // Set default tahun ajaran
  const year = new Date().getFullYear();
  document.getElementById('f-tahun-ajaran').value = `${year}/${year+1}`;
  TU.modal.open('modal-nilai');
}

function editNilai(id) {
  const n = allNilai.find(x => x.id == id);
  if (!n) return;
  document.getElementById('nilai-id').value = n.id;
  document.getElementById('modal-nilai-title').innerHTML = '<i data-lucide="pencil"></i> Edit Nilai';
  lucide.createIcons();
  document.getElementById('f-nama-siswa-nilai').value = n.nama || '';
  document.getElementById('f-rombel-nilai-auto').value = n.rombel || '';
  ['mapel','jenis','nilai','semester','tahun_ajaran','keterangan'].forEach(f => {
    const el = document.querySelector(`[name="${f}"]`);
    if (el) el.value = n[f] || '';
  });
  TU.modal.open('modal-nilai');
}

async function simpanNilai(e) {
  e.preventDefault();
  const btn = document.getElementById('btn-simpan-nilai');
  TU.btnLoading(btn, true);
  const formData = new FormData(document.getElementById('form-nilai'));
  const data = Object.fromEntries(formData.entries());
  data.rombel = document.getElementById('f-rombel-nilai-auto').value;
  const id = data.id; delete data.id;
  let r;
  if (id) { r = await GS.updateRow('DaftarNilai', id, data); }
  else { data.id = Date.now().toString(); r = await GS.addRow('DaftarNilai', data); }
  TU.btnLoading(btn, false);
  if (r.success) {
    TU.toast(id ? 'Nilai diperbarui' : 'Nilai berhasil diinput', 'success');
    TU.modal.close('modal-nilai');
    loadNilai();
  } else TU.toast(r.error || 'Gagal menyimpan', 'error');
}

function hapusNilai(id, nama) {
  TU.confirm(`Hapus nilai <strong>${nama}</strong>?`, async () => {
    const r = await GS.deleteRow('DaftarNilai', id);
    if (r.success) { TU.toast('Nilai dihapus', 'success'); loadNilai(); }
    else TU.toast(r.error || 'Gagal', 'error');
  });
}

function exportNilai() {
  TU.exportCSV(allNilai, 'nilai_siswa_'+new Date().toISOString().slice(0,10)+'.csv');
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
  fetch('api/log.php', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken }, body: JSON.stringify({ action: 'export', category: 'data', description: 'Export data Nilai Siswa ke CSV' }) });
}

document.addEventListener('DOMContentLoaded', () => { loadRombelNilai(); loadSiswaForNilai(); loadNilai(); });
</script>
JS;
?>

<?php include 'includes/footer.php'; ?>
