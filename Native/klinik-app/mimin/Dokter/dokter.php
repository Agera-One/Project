<?php
session_start();
// CEK LOGIN (sesuaikan dengan sistemmu)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

// KONEKSI DATABASE
include '../../konfigurasi/koneksi.php';

// === HANYA SATU BLOK KODE HAPUS ===
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // 1. HAPUS DATA DI TABEL pendaftaran YANG TERKAIT
    mysqli_query($conn, "DELETE FROM pendaftaran WHERE dokter_id = $id");
    
    // 2. HAPUS DATA DI TABEL jadwal_dokter YANG TERKAIT  
    mysqli_query($conn, "DELETE FROM jadwal_dokter WHERE dokter_id = $id");
    
    // 3. HAPUS DATA DOKTER
    if (mysqli_query($conn, "DELETE FROM dokter WHERE id = $id")) {
        $_SESSION['success_message'] = "Data dokter berhasil dihapus!";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus data dokter: " . mysqli_error($conn);
    }
    
    header("Location: ../Dokter/dokter.php");
    exit;
}

// INISIALISASI VARIABEL
$search = $_GET['search'] ?? '';
$poli_filter = $_GET['poli'] ?? '';
$status_filter = $_GET['status'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// BANGUN QUERY DASAR
$sql = "SELECT * FROM dokter WHERE 1=1";

// TAMBAHKAN FILTER
if ($search) {
    $search_term = mysqli_real_escape_string($conn, $search);
    $sql .= " AND (nama LIKE '%$search_term%' OR spesialisasi LIKE '%$search_term%')";
}
if ($poli_filter) {
    $sql .= " AND spesialisasi = '" . mysqli_real_escape_string($conn, $poli_filter) . "'";
}
if ($status_filter) {
    $sql .= " AND status = '" . mysqli_real_escape_string($conn, $status_filter) . "'";
}

// EKSEKUSI QUERY
$sql .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);

// AMBIL DATA UNTUK FILTER & STATISTIK
$total_result = mysqli_query($conn, "SELECT * FROM dokter WHERE 1=1" . 
    ($search ? " AND (nama LIKE '%" . mysqli_real_escape_string($conn, $search) . "%' OR spesialisasi LIKE '%" . mysqli_real_escape_string($conn, $search) . "%')" : "") .
    ($poli_filter ? " AND spesialisasi = '" . mysqli_real_escape_string($conn, $poli_filter) . "'" : "") .
    ($status_filter ? " AND status = '" . mysqli_real_escape_string($conn, $status_filter) . "'" : "")
);
$total_data = mysqli_num_rows($total_result);

