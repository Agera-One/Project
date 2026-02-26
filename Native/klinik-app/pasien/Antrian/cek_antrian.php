<?php
session_start();
include '../../konfigurasi/koneksi.php';

/* ===== PROTEKSI PASIEN ===== */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pasien') {
    session_destroy();
    header("Location: ../../auth/login.php?error=access_denied");
    exit;
}

$pasien_id = $_SESSION['pasien_id'] ?? 0;

if (empty($pasien_id)) {
    session_destroy();
    header("Location: ../../auth/login.php?error=invalid_session");
    exit;
}

// Ambil data pasien
$query_patient = "SELECT nama, no_rm FROM pasien WHERE id = ?";
$stmt = $conn->prepare($query_patient);
$stmt->bind_param("i", $pasien_id);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();

// Filter: status (all, menunggu, dipanggil, selesai, batal)
$filter_status = $_GET['status'] ?? 'all';
$valid_status = ['all', 'menunggu', 'dipanggil', 'selesai', 'batal'];
if (!in_array($filter_status, $valid_status)) {
    $filter_status = 'all';
}

// Filter: tanggal (hari ini, kemarin, semua)
$filter_tanggal = $_GET['tanggal'] ?? 'semua';
$valid_tanggal = ['semua', 'hari_ini', 'kemarin'];
if (!in_array($filter_tanggal, $valid_tanggal)) {
    $filter_tanggal = 'semua';
}

// Build query
$query = "SELECT p.*, d.nama as nama_dokter, pol.nama as nama_poli, j.hari, j.jam_mulai, j.jam_selesai
          FROM pendaftaran p
          JOIN dokter d ON p.dokter_id = d.id
          JOIN poli pol ON p.poli_id = pol.id
          LEFT JOIN jadwal_dokter j ON p.dokter_id = j.dokter_id AND p.poli_id = j.poli_id
          WHERE p.pasien_id = ?";

$params = [$pasien_id];
$types = 'i';

// Filter status
if ($filter_status !== 'all') {
    $query .= " AND p.status = ?";
    $params[] = $filter_status;
    $types .= 's';
}

// Filter tanggal
if ($filter_tanggal === 'hari_ini') {
    $query .= " AND p.tanggal = CURDATE()";
} elseif ($filter_tanggal === 'kemarin') {
    $query .= " AND p.tanggal = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
}

$query .= " ORDER BY p.tanggal DESC, p.created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$queues = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Hitung statistik
$statistik = [
    'total' => 0,
    'menunggu' => 0,
    'dipanggil' => 0,
    'selesai' => 0,
    'batal' => 0
];

