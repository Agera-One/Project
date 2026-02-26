<?php
session_start();
include '../../konfigurasi/koneksi.php';

/* ===== PROTEKSI ADMIN ===== */
// HANYA role 'admin' yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    session_destroy();
    header("Location: ../../auth/login.php?error=access_denied");
    exit;
}

/* ===== AMBIL DATA STATISTIK ===== */
$total_pasien = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM pasien"))['total'];
$total_dokter = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM dokter"))['total'];
$total_poli   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM poli"))['total'];
$total_antrian = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM pendaftaran WHERE status = 'menunggu'"))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Klinik Sehat</title>
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

        /* Header */
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

        /* Logout Button */
        .logout-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(239, 68, 68, 0.12);
            color: var(--danger);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.25s;
            text-decoration: none;
        }

        .logout-btn:hover {
            background: var(--danger);
            color: var(--white);
        }

        /* Main Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Welcome Section */
        .welcome-section {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            color: var(--white);
        }

        .welcome-section h2 {
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
        }

        .welcome-section p {
            opacity: 0.9;
            font-size: 0.95rem;
        }

        /* Section Title */
        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 1.5rem;
        }

        /* Menu Grid */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        /* Card */
        .card {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .card-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .card-icon.blue {
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary);
        }

        .card-icon.green {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .card-icon.orange {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .card-icon.purple {
            background: rgba(139, 92, 246, 0.1);
            color: #8b5cf6;
        }

        .card-icon.red {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .card-icon.cyan {
            background: rgba(6, 182, 212, 0.1);
            color: #06b6d4;
        }

        .card-icon.indigo {
            background: rgba(99, 102, 241, 0.1);
            color: #6366f1;
        }

        .card-icon.gray {
            background: rgba(100, 116, 139, 0.1);
            color: var(--secondary);
        }

        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .card-description {
            font-size: 0.875rem;
            color: var(--text-gray);
            line-height: 1.5;
        }

        /* Quick Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 1rem;
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

        .stat-icon.blue {
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary);
        }

        .stat-icon.green {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .stat-icon.orange {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .stat-icon.red {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .stat-content h3 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.25rem;
        }

        .stat-content p {
            font-size: 0.875rem;
            color: var(--text-gray);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-container {
                padding: 1rem;
            }

            .container {
                padding: 1rem;
            }

            .menu-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .welcome-section h2 {
                font-size: 1.5rem;
            }

            .user-info {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
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
                <a href="../../auth/logout.php" class="logout-btn" title="Logout">
                    <i class="fas fa-right-from-bracket"></i>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <h2>Selamat Datang, Administrator!</h2>
            <p>Kelola data klinik dan pantau aktivitas harian dengan mudah</p>
        </div>

        <!-- Menu Grid -->
        <h2 class="section-title">Menu Utama</h2>
        <div class="menu-grid">
            <a href="../RegisterPasien/register_pasien.php" class="card">
                <div class="card-icon blue">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h3 class="card-title">Registrasi Pasien</h3>
                <p class="card-description">Daftarkan pasien baru untuk pemeriksaan hari ini</p>
            </a>

            <a href="../Antrian/antrian.php" class="card">
                <div class="card-icon green">
                    <i class="fas fa-list-ol"></i>
                </div>
                <h3 class="card-title">Kelola Antrian</h3>
                <p class="card-description">Pantau dan kelola antrian pasien secara real-time</p>
            </a>

            <a href="../Pasien/pasien.php" class="card">
                <div class="card-icon purple">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="card-title">Data Pasien</h3>
                <p class="card-description">Lihat dan kelola data seluruh pasien terdaftar</p>
            </a>

            <a href="../Dokter/dokter.php" class="card">
                <div class="card-icon cyan">
                    <i class="fas fa-user-md"></i>
                </div>
                <h3 class="card-title">Data Dokter</h3>
                <p class="card-description">Kelola informasi dokter dan jadwal praktik</p>
            </a>

            <a href="../Poli/poli.php" class="card">
                <div class="card-icon orange">
                    <i class="fas fa-clinic-medical"></i>
                </div>
                <h3 class="card-title">Data Poli</h3>
                <p class="card-description">Manajemen poliklinik dan layanan kesehatan</p>
            </a>

            <a href="../Riwayat/riwayat.php" class="card">
                <div class="card-icon indigo">
                    <i class="fas fa-history"></i>
                </div>
                <h3 class="card-title">Riwayat Kunjungan</h3>
                <p class="card-description">Lihat histori kunjungan pasien ke klinik</p>
            </a>

            <a href="../Laporan/laporan.php" class="card">
                <div class="card-icon red">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h3 class="card-title">Laporan</h3>
                <p class="card-description">Laporan dan statistik aktivitas klinik</p>
            </a>

            <a href="../Pengaturan/pengaturan.php" class="card">
                <div class="card-icon gray">
                    <i class="fas fa-cog"></i>
                </div>
                <h3 class="card-title">Pengaturan</h3>
                <p class="card-description">Konfigurasi sistem dan profil pengguna</p>
            </a>
        </div>
    </main>
</body>
</html>