<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

include '../../konfigurasi/koneksi.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "ID poli tidak valid!";
    header("Location: ../Poli/poli.php");
    exit;
}

$poli_id = (int)$_GET['id'];

// Ambil data poli
$poli_query = mysqli_query($conn, "SELECT id, nama, status FROM poli WHERE id = $poli_id AND status = 'active' LIMIT 1");
if (!$poli_query || mysqli_num_rows($poli_query) === 0) {
    $_SESSION['error_message'] = "Poli tidak ditemukan atau tidak aktif!";
    header("Location: ../Poli/poli.php");
    exit;
}
$poli = mysqli_fetch_assoc($poli_query);

// Hitung statistik pasien (konsisten dengan poli.php)
$total_kunjungan = 0;
$total_pasien_unik = 0;

$stat_query = "
    SELECT 
        COUNT(*) as total_kunjungan,
        COUNT(DISTINCT pasien_id) as total_pasien_unik
    FROM pendaftaran
    WHERE poli_id = $poli_id AND status = 'selesai'
";
$stat_result = mysqli_query($conn, $stat_query);
if ($stat_result && mysqli_num_rows($stat_result) > 0) {
    $stat = mysqli_fetch_assoc($stat_result);
    $total_kunjungan = (int)$stat['total_kunjungan'];
    $total_pasien_unik = (int)$stat['total_pasien_unik'];
}

// Ambil dokter dan jadwal
$dokter_query = "
    SELECT DISTINCT d.id, d.nama
    FROM dokter d
    JOIN jadwal_dokter j ON d.id = j.dokter_id
    WHERE j.poli_id = $poli_id AND d.status = 'active'
    ORDER BY d.nama ASC
";
$dokter_list = mysqli_query($conn, $dokter_query);

$jadwal_per_dokter = [];
if ($dokter_list && mysqli_num_rows($dokter_list) > 0) {
    while ($dokter = mysqli_fetch_assoc($dokter_list)) {
        $dokter_id = $dokter['id'];
        $jadwal_query = "
            SELECT hari, jam_mulai, jam_selesai
            FROM jadwal_dokter
            WHERE dokter_id = $dokter_id AND poli_id = $poli_id
            ORDER BY FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')
        ";
        $jadwal_result = mysqli_query($conn, $jadwal_query);
        $jadwal = [];
        if ($jadwal_result) {
            while ($j = mysqli_fetch_assoc($jadwal_result)) {
                $jadwal[] = $j;
            }
        }
        $jadwal_per_dokter[] = [
            'dokter' => $dokter,
            'jadwal' => $jadwal
        ];
    }
}

