<?php
require_once 'config.php';
requireLogin();
$pageTitle    = 'Dashboard';
$pageSubtitle = 'Ringkasan Informasi Sekolah';

$conn = dbConnect();

// Get stats from MySQL
$stats = [
    'siswa'  => dbCount('DataSiswa'),
    'ptk'    => dbCount('DataPTK'),
    'rombel' => dbCount('Rombel'),
    'soal'   => dbCount('BankSoal'),
];

// === Dashboard Chart Queries ===

// 1. Gender distribution (all students)
$jkResult = $conn->query("SELECT jk, COUNT(*) as jumlah FROM DataSiswa GROUP BY jk ORDER BY jk");
$jkAll = ['L' => 0, 'P' => 0];
while ($row = $jkResult->fetch_assoc()) {
    $jkAll[$row['jk']] = (int)$row['jumlah'];
}

// 2. Age distribution (all students) - count per individual age
$ageResult = $conn->query("SELECT TIMESTAMPDIFF(YEAR, tgl_lahir, CURDATE()) as usia FROM DataSiswa WHERE tgl_lahir IS NOT NULL");
$ageAll = [];
for ($a = 6; $a <= 12; $a++) $ageAll[$a] = 0;
while ($row = $ageResult->fetch_assoc()) {
    $u = (int)$row['usia'];
    if (isset($ageAll[$u])) $ageAll[$u]++;
}

// 3. Gender per Kelas (1-6)
$jkKelasResult = $conn->query("SELECT LEFT(rombel, 1) as kelas, jk, COUNT(*) as jumlah FROM DataSiswa GROUP BY kelas, jk ORDER BY kelas");
$jkKelasLabels = [];
$jkKelasL = [];
$jkKelasP = [];
while ($row = $jkKelasResult->fetch_assoc()) {
    $k = $row['kelas'];
    if (!in_array($k, $jkKelasLabels)) $jkKelasLabels[] = $k;
}
// Reset and rebuild properly
$jkKelasLabels = ['1','2','3','4','5','6'];
$jkKelasData = ['L' => array_fill(0, 6, 0), 'P' => array_fill(0, 6, 0)];
$jkKelasResult2 = $conn->query("SELECT LEFT(rombel, 1) as kelas, jk, COUNT(*) as jumlah FROM DataSiswa GROUP BY kelas, jk ORDER BY kelas");
while ($row = $jkKelasResult2->fetch_assoc()) {
    $idx = array_search($row['kelas'], $jkKelasLabels);
    if ($idx !== false) {
        $jkKelasData[$row['jk']][$idx] = (int)$row['jumlah'];
    }
}

// 4. Total siswa per Kelas (1-6) — single bar chart
$jkKelasLabels = ['1','2','3','4','5','6'];
$ageKelasData = array_fill(0, 6, 0);
$ageKelasResult = $conn->query("SELECT LEFT(rombel, 1) as kelas, COUNT(*) as jumlah FROM DataSiswa GROUP BY kelas ORDER BY kelas");
while ($row = $ageKelasResult->fetch_assoc()) {
    $idx = array_search($row['kelas'], $jkKelasLabels);
    if ($idx !== false) $ageKelasData[$idx] = (int)$row['jumlah'];
}

// 5. Gender per Rombel
$jkRombelResult = $conn->query("SELECT rombel, jk, COUNT(*) as jumlah FROM DataSiswa GROUP BY rombel, jk ORDER BY rombel");
$jkRombelLabels = [];
$jkRombelDataL = [];
$jkRombelDataP = [];
$rombelMap = [];
while ($row = $jkRombelResult->fetch_assoc()) {
    $rombel = $row['rombel'];
    if (!isset($rombelMap[$rombel])) {
        $rombelMap[$rombel] = ['L' => 0, 'P' => 0];
        $jkRombelLabels[] = $rombel;
    }
    $rombelMap[$rombel][$row['jk']] = (int)$row['jumlah'];
}
foreach ($jkRombelLabels as $r) {
    $jkRombelDataL[] = $rombelMap[$r]['L'] ?? 0;
    $jkRombelDataP[] = $rombelMap[$r]['P'] ?? 0;
}

