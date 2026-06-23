<?php
require_once 'config.php';
requireLogin();
$pageTitle    = 'Data Siswa';
$pageSubtitle = 'Manajemen Data Peserta Didik';
$user = currentUser();
include 'includes/header.php';
?>

<div class="section-header">
  <div>
    <h2><span class="sh-icon"><i data-lucide="graduation-cap"></i></span> Data Siswa</h2>
    <p>Manajemen data seluruh peserta didik</p>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap">
    <button class="btn btn-light btn-sm" onclick="exportSiswa()"><i data-lucide="download"></i> Export CSV</button>
    <button class="btn btn-primary" onclick="TU.modal.open('modal-tambah-siswa')"><i data-lucide="plus"></i> Tambah Siswa</button>
  </div>
</div>

<!-- FILTER BAR -->
<div class="card" style="margin-bottom:16px">
  <div class="card-body" style="padding:14px 18px">
    <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
      <div style="flex:1;min-width:180px">
        <label class="form-label"><i data-lucide="search"></i> Cari Siswa</label>
        <div class="search-input-wrap">
          <span class="si-icon"><i data-lucide="search"></i></span>
          <input type="text" id="search-siswa" class="form-control" placeholder="Nama, NISN, NIPD...">
        </div>
      </div>
      <div style="min-width:140px">
        <label class="form-label">Rombel/Kelas</label>
        <select class="form-select" id="filter-rombel" onchange="fetchSiswa()">
          <option value="">Semua Kelas</option>
        </select>
      </div>
      <div style="min-width:120px">
        <label class="form-label">Jenis Kelamin</label>
        <select class="form-select" id="filter-jk" onchange="fetchSiswa()">
          <option value="">Semua</option>
          <option value="L">Laki-laki</option>
          <option value="P">Perempuan</option>
        </select>
      </div>
      <div style="min-width:120px">
        <label class="form-label">Agama</label>
        <select class="form-select" id="filter-agama" onchange="fetchSiswa()">
          <option value="">Semua</option>
          <option>Islam</option><option>Kristen</option><option>Katolik</option>
          <option>Hindu</option><option>Buddha</option><option>Konghucu</option>
        </select>
      </div>
      <button class="btn btn-outline-primary" onclick="loadSiswa()"><i data-lucide="refresh-cw"></i> Refresh</button>
    </div>
  </div>
</div>

<!-- TABLE -->
<div class="card">
  <div class="card-header">
    <h3><i data-lucide="clipboard-list"></i> Daftar Peserta Didik</h3>
    <span class="badge badge-primary" id="total-siswa">0 siswa</span>
  </div>
  <div class="table-wrapper">
    <table class="data-table" id="tbl-siswa">
      <thead>
        <tr>
          <th>No</th>
          <th data-sort="nipd" onclick="sortSiswa('nipd')">NIPD</th>
          <th data-sort="nisn" onclick="sortSiswa('nisn')">NISN</th>
          <th data-sort="nama" onclick="sortSiswa('nama')">Nama Lengkap</th>
          <th data-sort="jk" onclick="sortSiswa('jk')">JK</th>
          <th data-sort="tgl_lahir" onclick="sortSiswa('tgl_lahir')">TTL</th>
          <th data-sort="rombel" onclick="sortSiswa('rombel')">Rombel</th>
          <th data-sort="agama" onclick="sortSiswa('agama')">Agama</th>
          <th>Wali Murid</th>
          <th>HP Orang Tua</th>
          <th data-sort="status_siswa" onclick="sortSiswa('status_siswa')">Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody id="tbody-siswa">
        <tr><td colspan="12" class="text-center" style="padding:40px">
          <div class="spinner"></div>
          <p class="text-muted mt-2">Memuat data...</p>
        </td></tr>
      </tbody>
    </table>
  </div>
  <div class="card-footer">
    <div class="pagination" id="pagination-siswa"></div>
  </div>
</div>

