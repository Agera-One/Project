<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

include '../../konfigurasi/koneksi.php';

// Ambil semua poli aktif (tanpa kolom deskripsi)
$poli_list = mysqli_query($conn, "SELECT id, nama, status FROM poli ORDER BY nama ASC");

// Hitung statistik global
$total_poli = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM poli"));
$active_poli = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM poli WHERE status = 'active'"));
$total_dokter = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM dokter WHERE status = 'active'"));
$total_pasien = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pendaftaran WHERE status = 'selesai'"))['total'];

function getRingkasanJadwalByPoli($conn, $poli_id) {
    // Ambil semua jadwal untuk poli ini
    $query = "
        SELECT hari, jam_mulai, jam_selesai
        FROM jadwal_dokter
        WHERE poli_id = $poli_id
        ORDER BY FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')
    ";
    $result = mysqli_query($conn, $query);
    $jadwal = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $jadwal[] = [
                'hari' => $row['hari'],
                'jam_mulai' => $row['jam_mulai'],
                'jam_selesai' => $row['jam_selesai']
            ];
        }
    }

    if (empty($jadwal)) {
        return ['Belum ada jadwal'];
    }

    // Kelompokkan hari dengan jam yang sama
    $grup = [];
    foreach ($jadwal as $j) {
        $key = $j['jam_mulai'] . '-' . $j['jam_selesai'];
        if (!isset($grup[$key])) {
            $grup[$key] = ['jam' => $j['jam_mulai'] . ' - ' . $j['jam_selesai'], 'hari' => []];
        }
        $grup[$key]['hari'][] = $j['hari'];
    }

    // Gabungkan hari berurutan (sederhana: tidak full logic merge, tapi cukup untuk tampilan)
    $hasil = [];
    foreach ($grup as $g) {
        $hari_list = $g['hari'];
        // Urutkan sesuai urutan minggu
        $urutan_hari = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];
        usort($hari_list, function($a, $b) use ($urutan_hari) {
            return array_search($a, $urutan_hari) - array_search($b, $urutan_hari);
        });

        // Sederhanakan: jika semua hari kerja, tulis "Senin–Jumat"
        if (count($hari_list) >= 5 && 
            in_array('Senin', $hari_list) &&
            in_array('Selasa', $hari_list) &&
            in_array('Rabu', $hari_list) &&
            in_array('Kamis', $hari_list) &&
            in_array('Jumat', $hari_list)) {
            $text_hari = 'Senin - Jumat';
            if (in_array('Sabtu', $hari_list)) $text_hari .= ', Sabtu';
            if (in_array('Minggu', $hari_list)) $text_hari .= ', Minggu';
        } else {
            $text_hari = implode(', ', $hari_list);
        }

        $hasil[] = $text_hari . ': ' . $g['jam'];
    }

    return $hasil;
}

// Fungsi untuk mengambil dokter per poli
function getDokterByPoli($conn, $poli_id) {
    $query = "
        SELECT DISTINCT d.id, d.nama 
        FROM dokter d
        JOIN jadwal_dokter j ON d.id = j.dokter_id
        WHERE j.poli_id = $poli_id AND d.status = 'active'
        ORDER BY d.nama ASC
    ";
    $result = mysqli_query($conn, $query);
    $dokter = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $dokter[] = $row;
        }
    }
    return $dokter;
}

