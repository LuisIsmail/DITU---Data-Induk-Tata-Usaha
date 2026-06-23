<?php
require_once 'config.php';
requireLogin();
$pageTitle    = 'Buat Soal Ujian';
$pageSubtitle = 'Generate paket soal ujian dari bank soal';
$user = currentUser();
include 'includes/header.php';
?>

<div class="section-header">
  <div>
    <h2><span class="sh-icon"><i data-lucide="pencil-line"></i></span> Buat Soal Ujian</h2>
    <p>Generate paket soal ujian dari bank soal secara otomatis</p>
  </div>
</div>

<div style="display:grid;grid-template-columns:340px 1fr;gap:20px;align-items:start">

  <!-- FORM GENERATOR -->
  <div class="card" style="position:sticky;top:80px">
    <div class="card-header">
      <h3><i data-lucide="settings"></i> Konfigurasi Ujian</h3>
    </div>
    <div class="card-body">
      <div class="form-group">
        <label class="form-label">Judul Ujian <span class="req">*</span></label>
        <input type="text" id="judul-ujian" class="form-control" placeholder="Ulangan Harian / UTS / UAS...">
      </div>
      <div class="form-group">
        <label class="form-label">Mata Pelajaran <span class="req">*</span></label>
        <select id="gen-mapel" class="form-select">
          <option value="">Pilih Mata Pelajaran</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Kelas/Jenjang <span class="req">*</span></label>
        <select id="gen-kelas" class="form-select" onchange="filterBuatSoalMapel()">
          <option value="">Pilih Kelas</option>
          <option value="1">Kelas 1 (Fase A)</option>
          <option value="2">Kelas 2 (Fase A)</option>
          <option value="3">Kelas 3 (Fase B)</option>
          <option value="4">Kelas 4 (Fase B)</option>
          <option value="5">Kelas 5 (Fase C)</option>
          <option value="6">Kelas 6 (Fase C)</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Semester</label>
        <select id="gen-semester" class="form-select">
          <option value="1">Semester 1 (Ganjil)</option>
          <option value="2">Semester 2 (Genap)</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Tahun Pelajaran</label>
        <input type="text" id="gen-tapel" class="form-control" value="<?= date('Y') . '/' . (date('Y')+1) ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Tipe Soal</label>
        <div style="display:flex;gap:10px">
          <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:.85rem">
            <input type="checkbox" id="inc-pg" checked> Pilihan Ganda
          </label>
          <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:.85rem">
            <input type="checkbox" id="inc-isian"> Isian Singkat
          </label>
          <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:.85rem">
            <input type="checkbox" id="inc-uraian"> Uraian
          </label>
        </div>
      </div>
      <div id="jumlah-fields">
        <div id="field-pg" class="form-group">
          <label class="form-label">Jumlah Soal PG</label>
          <input type="number" id="jml-pg" class="form-control" value="20" min="1" max="100">
        </div>
        <div id="field-isian" class="form-group" style="display:none">
          <label class="form-label">Jumlah Soal Isian</label>
          <input type="number" id="jml-isian" class="form-control" value="10" min="1" max="50">
        </div>
        <div id="field-uraian" class="form-group" style="display:none">
          <label class="form-label">Jumlah Soal Uraian</label>
          <input type="number" id="jml-uraian" class="form-control" value="5" min="1" max="20">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Waktu Pengerjaan (menit)</label>
        <input type="number" id="gen-waktu" class="form-control" value="90" min="15">
      </div>
      <div class="form-group">
        <label class="form-label">Acak Urutan Soal</label>
        <select id="gen-acak" class="form-select">
          <option value="ya">Ya (Acak)</option>
          <option value="tidak">Tidak (Urutan Asli)</option>
        </select>
      </div>
      <button class="btn btn-primary btn-block" onclick="generateSoal()" id="btn-generate">
        <i data-lucide="dice-5"></i> Generate Paket Soal
      </button>
    </div>
  </div>

  <!-- PREVIEW PAKET SOAL -->
  <div>
    <div id="preview-area">
      <div class="card">
        <div class="card-body" style="text-align:center;padding:60px 20px">
          <div style="font-size:4rem;margin-bottom:16px"><i data-lucide="pencil-line"></i></div>
          <h4 style="color:var(--text);margin-bottom:8px">Belum Ada Paket Soal</h4>
          <p class="text-muted">Isi konfigurasi di sebelah kiri, lalu klik <strong>Generate Paket Soal</strong></p>
        </div>
      </div>
    </div>

    <div id="action-area" style="display:none;margin-top:16px">
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <button class="btn btn-success" onclick="simpanPaket()"><i data-lucide="save"></i> Simpan Paket</button>
        <button class="btn btn-info" onclick="printSoal()"><i data-lucide="printer"></i> Cetak Soal</button>
        <button class="btn btn-warning" onclick="printKunci()"><i data-lucide="key"></i> Cetak Kunci Jawaban</button>
        <button class="btn btn-outline-primary" onclick="generateSoal()"><i data-lucide="refresh-cw"></i> Generate Ulang</button>
      </div>
    </div>
  </div>