<!-- ============ MODAL TAMBAH/EDIT SISWA ============ -->
<div class="modal-overlay" id="modal-tambah-siswa">
  <div class="modal modal-xl">
    <div class="modal-header">
      <h4 id="modal-siswa-title"><i data-lucide="plus"></i> Tambah Data Siswa</h4>
      <button class="modal-close" onclick="TU.modal.close('modal-tambah-siswa')">✕</button>
    </div>
    <div class="modal-body">
      <form id="form-siswa" onsubmit="simpanSiswa(event)">
        <input type="hidden" id="siswa-id" name="id">

        <!-- TABS FORM -->
        <div class="tabs-container">
          <div class="tabs">
            <button type="button" class="tab-btn active" data-tab="identitas"><i data-lucide="user"></i> Identitas</button>
            <button type="button" class="tab-btn" data-tab="alamat"><i data-lucide="map-pin"></i> Alamat</button>
            <button type="button" class="tab-btn" data-tab="ortu"><i data-lucide="users"></i> Orang Tua</button>
            <button type="button" class="tab-btn" data-tab="sekolah"><i data-lucide="school"></i> Data Sekolah</button>
          </div>

          <!-- IDENTITAS -->
          <div class="tab-content active" data-tab-content="identitas">
            <div class="form-row">
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">NIPD <span class="req">*</span></label>
                  <input type="text" name="nipd" id="f-nipd" class="form-control" placeholder="Nomor Induk Peserta Didik">
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">NISN</label>
                  <input type="text" name="nisn" id="f-nisn" class="form-control" placeholder="Nomor Induk Siswa Nasional">
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">NIK Siswa</label>
                  <input type="text" name="nik" id="f-nik" class="form-control" placeholder="16 digit NIK">
                </div>
              </div>
            </div>
            <div class="form-row">
              <div class="form-col" style="flex:2">
                <div class="form-group">
                  <label class="form-label">Nama Lengkap <span class="req">*</span></label>
                  <input type="text" name="nama" id="f-nama" class="form-control" placeholder="Nama lengkap siswa" required>
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Jenis Kelamin <span class="req">*</span></label>
                  <select name="jk" id="f-jk" class="form-select" required>
                    <option value="">Pilih</option>
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="form-row">
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Tempat Lahir</label>
                  <input type="text" name="tempat_lahir" id="f-tempat-lahir" class="form-control" placeholder="Kota/Kabupaten">
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Tanggal Lahir</label>
                  <input type="date" name="tgl_lahir" id="f-tgl-lahir" class="form-control">
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Agama</label>
                  <select name="agama" id="f-agama" class="form-select">
                    <option value="">Pilih Agama</option>
                    <option>Islam</option><option>Kristen</option><option>Katolik</option>
                    <option>Hindu</option><option>Buddha</option><option>Konghucu</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="form-row">
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Anak Ke-</label>
                  <input type="number" name="anak_ke" id="f-anak-ke" class="form-control" placeholder="1" min="1">
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Jumlah Saudara Kandung</label>
                  <input type="number" name="jml_saudara" id="f-jml-saudara" class="form-control" min="0">
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">No. HP / WA Siswa</label>
                  <input type="text" name="hp_siswa" id="f-hp-siswa" class="form-control" placeholder="08xxx">
                </div>
              </div>
            </div>
          </div>

          <!-- ALAMAT -->
          <div class="tab-content" data-tab-content="alamat">
            <div class="form-group">
              <label class="form-label">Alamat Lengkap</label>
              <textarea name="alamat" id="f-alamat" class="form-control" placeholder="Jalan, Gang, Nomor Rumah..."></textarea>
            </div>
            <div class="form-row">
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">RT</label>
                  <input type="text" name="rt" id="f-rt" class="form-control" placeholder="001">
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">RW</label>
                  <input type="text" name="rw" id="f-rw" class="form-control" placeholder="001">
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Kelurahan/Desa</label>
                  <input type="text" name="kelurahan" id="f-kelurahan" class="form-control">
                </div>
              </div>
            </div>
            <div class="form-row">
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Kecamatan</label>
                  <input type="text" name="kecamatan" id="f-kecamatan" class="form-control">
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Kode Pos</label>
                  <input type="text" name="kode_pos" id="f-kode-pos" class="form-control" maxlength="5">
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Kabupaten/Kota</label>
                  <input type="text" name="kabupaten" id="f-kabupaten" class="form-control">
                </div>
              </div>
            </div>
            <div class="form-row">
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Provinsi</label>
                  <input type="text" name="provinsi" id="f-provinsi" class="form-control">
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Jarak Rumah ke Sekolah (km)</label>
                  <input type="number" name="jarak_sekolah" id="f-jarak" class="form-control" min="0" step="0.1">
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Transportasi ke Sekolah</label>
                  <select name="transportasi" id="f-transportasi" class="form-select">
                    <option value="">Pilih</option>
                    <option>Jalan Kaki</option>
                    <option>Kendaraan Pribadi</option>
                    <option>Angkutan Umum</option>
                    <option>Ojek/Taksi Online</option>
                    <option>Antar Jemput Sekolah</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <!-- ORANG TUA -->
          <div class="tab-content" data-tab-content="ortu">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
              <!-- AYAH -->
              <div>
                <h5 style="font-weight:700;margin-bottom:14px;color:var(--primary);font-size:.9rem"><i data-lucide="user"></i> Data Ayah</h5>
                <div class="form-group">
                  <label class="form-label">Nama Ayah</label>
                  <input type="text" name="nama_ayah" id="f-nama-ayah" class="form-control">
                </div>
                <div class="form-group">
                  <label class="form-label">NIK Ayah</label>
                  <input type="text" name="nik_ayah" id="f-nik-ayah" class="form-control" maxlength="16">
                </div>
                <div class="form-group">
                  <label class="form-label">Pendidikan Terakhir Ayah</label>
                  <select name="pendidikan_ayah" id="f-pend-ayah" class="form-select">
                    <option value="">Pilih</option>
                    <option>SD/Sederajat</option><option>SMP/Sederajat</option>
                    <option>SMA/Sederajat</option><option>D1/D2/D3</option>
                    <option>S1</option><option>S2</option><option>S3</option>
                    <option>Tidak Sekolah</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">Pekerjaan Ayah</label>
                  <input type="text" name="pekerjaan_ayah" id="f-kerja-ayah" class="form-control">
                </div>
                <div class="form-group">
                  <label class="form-label">Penghasilan Ayah / Bulan</label>
                  <select name="penghasilan_ayah" id="f-income-ayah" class="form-select">
                    <option value="">Pilih</option>
                    <option>Kurang dari Rp 500.000</option>
                    <option>Rp 500.000 - Rp 1.000.000</option>
                    <option>Rp 1.000.000 - Rp 2.000.000</option>
                    <option>Rp 2.000.000 - Rp 5.000.000</option>
                    <option>Lebih dari Rp 5.000.000</option>
                    <option>Tidak Berpenghasilan</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">No. HP Ayah</label>
                  <input type="text" name="hp_ayah" id="f-hp-ayah" class="form-control">
                </div>
              </div>
              <!-- IBU -->
              <div>
                <h5 style="font-weight:700;margin-bottom:14px;color:var(--secondary);font-size:.9rem"><i data-lucide="user"></i> Data Ibu</h5>
                <div class="form-group">
                  <label class="form-label">Nama Ibu</label>
                  <input type="text" name="nama_ibu" id="f-nama-ibu" class="form-control">
                </div>
                <div class="form-group">
                  <label class="form-label">NIK Ibu</label>
                  <input type="text" name="nik_ibu" id="f-nik-ibu" class="form-control" maxlength="16">
                </div>
                <div class="form-group">
                  <label class="form-label">Pendidikan Terakhir Ibu</label>
                  <select name="pendidikan_ibu" id="f-pend-ibu" class="form-select">
                    <option value="">Pilih</option>
                    <option>SD/Sederajat</option><option>SMP/Sederajat</option>
                    <option>SMA/Sederajat</option><option>D1/D2/D3</option>
                    <option>S1</option><option>S2</option><option>S3</option>
                    <option>Tidak Sekolah</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">Pekerjaan Ibu</label>
                  <input type="text" name="pekerjaan_ibu" id="f-kerja-ibu" class="form-control">
                </div>
                <div class="form-group">
                  <label class="form-label">Penghasilan Ibu / Bulan</label>
                  <select name="penghasilan_ibu" id="f-income-ibu" class="form-select">
                    <option value="">Pilih</option>
                    <option>Kurang dari Rp 500.000</option>
                    <option>Rp 500.000 - Rp 1.000.000</option>
                    <option>Rp 1.000.000 - Rp 2.000.000</option>
                    <option>Rp 2.000.000 - Rp 5.000.000</option>
                    <option>Lebih dari Rp 5.000.000</option>
                    <option>Tidak Berpenghasilan</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">No. HP Ibu</label>
                  <input type="text" name="hp_ibu" id="f-hp-ibu" class="form-control">
                </div>
              </div>
            </div>
            <!-- WALI -->
            <div style="border-top:1px solid var(--border);padding-top:16px;margin-top:16px">
              <h5 style="font-weight:700;margin-bottom:14px;color:var(--success);font-size:.9rem"><i data-lucide="users"></i> Data Wali (jika ada)</h5>
              <div class="form-row">
                <div class="form-col">
                  <div class="form-group">
                    <label class="form-label">Nama Wali</label>
                    <input type="text" name="nama_wali" id="f-nama-wali" class="form-control">
                  </div>
                </div>
                <div class="form-col">
                  <div class="form-group">
                    <label class="form-label">Hubungan dengan Siswa</label>
                    <input type="text" name="hubungan_wali" id="f-hub-wali" class="form-control" placeholder="Kakek, Paman, dll">
                  </div>
                </div>
                <div class="form-col">
                  <div class="form-group">
                    <label class="form-label">No. HP Wali</label>
                    <input type="text" name="hp_wali" id="f-hp-wali" class="form-control">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- DATA SEKOLAH -->
          <div class="tab-content" data-tab-content="sekolah">
            <div class="form-row">
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Rombel / Kelas <span class="req">*</span></label>
                  <select name="rombel" id="f-rombel-siswa" class="form-select" required>
                    <option value="">Pilih Rombel</option>
                  </select>
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Tahun Masuk</label>
                  <input type="text" name="tahun_masuk" id="f-tahun-masuk" class="form-control" placeholder="2024">
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Status Siswa</label>
                  <select name="status_siswa" id="f-status-siswa" class="form-select">
                    <option value="Aktif">Aktif</option>
                    <option value="Tidak Aktif">Tidak Aktif</option>
                    <option value="Pindah">Pindah</option>
                    <option value="Lulus">Lulus</option>
                    <option value="DO">Dikeluarkan</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="form-row">
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Asal Sekolah (SD/TK)</label>
                  <input type="text" name="asal_sekolah" id="f-asal-sekolah" class="form-control">
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Penerima KIP/Beasiswa</label>
                  <select name="kip" id="f-kip" class="form-select">
                    <option value="Tidak">Tidak</option>
                    <option value="KIP">KIP</option>
                    <option value="Beasiswa Lain">Beasiswa Lain</option>
                  </select>
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">No. KIP (jika ada)</label>
                  <input type="text" name="no_kip" id="f-no-kip" class="form-control">
                </div>
              </div>
            </div>
            <div class="form-row">
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Kebutuhan Khusus</label>
                  <select name="kebutuhan_khusus" id="f-kebutuhan-khusus" class="form-select">
                    <option value="Tidak Ada">Tidak Ada</option>
                    <option>Tunanetra</option><option>Tunarungu</option>
                    <option>Tunawicara</option><option>Tunagrahita</option>
                    <option>Tunadaksa</option><option>Autis</option>
                    <option>Lamban Belajar</option><option>Lainnya</option>
                  </select>
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Tinggi Badan (cm)</label>
                  <input type="number" name="tinggi_badan" id="f-tinggi" class="form-control" min="0">
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Berat Badan (kg)</label>
                  <input type="number" name="berat_badan" id="f-berat" class="form-control" min="0">
                </div>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Catatan</label>
              <textarea name="catatan" id="f-catatan" class="form-control" placeholder="Catatan tambahan mengenai siswa..."></textarea>
            </div>
          </div>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-light" onclick="TU.modal.close('modal-tambah-siswa')">Batal</button>
      <button type="submit" form="form-siswa" class="btn btn-primary" id="btn-simpan-siswa"><i data-lucide="save"></i> Simpan Data</button>
    </div>
  </div>