// 6. Total siswa per Rombel — multi-color bar chart
$ageRombelData = [];
foreach ($jkRombelLabels as $rombel) {
    $result = $conn->query("SELECT COUNT(*) as jumlah FROM DataSiswa WHERE rombel = '" . $conn->real_escape_string($rombel) . "'");
    $row = $result->fetch_assoc();
    $ageRombelData[] = (int)$row['jumlah'];
}

// 7. Religion distribution (all)
$agamaResult = $conn->query("SELECT agama, COUNT(*) as jumlah FROM DataSiswa WHERE agama IS NOT NULL GROUP BY agama ORDER BY jumlah DESC");
$agamaLabels = [];
$agamaData = [];
while ($row = $agamaResult->fetch_assoc()) {
    $agamaLabels[] = $row['agama'];
    $agamaData[] = (int)$row['jumlah'];
}

// 7b. Religion per Kelas (1-6)
$agamaKelasResult = $conn->query("SELECT LEFT(rombel, 1) as kelas, agama, COUNT(*) as jumlah FROM DataSiswa WHERE agama IS NOT NULL GROUP BY kelas, agama ORDER BY kelas, agama");
$agamaKelasLabels = ['1','2','3','4','5','6'];
$agamaUnique = array_unique($agamaData ? array_map(fn($r) => $r['agama'], iterator_to_array($conn->query("SELECT DISTINCT agama FROM DataSiswa WHERE agama IS NOT NULL"))) : []);
sort($agamaUnique);
$agamaKelasDataset = [];
foreach ($agamaUnique as $a) {
    $agamaKelasDataset[$a] = array_fill(0, 6, 0);
}
$agamaKelasResult2 = $conn->query("SELECT LEFT(rombel, 1) as kelas, agama, COUNT(*) as jumlah FROM DataSiswa WHERE agama IS NOT NULL GROUP BY kelas, agama ORDER BY kelas");
while ($row = $agamaKelasResult2->fetch_assoc()) {
    $idx = array_search($row['kelas'], $agamaKelasLabels);
    if ($idx !== false && isset($agamaKelasDataset[$row['agama']])) {
        $agamaKelasDataset[$row['agama']][$idx] = (int)$row['jumlah'];
    }
}

include 'includes/header.php';

// Pass data to JS
$chartData = json_encode([
    'jkAll'        => $jkAll,
    'ageAll'       => $ageAll,
    'jkKelas'      => ['labels' => $jkKelasLabels, 'L' => $jkKelasData['L'], 'P' => $jkKelasData['P']],
    'ageKelas'     => ['labels' => $jkKelasLabels, 'data' => $ageKelasData],
    'jkRombel'     => ['labels' => $jkRombelLabels, 'L' => $jkRombelDataL, 'P' => $jkRombelDataP],
    'ageRombel'    => ['labels' => $jkRombelLabels, 'data' => $ageRombelData],
    'agamaAll'     => ['labels' => $agamaLabels, 'data' => $agamaData],
    'agamaKelas'   => ['labels' => $agamaKelasLabels, 'datasets' => $agamaKelasDataset],
]);
?>

<!-- STAT CARDS -->
<div class="stat-grid">
  <div class="stat-card animate-in animate-delay-1">
    <div class="stat-icon blue"><i data-lucide="graduation-cap"></i></div>
    <div>
      <div class="stat-val" id="stat-siswa"><?= $stats['siswa'] ?></div>
      <div class="stat-label">Total Siswa</div>
      <div class="stat-trend up"><i data-lucide="trending-up"></i> Aktif</div>
    </div>
  </div>
  <div class="stat-card animate-in animate-delay-2">
    <div class="stat-icon teal"><i data-lucide="briefcase"></i></div>
    <div>
      <div class="stat-val" id="stat-ptk"><?= $stats['ptk'] ?></div>
      <div class="stat-label">Pendidik & Tenaga Kependidikan</div>
      <div class="stat-trend up"><i data-lucide="trending-up"></i> Aktif</div>
    </div>
  </div>
  <div class="stat-card animate-in animate-delay-3">
    <div class="stat-icon green"><i data-lucide="school"></i></div>
    <div>
      <div class="stat-val" id="stat-rombel"><?= $stats['rombel'] ?></div>
      <div class="stat-label">Rombongan Belajar</div>
    </div>
  </div>
  <div class="stat-card animate-in animate-delay-4">
    <div class="stat-icon orange"><i data-lucide="file-text"></i></div>
    <div>
      <div class="stat-val" id="stat-soal"><?= $stats['soal'] ?></div>
      <div class="stat-label">Bank Soal</div>
      <div class="stat-trend up"><i data-lucide="trending-up"></i> Total</div>
    </div>
  </div>