// Fungsi untuk menghitung total pasien per poli
function getTotalPasienByPoli($conn, $poli_id) {
    $query = "
        SELECT COUNT(*) as total
        FROM pendaftaran
        WHERE poli_id = $poli_id AND status = 'selesai'
    ";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return (int)$row['total'];
    }
    return 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Poli - Klinik Sehat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
     <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .page-title i {
            font-size: 2rem;
            color: var(--primary);
        }

        .page-title h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-dark);
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .stat-icon.orange {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .stat-icon.green {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .stat-icon.blue {
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary);
        }

        .stat-icon.purple {
            background: rgba(139, 92, 246, 0.1);
            color: #8b5cf6;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--text-gray);
        }

        .action-bar {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .search-box {
            flex: 1;
            min-width: 250px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-gray);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .poli-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        .poli-card {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .poli-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .poli-header {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .poli-icon-wrapper {
            width: 70px;
            height: 70px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            flex-shrink: 0;
        }

        .poli-icon-wrapper.blue {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: var(--white);
        }

        .poli-icon-wrapper.green {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: var(--white);
        }

        .poli-icon-wrapper.purple {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: var(--white);
        }

        .poli-icon-wrapper.orange {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: var(--white);
        }

        .poli-icon-wrapper.cyan {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            color: var(--white);
        }

        .poli-icon-wrapper.red {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: var(--white);
        }

        .poli-info {
            flex: 1;
        }

        .poli-name {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.25rem;
        }

        .poli-code {
            font-size: 0.75rem;
            color: var(--text-gray);
            font-family: monospace;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .status-badge.active {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .status-badge.inactive {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .poli-description {
            font-size: 0.875rem;
            color: var(--text-gray);
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .poli-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-item {
            background: var(--light);
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
        }

        .stat-item-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.25rem;
        }

        .stat-item-label {
            font-size: 0.75rem;
            color: var(--text-gray);
        }

        .doctor-section {
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.75rem;
        }

        .doctor-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .doctor-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background: var(--light);
            border-radius: 8px;
            font-size: 0.875rem;
        }

        .doctor-avatar-sm {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: var(--primary);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.75rem;
            flex-shrink: 0;
        }

        .doctor-name-sm {
            flex: 1;
            color: var(--text-dark);
            font-weight: 500;
        }

        .schedule-info {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .schedule-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.875rem;
        }

        .schedule-row i {
            width: 20px;
            color: var(--primary);
            text-align: center;
        }

        .schedule-row span {
            color: var(--text-gray);
        }

        .poli-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-sm {
            flex: 1;
            padding: 0.625rem 1rem;
            font-size: 0.75rem;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary);
            color: var(--white);
        }

        .btn-success {
            background: var(--success);
            color: var(--white);
        }

        .btn-success:hover {
            background: #059669;
        }

        @media (max-width: 768px) {
            .header-container {
                padding: 1rem;
            }

            .container {
                padding: 1rem;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .action-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                width: 100%;
            }

            .poli-grid {
                grid-template-columns: 1fr;
            }

            .user-info {
                display: none;
            }
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
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
            </div>
        </div>
    </header>

    <?php if (isset($_SESSION['success_message'])): ?>
    <div style="max-width: 1200px; margin: 0 auto 2rem; padding: 1rem; background: rgba(16, 185, 129, 0.1); color: var(--success); border-radius: 8px;">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success_message']) ?>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div style="max-width: 1200px; margin: 0 auto 2rem; padding: 1rem; background: rgba(239, 68, 68, 0.1); color: var(--danger); border-radius: 8px;">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error_message']) ?>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

    <main class="container">
        <div class="page-header">
            <div class="page-title">
                <i class="fas fa-clinic-medical"></i>
                <h2>Data Poliklinik</h2>
            </div>
            <a href="../Dashboard/dashboard.php" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Kembali ke Dashboard
            </a>
        </div>

        <!-- Statistik Global -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon orange">
                        <i class="fas fa-clinic-medical"></i>
                    </div>
                </div>
                <div class="stat-number"><?= $total_poli ?></div>
                <div class="stat-label">Total Poliklinik</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon green">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-number"><?= $active_poli ?></div>
                <div class="stat-label">Poli Aktif</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon blue">
                        <i class="fas fa-user-md"></i>
                    </div>
                </div>
                <div class="stat-number"><?= $total_dokter ?></div>
                <div class="stat-label">Total Dokter</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon purple">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stat-number"><?= $total_pasien ?></div>
                <div class="stat-label">Total Pasien</div>
            </div>
        </div>

      <div class="action-bar">
    <div style="display: flex; gap: 1rem; flex-wrap: wrap; width: 100%;">
        <div class="search-box" style="flex: 1; min-width: 200px;">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Cari nama poliklinik..." id="searchInput">
        </div>
           <a href="../Poli/tambah_poli.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah Poliklinik
    </a>
</div>
    </div>
 

        <!-- Daftar Poli Dinamis -->
        <div class="poli-grid" id="poliGrid">
            <?php if (mysqli_num_rows($poli_list) > 0): ?>
                <?php while ($poli = mysqli_fetch_assoc($poli_list)): 
                    $ringkasan_jadwal = getRingkasanJadwalByPoli($conn, $poli['id']); ?>
                    <?php
                    $dokter_list = getDokterByPoli($conn, $poli['id']);
                    $jumlah_dokter = count($dokter_list);
                    $total_pasien_poli = getTotalPasienByPoli($conn, $poli['id']);

                    $colors = ['blue', 'green', 'purple', 'orange', 'cyan', 'red'];
                    $color = $colors[abs(crc32($poli['nama'])) % count($colors)];
                    ?>
                    <div class="poli-card" data-nama="<?= strtolower(htmlspecialchars($poli['nama'])) ?>">
                        <div class="poli-header">
                            <div class="poli-icon-wrapper <?= $color ?>">
                                <i class="fas fa-clinic-medical"></i>
                            </div>
                            <div class="poli-info">
                                <div class="poli-name"><?= htmlspecialchars($poli['nama']) ?></div>
                            </div>
                        </div>

                        <?php if ($poli['status'] == 'active'): ?>
    <span class="status-badge active">
        <i class="fas fa-circle"></i> Aktif
    </span>
<?php else: ?>
    <span class="status-badge inactive">
        <i class="fas fa-circle"></i> Tidak Aktif
    </span>
<?php endif; ?>

                        <!-- Ganti deskripsi statis karena tidak ada di DB -->
                        <p class="poli-description">
                            Informasi layanan <?= htmlspecialchars($poli['nama']) ?>.
                        </p>

                        <div class="poli-stats">
                            <div class="stat-item">
                                <div class="stat-item-number"><?= $total_pasien_poli ?></div>
                                <div class="stat-item-label">Total Pasien</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-item-number"><?= $jumlah_dokter ?></div>
                                <div class="stat-item-label">Dokter</div>
                            </div>
                        </div>

                        <div class="doctor-section">
                            <div class="section-title">Dokter Bertugas</div>
                            <div class="doctor-list">
                                <?php if ($jumlah_dokter > 0): ?>
                                    <?php foreach ($dokter_list as $d): ?>
                                        <div class="doctor-item">
                                            <div class="doctor-avatar-sm">
                                                <?= strtoupper(substr($d['nama'], 0, 1)) . (isset($d['nama'][1]) ? strtoupper(substr($d['nama'], -1, 1)) : '') ?>
                                            </div>
                                            <div class="doctor-name-sm"><?= htmlspecialchars($d['nama']) ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="doctor-item">
                                        <div class="doctor-name-sm" style="color: var(--text-gray); font-style: italic;">Belum ada dokter</div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="schedule-info">
    <?php foreach ($ringkasan_jadwal as $jadwal): ?>
        <div class="schedule-row">
            <i class="fas fa-clock"></i>
            <span><?= htmlspecialchars($jadwal) ?></span>
        </div>
    <?php endforeach; ?>
</div>

                    <div class="poli-actions">
    <a href="../Poli/edit_poli.php?id=<?= $poli['id'] ?>" class="btn btn-outline btn-sm">
        <i class="fas fa-edit"></i> Edit
    </a>
    <a href="../Poli/detail_poli.php?id=<?= $poli['id'] ?>" class="btn btn-success btn-sm">
        <i class="fas fa-eye"></i> Detail
    </a>
    <!-- Tambahkan tombol HAPUS -->
    <a href="../Poli/hapus_poli.php?id=<?= $poli['id'] ?>" class="btn btn-sm" 
       style="background: var(--danger); color: white; padding: 0.625rem 1rem;"
       onclick="return confirm('Yakin ingin menghapus poliklinik ini? Tindakan ini tidak bisa dibatalkan!')">
        <i class="fas fa-trash"></i> Hapus
    </a>
</div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: var(--text-gray);">
                    <i class="fas fa-clinic-medical" style="font-size: 3rem; opacity: 0.5;"></i>
                    <p>Tidak ada poliklinik aktif.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        document.getElementById('searchInput').addEventListener('input', function() {
            const query = this.value.toLowerCase();
            const cards = document.querySelectorAll('.poli-card');
            cards.forEach(card => {
                const nama = card.getAttribute('data-nama');
                card.style.display = nama.includes(query) ? 'block' : 'none';
            });
        });
    </script>
</body>
</html>