foreach ($queues as $q) {
    $statistik['total']++;
    $statistik[$q['status']]++;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Antrian - Klinik Sehat</title>
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
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #06b6d4;
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

        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--border);
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

        /* Card */
        .card {
            background: var(--white);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        .card-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h2 {
            font-size: 1.5rem;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-header h2 i {
            color: var(--primary);
        }

        /* Filter Section */
        .filter-section {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-label {
            font-size: 0.875rem;
            color: var(--text-gray);
            font-weight: 600;
        }

        .filter-select {
            padding: 0.5rem 0.75rem;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 0.875rem;
            background: var(--white);
            cursor: pointer;
            transition: border-color 0.3s;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--white);
            border-radius: 12px;
            padding: 1.25rem;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--primary);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-card.total { border-left-color: var(--primary); }
        .stat-card.menunggu { border-left-color: var(--warning); }
        .stat-card.dipanggil { border-left-color: var(--success); }
        .stat-card.selesai { border-left-color: var(--info); }
        .stat-card.batal { border-left-color: var(--danger); }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .stat-icon.total { background: rgba(37, 99, 235, 0.1); color: var(--primary); }
        .stat-icon.menunggu { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
        .stat-icon.dipanggil { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .stat-icon.selesai { background: rgba(6, 182, 212, 0.1); color: var(--info); }
        .stat-icon.batal { background: rgba(239, 68, 68, 0.1); color: var(--danger); }

        .stat-content h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.25rem;
        }

        .stat-content p {
            font-size: 0.875rem;
            color: var(--text-gray);
        }

        /* Queue List */
        .queue-list {
            display: grid;
            gap: 1rem;
        }

        .queue-item {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--primary);
            transition: all 0.3s;
        }

        .queue-item:hover {
            box-shadow: var(--shadow-lg);
            transform: translateX(5px);
        }

        .queue-item.menunggu { border-left-color: var(--warning); }
        .queue-item.dipanggil { border-left-color: var(--success); }
        .queue-item.selesai { border-left-color: var(--info); }
        .queue-item.batal { border-left-color: var(--danger); }

        .queue-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border);
        }

        .queue-number {
            text-align: center;
            padding: 0.75rem 1.5rem;
            background: var(--light);
            border-radius: 8px;
        }

        .queue-number p {
            font-size: 0.875rem;
            color: var(--text-gray);
            margin-bottom: 0.25rem;
        }

        .queue-number h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
        }

        .queue-status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8125rem;
            font-weight: 600;
        }

        .queue-status-badge.menunggu {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .queue-status-badge.dipanggil {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .queue-status-badge.selesai {
            background: rgba(6, 182, 212, 0.1);
            color: var(--info);
        }

        .queue-status-badge.batal {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .queue-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .detail-item {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .detail-item i {
            color: var(--text-gray);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .detail-item span {
            font-size: 0.875rem;
            color: var(--text-dark);
        }

        .detail-item strong {
            color: var(--text-dark);
            font-weight: 600;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
            background: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--text-gray);
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            font-size: 1.25rem;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: var(--text-gray);
            margin-bottom: 1rem;
        }

        .empty-state a {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: var(--primary);
            color: var(--white);
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .empty-state a:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* Responsive */
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

            .filter-section {
                flex-direction: column;
                width: 100%;
            }

            .filter-group {
                width: 100%;
                flex-direction: column;
                align-items: flex-start;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .queue-details {
                grid-template-columns: 1fr;
            }

            .queue-number h2 {
                font-size: 2rem;
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
                    <div class="name"><?php echo htmlspecialchars($patient['nama']); ?></div>
                    <div class="role">Pasien</div>
                </div>
                <div class="user-avatar">
                    <?php echo strtoupper(substr($patient['nama'], 0, 2)); ?>
                </div>
                <a href="../../auth/logout.php" class="logout-btn" title="Logout">
                    <i class="fas fa-right-from-bracket"></i>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-list-ol"></i> Cek Antrian</h1>
            <a href="../dashboard.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>

        <!-- Card -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-history"></i> Riwayat Antrian Anda</h2>
                <span style="color: var(--text-gray); font-size: 0.875rem;">
                    Total: <?php echo $statistik['total']; ?> antrian
                </span>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <div class="filter-group">
                    <span class="filter-label">Status:</span>
                    <select class="filter-select" onchange="filterQueue('status', this.value)">
                        <option value="all" <?php echo $filter_status === 'all' ? 'selected' : ''; ?>>Semua</option>
                        <option value="menunggu" <?php echo $filter_status === 'menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                        <option value="dipanggil" <?php echo $filter_status === 'dipanggil' ? 'selected' : ''; ?>>Dipanggil</option>
                        <option value="selesai" <?php echo $filter_status === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                        <option value="batal" <?php echo $filter_status === 'batal' ? 'selected' : ''; ?>>Dibatalkan</option>
                    </select>
                </div>
                <div class="filter-group">
                    <span class="filter-label">Tanggal:</span>
                    <select class="filter-select" onchange="filterQueue('tanggal', this.value)">
                        <option value="semua" <?php echo $filter_tanggal === 'semua' ? 'selected' : ''; ?>>Semua</option>
                        <option value="hari_ini" <?php echo $filter_tanggal === 'hari_ini' ? 'selected' : ''; ?>>Hari Ini</option>
                        <option value="kemarin" <?php echo $filter_tanggal === 'kemarin' ? 'selected' : ''; ?>>Kemarin</option>
                    </select>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card total">
                    <div class="stat-icon total">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $statistik['total']; ?></h3>
                        <p>Total Antrian</p>
                    </div>
                </div>
                <div class="stat-card menunggu">
                    <div class="stat-icon menunggu">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $statistik['menunggu']; ?></h3>
                        <p>Menunggu</p>
                    </div>
                </div>
                <div class="stat-card dipanggil">
                    <div class="stat-icon dipanggil">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $statistik['dipanggil']; ?></h3>
                        <p>Dipanggil</p>
                    </div>
                </div>
                <div class="stat-card selesai">
                    <div class="stat-icon selesai">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $statistik['selesai']; ?></h3>
                        <p>Selesai</p>
                    </div>
                </div>
                <div class="stat-card batal">
                    <div class="stat-icon batal">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $statistik['batal']; ?></h3>
                        <p>Dibatalkan</p>
                    </div>
                </div>
            </div>

            <!-- Queue List -->
            <div class="queue-list">
                <?php if (count($queues) > 0): ?>
                    <?php foreach ($queues as $queue): ?>
                    <div class="queue-item <?php echo $queue['status']; ?>">
                        <div class="queue-header">
                            <div class="queue-number">
                                <p>Nomor Antrian</p>
                                <h2><?php echo htmlspecialchars($queue['no_antrian']); ?></h2>
                            </div>
                            <span class="queue-status-badge <?php echo $queue['status']; ?>">
                                <?php 
                                $status_labels = [
                                    'menunggu' => 'Menunggu',
                                    'dipanggil' => 'Dipanggil',
                                    'selesai' => 'Selesai',
                                    'batal' => 'Dibatalkan'
                                ];
                                echo $status_labels[$queue['status']] ?? $queue['status'];
                                ?>
                            </span>
                        </div>
                        <div class="queue-details">
                            <div class="detail-item">
                                <i class="fas fa-calendar"></i>
                                <div>
                                    <strong>Tanggal</strong><br>
                                    <span><?php echo date('d F Y', strtotime($queue['tanggal'])); ?></span>
                                </div>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-clinic-medical"></i>
                                <div>
                                    <strong>Poli</strong><br>
                                    <span><?php echo htmlspecialchars($queue['nama_poli']); ?></span>
                                </div>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-user-md"></i>
                                <div>
                                    <strong>Dokter</strong><br>
                                    <span><?php echo htmlspecialchars($queue['nama_dokter']); ?></span>
                                </div>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-clock"></i>
                                <div>
                                    <strong>Jadwal</strong><br>
                                    <span><?php 
                                        if ($queue['jam_mulai'] && $queue['jam_selesai']) {
                                            echo date('H:i', strtotime($queue['jam_mulai'])) . ' - ' . date('H:i', strtotime($queue['jam_selesai']));
                                        } else {
                                            echo '-';
                                        }
                                    ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>Tidak Ada Antrian</h3>
                        <p>Anda belum memiliki riwayat antrian. Silakan daftar poli terlebih dahulu.</p>
                        <a href="../Poli/daftar_poli.php">
                            <i class="fas fa-calendar-plus"></i> Daftar Poli Sekarang
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        function filterQueue(type, value) {
            const url = new URL(window.location.href);
            url.searchParams.set(type, value);
            window.location.href = url.toString();
        }
    </script>
</body>
</html>