</div>

<!-- QUICK ACCESS -->
<div class="section-header animate-in animate-delay-3">
  <div>
    <h2><span class="sh-icon"><i data-lucide="zap"></i></span> Akses Cepat</h2>
    <p>Menu yang sering digunakan</p>
  </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px;margin-bottom:28px">
  <?php
  $quick = [
    ['href'=>'siswa.php',     'icon'=>'graduation-cap', 'label'=>'Data Siswa',       'color'=>'blue'],
    ['href'=>'ptk.php',       'icon'=>'briefcase',      'label'=>'Data PTK',         'color'=>'teal', 'admin'=>true],
    ['href'=>'jadwal.php',    'icon'=>'calendar-days',  'label'=>'Jadwal',            'color'=>'cyan'],
    ['href'=>'bank_soal.php', 'icon'=>'library',        'label'=>'Bank Soal',        'color'=>'orange'],
    ['href'=>'buat_soal.php', 'icon'=>'pencil-line',    'label'=>'Buat Soal Ujian',  'color'=>'green'],
    ['href'=>'nilai.php',     'icon'=>'clipboard-list', 'label'=>'Daftar Nilai',     'color'=>'pink'],
    ['href'=>'kop.php',       'icon'=>'school',         'label'=>'Profil Sekolah',   'color'=>'blue', 'admin'=>true],
    ['href'=>'settings.php',  'icon'=>'settings',       'label'=>'Pengaturan',       'color'=>'teal', 'admin'=>true],
  ];
  foreach ($quick as $q):
    if (!empty($q['admin']) && !isAdmin()) continue;
    $colors = ['blue'=>'#2563eb','teal'=>'#0d9488','cyan'=>'#0891b2','orange'=>'#ea580c','green'=>'#059669','pink'=>'#db2777'];
    $c = $colors[$q['color']] ?? '#2563eb';
  ?>
  <a href="<?= $q['href'] ?>" style="background:var(--bg-card);border-radius:12px;padding:20px 16px;text-align:center;border:1px solid var(--border);box-shadow:var(--shadow-sm);transition:all .2s;display:block;text-decoration:none;color:var(--text);" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='var(--shadow)';this.style.borderColor='<?= $c ?>'" onmouseout="this.style.transform='';this.style.boxShadow='var(--shadow-sm)';this.style.borderColor='var(--border)'">
    <div style="font-size:1.8rem;margin-bottom:8px;color:<?= $c ?>"><i data-lucide="<?= $q['icon'] ?>"></i></div>
    <div style="font-size:.78rem;font-weight:600;color:var(--text)"><?= $q['label'] ?></div>
  </a>
  <?php endforeach; ?>
</div>

<!-- CHARTS SECTION -->
<div class="section-header">
  <div>
    <h2><span class="sh-icon"><i data-lucide="bar-chart-3"></i></span> Statistik Siswa</h2>
    <p>Grafik data siswa dari database</p>
  </div>
</div>

<!-- Row 1: JK All + Age All -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
  <div class="card">
    <div class="card-header"><h3><i data-lucide="users"></i> Jenis Kelamin Seluruh</h3></div>
    <div class="card-body" style="display:flex;justify-content:center;padding:20px">
      <div style="max-width:280px;width:100%"><canvas id="chart-jk-all"></canvas></div>
    </div>
  </div>
  <div class="card">
    <div class="card-header"><h3><i data-lucide="cake"></i> Usia Seluruh Siswa</h3></div>
    <div class="card-body" style="padding:20px"><canvas id="chart-age-all"></canvas></div>
  </div>
</div>

<!-- Row 2: JK per Kelas + Age per Kelas -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
  <div class="card">
    <div class="card-header"><h3><i data-lucide="users"></i> JK per Kelas</h3></div>
    <div class="card-body" style="padding:20px"><canvas id="chart-jk-kelas"></canvas></div>
  </div>
  <div class="card">
    <div class="card-header"><h3><i data-lucide="cake"></i> Total Siswa per Kelas</h3></div>
    <div class="card-body" style="padding:20px"><canvas id="chart-age-kelas"></canvas></div>
  </div>
</div>