</div>

<?php
$userNama = addslashes($user['nama']);
$kop = getKopSekolah();
$kopInstansi = addslashes($kop['instansi'] ?? 'Pemerintah Kabupaten Berau');
$kopDinas = addslashes($kop['dinas'] ?? 'Dinas Pendidikan');
$kopSekolah = addslashes($kop['school_name'] ?? 'SD Negeri 001 Gunung Sari');
$extraJs = <<<JS
<script>
let paketSoal = null;
const kopInstansi = '{$kopInstansi}';
const kopDinas = '{$kopDinas}';
const kopSekolah = '{$kopSekolah}';

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

function filterBuatSoalMapel() {
  const kelas = document.getElementById('gen-kelas').value;
  const sel = document.getElementById('gen-mapel');
  const prev = sel.value;
  const mapels = kelas ? (MAPEL_BY_KELAS[kelas] || ALL_MAPEL) : ALL_MAPEL;
  sel.innerHTML = '<option value="">Pilih Mata Pelajaran</option>' + mapels.map(m => '<option>' + m + '</option>').join('');
  if (mapels.includes(prev)) sel.value = prev;
}
filterBuatSoalMapel();

// Toggle checkbox -> show/hide jumlah field
['pg','isian','uraian'].forEach(t => {
  document.getElementById('inc-'+t).addEventListener('change', function() {
    document.getElementById('field-'+t).style.display = this.checked ? 'block' : 'none';
  });
});

async function generateSoal() {
  const mapel  = document.getElementById('gen-mapel').value;
  const kelas  = document.getElementById('gen-kelas').value;
  if (!mapel || !kelas) { TU.toast('Pilih mata pelajaran dan kelas terlebih dahulu', 'warning'); return; }

  const btn = document.getElementById('btn-generate');
  TU.btnLoading(btn, true);

  const filters = { mapel, kelas };
  const r = await GS.getData('BankSoal', filters);
  TU.btnLoading(btn, false);

  const bank = r.data || [];
  if (!bank.length) { TU.toast('Tidak ada soal di bank soal untuk mapel/kelas ini. Tambah soal di menu Bank Soal.', 'warning'); return; }

  const incPg     = document.getElementById('inc-pg').checked;
  const incIsian  = document.getElementById('inc-isian').checked;
  const incUraian = document.getElementById('inc-uraian').checked;
  const jmlPg     = parseInt(document.getElementById('jml-pg').value)||20;
  const jmlIsian  = parseInt(document.getElementById('jml-isian').value)||10;
  const jmlUraian = parseInt(document.getElementById('jml-uraian').value)||5;
  const acak      = document.getElementById('gen-acak').value === 'ya';

  function pick(arr, tipe, n) {
    let pool = arr.filter(s => s.tipe === tipe);
    if (acak) pool = pool.sort(() => Math.random() - 0.5);
    return pool.slice(0, n);
  }

  let soalDipilih = [];
  if (incPg)     soalDipilih = soalDipilih.concat(pick(bank,'pg',jmlPg));
  if (incIsian)  soalDipilih = soalDipilih.concat(pick(bank,'isian',jmlIsian));
  if (incUraian) soalDipilih = soalDipilih.concat(pick(bank,'uraian',jmlUraian));

  if (!soalDipilih.length) { TU.toast('Tidak ada soal bertipe yang dipilih di bank soal ini', 'warning'); return; }

  paketSoal = {
    judul:    document.getElementById('judul-ujian').value || `Ujian \${mapel} Kelas \${kelas}`,
    mapel, kelas,
    semester: document.getElementById('gen-semester').value,
    tapel:    document.getElementById('gen-tapel').value,
    waktu:    document.getElementById('gen-waktu').value,
    soal:     soalDipilih,
    dibuat:   new Date().toLocaleString('id-ID'),
    guru:     '{$userNama}',
  };

  renderPreview();
}

