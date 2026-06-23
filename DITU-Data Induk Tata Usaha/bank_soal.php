<?php
require_once 'config.php';
requireLogin();
$pageTitle    = 'Bank Soal';
$pageSubtitle = 'Kelola Bank Soal & Buat Paket Ujian';
$user = currentUser();
include 'includes/header.php';
?>

<div class="section-header">
  <div>
    <h2><span class="sh-icon"><i data-lucide="book-open"></i></span> Bank Soal</h2>
    <p>Kelola bank soal untuk semua mata pelajaran</p>
  </div>
  <div style="display:flex;gap:8px">
    <button class="btn btn-light btn-sm" onclick="exportSoal()"><i data-lucide="download"></i> Export</button>
    <button class="btn btn-primary" onclick="openTambahSoal()"><i data-lucide="plus"></i> Tambah Soal</button>
  </div>
</div>

<!-- FILTER -->
<div class="card" style="margin-bottom:16px">
  <div class="card-body" style="padding:14px 18px">
    <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
      <div style="flex:1;min-width:180px">
        <label class="form-label"><i data-lucide="search"></i> Cari Soal</label>
        <div class="search-input-wrap">
          <span class="si-icon"><i data-lucide="search"></i></span>
          <input type="text" id="search-soal" class="form-control" placeholder="Kata kunci pertanyaan...">
        </div>
      </div>
      <div style="min-width:140px">
        <label class="form-label">Mata Pelajaran</label>
        <select class="form-select" id="filter-mapel" onchange="loadSoal()">
          <option value="">Semua Mapel</option>
        </select>
      </div>
      <div style="min-width:120px">
        <label class="form-label">Jenjang/Kelas</label>
        <select class="form-select" id="filter-kelas-soal" onchange="loadSoal();filterMapelByKelas()">
          <option value="">Semua</option>
          <option value="1">Kelas 1 (Fase A)</option>
          <option value="2">Kelas 2 (Fase A)</option>
          <option value="3">Kelas 3 (Fase B)</option>
          <option value="4">Kelas 4 (Fase B)</option>
          <option value="5">Kelas 5 (Fase C)</option>
          <option value="6">Kelas 6 (Fase C)</option>
        </select>
      </div>
      <div style="min-width:120px">
        <label class="form-label">Tipe Soal</label>
        <select class="form-select" id="filter-tipe" onchange="loadSoal()">
          <option value="">Semua</option>
          <option value="pg">Pilihan Ganda</option>
          <option value="isian">Isian Singkat</option>
          <option value="uraian">Uraian</option>
        </select>
      </div>
      <button class="btn btn-outline-primary" onclick="loadSoal()"><i data-lucide="refresh-cw"></i> Refresh</button>
    </div>
  </div>
</div>

<!-- SOAL LIST -->
<div id="soal-container">
  <div class="text-center" style="padding:40px">
    <div class="spinner"></div>
    <p class="text-muted mt-2">Memuat bank soal...</p>
  </div>
</div>
<div class="pagination" id="pagination-soal" style="margin-top:16px"></div>