</div>

<!-- MODAL DETAIL SISWA -->
<div class="modal-overlay" id="modal-detail-siswa">
  <div class="modal modal-lg">
    <div class="modal-header">
      <h4><i data-lucide="user"></i> Detail Siswa</h4>
      <button class="modal-close" onclick="TU.modal.close('modal-detail-siswa')">✕</button>
    </div>
    <div class="modal-body" id="detail-siswa-body">
      <!-- Diisi JS -->
    </div>
    <div class="modal-footer">
      <button class="btn btn-light" onclick="TU.modal.close('modal-detail-siswa')">Tutup</button>
      <button class="btn btn-primary" id="btn-edit-from-detail"><i data-lucide="pencil"></i> Edit</button>
    </div>
  </div>
</div>

<?php
// Inject PHP values to JavaScript BEFORE the nowdoc block
$userIsAdmin = isAdmin() ? 'true' : 'false';
$userRombel = htmlspecialchars($user['rombel'] ?? '');
?>

<script>
  // PHP-injected values for role-based access
  window.__isAdmin = <?= $userIsAdmin ?>;
  window.__userRombel = '<?= $userRombel ?>';

  // Sort state (moved from heredoc for reliable global scope access)
  let sortKey = '';
  let sortDir = 'asc';
  const perPage = 15;

  // Sort function (moved from heredoc for reliable global scope access)
  function sortSiswa(key) {
    if (sortKey === key) {
      sortDir = sortDir === 'asc' ? 'desc' : 'asc';
    } else {
      sortKey = key;
      sortDir = 'asc';
    }
    TU.renderSortIndicator('tbl-siswa', sortKey, sortDir);
    renderSiswaPage();
  }