<!-- Row 3: JK per Rombel + Age per Rombel -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
  <div class="card">
    <div class="card-header"><h3><i data-lucide="users"></i> JK per Rombel</h3></div>
    <div class="card-body" style="padding:20px;overflow-x:auto"><canvas id="chart-jk-rombel" style="min-width:600px"></canvas></div>
  </div>
  <div class="card">
    <div class="card-header"><h3><i data-lucide="cake"></i> Total Siswa per Rombel</h3></div>
    <div class="card-body" style="padding:20px;overflow-x:auto"><canvas id="chart-age-rombel" style="min-width:600px"></canvas></div>
  </div>
</div>

<!-- Row 4: Agama All + Agama per Kelas -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
  <div class="card">
    <div class="card-header"><h3><i data-lucide="church"></i> Agama Seluruh Siswa</h3></div>
    <div class="card-body" style="display:flex;justify-content:center;padding:20px">
      <div style="max-width:300px;width:100%"><canvas id="chart-agama-all"></canvas></div>
    </div>
  </div>
  <div class="card">
    <div class="card-header"><h3><i data-lucide="church"></i> Agama per Kelas</h3></div>
    <div class="card-body" style="padding:20px"><canvas id="chart-agama-kelas"></canvas></div>
  </div>
</div>

<!-- Row 5: Bottom info -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;flex-wrap:wrap" class="animate-in animate-delay-8">
  <!-- School Info -->
  <div class="card">
    <div class="card-header">
      <h3><i data-lucide="school"></i> Informasi Sekolah</h3>
    </div>
    <div class="card-body" style="padding:16px 20px">
      <?php
      $kop = getKopSekolah();
$schoolInfo = [
  'Nama Sekolah'   => $kop['school_name'] ?? '-',
  'NPSN'           => $kop['npsn'] ?? '-',
  'NSS'            => $kop['nss']  ?? '-',
  'Akreditasi'     => $kop['akreditasi'] ?? '-',
  'Kepala Sekolah' => $kop['kepala_sekolah'] ?? '-',
  'Alamat'         => $kop['alamat'] ?? '-',
  'Email'          => $kop['email'] ?? '-',
];
      foreach ($schoolInfo as $k => $v):
      ?>
      <div style="display:flex;gap:10px;padding:7px 0;border-bottom:1px solid var(--border);font-size:.82rem">
        <span style="color:var(--text-muted);width:130px;flex-shrink:0"><?= $k ?></span>
        <span style="font-weight:500"><?= htmlspecialchars($v) ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Database Status -->
  <div class="card">
    <div class="card-header">
      <h3><i data-lucide="database"></i> Status Database</h3>
      <?php if (isAdmin()): ?>
      <a href="settings.php" class="btn btn-sm btn-outline-primary"><i data-lucide="settings"></i> Atur</a>
      <?php endif; ?>
    </div>
    <div class="card-body">
      <?php
      $dbOk = $conn->ping();
      ?>
      <div style="text-align:center;padding:20px">
        <div style="font-size:3rem;margin-bottom:12px"><?= $dbOk ? '✅' : '⚠️' ?></div>
        <div style="font-weight:700;font-size:1rem;margin-bottom:8px">
          MySQL <?= $dbOk ? '<span style="color:var(--success)">Terhubung</span>' : '<span style="color:var(--warning)">Terputus</span>' ?>
        </div>
        <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:16px">
          <?= $dbOk ? 'Data tersimpan di database lokal MySQL' : 'Periksa koneksi MySQL di XAMPP' ?>
        </p>
      </div>
      <div style="border-top:1px solid var(--border);padding-top:16px;margin-top:8px">
        <p style="font-size:.72rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">Status Penyimpanan</p>
        <?php
        $storages = [
          ['MySQL - Data Siswa',    $dbOk],
          ['MySQL - Data PTK',      $dbOk],
          ['MySQL - Bank Soal',     $dbOk],
          ['File Lokal - Foto',     is_dir(UPLOAD_DIR)],
          ['MySQL - Pengaturan',    $dbOk],
        ];
        foreach ($storages as [$name, $ok]):
        ?>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;font-size:.78rem">
          <span><?= $name ?></span>
          <span class="badge <?= $ok ? 'badge-success' : 'badge-warning' ?>"><?= $ok ? '✅ OK' : '⚠️ Offline' ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<?php