<!-- ============ MODAL TAMBAH SOAL ============ -->
<div class="modal-overlay" id="modal-tambah-soal">
  <div class="modal modal-lg">
    <div class="modal-header">
      <h4 id="modal-soal-title"><i data-lucide="plus"></i> Tambah Soal ke Bank Soal</h4>
      <button class="modal-close" onclick="TU.modal.close('modal-tambah-soal')">✕</button>
    </div>
    <div class="modal-body">
      <form id="form-soal" onsubmit="simpanSoal(event)">
        <input type="hidden" id="soal-id" name="id">
        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label class="form-label">Mata Pelajaran <span class="req">*</span></label>
              <select name="mapel" id="soal-mapel" class="form-select" required>
                <option value="">Pilih Mapel</option>
              </select>
            </div>
          </div>
          <div class="form-col">
            <div class="form-group">
              <label class="form-label">Kelas/Jenjang <span class="req">*</span></label>
              <select name="kelas" id="soal-kelas" class="form-select" required onchange="filterMapelByKelas()">
                <option value="">Pilih Kelas</option>
                <option value="1">Kelas 1 (Fase A)</option>
                <option value="2">Kelas 2 (Fase A)</option>
                <option value="3">Kelas 3 (Fase B)</option>
                <option value="4">Kelas 4 (Fase B)</option>
                <option value="5">Kelas 5 (Fase C)</option>
                <option value="6">Kelas 6 (Fase C)</option>
              </select>
            </div>
          </div>
          <div class="form-col">
            <div class="form-group">
              <label class="form-label">Tipe Soal <span class="req">*</span></label>
              <select name="tipe" id="soal-tipe" class="form-select" required onchange="toggleTipeForm(this.value)">
                <option value="">Pilih Tipe</option>
                <option value="pg">Pilihan Ganda</option>
                <option value="isian">Isian Singkat</option>
                <option value="uraian">Uraian</option>
              </select>
            </div>
          </div>
        </div>
        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label class="form-label">Semester</label>
              <select name="semester" class="form-select">
                <option value="1">Semester 1 (Ganjil)</option>
                <option value="2">Semester 2 (Genap)</option>
              </select>
            </div>
          </div>
          <div class="form-col">
            <div class="form-group">
              <label class="form-label">Tahun Pelajaran</label>
              <input type="text" name="tahun_ajaran" class="form-control" placeholder="2024/2025">
            </div>
          </div>
          <div class="form-col">
            <div class="form-group">
              <label class="form-label">Tingkat Kesulitan</label>
              <select name="kesulitan" class="form-select">
                <option value="Mudah">Mudah</option>
                <option value="Sedang" selected>Sedang</option>
                <option value="Sulit">Sulit</option>
              </select>
            </div>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Pertanyaan / Soal <span class="req">*</span></label>
          <textarea name="pertanyaan" id="soal-pertanyaan" class="form-control" rows="4"
                    placeholder="Tulis pertanyaan / soal di sini..." required></textarea>
        </div>

        <!-- PILIHAN GANDA -->
        <div id="form-pg" style="display:none">
          <label class="form-label">Pilihan Jawaban <span class="req">*</span></label>
          <div id="pilihan-container">
            <?php foreach (['A','B','C','D'] as $opt): ?>
            <div class="pilihan-input-row" style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
              <div style="width:30px;height:30px;background:var(--primary);color:#fff;border-radius:50%;
                          display:flex;align-items:center;justify-content:center;font-weight:700;
                          font-size:.8rem;flex-shrink:0"><?= $opt ?></div>
              <input type="text" name="pilihan_<?= strtolower($opt) ?>" class="form-control" placeholder="Pilihan <?= $opt ?>">
              <label style="display:flex;align-items:center;gap:5px;white-space:nowrap;font-size:.8rem;cursor:pointer">
                <input type="radio" name="jawaban_benar" value="<?= strtolower($opt) ?>" style="cursor:pointer">
                Benar
              </label>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- ISIAN/URAIAN -->
        <div id="form-isian">
          <div class="form-group">
            <label class="form-label">Kunci Jawaban <span class="req">*</span></label>
            <textarea name="kunci_jawaban" id="soal-kunci" class="form-control" rows="3"
                      placeholder="Tulis kunci jawaban di sini..."></textarea>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Pembahasan (Opsional)</label>
          <textarea name="pembahasan" class="form-control" rows="2"
                    placeholder="Penjelasan/pembahasan jawaban..."></textarea>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-light" onclick="TU.modal.close('modal-tambah-soal')">Batal</button>
      <button type="submit" form="form-soal" class="btn btn-primary" id="btn-simpan-soal"><i data-lucide="save"></i> Simpan Soal</button>
    </div>
  </div>
</div>

<script>
const _userName = '<?= $user['nama'] ?>';
</script>
<?php
$extraJs = <<<'JS'
<script>
let allSoal = [];
let soalPage = 1;
const soalPerPage = 10;

