<?php
session_start();
include '../../konfigurasi/koneksi.php';

// Proteksi admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

// Ambil tahun saat ini
$tahun = date('Y');

// Statistik: Kunjungan per Bulan (tahun ini)
$bulan_list = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];

$kunjungan_per_bulan = [];
$max_kunjungan = 0;
$bulan_teraktif = '';

foreach ($bulan_list as $bulan_num => $bulan_nama) {
    $query = "
        SELECT COUNT(*) as total 
        FROM pendaftaran 
        WHERE YEAR(tanggal) = $tahun 
        AND MONTH(tanggal) = $bulan_num 
        AND status = 'selesai'
    ";
    $result = mysqli_query($conn, $query);
    $total = 0;
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $total = (int)$row['total'];
    }
    $kunjungan_per_bulan[$bulan_nama] = $total;
    
    // Cari bulan teraktif
    if ($total > $max_kunjungan) {
        $max_kunjungan = $total;
        $bulan_teraktif = $bulan_nama;
    }
}

// Statistik: Pasien per Poli
$query_poli = "
    SELECT 
        poli.nama AS nama_poli,
        COUNT(pendaftaran.id) as total_kunjungan
    FROM poli
    LEFT JOIN pendaftaran ON poli.id = pendaftaran.poli_id AND pendaftaran.status = 'selesai'
    WHERE poli.status = 'active'
    GROUP BY poli.id, poli.nama
    ORDER BY total_kunjungan DESC