</script>

<?php
$extraJs = <<<'JS'
<script>
// loadSiswa, sortSiswa, renderSiswaTable, etc. are defined inline BEFORE this heredoc
let allSiswa = [];
let currentPage = 1;

// Load Rombel options
async function loadRombel() {
  const r = await GS.getData('Rombel');
  const data = r.data || [];
  const selects = document.querySelectorAll('#filter-rombel, #f-rombel-siswa');
  selects.forEach(sel => {
    const cur = sel.value;
    // Keep first option
    while (sel.options.length > 1) sel.remove(1);
    data.forEach(row => {
      const opt = new Option(row.nama_rombel, row.nama_rombel);
      sel.add(opt);
    });
    sel.value = cur;
  });
}

// Fetch data from server (only called on filter change or initial load)
async function fetchSiswa() {
  const tbody = document.getElementById('tbody-siswa');
  tbody.innerHTML = '<tr><td colspan="11" class="text-center" style="padding:30px"><div class="spinner"></div></td></tr>';

  const filters = {
    rombel: document.getElementById('filter-rombel').value,
    jk:     document.getElementById('filter-jk').value,
    agama:  document.getElementById('filter-agama').value,
  };

  // Guru hanya lihat kelasnya sendiri
  if (!window.__isAdmin) {
    filters.rombel = window.__userRombel;
    document.getElementById('filter-rombel').value = filters.rombel;
    document.getElementById('filter-rombel').disabled = true;
  }

  const r = await GS.getData('DataSiswa', filters);
  allSiswa = r.data || [];
  renderSiswaPage();
}