// === Kurikulum Merdeka - Mapel per Fase ===
const MAPEL_BY_KELAS = {
  1: ['Pendidikan Agama dan Budi Pekerti','Pendidikan Pancasila','Bahasa Indonesia','Matematika','Seni Budaya','PJOK','Muatan Lokal'],
  2: ['Pendidikan Agama dan Budi Pekerti','Pendidikan Pancasila','Bahasa Indonesia','Matematika','Seni Budaya','PJOK','Muatan Lokal'],
  3: ['Pendidikan Agama dan Budi Pekerti','Pendidikan Pancasila','Bahasa Indonesia','Matematika','IPAS','Seni Budaya','PJOK','Bahasa Inggris','Muatan Lokal'],
  4: ['Pendidikan Agama dan Budi Pekerti','Pendidikan Pancasila','Bahasa Indonesia','Matematika','IPAS','Seni Budaya','PJOK','Bahasa Inggris','Muatan Lokal'],
  5: ['Pendidikan Agama dan Budi Pekerti','Pendidikan Pancasila','Bahasa Indonesia','Matematika','IPAS','Seni Budaya','PJOK','Bahasa Inggris','Muatan Lokal'],
  6: ['Pendidikan Agama dan Budi Pekerti','Pendidikan Pancasila','Bahasa Indonesia','Matematika','IPAS','Seni Budaya','PJOK','Bahasa Inggris','Muatan Lokal'],
};
const ALL_MAPEL = [...new Set(Object.values(MAPEL_BY_KELAS).flat())].sort();

function populateMapelSelect(selectId, kelasVal) {
  const sel = document.getElementById(selectId);
  if (!sel) return;
  const prev = sel.value;
  const mapels = kelasVal ? (MAPEL_BY_KELAS[kelasVal] || ALL_MAPEL) : ALL_MAPEL;
  sel.innerHTML = '<option value="">Semua Mapel</option>' + mapels.map(m => '<option>' + m + '</option>').join('');
  if (mapels.includes(prev)) sel.value = prev;
}

function filterMapelByKelas() {
  const kelasFilter = document.getElementById('filter-kelas-soal')?.value || '';
  const kelasModal = document.getElementById('soal-kelas')?.value || '';
  populateMapelSelect('filter-mapel', kelasFilter);
  populateMapelSelect('soal-mapel', kelasModal);
}

// Init mapel selects on page load
filterMapelByKelas();

function toggleTipeForm(val) {
  document.getElementById('form-pg').style.display = val === 'pg' ? 'block' : 'none';
  document.getElementById('form-isian').style.display = val !== 'pg' ? 'block' : 'none';
  // Required attrs
  document.querySelector('[name="kunci_jawaban"]').required = (val !== 'pg');
}

function openTambahSoal() {
  document.getElementById('soal-id').value = '';
  document.getElementById('modal-soal-title').innerHTML = '<i data-lucide="plus"></i> Tambah Soal ke Bank Soal';
  lucide.createIcons();
  document.getElementById('form-soal').reset();
  toggleTipeForm('');
  TU.modal.open('modal-tambah-soal');
}

async function loadSoal() {
  document.getElementById('soal-container').innerHTML =
    '<div class="text-center" style="padding:40px"><div class="spinner"></div></div>';

  const filters = {
    mapel:  document.getElementById('filter-mapel').value,
    kelas:  document.getElementById('filter-kelas-soal').value,
    tipe:   document.getElementById('filter-tipe').value,
  };
  const r = await GS.getData('BankSoal', filters);
  allSoal = r.data || [];

  const q = document.getElementById('search-soal').value.toLowerCase();
  let filtered = allSoal;
  if (q) filtered = allSoal.filter(s => (s.pertanyaan||'').toLowerCase().includes(q));

  const p = TU.paginate(filtered, soalPage, soalPerPage);
  renderSoalList(p.items, (soalPage-1)*soalPerPage);
  TU.renderPagination(document.getElementById('pagination-soal'), p.page, p.pages,
    'function(pg){soalPage=pg;loadSoal()}'
  );
  lucide.createIcons();
}