";
$result_poli = mysqli_query($conn, $query_poli);
$stat_poli = [];
$total_kunjungan_semua = 0;
if ($result_poli) {
    while ($row = mysqli_fetch_assoc($result_poli)) {
        $stat_poli[] = $row;
        $total_kunjungan_semua += (int)$row['total_kunjungan'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Klinik Sehat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        a { text-decoration: none; color: inherit; }
        :root {
            --primary: #2563eb;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --gray: #64748b;
            --bg: #f8fafc;
            --white: #ffffff;
            --dark: #1e293b;
            --shadow: 0 4px 12px rgba(0,0,0,.08);
        }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: var(--bg);
            color: var(--dark);
        }

        .header {
            background: var(--white);
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .header-wrap {
            max-width: 1400px;
            margin: auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            display: flex;
            gap: .75rem;
            align-items: center;
        }
        .logo i { font-size: 1.8rem; color: var(--primary); }
        .logo h1 { font-size: 1.2rem; }
        .logo p { font-size: .75rem; color: var(--gray); }

        .user-area {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .user-info { text-align: right; }
        .user-info .name { font-weight: 600; font-size: .9rem; }
        .user-info .role { font-size: .75rem; color: var(--gray); }

        .avatar {
            width: 42px; height: 42px;
            border-radius: 50%;
            background: var(--primary);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logout-circle {
            width: 42px; height: 42px;
            border-radius: 50%;
            background: rgba(239,68,68,.12);
            color: var(--danger);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: .25s;
        }
        .logout-circle:hover {
            background: var(--danger);
            color: #fff;
        }

        .container {
            max-width: 1400px;
            margin: auto;
            padding: 2rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .page-title h2 {
            font-size: 1.75rem;
            font-weight: 700;
        }
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--white);
            color: var(--dark);
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-weight: 600;
        }
        .btn-back:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .card {
            background: var(--white);
            border-radius: 14px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }
        .card-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        .card-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }
        .card h3 {
            font-size: 1.125rem;
            color: var(--dark);
        }

        /* Deskripsi */
        .card-description {
            color: var(--gray);
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        /* Statistik Ringkasan */
        .summary-stats {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        .summary-item {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 10px;
            min-width: 180px;
        }
        .summary-label {
            font-size: 0.875rem;
            color: var(--gray);
            margin-bottom: 0.25rem;
        }
        .summary-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }

        /* Tabel */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        th {
            font-weight: 600;
            color: var(--gray);
            font-size: 0.875rem;
            text-transform: uppercase;
        }
        tr:last-child td {
            border-bottom: none;
        }
        .text-right {
            text-align: right;
        }

        /* Highlight bulan teraktif */
        .highlight {
            background-color: rgba(37, 99, 235, 0.05);
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .container { padding: 1rem; }
            .user-info { display: none; }
            th, td { padding: 0.75rem 0.5rem; font-size: 0.875rem; }
            .summary-stats { flex-direction: column; }
        }
    </style>
</head>
<body>

<header class="header">
    <div class="header-wrap">
        <div class="logo">
            <i class="fas fa-hospital"></i>
            <div>
                <h1>Klinik Sehat</h1>
                <p>Sistem Manajemen Klinik</p>
            </div>
        </div>
        <div class="user-area">
            <div class="user-info">
                <div class="name"><?= ucfirst($_SESSION['role']) ?></div>
                <div class="role">Administrator</div>
            </div>
            <div class="avatar"><i class="fas fa-user"></i></div>
            <a href="../../auth/logout.php" class="logout-circle" title="Logout">
                <i class="fas fa-right-from-bracket"></i>
            </a>
        </div>
    </div>
</header>

<main class="container">
    <div class="page-header">
        <div class="page-title">
            <h2>Laporan Statistik Klinik</h2>
        </div>
        <a href="../Dashboard/dashboard.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>

    <!-- Laporan Kunjungan per Bulan -->
    <div class="card">
        <div class="card-header">
            <div class="card-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <h3>Kunjungan Pasien Tahun <?= $tahun ?></h3>
        </div>
        
        <p class="card-description">
            Data kunjungan pasien yang telah selesai dilayani selama tahun <?= $tahun ?>.
            <?php if ($bulan_teraktif): ?>
                <strong>Bulan teraktif: <?= $bulan_teraktif ?> (<?= number_format($max_kunjungan) ?> kunjungan)</strong>.
            <?php endif; ?>
        </p>

        <div class="summary-stats">
            <div class="summary-item">
                <div class="summary-label">Total Kunjungan Tahun Ini</div>
                <div class="summary-value"><?= number_format(array_sum($kunjungan_per_bulan)) ?></div>
            </div>
            <?php if ($bulan_teraktif): ?>
            <div class="summary-item">
                <div class="summary-label">Bulan Teraktif</div>
                <div class="summary-value"><?= $bulan_teraktif ?></div>
            </div>
            <?php endif; ?>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Bulan</th>
                    <th class="text-right">Jumlah Kunjungan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($kunjungan_per_bulan as $bulan => $jumlah): ?>
                    <tr <?= ($bulan == $bulan_teraktif) ? 'class="highlight"' : '' ?>>
                        <td><?= htmlspecialchars($bulan) ?></td>
                        <td class="text-right"><?= number_format($jumlah) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Laporan Pasien per Poli -->
    <div class="card">
        <div class="card-header">
            <div class="card-icon">
                <i class="fas fa-clinic-medical"></i>
            </div>
            <h3>Distribusi Kunjungan per Poliklinik</h3>
        </div>
        
        <p class="card-description">
            Rincian kunjungan pasien berdasarkan poliklinik sejak awal pencatatan.
            Total keseluruhan: <strong><?= number_format($total_kunjungan_semua) ?> kunjungan</strong>.
        </p>

        <table>
            <thead>
                <tr>
                    <th>Poliklinik</th>
                    <th class="text-right">Total Kunjungan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($stat_poli)): ?>
                    <?php foreach ($stat_poli as $poli): ?>
                        <tr>
                            <td><?= htmlspecialchars($poli['nama_poli']) ?></td>
                            <td class="text-right"><?= number_format((int)$poli['total_kunjungan']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" style="text-align: center; color: var(--gray);">Belum ada data kunjungan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

</body>
</html>