<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

include '../../konfigurasi/koneksi.php';

// Ambil parameter filter
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');
$poli_id = $_GET['poli_id'] ?? '';
$search = trim($_GET['search'] ?? '');

// Bangun kondisi WHERE
$where = "p.status = 'selesai'";
$params = [];

// Filter bulan & tahun
$where .= " AND MONTH(p.tanggal) = ? AND YEAR(p.tanggal) = ?";
$params[] = $bulan;
$params[] = $tahun;

if ($poli_id) {
    $where .= " AND p.poli_id = ?";
    $params[] = $poli_id;
}
if ($search) {
    $where .= " AND (pasien.nama LIKE ? OR pasien.no_rm LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Query utama
$query = "
    SELECT 
        p.id,
        p.tanggal,
        p.no_antrian,
        pasien.nama AS nama_pasien,
        pasien.no_rm,
        poli.nama AS nama_poli,
        dokter.nama AS nama_dokter
    FROM pendaftaran p
    JOIN pasien ON p.pasien_id = pasien.id
    JOIN poli ON p.poli_id = poli.id
    JOIN dokter ON p.dokter_id = dokter.id
    WHERE $where
    ORDER BY p.tanggal DESC, p.no_antrian DESC
";

// Eksekusi query
$stmt = mysqli_prepare($conn, $query);
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$riwayat_list = mysqli_stmt_get_result($stmt);

// Hitung statistik global
$total_kunjungan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pendaftaran WHERE status = 'selesai'"))['total'];
$kunjungan_bulan_ini = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) as total 
    FROM pendaftaran 
    WHERE status = 'selesai' 
    AND MONTH(tanggal) = MONTH(CURDATE()) 
    AND YEAR(tanggal) = YEAR(CURDATE())
"))['total'];
$pasien_unik = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT pasien_id) as total FROM pendaftaran WHERE status = 'selesai'"))['total'];

// Ambil daftar poli
$poli_options = mysqli_query($conn, "SELECT id, nama FROM poli WHERE status = 'active' ORDER BY nama ASC");

// Daftar bulan
$bulan_list = [
    '01' => 'Januari',
    '02' => 'Februari',
    '03' => 'Maret',
    '04' => 'April',
    '05' => 'Mei',
    '06' => 'Juni',
    '07' => 'Juli',
    '08' => 'Agustus',
    '09' => 'September',
    '10' => 'Oktober',
    '11' => 'November',
    '12' => 'Desember'
];