$extraJs = <<<JS
<script>
const CD = {$chartData};

// Dashboard entrance animation: stagger quick access cards
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.section-header[style*="grid-template-columns:repeat(auto-fill"] ~ div[style*="grid-template-columns"] a').forEach((card, i) => {
    card.style.opacity = '0';
    card.style.transform = 'scale(.92) translateY(12px)';
    card.style.transition = 'none';
    setTimeout(() => {
      card.style.transition = 'opacity .45s cubic-bezier(.23,1,.32,1), transform .45s cubic-bezier(.23,1,.32,1)';
      card.style.opacity = '1';
      card.style.transform = 'scale(1) translateY(0)';
    }, 100 + i * 60);
  });
});

// Color palette for charts
const COLORS = {
  blue: '#2563eb', green: '#059669', orange: '#ea580c', pink: '#db2777',
  teal: '#0d9488', cyan: '#0891b2', yellow: '#d97706', red: '#dc2626',
  indigo: '#4f46e5', emerald: '#10b981', sky: '#0284c7', rose: '#e11d48',
};
const PALETTE = ['#2563eb','#0d9488','#ea580c','#db2777','#059669','#0891b2','#d97706','#dc2626','#4f46e5','#10b981'];

// Modern chart defaults
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.font.weight = 500;
Chart.defaults.animation.duration = 1200;
Chart.defaults.animation.easing = 'easeOutQuart';
Chart.defaults.animation.delay = (ctx) => {
  if (ctx.type === 'data' && ctx.mode === 'default') return ctx.dataIndex * 80 + ctx.datasetIndex * 120;
  return 0;
};
Chart.defaults.elements.line.tension = 0.4;

const txtColor = getComputedStyle(document.documentElement).getPropertyValue('--text-muted').trim() || '#64748b';
const brdColor = getComputedStyle(document.documentElement).getPropertyValue('--border').trim() || '#e2e8f0';
Chart.defaults.color = txtColor;
Chart.defaults.borderColor = brdColor;

// Helper: create canvas gradient
function makeGradient(ctx, c1, c2) {
  const g = ctx.createLinearGradient(0, 0, 0, 300);
  g.addColorStop(0, c1);
  g.addColorStop(1, c2);
  return g;
}

// Modern tooltip config
const tooltipCfg = {
  backgroundColor: 'rgba(15,23,42,.92)',
  titleColor: '#f8fafc',
  bodyColor: '#cbd5e1',
  titleFont: { size: 13, weight: 700 },
  bodyFont: { size: 12 },
  padding: 12,
  cornerRadius: 10,
  boxPadding: 4,
  usePointStyle: true,
  displayColors: true,
};

// Modern legend config
const legendCfg = { position: 'bottom', labels: { padding: 16, usePointStyle: true, pointStyleWidth: 10, font: { size: 12, weight: 500 } } };

// 1. JK Seluruh (Doughnut) — modern doughnut with cutout
new Chart(document.getElementById('chart-jk-all'), {
  type: 'doughnut',
  data: {
    labels: ['Laki-laki', 'Perempuan'],
    datasets: [{
      data: [CD.jkAll.L, CD.jkAll.P],
      backgroundColor: [COLORS.blue, COLORS.pink],
      borderWidth: 3,
      borderColor: getComputedStyle(document.body).getPropertyValue('--bg-card').trim() || '#fff',
      borderRadius: 6,
      hoverOffset: 12,
    }]
  },
  options: {
    responsive: true,
    cutout: '62%',
    plugins: { legend: legendCfg, tooltip: tooltipCfg }
  }
});

// 2. Usia Seluruh (Line + Area Fill — cubic monotone)
(function() {
  const ctx = document.getElementById('chart-age-all').getContext('2d');
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: Object.keys(CD.ageAll).map(a => a + ' th'),
      datasets: [{
        label: 'Jumlah Siswa',
        data: Object.values(CD.ageAll),
        borderColor: COLORS.blue,
        backgroundColor: function(ctx) {
          const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
          g.addColorStop(0, 'rgba(37,99,235,.25)');
          g.addColorStop(1, 'rgba(37,99,235,.02)');
          return g;
        },
        fill: true,
        tension: 0.4,
        cubicInterpolationMode: 'monotone',
        borderWidth: 3,
        pointRadius: 5,
        pointHoverRadius: 8,
        pointBackgroundColor: '#fff',
        pointBorderColor: COLORS.blue,
        pointBorderWidth: 2,
      }]
    },
    options: {
      responsive: true,
      interaction: { intersect: false, mode: 'index' },
      plugins: { legend: { display: false }, tooltip: tooltipCfg },
      scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.04)' }, ticks: { font: { size: 11 } } }, x: { grid: { display: false }, ticks: { font: { size: 11 } } } }
    }
  });
})();