// Render current page from cached data (fast — no API call)
function renderSiswaPage() {
  const q = document.getElementById('search-siswa').value.toLowerCase();
  let filtered = allSiswa;
  if (q) filtered = allSiswa.filter(s =>
    (s.nama||'').toLowerCase().includes(q) ||
    (s.nisn||'').toLowerCase().includes(q) ||
    (s.nipd||'').toLowerCase().includes(q)
  );

  document.getElementById('total-siswa').textContent = filtered.length + ' siswa';

  let display = filtered;
  if (sortKey) display = TU.sortData(filtered, sortKey, sortDir);

  const p = TU.paginate(display, currentPage, perPage);
  renderSiswaTable(p.items, (currentPage - 1) * perPage);
  TU.renderPagination(document.getElementById('pagination-siswa'), p.page, p.pages,
    'function(pg){currentPage=pg;renderSiswaPage()}'
  );
  lucide.createIcons();
}

function renderSiswaTable(rows, offset) {
  const tbody = document.getElementById('tbody-siswa');
  if (!rows.length) {
    tbody.innerHTML = '<tr><td colspan="12"><div class="empty-state"><div class="es-icon"><i data-lucide=\"graduation-cap\"></i></div><h4>Belum ada data siswa</h4><p>Tambah data siswa baru dengan tombol di atas</p></div></td></tr>';
    return;
  }
  tbody.innerHTML = rows.map((s, i) => `
    <tr>
      <td>${offset + i + 1}</td>
      <td><code>${s.nipd||'-'}</code></td>
      <td><code>${s.nisn||'-'}</code></td>
      <td>
        <div style="font-weight:600">${s.nama||'-'}</div>
        <div style="font-size:.72rem;color:var(--text-muted)">${s.tempat_lahir||''} ${s.tgl_lahir?', '+TU.formatDate(s.tgl_lahir):''}</div>
      </td>
      <td><span class="badge ${s.jk==='L'?'badge-info':'badge-teal'}">${s.jk==='L'?'♂ L':'♀ P'}</span></td>
      <td style="font-size:.8rem">${s.tempat_lahir||''}<br><small>${s.tgl_lahir?TU.formatDate(s.tgl_lahir):''}</small></td>
      <td><span class="badge badge-primary">${s.rombel||'-'}</span></td>
      <td style="font-size:.8rem">${s.agama||'-'}</td>
      <td style="font-size:.8rem">${s.nama_ayah||s.nama_wali||'-'}</td>
      <td style="font-size:.8rem">${s.hp_ayah||s.hp_ibu||'-'}</td>
      <td>
        <span class="badge ${s.status_siswa==='Aktif'?'badge-success':s.status_siswa==='Lulus'?'badge-info':'badge-warning'}">${s.status_siswa||'-'}</span>
      </td>
      <td>
        <div style="display:flex;gap:4px">
          <button class="btn btn-sm btn-info" onclick="detailSiswa('${s.id}')" title="Detail"><i data-lucide="eye"></i></button>
          <button class="btn btn-sm btn-warning" onclick="editSiswa('${s.id}')" title="Edit"><i data-lucide="pencil"></i></button>
          <button class="btn btn-sm btn-danger" onclick="hapusSiswa('${s.id}','${s.nama}')" title="Hapus"><i data-lucide="trash-2"></i></button>
        </div>
      </td>
    </tr>`).join('');
}