function renderSoalList(rows, offset) {
  const cont = document.getElementById('soal-container');
  if (!rows.length) {
    cont.innerHTML = '<div class="empty-state"><div class="es-icon"><i data-lucide=\"pencil-line\"></i></div><h4>Belum ada soal</h4><p>Tambah soal baru dengan tombol di atas</p></div>';
    return;
  }
  cont.innerHTML = rows.map((s, i) => {
    let pilihanHtml = '';
    if (s.tipe === 'pg') {
      ['a','b','c','d','e'].forEach(opt => {
        if (!s[`pilihan_${opt}`]) return;
        const isBenar = s.jawaban_benar === opt;
        pilihanHtml += `<div class="pilihan-item ${isBenar?'correct':''}">
          <span class="pilihan-label">${opt.toUpperCase()}</span>
          <span>${s[`pilihan_${opt}`]}</span>
          ${isBenar ? '<span style="margin-left:auto;font-size:.75rem">✅ Jawaban Benar</span>' : ''}
        </div>`;
      });
    } else {
      pilihanHtml = `<div style="background:var(--bg);border:1px solid var(--border);border-radius:var(--radius-sm);padding:10px;font-size:.82rem;margin-top:8px">
        <strong style="color:var(--success)">Kunci:</strong> ${s.kunci_jawaban||'-'}
      </div>`;
    }
    return `<div class="soal-card">
      <div class="soal-header">
        <div class="soal-number">${offset+i+1}</div>
        <div style="flex:1">
          <div style="font-size:.87rem;font-weight:500;line-height:1.5">${s.pertanyaan||''}</div>
          <div class="soal-meta">
            <span class="badge badge-primary">${s.mapel||'-'}</span>
            <span class="badge badge-info">Kelas ${s.kelas||'-'}</span>
            <span class="badge ${s.tipe==='pg'?'badge-success':s.tipe==='isian'?'badge-orange':'badge-teal'}">${s.tipe==='pg'?'Pilihan Ganda':s.tipe==='isian'?'Isian':'Uraian'}</span>
            <span class="badge ${s.kesulitan==='Mudah'?'badge-success':s.kesulitan==='Sulit'?'badge-danger':'badge-warning'}">${s.kesulitan||'Sedang'}</span>
            ${s.tahun_ajaran?`<span class="badge badge-info">${s.tahun_ajaran} Sem.${s.semester||'1'}</span>`:''}
          </div>
        </div>
        <div style="display:flex;gap:4px">
          <button class="btn btn-sm btn-info" onclick="togglePembahasan('${s.id}')"><i data-lucide="eye"></i></button>
          <button class="btn btn-sm btn-warning" onclick="editSoal('${s.id}')"><i data-lucide="pencil"></i></button>
          <button class="btn btn-sm btn-danger" onclick="hapusSoal('${s.id}')"><i data-lucide="trash-2"></i></button>
        </div>
      </div>
      ${s.tipe==='pg'?`<div class="pilihan-list">${pilihanHtml}</div>`:pilihanHtml}
      ${s.pembahasan?`<div class="tip-box"><strong><i data-lucide=\"lightbulb\"></i> Pembahasan:</strong> ${s.pembahasan}</div>`:''}
    </div>`;
  }).join('');
}

function togglePembahasan(id) {
  const s = allSoal.find(x => x.id == id);
  if (!s) return;
  // Build preview content
  const tp = s.tipe === 'pg' ? 'Pilihan Ganda' : s.tipe === 'isian' ? 'Isian' : 'Uraian';
  let choices = '';
  if (s.tipe === 'pg') {
    ['a','b','c','d'].forEach(k => {
      if (s['pilihan_'+k]) choices += `<div style="padding:6px 0;border-bottom:1px solid var(--border);font-size:.85rem"><strong>${k.toUpperCase()}.</strong> ${s['pilihan_'+k]}</div>`;
    });
  }
  const html = `
    <div style="padding:4px">
      <div style="display:flex;gap:8px;margin-bottom:12px;flex-wrap:wrap">
        <span class="badge badge-primary">${s.mapel||'-'}</span>
        <span class="badge badge-info">Kelas ${s.kelas||'-'}</span>
        <span class="badge badge-success">${tp}</span>
        <span class="badge ${s.kesulitan==='Mudah'?'badge-success':s.kesulitan==='Sulit'?'badge-danger':'badge-warning'}">${s.kesulitan||'Sedang'}</span>
      </div>
      <div style="font-size:.95rem;line-height:1.6;margin-bottom:12px">${s.pertanyaan||'-'}</div>
      ${choices ? '<div style="margin-bottom:12px">'+choices+'</div>' : ''}
      <div style="padding:8px 12px;background:var(--bg);border-radius:6px;border:1px solid var(--border);font-size:.85rem"><strong style="color:var(--success)">Kunci Jawaban:</strong> ${s.kunci_jawaban||'-'}</div>
      ${s.pembahasan ? '<div class="tip-box" style="margin-top:10px"><strong><i data-lucide=\"lightbulb\"></i> Pembahasan:</strong> '+s.pembahasan+'</div>' : ''}
    </div>`;
  // Open in a simple alert/modal overlay
  const overlay = document.createElement('div');
  overlay.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.5);z-index:10000;display:flex;align-items:center;justify-content:center';
  overlay.innerHTML = '<div style="background:var(--bg-card);border-radius:12px;padding:24px;max-width:600px;width:90%;max-height:80vh;overflow-y:auto;box-shadow:var(--shadow-lg)"><div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px"><h3 style="margin:0;font-size:1rem"><i data-lucide=\"eye\"></i> Preview Soal</h3><button onclick="this.closest(\'div[style*=fixed]\').remove()" style="background:none;border:none;font-size:1.2rem;cursor:pointer"><i data-lucide=\"x\"></i></button></div>'+html+'</div>';
  overlay.addEventListener('click', e => { if (e.target === overlay) overlay.remove(); });
  document.body.appendChild(overlay);
}

function editSoal(id) {
  const s = allSoal.find(x => x.id == id);
  if (!s) return;
  document.getElementById('soal-id').value = s.id;
  document.getElementById('modal-soal-title').innerHTML = '<i data-lucide="pencil"></i> Edit Soal';
  lucide.createIcons();
  const fields = ['mapel','kelas','tipe','semester','tahun_ajaran','kesulitan','pertanyaan','kunci_jawaban','pembahasan',
    'pilihan_a','pilihan_b','pilihan_c','pilihan_d'];
  fields.forEach(f => {
    const el = document.querySelector(`#form-soal [name="${f}"]`);
    if (el) el.value = s[f] || '';
  });
  if (s.jawaban_benar) {
    const rb = document.querySelector(`[name="jawaban_benar"][value="${s.jawaban_benar}"]`);
    if (rb) rb.checked = true;
  }
  toggleTipeForm(s.tipe);
  TU.modal.open('modal-tambah-soal');
}