// 3. JK per Kelas (Grouped Bar) — with gradient fills
(function() {
  const ctx = document.getElementById('chart-jk-kelas').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: CD.jkKelas.labels.map(k => 'Kelas ' + k),
      datasets: [
        { label: 'Laki-laki', data: CD.jkKelas.L, backgroundColor: makeGradient(ctx, COLORS.blue, '#93c5fd'), borderRadius: 8, borderSkipped: false, barPercentage: 0.65, hoverBackgroundColor: COLORS.blue },
        { label: 'Perempuan', data: CD.jkKelas.P, backgroundColor: makeGradient(ctx, COLORS.pink, '#f9a8d4'), borderRadius: 8, borderSkipped: false, barPercentage: 0.65, hoverBackgroundColor: COLORS.pink }
      ]
    },
    options: {
      responsive: true,
      plugins: { legend: legendCfg, tooltip: tooltipCfg },
      scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.04)' }, ticks: { font: { size: 11 } } }, x: { grid: { display: false }, ticks: { font: { size: 11 } } } }
    }
  });
})();

// 4. Total Siswa per Kelas (Line + Area Fill — cubic monotone)
(function() {
  const ctx = document.getElementById('chart-age-kelas').getContext('2d');
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: CD.ageKelas.labels.map(k => 'Kelas ' + k),
      datasets: [{
        label: 'Jumlah Siswa',
        data: CD.ageKelas.data,
        segment: {
          borderColor: function(ctx) { return PALETTE[ctx.p0DataIndex % PALETTE.length]; },
        },
        backgroundColor: function(ctx) {
          const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
          g.addColorStop(0, 'rgba(13,148,136,.25)');
          g.addColorStop(1, 'rgba(13,148,136,.02)');
          return g;
        },
        fill: true,
        tension: 0.4,
        cubicInterpolationMode: 'monotone',
        borderWidth: 3,
        pointRadius: 6,
        pointHoverRadius: 9,
        pointBackgroundColor: '#fff',
        pointBorderColor: COLORS.teal,
        pointBorderWidth: 2.5,
      }]
    },
    options: {
      responsive: true,
      interaction: { intersect: false, mode: 'index' },
      plugins: { legend: { display: false }, tooltip: tooltipCfg },
      scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.04)' }, ticks: { font: { size: 11 } } }, x: { grid: { display: false }, ticks: { font: { size: 11 } } } }
    }
  });
})();

// 5. JK per Rombel (Stacked Bar) — with gradient
(function() {
  const ctx = document.getElementById('chart-jk-rombel').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: CD.jkRombel.labels,
      datasets: [
        { label: 'Laki-laki', data: CD.jkRombel.L, backgroundColor: makeGradient(ctx, COLORS.blue, '#60a5fa'), borderRadius: {topLeft: 6, topRight: 6}, borderSkipped: false, hoverBackgroundColor: COLORS.blue },
        { label: 'Perempuan', data: CD.jkRombel.P, backgroundColor: makeGradient(ctx, COLORS.pink, '#f472b6'), borderRadius: {topLeft: 6, topRight: 6}, borderSkipped: false, hoverBackgroundColor: COLORS.pink }
      ]
    },
    options: {
      responsive: true,
      plugins: { legend: legendCfg, tooltip: tooltipCfg },
      scales: { x: { stacked: true, grid: { display: false }, ticks: { font: { size: 11 } } }, y: { stacked: true, beginAtZero: true, grid: { color: 'rgba(0,0,0,.04)' }, ticks: { font: { size: 11 } } } }
    }
  });
})();