function detailSiswa(id) {
  const s = allSiswa.find(x => x.id == id);
  if (!s) return;
  document.getElementById('btn-edit-from-detail').onclick = () => {
    TU.modal.close('modal-detail-siswa');
    editSiswa(id);
  };
  const rows = (obj, keys) => keys.map(([k,l]) =>
    `<tr><td style="color:var(--text-muted);width:150px;font-size:.8rem">${l}</td><td style="font-size:.82rem;font-weight:500">${obj[k]||'-'}</td></tr>`
  ).join('');
  document.getElementById('detail-siswa-body').innerHTML = `
    <div class="tabs-container">
      <div class="tabs">
        <button type="button" class="tab-btn active" data-tab="d-id"><i data-lucide="user"></i> Identitas</button>
        <button type="button" class="tab-btn" data-tab="d-al"><i data-lucide="map-pin"></i> Alamat</button>
        <button type="button" class="tab-btn" data-tab="d-ot"><i data-lucide="users"></i> Orang Tua</button>
        <button type="button" class="tab-btn" data-tab="d-sk"><i data-lucide="school"></i> Sekolah</button>
      </div>
      <div class="tab-content active" data-tab-content="d-id">
        <table class="data-table"><tbody>${rows(s,[['nipd','NIPD'],['nisn','NISN'],['nik','NIK'],['nama','Nama'],['jk','JK'],['tempat_lahir','Tempat Lahir'],['tgl_lahir','Tanggal Lahir'],['agama','Agama'],['anak_ke','Anak Ke'],['jml_saudara','Jml Saudara'],['hp_siswa','HP Siswa']])}</tbody></table>
      </div>
      <div class="tab-content" data-tab-content="d-al">
        <table class="data-table"><tbody>${rows(s,[['alamat','Alamat'],['rt','RT'],['rw','RW'],['kelurahan','Kelurahan'],['kecamatan','Kecamatan'],['kabupaten','Kabupaten'],['provinsi','Provinsi'],['kode_pos','Kode Pos'],['jarak_sekolah','Jarak ke Sekolah'],['transportasi','Transportasi']])}</tbody></table>
      </div>
      <div class="tab-content" data-tab-content="d-ot">
        <table class="data-table"><tbody>${rows(s,[['nama_ayah','Nama Ayah'],['nik_ayah','NIK Ayah'],['pendidikan_ayah','Pend. Ayah'],['pekerjaan_ayah','Pekerjaan Ayah'],['penghasilan_ayah','Penghasilan Ayah'],['hp_ayah','HP Ayah'],['nama_ibu','Nama Ibu'],['nik_ibu','NIK Ibu'],['pendidikan_ibu','Pend. Ibu'],['pekerjaan_ibu','Pekerjaan Ibu'],['penghasilan_ibu','Penghasilan Ibu'],['hp_ibu','HP Ibu'],['nama_wali','Nama Wali'],['hubungan_wali','Hubungan Wali'],['hp_wali','HP Wali']])}</tbody></table>
      </div>
      <div class="tab-content" data-tab-content="d-sk">
        <table class="data-table"><tbody>${rows(s,[['rombel','Rombel'],['tahun_masuk','Tahun Masuk'],['status_siswa','Status'],['asal_sekolah','Asal Sekolah'],['kip','KIP/Beasiswa'],['no_kip','No. KIP'],['kebutuhan_khusus','Kebutuhan Khusus'],['tinggi_badan','Tinggi Badan'],['berat_badan','Berat Badan'],['catatan','Catatan']])}</tbody></table>
      </div>
    </div>`;
  TU.initTabs('#modal-detail-siswa .tabs-container');
  TU.modal.open('modal-detail-siswa');
  lucide.createIcons();
}

