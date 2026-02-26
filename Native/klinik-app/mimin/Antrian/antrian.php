<?php
session_start();
// CEK LOGIN (opsional, sesuaikan dengan sistemmu)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Jika pakai login, redirect ke login
    // header("Location: ../../auth/login.php");
    // exit;
}

// ==========================
// KONEKSI DATABASE
// ==========================
$host = "localhost";
$user = "root";
$pass = "";
$db   = "klinik_db";

$conn = new mysqli($host, $user, $pass, $db, 3307);
if ($conn->connect_error) {
    die("Gagal koneksi: " . $conn->connect_error);
}

// ==========================
// HANDLE PERUBAHAN STATUS
// ==========================
if ($_POST['action'] ?? false) {
    $id = (int)$_POST['id'];
    $action = $_POST['action'];

    if ($action === 'panggil') {
        $conn->query("UPDATE pendaftaran SET status = 'sedang dilayani' WHERE id = $id");
    } elseif ($action === 'selesai') {
        $conn->query("UPDATE pendaftaran SET status = 'selesai' WHERE id = $id");
    } elseif ($action === 'tidak_hadir') {
        $conn->query("UPDATE pendaftaran SET status = 'tidak hadir' WHERE id = $id");
    }

    // Redirect untuk hindari resubmit
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

// ==========================
// AMBIL FILTER DARI GET
// ==========================
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$poli_id = $_GET['poli_id'] ?? '';
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Format tanggal untuk tampilan
$tanggal_tampil = date('d F Y', strtotime($tanggal));

// Bangun query dasar
$sql = "
SELECT pendaftaran.*, pasien.nama AS pasien_nama, pasien.no_rm, 
       dokter.nama AS dokter_nama, poli.nama AS poli_nama
FROM pendaftaran
JOIN pasien ON pendaftaran.pasien_id = pasien.id
JOIN dokter ON pendaftaran.dokter_id = dokter.id
JOIN poli ON pendaftaran.poli_id = poli.id
WHERE pendaftaran.tanggal = '$tanggal'
";

// Tambahkan filter
if ($poli_id) {
    $sql .= " AND poli.id = " . (int)$poli_id;
}
if ($status_filter) {
    $sql .= " AND pendaftaran.status = '" . $conn->real_escape_string($status_filter) . "'";
}
if ($search) {
    $search = $conn->real_escape_string($search);
    $sql .= " AND (pasien.nama LIKE '%$search%' OR pasien.no_rm LIKE '%$search%')";
}

$sql .= " ORDER BY pendaftaran.no_antrian ASC";

$result = $conn->query($sql);

// Ambil daftar poli untuk dropdown
$poliResult = $conn->query("SELECT id, nama FROM poli ORDER BY nama ASC");

// ==========================
// PERBAIKAN: STATISTIK KONSISTEN DENGAN FILTER
// ==========================
$base_count_sql = "
    SELECT pendaftaran.id, pendaftaran.status
    FROM pendaftaran
    JOIN pasien ON pendaftaran.pasien_id = pasien.id
    JOIN dokter ON pendaftaran.dokter_id = dokter.id
    JOIN poli ON pendaftaran.poli_id = poli.id
    WHERE pendaftaran.tanggal = '$tanggal'
";

// Terapkan filter yang sama (kecuali status untuk total)
$count_sql = $base_count_sql;
if ($poli_id) {
    $count_sql .= " AND poli.id = " . (int)$poli_id;
}
if ($search) {
    $search = $conn->real_escape_string($search);
    $count_sql .= " AND (pasien.nama LIKE '%$search%' OR pasien.no_rm LIKE '%$search%')";
}

// Total
$total_result = $conn->query("SELECT COUNT(*) as total FROM ($count_sql) AS t");
$total = $total_result ? (int)$total_result->fetch_assoc()['total'] : 0;

// Menunggu
$menunggu_result = $conn->query("SELECT COUNT(*) as total FROM ($count_sql) AS t WHERE t.status = 'menunggu'");
$menunggu = $menunggu_result ? (int)$menunggu_result->fetch_assoc()['total'] : 0;

// Sedang dilayani
$proses_result = $conn->query("SELECT COUNT(*) as total FROM ($count_sql) AS t WHERE t.status = 'sedang dilayani'");
$proses = $proses_result ? (int)$proses_result->fetch_assoc()['total'] : 0;

// Selesai
$selesai_result = $conn->query("SELECT COUNT(*) as total FROM ($count_sql) AS t WHERE t.status = 'selesai'");
$selesai = $selesai_result ? (int)$selesai_result->fetch_assoc()['total'] : 0;

// Tidak Hadir
$tidak_hadir_result = $conn->query("SELECT COUNT(*) as total FROM ($count_sql) AS t WHERE t.status = 'tidak hadir'");
$tidak_hadir = $tidak_hadir_result ? (int)$tidak_hadir_result->fetch_assoc()['total'] : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Antrian - Klinik Sehat</title>
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
            align-items: flex-start;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-title {
            display: flex;
            flex-direction: column;
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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

        .stat-icon.gray {
            background: rgba(100, 116, 139, 0.1);
            color: var(--secondary);
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

        .queue-section {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
        }

        .queue-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .queue-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-dark);
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

        .queue-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .queue-item {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding: 1.25rem;
            border: 2px solid var(--border);
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .queue-item:hover {
            border-color: var(--primary);
            background: rgba(37, 99, 235, 0.02);
        }

        .queue-number {
            flex-shrink: 0;
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            background: var(--primary);
            color: var(--white);
        }

        .queue-info {
            flex: 1;
        }

        .patient-name {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.25rem;
        }

        .queue-details {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--text-gray);
        }

        .detail-item i {
            color: var(--primary);
        }

        .queue-status {
            flex-shrink: 0;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge.waiting {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .badge.process {
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary);
        }

        .badge.done {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .badge.skip {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .queue-actions {
            flex-shrink: 0;
            display: flex;
            gap: 0.5rem;
        }

        .btn-icon {
            width: 36px;
            height: 36px;
            border: none;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-icon.success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .btn-icon.success:hover {
            background: var(--success);
            color: var(--white);
        }

        .btn-icon.danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .btn-icon.danger:hover {
            background: var(--danger);
            color: var(--white);
        }

        .btn-icon.warning {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .btn-icon.warning:hover {
            background: var(--warning);
            color: var(--white);
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
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

            .filter-grid {
                grid-template-columns: 1fr;
            }

            .queue-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .queue-details {
                width: 100%;
            }

            .queue-actions {
                width: 100%;
                justify-content: flex-end;
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
            <div class="user-avatar"><i class="fas fa-user"></i></div>
        </div>
    </div>
</header>

<main class="container">
    <!-- PAGE HEADER -->
    <div class="page-header">
        <div class="page-title">
            <h2>Kelola Antrian</h2>
            <div class="page-subtitle">Tanggal: <?= htmlspecialchars($tanggal_tampil) ?></div>
        </div>
        <a href="../Dashboard/dashboard.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
    </div>

    <!-- STATS - 5 KOTAK DALAM SATU BARIS -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-label">Total Antrian</span>
                <div class="stat-icon blue"><i class="fas fa-list-ol"></i></div>
            </div>
            <div class="stat-number"><?= $total ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-label">Menunggu</span>
                <div class="stat-icon orange"><i class="fas fa-hourglass-half"></i></div>
            </div>
            <div class="stat-number"><?= $menunggu ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-label">Sedang Dilayani</span>
                <div class="stat-icon blue"><i class="fas fa-stethoscope"></i></div>
            </div>
            <div class="stat-number"><?= $proses ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-label">Selesai</span>
                <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
            </div>
            <div class="stat-number"><?= $selesai ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-label">Tidak Hadir</span>
                <div class="stat-icon red"><i class="fas fa-user-slash"></i></div>
            </div>
            <div class="stat-number"><?= $tidak_hadir ?></div>
        </div>
    </div>

    <!-- FILTER SECTION -->
    <div class="filter-section">
        <form method="GET">
            <div class="filter-grid">
                <div class="form-group">
                    <label>Poli klinik</label>
                    <select name="poli_id">
                        <option value="">Semua Poli</option>
                        <?php if($poliResult && $poliResult->num_rows > 0): ?>
                            <?php while($poli = $poliResult->fetch_assoc()): ?>
                                <option value="<?php echo $poli['id']; ?>" <?= ($poli_id == $poli['id']) ? 'selected' : '' ?>>
                                    <?php echo htmlspecialchars($poli['nama']); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="">Semua Status</option>
                        <option value="menunggu" <?= ($status_filter == 'menunggu') ? 'selected' : '' ?>>Menunggu</option>
                        <option value="sedang dilayani" <?= ($status_filter == 'sedang dilayani') ? 'selected' : '' ?>>Sedang Dilayani</option>
                        <option value="selesai" <?= ($status_filter == 'selesai') ? 'selected' : '' ?>>Selesai</option>
                        <option value="tidak hadir" <?= ($status_filter == 'tidak hadir') ? 'selected' : '' ?>>Tidak Hadir</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>">
                </div>
                <div class="form-group">
                    <label>Cari Pasien</label>
                    <input type="text" name="search" placeholder="Nama atau No. RM" value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div style="margin-top: 1rem; text-align: right;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Terapkan Filter</button>
            </div>
        </form>
    </div>

    <!-- QUEUE LIST -->
    <div class="queue-section">
        <div class="queue-header">
            <h3 class="queue-title">Daftar Antrian</h3>
            <a href="<?= $_SERVER['REQUEST_URI'] ?>" class="btn btn-primary"><i class="fas fa-sync-alt"></i> Refresh</a>
        </div>

        <div class="queue-list">
            <?php if($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <?php
                    $status = strtolower($row['status']);
                    $badgeClass = 'waiting';
                    $badgeIcon  = 'fa-hourglass-half';
                    $badgeText  = ucfirst($status);

                    if($status == 'menunggu'){
                        $badgeClass='waiting'; $badgeIcon='fa-hourglass-half'; $badgeText='Menunggu';
                    } elseif(in_array($status, ['dipanggil','sedang','sedang dilayani'])){
                        $badgeClass='process'; $badgeIcon='fa-spinner'; $badgeText='Sedang Dilayani';
                    } elseif($status=='selesai'){
                        $badgeClass='done'; $badgeIcon='fa-check-circle'; $badgeText='Selesai';
                    } elseif($status=='tidak hadir'){
                        $badgeClass='skip'; $badgeIcon='fa-user-slash'; $badgeText='Tidak Hadir';
                    }
                    ?>
                    <div class="queue-item">
                        <div class="queue-number"><?php echo htmlspecialchars($row['no_antrian']); ?></div>
                        <div class="queue-info">
                            <div class="patient-name"><?php echo htmlspecialchars($row['pasien_nama']); ?></div>
                            <div class="queue-details">
                                <div class="detail-item"><i class="fas fa-id-card"></i> <span><?php echo htmlspecialchars($row['no_rm']); ?></span></div>
                                <div class="detail-item"><i class="fas fa-clinic-medical"></i> <span><?php echo htmlspecialchars($row['poli_nama']); ?></span></div>
                                <div class="detail-item"><i class="fas fa-user-md"></i> <span><?php echo htmlspecialchars($row['dokter_nama']); ?></span></div>
                                <div class="detail-item"><i class="fas fa-clock"></i> <span><?php echo date('H:i', strtotime($row['created_at'])); ?></span></div>
                            </div>
                        </div>
                        <div class="queue-status">
                            <span class="badge <?php echo $badgeClass; ?>">
                                <i class="fas <?php echo $badgeIcon; ?>"></i> <?php echo $badgeText; ?>
                            </span>
                        </div>
                        <div class="queue-actions">
                            <?php if($status=='menunggu'): ?>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Panggil pasien ini?')">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="action" value="panggil">
                                    <button type="submit" class="btn-icon success" title="Panggil"><i class="fas fa-bell"></i></button>
                                </form>
                            <?php elseif(in_array($status,['sedang','sedang dilayani','dipanggil'])): ?>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Tandai selesai?')">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="action" value="selesai">
                                    <button type="submit" class="btn-icon success" title="Selesai"><i class="fas fa-check"></i></button>
                                </form>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Tandai tidak hadir?')">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="action" value="tidak_hadir">
                                    <button type="submit" class="btn-icon danger" title="Tidak Hadir"><i class="fas fa-times"></i></button>
                                </form>
                            <?php else: ?>
                                <!-- Tidak ada aksi untuk status selesai/tidak hadir -->
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-list-ol"></i>
                    <p>Tidak ada antrian untuk tanggal <strong><?= htmlspecialchars($tanggal_tampil) ?></strong>.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>
</body>
</html>