// 6. Total Siswa per Rombel (Line + Area Fill — cubic monotone)
(function() {
  const ctx = document.getElementById('chart-age-rombel').getContext('2d');
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: CD.ageRombel.labels,
      datasets: [{
        label: 'Jumlah Siswa',
        data: CD.ageRombel.data,
        borderColor: COLORS.orange,
        backgroundColor: function(ctx) {
          const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
          g.addColorStop(0, 'rgba(234,88,12,.20)');
          g.addColorStop(1, 'rgba(234,88,12,.02)');
          return g;
        },
        fill: true,
        tension: 0.4,
        cubicInterpolationMode: 'monotone',
        borderWidth: 3,
        pointRadius: 5,
        pointHoverRadius: 8,
        pointBackgroundColor: '#fff',
        pointBorderColor: COLORS.orange,
        pointBorderWidth: 2,
      }]
    },
    options: {
      responsive: true,
      interaction: { intersect: false, mode: 'index' },
      plugins: { legend: { display: false }, tooltip: tooltipCfg },
      scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.04)' }, ticks: { font: { size: 11 } } }, x: { grid: { display: false }, ticks: { font: { size: 11 } } } }
    }
  });
})();

// 7. Agama Seluruh (Doughnut) — modern with cutout
new Chart(document.getElementById('chart-agama-all'), {
  type: 'doughnut',
  data: {
    labels: CD.agamaAll.labels,
    datasets: [{
      data: CD.agamaAll.data,
      backgroundColor: PALETTE.slice(0, CD.agamaAll.labels.length),
      borderWidth: 3,
      borderColor: getComputedStyle(document.body).getPropertyValue('--bg-card').trim() || '#fff',
      borderRadius: 4,
      hoverOffset: 10,
    }]
  },
  options: {
    responsive: true,
    cutout: '58%',
    plugins: { legend: legendCfg, tooltip: tooltipCfg }
  }
});

// 8. Agama per Kelas (Multi-Line — cubic monotone, colorful points)
const agamaDatasets = Object.entries(CD.agamaKelas.datasets).map(([agama, data], i) => ({
  label: agama, data,
  borderColor: PALETTE[i % PALETTE.length],
  backgroundColor: PALETTE[i % PALETTE.length],
  pointBackgroundColor: PALETTE[i % PALETTE.length],
  pointBorderColor: PALETTE[i % PALETTE.length],
  pointRadius: 4,
  pointHoverRadius: 7,
  pointBorderWidth: 1.5,
  borderWidth: 2.5,
  tension: 0.4,
  cubicInterpolationMode: 'monotone',
  fill: false,
}));
new Chart(document.getElementById('chart-agama-kelas'), {
  type: 'line',
  data: { labels: CD.agamaKelas.labels.map(k => 'Kelas ' + k), datasets: agamaDatasets },
  options: {
    responsive: true,
    interaction: { intersect: false, mode: 'index' },
    plugins: { legend: { ...legendCfg, labels: { ...legendCfg.labels, font: { size: 11, weight: 500 }, usePointStyle: true, pointStyle: 'circle' } }, tooltip: tooltipCfg },
    scales: { x: { grid: { display: false }, ticks: { font: { size: 11 } } }, y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.04)' }, ticks: { font: { size: 11 } } } }
  }
});

// Initial entrance animation: disable animation on creation, then trigger clean entrance
// This replicates the exact same pattern as the theme toggle
setTimeout(() => {
  Object.values(Chart.instances).forEach(c => {
    c.options.animation = false;
    c.update('none');
  });
  // After charts are rendered at zero without animation, animate them in
  requestAnimationFrame(() => {
    Object.values(Chart.instances).forEach(c => {
      c.reset();
      c.options.animation = { duration: 1200, easing: 'easeOutQuart', delay: (ctx) => {
        if (ctx.type === 'data' && ctx.mode === 'default') return ctx.dataIndex * 80 + ctx.datasetIndex * 120;
        return 0;
      }};
      c.update();
    });
  });
}, 100);

// Theme-reactive: update chart colors on theme toggle with animation
const origToggle = window.toggleTheme;
window.toggleTheme = function() {
  origToggle();
  setTimeout(() => {
    const t = getComputedStyle(document.documentElement).getPropertyValue('--text-muted').trim();
    const b = getComputedStyle(document.documentElement).getPropertyValue('--border').trim();
    Chart.defaults.color = t || '#64748b';
    Chart.defaults.borderColor = b || '#e2e8f0';
    Chart.instances && Object.values(Chart.instances).forEach(c => {
      c.reset();
      c.update();
    });
  }, 50);
};
</script>
JS;
?>

<?php include 'includes/footer.php'; ?>