$spesialisasi_result = mysqli_query($conn, "SELECT DISTINCT spesialisasi FROM dokter WHERE spesialisasi != '' AND spesialisasi IS NOT NULL ORDER BY spesialisasi ASC");
$total_dokter = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM dokter"));
$dokter_aktif = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM dokter WHERE status = 'active'"));
$jumlah_spesialisasi = mysqli_num_rows(mysqli_query($conn, "SELECT DISTINCT spesialisasi FROM dokter WHERE spesialisasi != '' AND spesialisasi IS NOT NULL"));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Dokter - Klinik Sehat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ... (CSS tetap sama seperti sebelumnya) ... */
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
            flex-wrap: wrap;
            gap: 1rem;
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
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
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

        .stat-icon.cyan {
            background: rgba(6, 182, 212, 0.1);
            color: #06b6d4;
        }

        .stat-icon.green {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
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

        .filter-group {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-select {
            padding: 0.75rem 1rem;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 0.875rem;
            background: var(--white);
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 200px;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary);
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
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-outline {
            background: var(--white);
            color: var(--text-dark);
            border: 2px solid var(--border);
        }

        .btn-outline:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .btn-danger {
            background: var(--danger);
            color: var(--white);
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .doctors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        .doctor-card {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .doctor-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .doctor-header {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            align-items: flex-start;
        }

        .doctor-avatar {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .doctor-info {
            flex: 1;
        }

        .doctor-name {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.25rem;
        }

        .doctor-specialty {
            font-size: 0.875rem;
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .doctor-sip {
            font-size: 0.75rem;
            color: var(--text-gray);
        }

        .doctor-details {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .detail-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.875rem;
        }

        .detail-row i {
            width: 20px;
            color: var(--primary);
            text-align: center;
        }

        .detail-row span {
            color: var(--text-gray);
        }

        .schedule-section {
            margin-bottom: 1.5rem;
        }

        .schedule-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.75rem;
        }

        .schedule-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .schedule-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.5rem 0.75rem;
            background: var(--light);
            border-radius: 6px;
            font-size: 0.875rem;
        }

        .schedule-day {
            color: var(--text-dark);
            font-weight: 600;
        }

        .schedule-time {
            color: var(--text-gray);
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

        .doctor-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-sm {
            flex: 1;
            padding: 0.625rem 1rem;
            font-size: 0.75rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .page-item {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .page-item.active {
            background: var(--primary);
            color: var(--white);
        }

        .page-item:not(.active):hover {
            background: var(--light);
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            background: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow);
            grid-column: 1 / -1;
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--border);
            margin-bottom: 1rem;
        }

        .empty-state p {
            color: var(--text-gray);
            font-size: 1rem;
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
                grid-template-columns: repeat(2, 1fr);
            }

            @media (max-width: 480px) {
                .stats-grid {
                    grid-template-columns: 1fr;
                }
            }

            .action-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                width: 100%;
            }

            .filter-group {
                width: 100%;
                flex-direction: column;
            }

            .filter-select {
                width: 100%;
            }

            .doctors-grid {
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

    <main class="container">
        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?= $_SESSION['success_message'] ?></span>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= $_SESSION['error_message'] ?></span>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="page-header">
            <div class="page-title">
                <i class="fas fa-user-md"></i>
                <h2>Data Dokter</h2>
            </div>
            <a href="../Dashboard/dashboard.php" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Kembali ke Dashboard
            </a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon cyan">
                        <i class="fas fa-user-md"></i>
                    </div>
                </div>
                <div class="stat-number"><?= $total_dokter ?></div>
                <div class="stat-label">Total Dokter</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon green">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-number"><?= $dokter_aktif ?></div>
                <div class="stat-label">Dokter Aktif</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon purple">
                        <i class="fas fa-clinic-medical"></i>
                    </div>
                </div>
                <div class="stat-number"><?= $jumlah_spesialisasi ?></div>
                <div class="stat-label">Spesialisasi</div>
            </div>
        </div>

       <div class="action-bar">
    <form method="GET" id="filterForm" style="display: flex; gap: 1rem; flex-wrap: wrap; width: 100%;">
        <div class="search-box" style="flex: 1; min-width: 250px; position: relative;">
            <i class="fas fa-search"></i>
            <input type="text" name="search" placeholder="Cari nama dokter atau spesialisasi..." value="<?= htmlspecialchars($search) ?>">
        </div>
        
        <div class="filter-group" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
            <select name="poli" class="filter-select">
                <option value="">Semua Spesialisasi</option>
                <?php 
                // Reset pointer result set untuk digunakan kembali
                if ($spesialisasi_result) mysqli_data_seek($spesialisasi_result, 0);
                while ($spes = mysqli_fetch_assoc($spesialisasi_result)): ?>
                    <option value="<?= htmlspecialchars($spes['spesialisasi']) ?>" <?= ($poli_filter == $spes['spesialisasi']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($spes['spesialisasi']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            
            <select name="status" class="filter-select">
                <option value="">Semua Status</option>
                <option value="active" <?= ($status_filter == 'active') ? 'selected' : '' ?>>Aktif</option>
                <option value="inactive" <?= ($status_filter == 'inactive') ? 'selected' : '' ?>>Tidak Aktif</option>
            </select>
        </div>
        
        <div style="display: flex; gap: 1rem; align-self: center;">
            <button type="submit" class="btn btn-primary" style="padding: 0.625rem 1.25rem;">
                <i class="fas fa-filter"></i> Terapkan Filter
            </button>
            <a href="../Dokter/tambah_dokter.php" class="btn btn-outline" style="padding: 0.625rem 1.25rem;">
                <i class="fas fa-plus"></i> Tambah Dokter
            </a>
        </div>
    </form>
</div>

        <div class="doctors-grid">
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="doctor-card">
                        <div class="doctor-header">
                            <div class="doctor-avatar"><?= strtoupper(substr($row['nama'], 0, 2)) ?></div>
                            <div class="doctor-info">
                                <div class="doctor-name"><?= htmlspecialchars($row['nama']) ?></div>
                                <div class="doctor-specialty"><?= htmlspecialchars($row['spesialisasi']) ?></div>
                            </div>
                        </div>

                        <span class="status-badge <?= ($row['status'] == 'active') ? 'active' : 'inactive' ?>">
                            <i class="fas fa-circle"></i>
                            <?= ($row['status'] == 'active') ? 'Aktif' : 'Tidak Aktif' ?>
                        </span>

                        <div class="doctor-details">
                            <div class="detail-row">
                                <i class="fas fa-phone"></i>
                                <span><?= htmlspecialchars($row['no_hp'] ?: '-') ?></span>
                            </div>
                            <div class="detail-row">
                                <i class="fas fa-envelope"></i>
                                <span><?= htmlspecialchars($row['email'] ?: '-') ?></span>
                            </div>
                        </div>

                        <!-- JADWAL DARI TABEL jadwal_dokter -->
                        <div class="schedule-section">
                            <div class="schedule-title">Jadwal Praktik</div>
                            <div class="schedule-list">
                                <?php
                                // Ambil jadwal dari tabel jadwal_dokter
                                $jadwal_query = mysqli_query($conn, "SELECT hari, jam_mulai, jam_selesai FROM jadwal_dokter WHERE dokter_id = " . $row['id'] . " ORDER BY FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')");
                                if (mysqli_num_rows($jadwal_query) > 0) {
                                    while ($jadwal = mysqli_fetch_assoc($jadwal_query)) {
                                        echo '<div class="schedule-item">';
                                        echo '<span class="schedule-day">' . htmlspecialchars($jadwal['hari']) . '</span>';
                                        echo '<span class="schedule-time">' . date('H:i', strtotime($jadwal['jam_mulai'])) . ' - ' . date('H:i', strtotime($jadwal['jam_selesai'])) . '</span>';
                                        echo '</div>';
                                    }
                                } else {
                                    echo '<div class="schedule-item"><span class="schedule-day">Belum ada jadwal</span></div>';
                                }
                                ?>
                            </div>
                        </div>

                        <div class="doctor-actions">
                            <a href="../Dokter/edit_dokter.php?id=<?= $row['id'] ?>" class="btn btn-outline btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="../Dokter/detail_dokter.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                            
                            <!-- TOMBOL HAPUS -->
                            <a href="../Dokter/dokter.php?action=delete&id=<?= $row['id'] ?>" 
                               class="btn btn-danger btn-sm" 
                               onclick="return confirm('Yakin ingin menghapus <?= addslashes($row['nama']) ?>?')">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-user-md"></i>
                    <p>Belum ada data dokter yang terdaftar.</p>
                    <a href="../Dokter/tambah_dokter.php" class="btn btn-primary" style="margin-top: 1rem;">
                        <i class="fas fa-plus"></i> Tambah Dokter Baru
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_data > $limit): ?>
            <div class="pagination">
                <?php
                $total_pages = ceil($total_data / $limit);
                $max_visible = 5;
                $start_page = max(1, $page - floor($max_visible / 2));
                $end_page = min($total_pages, $start_page + $max_visible - 1);
                
                $filter_params = '&search=' . urlencode($search) . '&poli=' . urlencode($poli_filter) . '&status=' . urlencode($status_filter);
                
                if ($start_page > 1) {
                    echo '<a href="../Dokter/dokter.php?page=1' . $filter_params . '" class="page-item">1</a>';
                    if ($start_page > 2) {
                        echo '<span class="page-item">...</span>';
                    }
                }
                
                for ($i = $start_page; $i <= $end_page; $i++) {
                    $active_class = ($i == $page) ? 'active' : '';
                    echo '<a href="../Dokter/dokter.php?page=' . $i . $filter_params . '" class="page-item ' . $active_class . '">' . $i . '</a>';
                }
                
                if ($end_page < $total_pages) {
                    if ($end_page < $total_pages - 1) {
                        echo '<span class="page-item">...</span>';
                    }
                    echo '<a href="../Dokter/dokter.php?page=' . $total_pages . $filter_params . '" class="page-item">' . $total_pages . '</a>';
                }
                ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>