function renderPreview() {
  if (!paketSoal) return;
  const s = paketSoal;
  const pgSoal     = s.soal.filter(x => x.tipe === 'pg');
  const isianSoal  = s.soal.filter(x => x.tipe === 'isian');
  const uraianSoal = s.soal.filter(x => x.tipe === 'uraian');

  let nomor = 0;
  const renderSoalItem = (soal, showKunci = false) => {
    nomor++;
    let extra = '';
    if (soal.tipe === 'pg') {
      const opts = ['a','b','c','d','e'].filter(o => soal[`pilihan_\${o}`]);
      extra = opts.map(o => {
        const isBenar = soal.jawaban_benar === o && showKunci;
        return `<div style="display:flex;gap:8px;align-items:center;padding:4px 0">
          <span style="width:20px;height:20px;border:1.5px solid \${isBenar?'var(--success)':'var(--border)'};
                border-radius:50%;display:flex;align-items:center;justify-content:center;
                font-size:.7rem;font-weight:700;flex-shrink:0;
                background:\${isBenar?'var(--success)':'transparent'};
                color:\${isBenar?'#fff':'var(--text)'}">\${o.toUpperCase()}</span>
          <span style="font-size:.83rem;\${isBenar?'color:var(--success);font-weight:600':''}">\${soal[`pilihan_\${o}`]}</span>
        </div>`;
      }).join('');
    } else if (showKunci) {
      extra = `<div class="answer-box">
                <strong>Jawaban:</strong> \${soal.kunci_jawaban||'-'}
              </div>`;
    } else {
      extra = soal.tipe === 'isian'
        ? '<div style="border-bottom:1px solid #333;margin:10px 0 4px;width:60%;height:24px"></div>'
        : '<div style="border:1px solid #ccc;border-radius:4px;height:80px;margin-top:8px"></div>';
    }
    return `<div style="margin-bottom:16px;page-break-inside:avoid">
      <div style="display:flex;gap:10px">
        <span style="font-weight:700;min-width:24px">\${nomor}.</span>
        <div style="flex:1">
          <div style="font-size:.87rem;margin-bottom:6px">\${soal.pertanyaan}</div>
          \${extra}
        </div>
      </div>
    </div>`;
  };

  const buildSheet = (showKunci) => {
    nomor = 0;
    let html = `<div id="print-soal" style="font-family:'Times New Roman',serif;max-width:800px;margin:0 auto">
      <!-- KOP -->
      <div style="text-align:center;margin-bottom:12px">
        <div style="font-weight:900;font-size:11pt;text-transform:uppercase">${kopInstansi}</div>
        <div style="font-weight:700;font-size:10pt;text-transform:uppercase;margin-top:1px">${kopDinas}</div>
      </div>
      <hr style="border:none;border-top:1px solid #000;margin:4px 0">
      <div style="text-align:center;margin-bottom:6px">
        <div style="font-weight:900;font-size:12pt;text-transform:underline">${kopSekolah}</div>
      </div>
      <hr style="border:none;border-top:2px solid #000;margin:4px 0">
      <div style="text-align:center;margin-bottom:16px">
        <h3 style="font-size:11pt;margin:10px 0 2px">\${showKunci?'KUNCI JAWABAN — ':''}\${s.judul.toUpperCase()}</h3>
        <p style="font-size:9pt;margin:2px 0">Mata Pelajaran: <strong>\${s.mapel}</strong> | Kelas: <strong>\${s.kelas}</strong> |
          Semester: <strong>\${s.semester}</strong> | T.P: <strong>\${s.tapel}</strong> |
          Waktu: <strong>\${s.waktu} menit</strong></p>
      </div>`;
    if (!showKunci) {
      html += `<div style="display:flex;gap:20px;margin-bottom:20px;font-size:.82rem">
        <div>Nama : ___________________________</div>
        <div>Kelas : ______________</div>
        <div>No. Absen : _________</div>
        <div>Nilai : _________</div>
      </div>`;
    }
    if (pgSoal.length) {
      html += `<div style="font-weight:700;margin-bottom:12px;font-size:.9rem">I. Pilihan Ganda</div>
               <p style="font-size:.78rem;margin-bottom:12px;font-style:italic">Pilih jawaban yang paling tepat!</p>`;
      pgSoal.forEach(s2 => html += renderSoalItem(s2, showKunci));
    }
    if (isianSoal.length) {
      html += `<div style="font-weight:700;margin:20px 0 12px;font-size:.9rem">II. Isian Singkat</div>
               <p style="font-size:.78rem;margin-bottom:12px;font-style:italic">Isilah titik-titik berikut dengan jawaban yang tepat!</p>`;
      isianSoal.forEach(s2 => html += renderSoalItem(s2, showKunci));
    }
    if (uraianSoal.length) {
      html += `<div style="font-weight:700;margin:20px 0 12px;font-size:.9rem">III. Uraian</div>
               <p style="font-size:.78rem;margin-bottom:12px;font-style:italic">Jawablah pertanyaan berikut dengan benar dan lengkap!</p>`;
      uraianSoal.forEach(s2 => html += renderSoalItem(s2, showKunci));
    }
    html += `<div style="margin-top:30px;text-align:right;font-size:.78rem">
      <p>Dibuat: \${s.dibuat}</p>
      <p>Oleh: \${s.guru}</p>
    </div></div>`;
    return html;
  };

  document.getElementById('preview-area').innerHTML = `
    <div class="card">
      <div class="card-header">
        <h3><i data-lucide="file-text"></i> Preview Paket Soal — \${s.judul}</h3>
        <div style="display:flex;gap:8px">
          <span class="badge badge-primary">\${s.soal.length} Soal</span>
          <span class="badge badge-info">\${s.mapel}</span>
          <span class="badge badge-success">Kelas \${s.kelas}</span>
        </div>
      </div>
      <div class="card-body">
        \${buildSheet(false)}
      </div>
    </div>`;

  document.getElementById('action-area').style.display = 'flex';
  window._printKunciHtml = buildSheet(true);
  TU.toast(`Berhasil generate \${s.soal.length} soal`, 'success');
  lucide.createIcons();
}