// Tahun dari 2020 sampai sekarang
$tahun_list = range(2020, date('Y'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Kunjungan - Klinik Sehat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ... (SELURUH CSS SAMA SEPERTI SEBELUMNYA) ... */
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
        .stat-icon.indigo {
            background: rgba(99, 102, 241, 0.1);
            color: #6366f1;
        }
        .stat-icon.blue {
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary);
        }
        .stat-icon.green {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
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
        .filter-section {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .form-group label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-dark);
        }
        .form-group select,
        .form-group input {
            padding: 0.75rem;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }
        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
        }
        .filter-actions {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
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
        .btn-outline {
            background: transparent;
            border: 2px solid var(--border);
            color: var(--text-dark);
        }
        .btn-outline:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        .visit-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        .visit-card {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        .visit-card:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow-lg);
        }
        .visit-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--light);
        }
        .visit-patient {
            flex: 1;
        }
        .patient-name {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }
        .patient-meta {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
        }
        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--text-gray);
        }
        .meta-item i {
            color: var(--primary);
        }
        .visit-date {
            text-align: right;
        }
        .date-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary);
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .time-text {
            font-size: 0.75rem;
            color: var(--text-gray);
        }
        .visit-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .detail-box {
            background: var(--light);
            border-radius: 8px;
            padding: 1rem;
        }
        .detail-title {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-gray);
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }
        .detail-content {
            font-size: 0.875rem;
            color: var(--text-dark);
        }
        .visit-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
        }
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
        }
        .btn-success {
            background: var(--success);
            color: var(--white);
        }
        .btn-success:hover {
            background: #059669;
        }
        @media (max-width: 768px) {
            .header-container { padding: 1rem; }
            .container { padding: 1rem; }
            .page-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
            .stats-grid { grid-template-columns: 1fr; }
            .filter-grid { grid-template-columns: 1fr; }
            .visit-header { flex-direction: column; gap: 1rem; }
            .visit-date { text-align: left; }
            .visit-details { grid-template-columns: 1fr; }
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
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="page-header">
            <div class="page-title">
                <i class="fas fa-history"></i>
                <h2>Riwayat Kunjungan</h2>
            </div>
            <a href="../Dashboard/dashboard.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>

        <!-- Statistik -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon indigo">
                        <i class="fas fa-history"></i>
                    </div>
                </div>
                <div class="stat-number"><?= number_format($total_kunjungan) ?></div>
                <div class="stat-label">Total Kunjungan</div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon blue">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
                <div class="stat-number"><?= number_format($kunjungan_bulan_ini) ?></div>
                <div class="stat-label">Kunjungan Bulan Ini</div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon green">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
                <div class="stat-number"><?= number_format($pasien_unik) ?></div>
                <div class="stat-label">Pasien Unik</div>
            </div>
        </div>

        <!-- Filter Form -->
        <form method="GET" class="filter-section">
            <div class="filter-grid">
                <div class="form-group">
                    <label>Bulan</label>
                    <select name="bulan">
                        <?php foreach ($bulan_list as $val => $label): ?>
                            <option value="<?= $val ?>" <?= ($bulan == $val) ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tahun</label>
                    <select name="tahun">
                        <?php foreach ($tahun_list as $thn): ?>
                            <option value="<?= $thn ?>" <?= ($tahun == $thn) ? 'selected' : '' ?>>
                                <?= $thn ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Poliklinik</label>
                    <select name="poli_id">
                        <option value="">Semua Poli</option>
                        <?php 
                        // Reset pointer karena sudah dipakai sebelumnya
                        mysqli_data_seek($poli_options, 0);
                        while ($poli = mysqli_fetch_assoc($poli_options)): ?>
                            <option value="<?= $poli['id'] ?>" <?= ($poli_id == $poli['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($poli['nama']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Cari Pasien</label>
                    <input type="text" name="search" placeholder="Nama atau No. RM" value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="filter-actions">
                <button type="submit" name="action" value="reset" class="btn btn-outline">
                    <i class="fas fa-redo"></i> Reset Filter
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Cari
                </button>
            </div>
        </form>

        <!-- Daftar Riwayat -->
        <div class="visit-list">
            <?php if (mysqli_num_rows($riwayat_list) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($riwayat_list)): ?>
                    <div class="visit-card">
                        <div class="visit-header">
                            <div class="visit-patient">
                                <div class="patient-name"><?= htmlspecialchars($row['nama_pasien']) ?></div>
                                <div class="patient-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-id-card"></i>
                                        <span><?= htmlspecialchars($row['no_rm']) ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-phone"></i>
                                        <span><?= htmlspecialchars($row['no_antrian']) ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="visit-date">
                                <div class="date-badge">
                                    <i class="fas fa-calendar"></i>
                                    <?= date('d M Y', strtotime($row['tanggal'])) ?>
                                </div>
                                <div class="time-text">No. Antrian: <?= htmlspecialchars($row['no_antrian']) ?></div>
                            </div>
                        </div>

                        <div class="visit-details">
                            <div class="detail-box">
                                <div class="detail-title">Poliklinik</div>
                                <div class="detail-content">
                                    <i class="fas fa-clinic-medical" style="color: var(--primary); margin-right: 0.5rem;"></i>
                                    <?= htmlspecialchars($row['nama_poli']) ?>
                                </div>
                            </div>
                            <div class="detail-box">
                                <div class="detail-title">Dokter Pemeriksa</div>
                                <div class="detail-content">
                                    <i class="fas fa-user-md" style="color: var(--primary); margin-right: 0.5rem;"></i>
                                    <?= htmlspecialchars($row['nama_dokter']) ?>
                                </div>
                            </div>
                        </div>

                        <div class="visit-actions">
                            <a href="../Riwayat/detail_kunjungan.php?id=<?= $row['id'] ?>" class="btn btn-success btn-sm">
                                <i class="fas fa-eye"></i> Detail Lengkap
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem; color: var(--text-gray);">
                    <i class="fas fa-history" style="font-size: 3rem; opacity: 0.5; margin-bottom: 1rem;"></i>
                    <p>Tidak ada data kunjungan pada periode ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>