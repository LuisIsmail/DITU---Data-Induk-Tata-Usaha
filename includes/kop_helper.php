<?php
/**
 * Kop Surat Helper — Single source of truth for letterhead HTML
 * Used by: kop.php (preview), paket_soal.php (print), rapor.php (print)
 */

/**
 * Generate kop surat HTML for print/preview.
 * @param array $kop — data from getKopSekolah()
 * @param bool $printMode — if true, uses absolute positioning for print window
 * @return string HTML
 */
function getKopSuratHtml(array $kop, bool $printMode = false): string {
    $instansi    = htmlspecialchars($kop['instansi'] ?? 'PEMERINTAH KABUPATEN BERAU');
    $dinas       = htmlspecialchars($kop['dinas'] ?? 'DINAS PENDIDIKAN');
    $schoolName  = htmlspecialchars($kop['school_name'] ?? 'SD NEGERI 001 GUNUNG SARI');
    $npsn        = htmlspecialchars($kop['npsn'] ?? '');
    $nss         = htmlspecialchars($kop['nss'] ?? '');
    $akreditasi  = htmlspecialchars($kop['akreditasi'] ?? '');
    $email       = htmlspecialchars($kop['email'] ?? '');
    $alamat      = htmlspecialchars($kop['alamat'] ?? '');
    $kecamatan   = htmlspecialchars($kop['kecamatan'] ?? '');
    $kabupaten   = htmlspecialchars($kop['kabupaten'] ?? '');
    $provinsi    = htmlspecialchars($kop['provinsi'] ?? '');

    // Build full address
    $alamatParts = array_filter([$alamat, $kecamatan ? "Kec. $kecamatan" : '', $kabupaten ? "Kab. $kabupaten" : '', $provinsi ? "Prov. $provinsi" : '']);
    $alamatFull  = htmlspecialchars(implode(', ', $alamatParts));

    // Logo URLs
    $logoKiri  = $kop['logo_kiri'] ?? '';
    $logoKanan = $kop['logo_kanan'] ?? '';

    $logoKiriHtml  = !empty($logoKiri)
        ? '<img src="data/uploads/' . htmlspecialchars($logoKiri) . '" style="width:64px;height:64px;object-fit:contain;flex-shrink:0">'
        : '';
    $logoKananHtml = !empty($logoKanan)
        ? '<img src="data/uploads/' . htmlspecialchars($logoKanan) . '" style="width:64px;height:64px;object-fit:contain;flex-shrink:0">'
        : '';

    return <<<HTML
<div class="kop-print-area" style="text-align:center;font-size:.78rem;color:var(--text);line-height:1.5;padding:10px 0">
  <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:6px">
    $logoKiriHtml
    <div style="flex:1;text-align:center">
      <div style="font-weight:900;font-size:11pt;text-transform:uppercase;letter-spacing:.5px">$instansi</div>
      <div style="font-weight:700;font-size:10pt;text-transform:uppercase;margin-top:1px">$dinas</div>
    </div>
    $logoKananHtml
  </div>
  <div style="font-weight:900;font-size:12pt;text-transform:uppercase;margin:1px 0 6px;letter-spacing:.3px">$schoolName</div>
  <div style="font-size:8pt;font-style:italic;color:var(--text-muted);margin-bottom:3px">$alamatFull</div>
  <div style="font-size:8pt;color:var(--text-muted);margin-bottom:2px">
    <span>NPSN : $npsn</span>
    <span style="margin:0 4px">|</span>
    <span>Gmail : $email</span>
    <span style="margin:0 4px">|</span>
    <span>NSS : $nss</span>
  </div>
  <div style="font-weight:700;font-size:9pt;margin-top:4px">TERAKREDITASI : $akreditasi</div>
  <div style="margin-top:10px;border-top:3px double var(--text)"></div>
</div>
HTML;
}

/**
 * Generate kop surat HTML escaped for embedding in JavaScript string.
 * @param array $kop — data from getKopSekolah()
 * @return string JavaScript string literal (quoted, escaped)
 */
function getKopSuratJsHtml(array $kop): string {
    $html = getKopSuratHtml($kop);
    // Escape for JS single-quote string
    $escaped = addcslashes($html, "\\\'");
    $escaped = str_replace("\n", ' ', $escaped);
    $escaped = str_replace("\r", '', $escaped);
    return "'" . $escaped . "'";
}