function editSiswa(id) {
  const s = allSiswa.find(x => x.id == id);
  if (!s) return;
  document.getElementById('siswa-id').value = s.id;
  document.getElementById('modal-siswa-title').innerHTML = '<i data-lucide="pencil"></i> Edit Data Siswa';
  lucide.createIcons();
  // Fill all fields
  const fields = ['nipd','nisn','nik','nama','jk','tempat_lahir','tgl_lahir','agama','anak_ke','jml_saudara','hp_siswa',
    'alamat','rt','rw','kelurahan','kecamatan','kabupaten','provinsi','kode_pos',
    'nama_ayah','nik_ayah','pendidikan_ayah','pekerjaan_ayah','penghasilan_ayah','hp_ayah',
    'nama_ibu','nik_ibu','pendidikan_ibu','pekerjaan_ibu','penghasilan_ibu','hp_ibu',
    'nama_wali','hubungan_wali','hp_wali',
    'rombel','tahun_masuk','status_siswa','asal_sekolah','kip','no_kip','kebutuhan_khusus',
    'tinggi_badan','berat_badan','catatan','jarak_sekolah','transportasi','jml_saudara'];
  fields.forEach(f => {
    const el = document.querySelector(`[name="${f}"]`);
    if (el) el.value = s[f] || '';
  });
  TU.modal.open('modal-tambah-siswa');
}

async function simpanSiswa(e) {
  e.preventDefault();
  const btn = document.getElementById('btn-simpan-siswa');
  TU.btnLoading(btn, true);
  const formData = new FormData(document.getElementById('form-siswa'));
  const data = Object.fromEntries(formData.entries());
  const id = data.id;
  delete data.id;
  let r;
  if (id) {
    r = await GS.updateRow('DataSiswa', id, data);
  } else {
    data.id = Date.now().toString();
    r = await GS.addRow('DataSiswa', data);
  }
  TU.btnLoading(btn, false);
  if (r.success) {
    TU.toast(id ? 'Data berhasil diperbarui' : 'Siswa berhasil ditambahkan', 'success');
    TU.modal.close('modal-tambah-siswa');
    document.getElementById('form-siswa').reset();
    document.getElementById('siswa-id').value = '';
    document.getElementById('modal-siswa-title').innerHTML = '<i data-lucide="plus"></i> Tambah Data Siswa';
    lucide.createIcons();
    fetchSiswa();
  } else {
    TU.toast(r.error || 'Gagal menyimpan data', 'error');
  }
}

function hapusSiswa(id, nama) {
  TU.confirm(`Hapus data siswa <strong>${nama}</strong>? Data tidak dapat dikembalikan.`, async () => {
    const r = await GS.deleteRow('DataSiswa', id);
    if (r.success) { TU.toast('Data siswa berhasil dihapus', 'success'); fetchSiswa(); }
    else TU.toast(r.error || 'Gagal menghapus', 'error');
  });
}

function exportSiswa() {
  const q = document.getElementById('search-siswa').value.toLowerCase();
  let data = allSiswa;
  if (q) data = data.filter(s => (s.nama||'').toLowerCase().includes(q));
  TU.exportCSV(data, 'data_siswa_' + new Date().toISOString().slice(0,10) + '.csv');
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
  fetch('api/log.php', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken }, body: JSON.stringify({ action: 'export', category: 'data', description: 'Export data Siswa ke CSV' }) });
}

// Search handler with debounce
let searchTimer;
document.getElementById('search-siswa').addEventListener('input', () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => { currentPage = 1; renderSiswaPage(); }, 300);
});

// Init
document.addEventListener('DOMContentLoaded', () => {
  loadRombel();
  fetchSiswa();
});
</script>
JS;
?>

<?php include 'includes/footer.php'; ?>