async function simpanPaket() {
  if (!paketSoal) return;
  const data = {
    id: Date.now().toString(),
    judul: paketSoal.judul,
    mapel: paketSoal.mapel,
    kelas: paketSoal.kelas,
    semester: paketSoal.semester,
    tapel: paketSoal.tapel,
    waktu: paketSoal.waktu,
    jumlah_soal: paketSoal.soal.length,
    soal_ids: paketSoal.soal.map(s=>s.id).join(','),
    dibuat_oleh: paketSoal.guru,
    dibuat_at: new Date().toISOString(),
  };
  const r = await GS.addRow('PaketSoal', data);
  if (r.success) {
    // Log activity
    await fetch('api/log.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'generate', category: 'data', description: 'Membuat paket soal: ' + paketSoal.judul + ' (' + paketSoal.soal.length + ' soal)' }) });
    TU.toast('Paket soal berhasil disimpan', 'success');
  }
  else TU.toast(r.error||'Gagal menyimpan paket', 'error');
}

function printSoal() {
  const el = document.getElementById('print-soal');
  if (!el) return;
  fetch('api/log.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'cetak', category: 'umum', description: 'Mencetak soal ujian' }) });
  const w = window.open('','_blank','width=900,height=700');
  w.document.write(`<html><head><title>Soal Ujian</title>
    <style>body{font-family:'Times New Roman',serif;padding:30px;font-size:12pt}
    @media print{.no-print{display:none}}</style></head>
    <body>\${el.outerHTML}</body></html>`);
  w.document.close(); w.focus();
  setTimeout(()=>{w.print();w.close();},500);
}

function printKunci() {
  if (!window._printKunciHtml) return;
  fetch('api/log.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'cetak', category: 'umum', description: 'Mencetak kunci jawaban' }) });
  const w = window.open('','_blank','width=900,height=700');
  w.document.write(`<html><head><title>Kunci Jawaban</title>
    <style>body{font-family:'Times New Roman',serif;padding:30px;font-size:12pt}</style></head>
    <body>\${window._printKunciHtml}</body></html>`);
  w.document.close(); w.focus();
  setTimeout(()=>{w.print();w.close();},500);
}
</script>
JS;
?>
<?php include 'includes/footer.php'; ?>