// Ambil daftar pasien yang pernah selesai dilayani di poli ini
$daftar_pasien = mysqli_query($conn, "
    SELECT 
        p.tanggal,
        p.no_antrian,
        pasien.nama AS nama_pasien,
        pasien.no_rm
    FROM pendaftaran p
    JOIN pasien ON p.pasien_id = pasien.id
    WHERE p.poli_id = $poli_id AND p.status = 'selesai'
    ORDER BY p.tanggal DESC, p.no_antrian ASC
");

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Poli - <?= htmlspecialchars($poli['nama']) ?> - Klinik Sehat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ... (SELURUH CSS TETAP SAMA SEPERTI YANG KAMU MILIKI) ... */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #64748b;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --light: #f8fafc;
            --white: #ffffff;
            --text-dark: #1e293b;
            --text-gray: #64748b;
            --border: #e2e8f0;
            --shadow: 0 1px 3px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 25px rgba(0,0,0,0.1);
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--light);
            color: var(--text-dark);
            line-height: 1.6;
        }
        .header {
            background: var(--white);
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .logo i {
            font-size: 1.75rem;
            color: var(--primary);
        }
        .logo-text h1 {
            font-size: 1.25rem;
            color: var(--text-dark);
            font-weight: 700;
        }
        .logo-text p {
            font-size: 0.75rem;
            color: var(--text-gray);
        }
        .user-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .user-info {
            text-align: right;
        }
        .user-info .name {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-dark);
        }
        .user-info .role {
            font-size: 0.75rem;
            color: var(--text-gray);
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .page-title h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.25rem;
        }
        .page-subtitle {
            font-size: 1rem;
            color: var(--text-gray);
            font-weight: 500;
        }
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--white);
            color: var(--text-dark);
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        .btn-back:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        .card {
            background: var(--white);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }
        .card-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--light);
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
            font-size: 1.25rem;
        }
        .card-header h3 {
            font-size: 1.125rem;
            color: var(--text-dark);
        }

        /* Statistik dalam card */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1.5rem;
            margin-top: 1rem;
        }
        .stat-item {
            background: var(--light);
            border-radius: 10px;
            padding: 1.25rem;
            text-align: center;
        }
        .stat-number {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }
        .stat-label {
            font-size: 0.875rem;
            color: var(--text-gray);
        }

        .doctor-schedule {
            margin-bottom: 2rem;
        }
        .doctor-name {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-dark);
            margin: 1rem 0 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px dashed var(--border);
        }
        .schedule-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .schedule-item {
            display: flex;
            gap: 1rem;
            padding: 0.5rem 0;
        }
        .schedule-day {
            width: 100px;
            font-weight: 600;
            color: var(--primary);
        }
        .schedule-time {
            color: var(--text-gray);
        }
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: var(--text-gray);
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        @media (max-width: 768px) {
            .header-container { padding: 1rem; }
            .container { padding: 1rem; }
            .page-header { flex-direction: column; align-items: flex-start; }
            .user-info { display: none; }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-container">
            <div class="logo">
                <i class="fas fa-hospital"></i>
                <div class="logo-text">
                    <h1>Klinik Sehat</h1>
                    <p>Sistem Manajemen Klinik</p>
                </div>
            </div>
            <div class="user-section">
                <div class="user-info">
                    <div class="name"><?= ucfirst($_SESSION['role']) ?></div>
                    <div class="role">Administrator</div>
                </div>
                <div class="user-avatar"><i class="fas fa-user"></i></div>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="page-header">
            <div class="page-title">
                <h2>Detail Poliklinik</h2>
                <div class="page-subtitle"><?= htmlspecialchars($poli['nama']) ?></div>
            </div>
            <a href="../Poli/poli.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Kembali ke Data Poli
            </a>
        </div>

        <!-- Jadwal Praktik -->
        <div class="card">
            <div class="card-header">
                <div class="card-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h3>Jadwal Praktik Dokter</h3>
            </div>

            <?php if (!empty($jadwal_per_dokter)): ?>
                <?php foreach ($jadwal_per_dokter as $item): 
                    $dokter = $item['dokter'];
                    $jadwal = $item['jadwal'];
                ?>
                    <div class="doctor-schedule">
                        <div class="doctor-name"><?= htmlspecialchars($dokter['nama']) ?></div>
                        <?php if (!empty($jadwal)): ?>
                            <div class="schedule-list">
                                <?php foreach ($jadwal as $j): ?>
                                    <div class="schedule-item">
                                        <div class="schedule-day"><?= htmlspecialchars($j['hari']) ?></div>
                                        <div class="schedule-time">
                                            <?= htmlspecialchars($j['jam_mulai']) ?> – <?= htmlspecialchars($j['jam_selesai']) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="empty-state" style="padding: 0.5rem 0; color: var(--text-gray);">Belum ada jadwal.</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <p>Tidak ada dokter yang bertugas di poli ini.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Statistik Pasien (menggantikan "Antrian Hari Ini") -->
      <!-- Daftar Pasien Detail -->
<div class="card">
    <div class="card-header">
        <div class="card-icon">
            <i class="fas fa-user-injured"></i>
        </div>
        <h3>Riwayat Pasien</h3>
    </div>

    <?php if ($daftar_pasien && mysqli_num_rows($daftar_pasien) > 0): ?>
        <div style="max-height: 400px; overflow-y: auto;">
            <?php while ($p = mysqli_fetch_assoc($daftar_pasien)): ?>
                <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border-bottom: 1px solid var(--border);">
                    <div style="font-weight: bold; color: var(--primary); min-width: 70px;">
                        <?= htmlspecialchars($p['no_antrian']) ?>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600;"><?= htmlspecialchars($p['nama_pasien']) ?></div>
                        <div style="font-size: 0.875rem; color: var(--text-gray);">
                            No. RM: <?= htmlspecialchars($p['no_rm']) ?> | 
                            Tanggal: <?= date('d M Y', strtotime($p['tanggal'])) ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-user-injured"></i>
            <p>Belum ada pasien yang selesai dilayani di poli ini.</p>
        </div>
    <?php endif; ?>
</div>
    </main>
</body>
</html>