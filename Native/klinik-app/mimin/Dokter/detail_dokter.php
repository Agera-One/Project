<?php
session_start();
// CEK LOGIN (sesuaikan dengan sistemmu)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

// KONEKSI DATABASE
include '../../konfigurasi/koneksi.php';

// AMBIL DATA DOKTER BERDASARKAN ID
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $query = mysqli_query($conn, "SELECT * FROM dokter WHERE id = $id");
    if (mysqli_num_rows($query) > 0) {
        $dokter = mysqli_fetch_assoc($query);
    } else {
        $_SESSION['error_message'] = "Data dokter tidak ditemukan!";
        header("Location: ../Dokter/dokter.php");
        exit;
    }
} else {
    $_SESSION['error_message'] = "ID dokter tidak valid!";
    header("Location: ../Dokter/dokter.php");
    exit;
}

// AMBIL JADWAL PRAKTIK DARI DATABASE
$jadwal_praktik = [];
$jadwal_query = mysqli_query($conn, 
    "SELECT hari, jam_mulai, jam_selesai 
     FROM jadwal_dokter 
     WHERE dokter_id = $id 
     ORDER BY FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')"
);
if ($jadwal_query) {
    while ($row = mysqli_fetch_assoc($jadwal_query)) {
        $jadwal_praktik[] = [
            'hari' => htmlspecialchars($row['hari']),
            'jam' => htmlspecialchars($row['jam_mulai']) . ' - ' . htmlspecialchars($row['jam_selesai'])
        ];
    }
}

// AMBIL DATA ANTRIAN HARI INI
$tanggal_hari_ini = date('Y-m-d');
$antrian_hari_ini = mysqli_query($conn, 
    "SELECT p.*, pasien.nama AS nama_pasien, pasien.no_rm 
     FROM pendaftaran p 
     JOIN pasien ON p.pasien_id = pasien.id 
     WHERE p.dokter_id = $id AND p.tanggal = '$tanggal_hari_ini' 
     ORDER BY p.no_antrian ASC"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Dokter - Klinik Sehat</title>
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

        /* Main Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Breadcrumb */
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }

        .breadcrumb a {
            color: var(--text-gray);
            text-decoration: none;
        }

        .breadcrumb a:hover {
            color: var(--primary);
        }

        .breadcrumb .active {
            color: var(--text-dark);
            font-weight: 600;
        }

        /* Page Header */
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

        /* Doctor Profile Card */
        .profile-card {
            background: var(--white);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        .profile-header {
            display: flex;
            gap: 2rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .doctor-avatar-large {
            width: 120px;
            height: 120px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .doctor-info-large {
            flex: 1;
        }

        .doctor-name-large {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .doctor-specialty-large {
            font-size: 1.125rem;
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .status-badge.active {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .status-badge.inactive {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        /* Details Grid */
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .detail-section {
            background: var(--light);
            border-radius: 12px;
            padding: 1.5rem;
        }

        .section-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--border);
        }

        .detail-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .detail-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: var(--white);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .detail-content {
            flex: 1;
        }

        .detail-label {
            font-size: 0.875rem;
            color: var(--text-gray);
            margin-bottom: 0.25rem;
        }

        .detail-value {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        /* Schedule Section */
        .schedule-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .schedule-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem;
            background: var(--white);
            border-radius: 8px;
            border-left: 4px solid var(--primary);
        }

        .schedule-day {
            font-weight: 600;
            color: var(--text-dark);
        }

        .schedule-time {
            color: var(--text-gray);
        }

        /* Today's Queue */
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

        .queue-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .queue-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
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
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            font-weight: 700;
            background: var(--primary);
            color: var(--white);
        }

        .queue-patient {
            flex: 1;
        }

        .patient-name {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.25rem;
        }

        .patient-rm {
            font-size: 0.875rem;
            color: var(--text-gray);
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

        /* Empty State */
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
            }

            .profile-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .doctor-info-large {
                text-align: center;
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

        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title">
                <h2>Detail Dokter</h2>
                <div class="page-subtitle">Informasi lengkap dokter <?= htmlspecialchars($dokter['nama']) ?></div>
            </div>
            <a href="../Dokter/dokter.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Kembali ke Data Dokter
            </a>
        </div>

        <!-- Doctor Profile -->
        <div class="profile-card">
            <div class="profile-header">
                <div class="doctor-avatar-large"><?= strtoupper(substr($dokter['nama'], 0, 2)) ?></div>
                <div class="doctor-info-large">
                    <div class="doctor-name-large"><?= htmlspecialchars($dokter['nama']) ?></div>
                    <div class="doctor-specialty-large"><?= htmlspecialchars($dokter['spesialisasi']) ?></div>
                    <span class="status-badge <?= ($dokter['status'] == 'active') ? 'active' : 'inactive' ?>">
                        <i class="fas fa-circle"></i>
                        <?= ($dokter['status'] == 'active') ? 'Aktif' : 'Tidak Aktif' ?>
                    </span>
                </div>
            </div>

            <div class="details-grid">
                <!-- Personal Information -->
                <div class="detail-section">
                    <h3 class="section-title">Informasi Pribadi</h3>
                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="detail-content">
                            <div class="detail-label">No. HP</div>
                            <div class="detail-value"><?= htmlspecialchars($dokter['no_hp'] ?: '-') ?></div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="detail-content">
                            <div class="detail-label">Email</div>
                            <div class="detail-value"><?= htmlspecialchars($dokter['email'] ?: '-') ?></div>
                        </div>
                    </div>
                </div>

                <!-- Practice Schedule -->
                <div class="detail-section">
                    <h3 class="section-title">Jadwal Praktik</h3>
                    <div class="schedule-list">
    <?php if (!empty($jadwal_praktik)): ?>
        <?php foreach ($jadwal_praktik as $jadwal): ?>
            <div class="schedule-item">
                <div class="schedule-day"><?= $jadwal['hari'] ?></div>
                <div class="schedule-time"><?= $jadwal['jam'] ?></div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state" style="padding: 1rem; color: var(--text-gray);">
            <i class="fas fa-calendar-times"></i>
            <p>Tidak ada jadwal praktik terdaftar</p>
        </div>
    <?php endif; ?>
</div>
                </div>
            </div>
        </div>

        <!-- Today's Queue -->
        <div class="queue-section">
            <div class="queue-header">
                <h3 class="queue-title">Antrian Hari Ini (<?= date('d F Y') ?>)</h3>
            </div>
            
            <?php if (mysqli_num_rows($antrian_hari_ini) > 0): ?>
                <div class="queue-list">
                    <?php while ($antrian = mysqli_fetch_assoc($antrian_hari_ini)): ?>
                        <?php
                        $status = strtolower($antrian['status']);
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
                            <div class="queue-number"><?= htmlspecialchars($antrian['no_antrian']) ?></div>
                            <div class="queue-patient">
                                <div class="patient-name"><?= htmlspecialchars($antrian['nama_pasien']) ?></div>
                                <div class="patient-rm">No. RM: <?= htmlspecialchars($antrian['no_rm']) ?></div>
                            </div>
                            <div class="queue-status">
                                <span class="badge <?= $badgeClass ?>">
                                    <i class="fas <?= $badgeIcon ?>"></i> <?= $badgeText ?>
                                </span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-check"></i>
                    <p>Tidak ada antrian untuk dokter ini hari ini</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>