async function simpanSoal(e) {
  e.preventDefault();
  const btn = document.getElementById('btn-simpan-soal');
  TU.btnLoading(btn, true);
  const fd = new FormData(document.getElementById('form-soal'));
  const data = Object.fromEntries(fd.entries());
  const id = data.id; delete data.id;
  let r;
  if (id) { r = await GS.updateRow('BankSoal', id, data); }
  else { data.id = Date.now().toString(); data.dibuat_oleh = _userName; r = await GS.addRow('BankSoal', data); }
  TU.btnLoading(btn, false);
  if (r.success) {
    const logMsg = id ? 'Mengedit soal di bank soal' : 'Menambah soal baru ke bank soal (' + (data.mapel || '') + ' Kelas ' + (data.kelas || '') + ')';
    fetch('api/log.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: id ? 'ubah' : 'tambah', category: 'data', description: logMsg }) });
    TU.toast(id ? 'Soal diperbarui' : 'Soal berhasil ditambahkan', 'success');
    TU.modal.close('modal-tambah-soal');
    loadSoal();
  } else { TU.toast(r.error||'Gagal menyimpan', 'error'); }
}

function hapusSoal(id) {
  TU.confirm('Hapus soal ini dari bank soal?', async () => {
    const r = await GS.deleteRow('BankSoal', id);
    if (r.success) { TU.toast('Soal berhasil dihapus','success'); loadSoal(); }
    else TU.toast(r.error||'Gagal menghapus','error');
  });
}

function exportSoal() {
  TU.exportCSV(allSoal, 'bank_soal_'+new Date().toISOString().slice(0,10)+'.csv');
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
  fetch('api/log.php', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken }, body: JSON.stringify({ action: 'export', category: 'data', description: 'Export data Bank Soal ke CSV' }) });
}

let stimer;
document.getElementById('search-soal').addEventListener('input', () => {
  clearTimeout(stimer);
  stimer = setTimeout(() => { soalPage=1; loadSoal(); }, 300);
});

document.addEventListener('DOMContentLoaded', loadSoal);
</script>
JS;
?>
<?php include 'includes/footer.php'; ?>
