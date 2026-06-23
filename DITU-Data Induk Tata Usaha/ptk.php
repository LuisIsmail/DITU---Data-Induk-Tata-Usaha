<?php
require_once 'config.php';
requireAdmin();
$pageTitle    = 'Data PTK';
$pageSubtitle = 'Pendidik & Tenaga Kependidikan';
$user = currentUser();
include 'includes/header.php';
?>

<div class="section-header">
  <div>
    <h2><span class="sh-icon"><i data-lucide="briefcase"></i></span> Data PTK</h2>
    <p>Manajemen data Pendidik & Tenaga Kependidikan</p>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap">
    <button class="btn btn-light btn-sm" onclick="exportPTK()"><i data-lucide="download"></i> Export CSV</button>
    <button class="btn btn-primary" onclick="openTambahPTK()"><i data-lucide="plus"></i> Tambah PTK</button>
  </div>
</div>
<style>
  .doc-preview { margin-top: 6px; display: none; }
  .doc-preview img { max-height: 60px; border-radius: 6px; border: 2px solid var(--border); }
  .doc-preview .doc-link {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 12px; background: var(--bg-secondary, #f0f0f0);
    border-radius: 6px; font-size: .8rem; color: var(--primary);
    text-decoration: none; border: 1px dashed var(--border);
  }
  .doc-preview .doc-link:hover { background: var(--bg-tertiary, #e8e8e8); }
  .doc-preview .doc-uploaded {
    display: flex; align-items: center; gap: 8px;
    padding: 6px 10px; background: rgba(34,197,94,.08);
    border: 1px solid rgba(34,197,94,.3); border-radius: 6px;
    font-size: .78rem; color: #16a34a;
  }
  .doc-preview .doc-remove { cursor: pointer; color: #ef4444; font-weight: 700; margin-left: auto; }
</style>

<!-- FILTER -->
<div class="card" style="margin-bottom:16px">
  <div class="card-body" style="padding:14px 18px">
    <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
      <div style="flex:1;min-width:180px">
        <label class="form-label"><i data-lucide="search"></i> Cari PTK</label>
        <div class="search-input-wrap">
          <span class="si-icon"><i data-lucide="search"></i></span>
          <input type="text" id="search-ptk" class="form-control" placeholder="Nama, NIP, NUPTK...">
        </div>
      </div>
      <div style="min-width:140px">
        <label class="form-label">Status Kepegawaian</label>
        <select class="form-select" id="filter-status-ptk" onchange="loadPTK()">
          <option value="">Semua</option>
          <option>PNS</option><option>PPPK</option><option>GTT/PTT</option><option>Honorer</option>
        </select>
      </div>
      <div style="min-width:130px">
        <label class="form-label">Jenis PTK</label>
        <select class="form-select" id="filter-jenis-ptk" onchange="loadPTK()">
          <option value="">Semua</option>
          <option>Kepala Sekolah</option>
          <option>Guru Kelas</option>
          <option>Guru Mapel</option>
          <option>Tenaga Kependidikan</option>
        </select>
      </div>
      <button class="btn btn-outline-primary" onclick="loadPTK()"><i data-lucide="refresh-cw"></i> Refresh</button>
    </div>
  </div>
</div>

<!-- TABLE -->
<div class="card">
  <div class="card-header">
    <h3><i data-lucide="clipboard-list"></i> Daftar PTK</h3>
    <span class="badge badge-primary" id="total-ptk">0 orang</span>
  </div>
  <div class="table-wrapper">
    <table class="data-table" id="tbl-ptk">
      <thead>
        <tr>
          <th>No</th>
          <th>Foto</th>
          <th data-sort="nip" onclick="sortPTK('nip')">NIP / NUPTK</th>
          <th data-sort="nama" onclick="sortPTK('nama')">Nama Lengkap</th>
          <th data-sort="jk" onclick="sortPTK('jk')">JK</th>
          <th data-sort="jenis_ptk" onclick="sortPTK('jenis_ptk')">Jenis PTK</th>
          <th data-sort="status_kepeg" onclick="sortPTK('status_kepeg')">Status</th>
          <th data-sort="mapel_diampu" onclick="sortPTK('mapel_diampu')">Mapel / Tugas</th>
          <th>Rombel Diampu</th>
          <th>HP</th>
          <th>Aksi</th>
          <th>User</th>
        </tr>
      </thead>
      <tbody id="tbody-ptk">
        <tr><td colspan="11" class="text-center" style="padding:40px">
          <div class="spinner"></div>
          <p class="text-muted mt-2">Memuat data...</p>
        </td></tr>
      </tbody>
    </table>
  </div>
  <div class="card-footer">
    <div class="pagination" id="pagination-ptk"></div>
  </div>
</div>

<!-- ======= MODAL TAMBAH/EDIT PTK ======= -->
<div class="modal-overlay" id="modal-tambah-ptk">
  <div class="modal modal-xl">
    <div class="modal-header">
      <h4 id="modal-ptk-title"><i data-lucide="plus"></i> Tambah Data PTK</h4>
      <button class="modal-close" onclick="TU.modal.close('modal-tambah-ptk')">✕</button>
    </div>
    <div class="modal-body">
      <form id="form-ptk" onsubmit="simpanPTK(event)">
        <input type="hidden" id="ptk-id" name="id">
        <div class="tabs-container">
          <div class="tabs">
            <button type="button" class="tab-btn" data-tab="p-identitas"><i data-lucide="user"></i> Identitas</button>
            <button type="button" class="tab-btn" data-tab="p-kepeg"><i data-lucide="clipboard-list"></i> Kepegawaian</button>
            <button type="button" class="tab-btn" data-tab="p-akademik"><i data-lucide="graduation-cap"></i> Akademik</button>
            <button type="button" class="tab-btn" data-tab="p-alamat"><i data-lucide="map-pin"></i> Alamat & Kontak</button>
            <button type="button" class="tab-btn" data-tab="p-tugas"><i data-lucide="school"></i> Tugas Mengajar</button>
            <button type="button" class="tab-btn" data-tab="p-dokumen"><i data-lucide="file-text"></i> Dokumen</button>
          </div>

          <!-- IDENTITAS -->
          <div class="tab-content active" data-tab-content="p-identitas">
            <div class="form-row">
              <div class="form-col" style="flex:2">
                <div class="form-group">
                  <label class="form-label">Nama Lengkap <span class="req">*</span></label>
                  <input type="text" name="nama" class="form-control" required placeholder="Nama lengkap dengan gelar">
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Jenis Kelamin <span class="req">*</span></label>
                  <select name="jk" class="form-select" required>
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
                  <label class="form-label">NIK <span class="req">*</span></label>
                  <input type="text" name="nik" class="form-control" maxlength="16" placeholder="16 digit NIK">
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Tempat Lahir</label>
                  <input type="text" name="tempat_lahir" class="form-control">
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Tanggal Lahir</label>
                  <input type="date" name="tgl_lahir" class="form-control">
                </div>
              </div>
            </div>
            <div class="form-row">
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Agama</label>
                  <select name="agama" class="form-select">
                    <option value="">Pilih</option>
                    <option>Islam</option><option>Kristen</option><option>Katolik</option>
                    <option>Hindu</option><option>Buddha</option><option>Konghucu</option>
                  </select>
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Status Pernikahan</label>
                  <select name="status_nikah" class="form-select">
                    <option value="">Pilih</option>
                    <option>Belum Menikah</option>
                    <option>Menikah</option>
                    <option>Cerai Hidup</option>
                    <option>Cerai Mati</option>
                  </select>
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Jumlah Anak</label>
                  <input type="number" name="jml_anak" class="form-control" min="0">
                </div>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Upload Foto</label>
              <input type="file" name="foto_file" id="ptk-foto-file" class="form-control" accept="image/*">
              <p class="form-hint">Foto akan tersimpan ke penyimpanan lokal. Maks 2MB.</p>
              <input type="hidden" name="foto" id="ptk-foto-id">
              <div style="margin-top:8px">
                <img id="ptk-foto-preview" src="" alt="preview" style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:2px solid var(--border);display:none">
              </div>
            </div>
          </div>

          <!-- KEPEGAWAIAN -->
          <div class="tab-content" data-tab-content="p-kepeg">
            <div class="form-row">
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">NIP</label>
                  <input type="text" name="nip" class="form-control" placeholder="Kosongkan jika non-PNS">
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">NUPTK</label>
                  <input type="text" name="nuptk" class="form-control" maxlength="16">
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">NRG (Guru Bersertifikat)</label>
                  <input type="text" name="nrg" class="form-control">
                </div>
              </div>
            </div>
            <div class="form-row">
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Jenis PTK <span class="req">*</span></label>
                  <select name="jenis_ptk" class="form-select" required>
                    <option value="">Pilih</option>
                    <option>Kepala Sekolah</option>
                    <option>Guru Kelas</option>
                    <option>Guru Mapel</option>
                    <option>Guru Pendamping</option>
                    <option>Tenaga Kependidikan</option>
                    <option>Pustakawan</option>
                    <option>Penjaga Sekolah</option>
                    <option>Operator Sekolah</option>
                  </select>
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Status Kepegawaian <span class="req">*</span></label>
                  <select name="status_kepeg" class="form-select" required>
                    <option value="">Pilih</option>
                    <option>PNS</option>
                    <option>PPPK</option>
                    <option>GTT/PTT</option>
                    <option>Honorer</option>
                    <option>Sukarela</option>
                  </select>
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Status Aktif</label>
                  <select name="status_aktif" class="form-select">
                    <option value="Aktif">Aktif</option>
                    <option value="Non Aktif">Non Aktif</option>
                    <option value="Pensiun">Pensiun</option>
                    <option value="Mutasi">Mutasi</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="form-row">
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">TMT Pengangkatan</label>
                  <input type="date" name="tmt_pengangkatan" class="form-control">
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">TMT di Sekolah Ini</label>
                  <input type="date" name="tmt_sekolah" class="form-control">
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Golongan / Pangkat</label>
                  <select name="golongan" class="form-select">
                    <option value="">-</option>
                    <option>II/a</option><option>II/b</option><option>II/c</option><option>II/d</option>
                    <option>III/a</option><option>III/b</option><option>III/c</option><option>III/d</option>
                    <option>IV/a</option><option>IV/b</option><option>IV/c</option><option>IV/d</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="form-row">
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Sertifikasi</label>
                  <select name="sertifikasi" class="form-select">
                    <option value="Belum">Belum Sertifikasi</option>
                    <option value="Sudah">Sudah Sertifikasi</option>
                    <option value="Proses">Dalam Proses</option>
                  </select>
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Tahun Sertifikasi</label>
                  <input type="text" name="tahun_sertif" class="form-control" placeholder="2020">
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Gaji Pokok (Rp)</label>
                  <input type="number" name="gaji_pokok" class="form-control" min="0">
                </div>
              </div>
            </div>
          </div>

          <!-- AKADEMIK -->
          <div class="tab-content" data-tab-content="p-akademik">
            <div class="form-row">
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Pendidikan Terakhir</label>
                  <select name="pendidikan" class="form-select">
                    <option value="">Pilih</option>
                    <option>SMA/Sederajat</option>
                    <option>D1</option><option>D2</option><option>D3</option>
                    <option>S1</option><option>S2</option><option>S3</option>
                  </select>
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Jurusan / Prodi</label>
                  <input type="text" name="jurusan" class="form-control" placeholder="PGSD, Matematika, dll">
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Nama Perguruan Tinggi</label>
                  <input type="text" name="perguruan_tinggi" class="form-control">
                </div>
              </div>
            </div>
            <div class="form-row">
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Tahun Lulus</label>
                  <input type="text" name="tahun_lulus" class="form-control" placeholder="2010">
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">No. Ijazah</label>
                  <input type="text" name="no_ijazah" class="form-control">
                </div>
              </div>
            </div>
          </div>

          <!-- ALAMAT & KONTAK -->
          <div class="tab-content" data-tab-content="p-alamat">
            <div class="form-group">
              <label class="form-label">Alamat Lengkap</label>
              <textarea name="alamat" class="form-control" rows="3" placeholder="Jalan, RT/RW, Nomor Rumah..."></textarea>
            </div>
            <div class="form-row">
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">RT / RW</label>
                  <input type="text" name="rt_rw" class="form-control" placeholder="001/002">
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Kelurahan/Desa</label>
                  <input type="text" name="kelurahan" class="form-control">
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Kecamatan</label>
                  <input type="text" name="kecamatan" class="form-control">
                </div>
              </div>
            </div>
            <div class="form-row">
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Kabupaten/Kota</label>
                  <input type="text" name="kabupaten" class="form-control">
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Provinsi</label>
                  <input type="text" name="provinsi" class="form-control">
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Kode Pos</label>
                  <input type="text" name="kode_pos" class="form-control" maxlength="5">
                </div>
              </div>
            </div>
            <div class="form-row">
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">No. HP / WA <span class="req">*</span></label>
                  <input type="text" name="hp" class="form-control" placeholder="08xxx" required>
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Email</label>
                  <input type="email" name="email" class="form-control" placeholder="nama@email.com">
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">No. Rekening Bank</label>
                  <input type="text" name="no_rekening" class="form-control">
                </div>
              </div>
            </div>
            <div class="form-row">
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Nama Bank</label>
                  <input type="text" name="nama_bank" class="form-control" placeholder="BRI, BNI, Mandiri...">
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">NPWP</label>
                  <input type="text" name="npwp" class="form-control">
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">No. BPJS Kesehatan</label>
                  <input type="text" name="bpjs" class="form-control">
                </div>
              </div>
            </div>
          </div>

          <!-- TUGAS MENGAJAR -->
          <div class="tab-content" data-tab-content="p-tugas">
            <div class="form-row">
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Mata Pelajaran Diampu</label>
                  <input type="text" name="mapel_diampu" class="form-control" placeholder="Matematika, IPA, Semua Mapel...">
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Rombel / Kelas Diampu</label>
                  <select name="rombel_diampu" class="form-select">
                    <option value="">Pilih Rombel</option>
                    <?php for($k=1;$k<=6;$k++): foreach(['A','B','C','D','E','F'] as $huruf): ?>
                    <option value="<?= $k ?><?= $huruf ?>"><?= $k ?><?= $huruf ?></option>
                    <?php endforeach; endfor; ?>
                  </select>
                  <p class="form-hint">Untuk Guru Kelas, pilih rombel yang menjadi tanggung jawabnya</p>
                </div>
              </div>
            </div>
            <div class="form-row">
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Jumlah Jam Mengajar / Minggu</label>
                  <input type="number" name="jam_mengajar" class="form-control" min="0">
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label">Tugas Tambahan</label>
                  <input type="text" name="tugas_tambahan" class="form-control" placeholder="Bendahara, Waka Kurikulum, dll">
                </div>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Username Login Sistem</label>
              <input type="text" name="username_sistem" class="form-control" placeholder="Username untuk login ke TU">
              <p class="form-hint">Biarkan kosong jika PTK tidak perlu akses sistem</p>
            </div>
            <div class="form-group">
              <label class="form-label">Catatan</label>
              <textarea name="catatan" class="form-control" rows="3" placeholder="Catatan tambahan..."></textarea>
            </div>
          </div>

          <!-- DOKUMEN -->
          <div class="tab-content" data-tab-content="p-dokumen">
            <p class="form-hint" style="margin-bottom:16px;color:var(--text-muted)">Upload dokumen pendukung PTK. Format: JPG, PNG, PDF. <strong>Maksimal 1MB per file.</strong></p>
            <div class="form-row">
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label"><i data-lucide="file-text"></i> Ijazah SD</label>
                  <input type="file" class="form-control doc-file" data-field="dokumen_ijazah_sd" accept="image/*,.pdf">
                  <input type="hidden" name="dokumen_ijazah_sd" class="doc-hidden">
                  <div class="doc-preview" data-preview="dokumen_ijazah_sd"></div>
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label"><i data-lucide="file-text"></i> Ijazah SMP</label>
                  <input type="file" class="form-control doc-file" data-field="dokumen_ijazah_smp" accept="image/*,.pdf">
                  <input type="hidden" name="dokumen_ijazah_smp" class="doc-hidden">
                  <div class="doc-preview" data-preview="dokumen_ijazah_smp"></div>
                </div>
              </div>
            </div>
            <div class="form-row">
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label"><i data-lucide="file-text"></i> Ijazah SMA</label>
                  <input type="file" class="form-control doc-file" data-field="dokumen_ijazah_sma" accept="image/*,.pdf">
                  <input type="hidden" name="dokumen_ijazah_sma" class="doc-hidden">
                  <div class="doc-preview" data-preview="dokumen_ijazah_sma"></div>
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label"><i data-lucide="file-text"></i> Ijazah S1</label>
                  <input type="file" class="form-control doc-file" data-field="dokumen_ijazah_s1" accept="image/*,.pdf">
                  <input type="hidden" name="dokumen_ijazah_s1" class="doc-hidden">
                  <div class="doc-preview" data-preview="dokumen_ijazah_s1"></div>
                </div>
              </div>
            </div>
            <div class="form-row">
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label"><i data-lucide="credit-card"></i> Kartu Keluarga (KK)</label>
                  <input type="file" class="form-control doc-file" data-field="dokumen_kk" accept="image/*,.pdf">
                  <input type="hidden" name="dokumen_kk" class="doc-hidden">
                  <div class="doc-preview" data-preview="dokumen_kk"></div>
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label"><i data-lucide="credit-card"></i> KTP</label>
                  <input type="file" class="form-control doc-file" data-field="dokumen_ktp" accept="image/*,.pdf">
                  <input type="hidden" name="dokumen_ktp" class="doc-hidden">
                  <div class="doc-preview" data-preview="dokumen_ktp"></div>
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label"><i data-lucide="credit-card"></i> NPWP</label>
                  <input type="file" class="form-control doc-file" data-field="dokumen_npwp" accept="image/*,.pdf">
                  <input type="hidden" name="dokumen_npwp" class="doc-hidden">
                  <div class="doc-preview" data-preview="dokumen_npwp"></div>
                </div>
              </div>
            </div>
            <div class="form-row">
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label"><i data-lucide="award"></i> Sertifikat 1</label>
                  <input type="file" class="form-control doc-file" data-field="dokumen_sertif_1" accept="image/*,.pdf">
                  <input type="hidden" name="dokumen_sertif_1" class="doc-hidden">
                  <div class="doc-preview" data-preview="dokumen_sertif_1"></div>
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label"><i data-lucide="award"></i> Sertifikat 2</label>
                  <input type="file" class="form-control doc-file" data-field="dokumen_sertif_2" accept="image/*,.pdf">
                  <input type="hidden" name="dokumen_sertif_2" class="doc-hidden">
                  <div class="doc-preview" data-preview="dokumen_sertif_2"></div>
                </div>
              </div>
            </div>
            <div class="form-row">
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label"><i data-lucide="award"></i> Sertifikat 3</label>
                  <input type="file" class="form-control doc-file" data-field="dokumen_sertif_3" accept="image/*,.pdf">
                  <input type="hidden" name="dokumen_sertif_3" class="doc-hidden">
                  <div class="doc-preview" data-preview="dokumen_sertif_3"></div>
                </div>
              </div>
              <div class="form-col">
                <div class="form-group">
                  <label class="form-label"><i data-lucide="award"></i> Sertifikat 4</label>
                  <input type="file" class="form-control doc-file" data-field="dokumen_sertif_4" accept="image/*,.pdf">
                  <input type="hidden" name="dokumen_sertif_4" class="doc-hidden">
                  <div class="doc-preview" data-preview="dokumen_sertif_4"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-light" onclick="TU.modal.close('modal-tambah-ptk')">Batal</button>
      <button type="submit" form="form-ptk" class="btn btn-primary" id="btn-simpan-ptk"><i data-lucide="save"></i> Simpan Data PTK</button>
    </div>
  </div>
</div>

<?php
$sortScript = <<<SORTJS
<script>
// Sort state (moved from heredoc for reliable global scope access)
let ptkSortKey = '';
let ptkSortDir = 'asc';
const ptkPerPage = 15;

// Sort function (moved from heredoc for reliable global scope access)
function sortPTK(key) {
  if (ptkSortKey === key) {
    ptkSortDir = ptkSortDir === 'asc' ? 'desc' : 'asc';
  } else {
    ptkSortKey = key;
    ptkSortDir = 'asc';
  }
  TU.renderSortIndicator('tbl-ptk', ptkSortKey, ptkSortDir);
  loadPTK();
}
</script>
SORTJS;
$extraJs = <<<'JS'
<script>
let allPTK = [];
let ptkPage = 1;
// ptkSortKey, ptkSortDir, ptkPerPage are defined inline BEFORE this heredoc

function openTambahPTK() {
  document.getElementById('ptk-id').value = '';
  document.getElementById('modal-ptk-title').innerHTML = '<i data-lucide="plus"></i> Tambah Data PTK';
  lucide.createIcons();
  document.getElementById('form-ptk').reset();
  document.getElementById('ptk-foto-preview').style.display = 'none';
  // Reset document previews
  document.querySelectorAll('.doc-hidden').forEach(h => h.value = '');
  document.querySelectorAll('.doc-preview').forEach(p => { p.innerHTML = ''; p.style.display = 'none'; });
  document.querySelectorAll('.doc-file').forEach(f => f.value = '');
  TU.modal.open('modal-tambah-ptk');
}

async function loadPTK() {
  document.getElementById('tbody-ptk').innerHTML =
    '<tr><td colspan="11" class="text-center" style="padding:30px"><div class="spinner"></div></td></tr>';
  const filters = {
    status_kepeg: document.getElementById('filter-status-ptk').value,
    jenis_ptk:    document.getElementById('filter-jenis-ptk').value,
  };
  const r = await GS.getData('DataPTK', filters);
  allPTK = r.data || [];
  const q = document.getElementById('search-ptk').value.toLowerCase();
  let filtered = allPTK;
  if (q) filtered = allPTK.filter(p =>
    (p.nama||'').toLowerCase().includes(q) ||
    (p.nip||'').toLowerCase().includes(q) ||
    (p.nuptk||'').toLowerCase().includes(q)
  );
  let display = filtered;
  if (ptkSortKey) display = TU.sortData(filtered, ptkSortKey, ptkSortDir);

  document.getElementById('total-ptk').textContent = display.length + ' orang';
  const p = TU.paginate(display, ptkPage, ptkPerPage);
  renderPTKTable(p.items, (ptkPage-1)*ptkPerPage);
  TU.renderPagination(document.getElementById('pagination-ptk'), p.page, p.pages,
    'function(pg){ptkPage=pg;loadPTK()}'
  );
  lucide.createIcons();
}

function renderPTKTable(rows, offset) {
  const tbody = document.getElementById('tbody-ptk');
  if (!rows.length) {
    tbody.innerHTML = '<tr><td colspan="11"><div class="empty-state"><div class="es-icon"><i data-lucide=\"briefcase\"></i></div><h4>Belum ada data PTK</h4></div></td></tr>';
    return;
  }
  tbody.innerHTML = rows.map((p, i) => {
    const fotoUrl = p.foto ? (p.foto.startsWith('http') ? p.foto : (p.foto.includes('/') ? p.foto : (p.foto.startsWith('doc_') ? 'data/uploads/ptk_docs/' + p.foto : (p.user_id ? 'data/uploads/' + p.user_id + '/' + p.foto : 'data/uploads/ptk_docs/' + p.foto)))) : '';
    const statusColor = p.status_kepeg==='PNS'?'badge-success':p.status_kepeg==='PPPK'?'badge-info':'badge-warning';
    return `<tr>
      <td>${offset+i+1}</td>
      <td>
        <div style="width:36px;height:36px;border-radius:50%;overflow:hidden;background:var(--bg-secondary);display:flex;align-items:center;justify-content:center;color:var(--text-muted);font-weight:700;font-size:.85rem">
          ${fotoUrl ? `<img src="${fotoUrl}" style="width:100%;height:100%;object-fit:cover">` : (p.nama||'?')[0].toUpperCase()}
        </div>
      </td>
      <td style="font-size:.78rem"><code>${p.nip||p.nuptk||'-'}</code></td>
      <td><div style="font-weight:600">${p.nama||'-'}</div><div style="font-size:.7rem;color:var(--text-muted)">${p.email||''}</div></td>
      <td><span class="badge ${p.jk==='L'?'badge-info':'badge-teal'}">${p.jk||'-'}</span></td>
      <td style="font-size:.8rem">${p.jenis_ptk||'-'}</td>
      <td><span class="badge ${statusColor}">${p.status_kepeg||'-'}</span></td>
      <td style="font-size:.8rem">${p.mapel_diampu||'-'}</td>
      <td><span class="badge badge-primary">${p.rombel_diampu||'-'}</span></td>
      <td style="font-size:.8rem">${p.hp||'-'}</td>
      <td>
        <div style="display:flex;gap:4px">
          <button class="btn btn-sm btn-info" onclick="detailPTK('${p.id}')"><i data-lucide="eye"></i></button>
          <button class="btn btn-sm btn-warning" onclick="editPTK('${p.id}')"><i data-lucide="pencil"></i></button>
          <button class="btn btn-sm btn-danger" onclick="hapusPTK('${p.id}','${p.nama}')"><i data-lucide="trash-2"></i></button>
        </div>
      </td>
      <td>${p.username_sistem ? '<code>' + p.username_sistem + '</code>' : '<span style="color:var(--text-muted);font-size:.75rem">—</span>'}</td>
    </tr>`;
  }).join('');
}

function editPTK(id) {
  const p = allPTK.find(x => x.id == id);
  if (!p) return;
  // Restore form state (in case detailPTK disabled fields and removed footer)
  document.querySelectorAll('#form-ptk input, #form-ptk select, #form-ptk textarea').forEach(el => el.disabled = false);
  const footer = document.querySelector('#modal-tambah-ptk .modal-footer');
  footer.innerHTML = '<button class="btn btn-light" onclick="TU.modal.close(\'modal-tambah-ptk\')">Batal</button><button type="submit" form="form-ptk" class="btn btn-primary" id="btn-simpan-ptk"><i data-lucide="save"></i> Simpan Data PTK</button>';
  lucide.createIcons();
  document.getElementById('ptk-id').value = p.id;
  document.getElementById('modal-ptk-title').innerHTML = '<i data-lucide="pencil"></i> Edit Data PTK';
  lucide.createIcons();
  const fields = ['nama','jk','nik','tempat_lahir','tgl_lahir','agama','status_nikah','jml_anak',
    'nip','nuptk','nrg','jenis_ptk','status_kepeg','status_aktif','tmt_pengangkatan','tmt_sekolah',
    'golongan','sertifikasi','tahun_sertif','gaji_pokok',
    'pendidikan','jurusan','perguruan_tinggi','tahun_lulus','no_ijazah',
    'alamat','rt_rw','kelurahan','kecamatan','kabupaten','provinsi','kode_pos',
    'hp','email','no_rekening','nama_bank','npwp','bpjs',
    'mapel_diampu','rombel_diampu','jam_mengajar','tugas_tambahan','username_sistem','catatan'];
  fields.forEach(f => {
    const el = document.querySelector(`#form-ptk [name="${f}"]`);
    if (el) el.value = p[f] || '';
  });
  if (p.foto) {
    document.getElementById('ptk-foto-id').value = p.foto;
    const prev = document.getElementById('ptk-foto-preview');
    prev.src = p.foto.startsWith('http') ? p.foto : (p.foto.includes('/') ? p.foto : (p.foto.startsWith('doc_') ? 'data/uploads/ptk_docs/' + p.foto : (p.user_id ? 'data/uploads/' + p.user_id + '/' + p.foto : 'data/uploads/ptk_docs/' + p.foto)));
    prev.style.display = 'block';
  }
    // Populate document previews
  const docFields = ['dokumen_ijazah_sd','dokumen_ijazah_smp','dokumen_ijazah_sma','dokumen_ijazah_s1',
    'dokumen_kk','dokumen_ktp','dokumen_npwp',
    'dokumen_sertif_1','dokumen_sertif_2','dokumen_sertif_3','dokumen_sertif_4'];
  docFields.forEach(f => {
    const hidden = document.querySelector('.doc-hidden[name="' + f + '"]');
    const preview = document.querySelector('.doc-preview[data-preview="' + f + '"]');
    if (hidden && p[f]) {
      hidden.value = p[f];
      if (preview) {
        const isPdf = p[f].endsWith('.pdf');
        preview.innerHTML = isPdf
          ? '<a href="data/uploads/ptk_docs/' + p[f] + '" target="_blank" class="doc-link"><i data-lucide=\"file-text\"></i> ' + p[f] + '</a>'
          : '<img src="data/uploads/ptk_docs/' + p[f] + '">';
        preview.style.display = 'block';
      }
    } else if (preview) {
      preview.innerHTML = '';
      preview.style.display = 'none';
    }
  });TU.modal.open('modal-tambah-ptk');
}

function detailPTK(id) {
  const p = allPTK.find(x => x.id == id);
  if (!p) return;
  editPTK(id);
  document.getElementById('modal-ptk-title').innerHTML = '<i data-lucide="eye"></i> Detail Data PTK';
  lucide.createIcons();
  document.querySelectorAll('#form-ptk input, #form-ptk select, #form-ptk textarea').forEach(el => el.disabled = true);
  const footer = document.querySelector('#modal-tambah-ptk .modal-footer');
  footer.innerHTML = `<button type="button" class="btn btn-outline" onclick="TU.modal.close('modal-tambah-ptk'); document.querySelectorAll('#form-ptk input, #form-ptk select, #form-ptk textarea').forEach(el => el.disabled = false);"><i data-lucide="x"></i> Tutup</button>`;
  lucide.createIcons();
}

async function simpanPTK(e) {
  e.preventDefault();
  const btn = document.getElementById('btn-simpan-ptk');
  TU.btnLoading(btn, true);

  // Handle foto upload
  const fotoFile = document.getElementById('ptk-foto-file').files[0];
  if (fotoFile) {
    if (fotoFile.size > 2 * 1024 * 1024) {
      TU.toast('Ukuran foto maks 2MB', 'error');
      TU.btnLoading(btn, false);
      return;
    }
    const fd2 = new FormData();
    fd2.append('file', fotoFile);
    const upRes = await fetch('api/upload_ptk.php', { method: 'POST', body: fd2 });
    const up = await upRes.json();
    if (up.success) document.getElementById('ptk-foto-id').value = up.filename;
    else { TU.toast(up.error || 'Gagal upload foto', 'error'); TU.btnLoading(btn, false); return; }
  }

  // Handle document uploads (10 fields, max 1MB each)
  const docInputs = document.querySelectorAll('.doc-file');
  for (const docInput of docInputs) {
    const file = docInput.files[0];
    const fieldName = docInput.dataset.field;
    const hiddenInput = document.querySelector('.doc-hidden[name="' + fieldName + '"]');
    if (file) {
      if (file.size > 1024 * 1024) {
        TU.toast('File ' + file.name + ' melebihi 1MB', 'error');
        TU.btnLoading(btn, false);
        return;
      }
      const fd = new FormData();
      fd.append('file', file);
      const res = await fetch('api/upload_ptk.php', { method: 'POST', body: fd });
      const result = await res.json();
      if (result.success) {
        hiddenInput.value = result.filename;
      } else {
        TU.toast(result.error || 'Gagal upload ' + file.name, 'error');
        TU.btnLoading(btn, false);
        return;
      }
    }
  }

  const fd = new FormData(document.getElementById('form-ptk'));
  const data = Object.fromEntries(fd.entries());
  delete data.foto_file;
  const id = data.id; delete data.id;
  let r;
  if (id) { r = await GS.updateRow('DataPTK', id, data); }
  else { data.id = Date.now().toString(); r = await GS.addRow('DataPTK', data); }
  TU.btnLoading(btn, false);
  if (r.success) {
    TU.toast(id ? 'Data PTK diperbarui' : 'PTK berhasil ditambahkan', 'success');
    TU.modal.close('modal-tambah-ptk');
    loadPTK();
  } else TU.toast(r.error || 'Gagal menyimpan', 'error');
}

function hapusPTK(id, nama) {
  TU.confirm(`Hapus data PTK <strong>${nama}</strong>?`, async () => {
    const r = await GS.deleteRow('DataPTK', id);
    if (r.success) { TU.toast('Data PTK berhasil dihapus','success'); loadPTK(); }
    else TU.toast(r.error||'Gagal','error');
  });
}

function exportPTK() {
  TU.exportCSV(allPTK, 'data_ptk_'+new Date().toISOString().slice(0,10)+'.csv');
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
  fetch('api/log.php', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken }, body: JSON.stringify({ action: 'export', category: 'data', description: 'Export data PTK ke CSV' }) });
}

// Foto preview
document.getElementById('ptk-foto-file').addEventListener('change', function() {
  const file = this.files[0];
  if (!file) return;
  const prev = document.getElementById('ptk-foto-preview');
  const reader = new FileReader();
  reader.onload = e => { prev.src = e.target.result; prev.style.display='block'; };
  reader.readAsDataURL(file);
});

let stimer;
document.getElementById('search-ptk').addEventListener('input', () => {
  clearTimeout(stimer);
  stimer = setTimeout(() => { ptkPage=1; loadPTK(); }, 300);
});

document.addEventListener('DOMContentLoaded', loadPTK);
</script>
JS;
?>
<?php echo $sortScript; ?>
<?php include 'includes/footer.php'; ?>
