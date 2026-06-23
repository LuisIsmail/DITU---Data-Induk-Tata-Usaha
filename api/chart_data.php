<?php
/**
 * API: Chart Data Siswa
 * Returns aggregated student statistics for dashboard charts.
 */
require_once '../config.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$conn = dbConnect();
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database tidak terhubung']);
    exit;
}

try {
    // 1. Gender distribution
    $jkRows = dbFetch("SELECT jk, COUNT(*) as total FROM DataSiswa GROUP BY jk");
    $genderAll = ['L' => 0, 'P' => 0];
    foreach ($jkRows as $r) {
        $key = strtoupper(trim($r['jk'] ?? ''));
        if ($key === 'L' || $key === 'LAKI-LAKI') {
            $genderAll['L'] += (int)$r['total'];
        } elseif ($key === 'P' || $key === 'PEREMPUAN') {
            $genderAll['P'] += (int)$r['total'];
        }
    }

    // 2. Age distribution
    $ageData = dbFetch("SELECT TIMESTAMPDIFF(YEAR, tgl_lahir, CURDATE()) as usia FROM DataSiswa WHERE tgl_lahir IS NOT NULL AND tgl_lahir != '0000-00-00'");
    $ageBuckets = [];
    foreach ($ageData as $r) {
        $usia = (int)$r['usia'];
        if ($usia >= 5 && $usia <= 18) {
            $ageBuckets[$usia] = ($ageBuckets[$usia] ?? 0) + 1;
        }
    }
    ksort($ageBuckets);

    // 3. Gender + Age per Kelas
    $kelasRows = dbFetch("SELECT SUBSTRING(rombel, 1, 1) as kelas, jk, TIMESTAMPDIFF(YEAR, tgl_lahir, CURDATE()) as usia FROM DataSiswa WHERE rombel IS NOT NULL AND rombel != '' AND tgl_lahir IS NOT NULL AND tgl_lahir != '0000-00-00'");
    $perKelas = [];
    for ($k = 1; $k <= 6; $k++) {
        $perKelas[(string)$k] = ['L' => 0, 'P' => 0, 'usia' => []];
    }
    foreach ($kelasRows as $r) {
        $k = trim($r['kelas'] ?? '');
        if (!isset($perKelas[$k])) continue;
        $jk = strtoupper(trim($r['jk'] ?? ''));
        if ($jk === 'L' || $jk === 'LAKI-LAKI') $perKelas[$k]['L']++;
        elseif ($jk === 'P' || $jk === 'PEREMPUAN') $perKelas[$k]['P']++;
        $usia = (int)$r['usia'];
        if ($usia >= 5 && $usia <= 18) $perKelas[$k]['usia'][$usia] = ($perKelas[$k]['usia'][$usia] ?? 0) + 1;
    }
    foreach ($perKelas as $k => &$v) ksort($v['usia']);
    unset($v);

    // 4. Gender + Age per Rombel
    $rombelRows = dbFetch("SELECT UPPER(TRIM(rombel)) as rombel, jk, TIMESTAMPDIFF(YEAR, tgl_lahir, CURDATE()) as usia FROM DataSiswa WHERE rombel IS NOT NULL AND rombel != '' AND tgl_lahir IS NOT NULL AND tgl_lahir != '0000-00-00'");
    $perRombel = [];
    foreach ($rombelRows as $r) {
        $rb = trim($r['rombel'] ?? '');
        if (!$rb) continue;
        if (!isset($perRombel[$rb])) $perRombel[$rb] = ['L' => 0, 'P' => 0, 'usia' => []];
        $jk = strtoupper(trim($r['jk'] ?? ''));
        if ($jk === 'L' || $jk === 'LAKI-LAKI') $perRombel[$rb]['L']++;
        elseif ($jk === 'P' || $jk === 'PEREMPUAN') $perRombel[$rb]['P']++;
        $usia = (int)$r['usia'];
        if ($usia >= 5 && $usia <= 18) $perRombel[$rb]['usia'][$usia] = ($perRombel[$rb]['usia'][$usia] ?? 0) + 1;
    }
    uksort($perRombel, 'strnatcmp');
    foreach ($perRombel as $rb => &$v) ksort($v['usia']);
    unset($v);

    // 5. Total
    $totalSiswa = dbCount('DataSiswa');

    echo json_encode([
        'success'     => true,
        'total_siswa' => (int)$totalSiswa,
        'gender_all'  => $genderAll,
        'age_all'     => $ageBuckets,
        'per_kelas'   => $perKelas,
        'per_rombel'  => $perRombel,
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
