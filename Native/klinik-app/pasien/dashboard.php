<?php
session_start();

// ✅ AMBIL PESAN SUKSES DARI SESSION (SEBELUM include koneksi)
$success_message = '';
if (isset($_SESSION['success_daftar_poli'])) {
    $success_message = $_SESSION['success_daftar_poli'];
    unset($_SESSION['success_daftar_poli']); // Hapus setelah diambil
}

include '../konfigurasi/koneksi.php';

/* ===== PROTEKSI PASIEN ===== */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pasien') {
    session_destroy();
    header("Location: ../auth/login.php?error=access_denied");
    exit;
}

/* ===== AMBIL DATA PASIEN ===== */
$pasien_id = $_SESSION['pasien_id'] ?? 0;

if (empty($pasien_id)) {
    session_destroy();
    header("Location: ../auth/login.php?error=invalid_session");
    exit;
}

$query_patient = "SELECT p.*, u.email as user_email 
                  FROM pasien p 
                  LEFT JOIN users u ON p.user_id = u.id 
                  WHERE p.id = ?";
$stmt = $conn->prepare($query_patient);
$stmt->bind_param("i", $pasien_id);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();

if (!$patient) {
    session_destroy();
    header("Location: ../auth/login.php?error=user_not_found");
    exit;
}

/* ===== DAFTAR SEMUA ANTRIAN PASIEN ===== */
$query_all_queues = "SELECT p.*, d.nama as nama_dokter, pol.nama as nama_poli, 
                     j.hari, j.jam_mulai, j.jam_selesai
                     FROM pendaftaran p
                     JOIN dokter d ON p.dokter_id = d.id
                     JOIN poli pol ON p.poli_id = pol.id
                     LEFT JOIN jadwal_dokter j ON p.dokter_id = j.dokter_id 
                         AND p.poli_id = j.poli_id
                     WHERE p.pasien_id = ?
                     ORDER BY p.tanggal DESC, p.created_at DESC";
$stmt = $conn->prepare($query_all_queues);
$stmt->bind_param("i", $pasien_id);
$stmt->execute();
$all_queues = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Pastikan kita menginisialisasi $active_queue sebagai null
$active_queue = null;
$today = date('Y-m-d');

// Cari antrian aktif hari ini (status menunggu atau dipanggil)
foreach ($all_queues as $queue) {
    // Pastikan tanggal di database dalam format yang bisa dibandingkan
    $queue_date = $queue['tanggal'] ?? '';
    
    // Normalisasi tanggal jika perlu
    if (!empty($queue_date) && strpos($queue_date, '-') !== false) {
        $queue_date = date('Y-m-d', strtotime($queue_date));
    }
    
    if ($queue_date == $today && in_array($queue['status'], ['menunggu', 'dipanggil'])) {
        $active_queue = $queue;
        break;
    }
}

/* ===== INFORMASI KLINIK DARI DATABASE ===== */
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'setting'");
$clinic_info = [];

if ($table_check && mysqli_num_rows($table_check) > 0) {
    $query_settings = "SELECT setting_key, setting_value FROM setting WHERE setting_key IN (
                        'nama_klinik', 
                        'alamat',
                        'telepon',
                        'jam_operasional'
                      )";
    $settings_result = mysqli_query($conn, $query_settings);
    
    if ($settings_result) {
        while ($row = mysqli_fetch_assoc($settings_result)) {
            $clinic_info[$row['setting_key']] = $row['setting_value'];
        }
    }
}

// ✅ HAPUS DUPLIKASI - HANYA 1 SET DEFAULT VALUES
$default_values = [
    'telepon' => '024-1234567',
    'alamat' => 'Jl. Sehat Sentosa No. 123, Semarang',
    'jam_operasional' => 'Senin - Sabtu, 08:00 - 20:00'
];

foreach ($default_values as $key => $value) {
    if (!isset($clinic_info[$key])) {
        $clinic_info[$key] = $value;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pasien - Klinik Sehat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ... CSS SAMA SEPERTI SEBELUMNYA ... */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
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
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        .welcome-section {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            color: var(--white);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .welcome-text h2 {
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
        }
        .welcome-text p {
            opacity: 0.9;
            font-size: 0.95rem;
        }
        .welcome-info {
            text-align: right;
        }
        .no-rm {
            font-size: 0.875rem;
            opacity: 0.9;
            margin-bottom: 0.25rem;
        }
        .no-rm-value {
            font-size: 1.5rem;
            font-weight: 700;
        }
        .active-queue {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--success);
        }
        .active-queue-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        .active-queue-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        .active-queue-header h3 {
            font-size: 1.125rem;
            color: var(--text-dark);
        }
        .queue-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            padding: 1rem;
            background: var(--light);
            border-radius: 8px;
        }
        .queue-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .queue-item i {
            color: var(--text-gray);
            font-size: 0.875rem;
        }
        .queue-item span {
            font-size: 0.875rem;
            color: var(--text-dark);
        }
        .queue-number {
            text-align: center;
            padding: 1rem;
            margin-top: 1rem;
        }
        .queue-number p {
            font-size: 0.875rem;
            color: var(--text-gray);
            margin-bottom: 0.5rem;
        }
        .queue-number h2 {
            font-size: 3rem;
            color: var(--success);
            font-weight: 700;
        }
        .queue-status {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8125rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }
        .status-menunggu {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }
        .status-dipanggil {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }
        .status-selesai {
            background: rgba(59, 130, 246, 0.1);
            color: var(--primary);
        }
        .status-batal {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }
        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--border);
        }
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .card {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
            border-left: 4px solid var(--border);
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
            margin-bottom: 0.75rem;
        }
        .info-box {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }
        .info-box h3 {
            font-size: 1.125rem;
            color: var(--text-dark);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .info-box h3 i {
            color: var(--primary);
        }
        .info-list {
            display: grid;
            gap: 0.75rem;
        }
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem;
            background: var(--light);
            border-radius: 8px;
        }
        .info-label {
            font-size: 0.875rem;
            color: var(--text-gray);
            font-weight: 500;
        }
        .info-value {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-dark);
            text-align: right;
        }
        
        /* ALERT SUKSES BARU */
        .alert-success-custom {
            background: #ecfdf5;
            border-left: 4px solid #10b981;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            color: #065f46;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.15);
        }
        .alert-success-custom i {
            font-size: 1.25rem;
        }
        
        @media (max-width: 768px) {
            .header-container {
                padding: 1rem;
            }
            .container {
                padding: 1rem;
            }
            .welcome-section {
                flex-direction: column;
                text-align: center;
            }
            .welcome-info {
                text-align: center;
                margin-top: 1rem;
            }
            .menu-grid {
                grid-template-columns: 1fr;
            }
            .queue-details {
                grid-template-columns: 1fr;
            }
            .welcome-text h2 {
                font-size: 1.5rem;
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
                    <div class="name"><?php echo htmlspecialchars($patient['nama']); ?></div>
                    <div class="role">Pasien</div>
                </div>
                <div class="user-avatar">
                    <?php echo strtoupper(substr($patient['nama'], 0, 2)); ?>
                </div>
                <a href="../auth/logout.php" class="logout-btn" title="Logout">
                    <i class="fas fa-right-from-bracket"></i>
                </a>
            </div>
        </div>
    </header>

    <main class="container">
        <!-- ALERT SUKSES (Jika ada pesan dari daftar poli) -->
        <?php if (!empty($success_message)): ?>
        <div class="alert-success-custom">
            <i class="fas fa-check-circle"></i>
            <span><?php echo htmlspecialchars($success_message); ?></span>
        </div>
        <?php endif; ?>

        <div class="welcome-section">
            <div class="welcome-text">
                <h2>Selamat Datang, <?php echo htmlspecialchars($patient['nama']); ?>!</h2>
                <p>Semoga Anda selalu sehat dan bugar</p>
            </div>
            <div class="welcome-info">
                <div class="no-rm">Nomor Rekam Medis</div>
                <div class="no-rm-value"><?php echo htmlspecialchars($patient['no_rm']); ?></div>
            </div>
        </div>

        <?php if ($active_queue): ?>
        <div class="active-queue">
            <div class="active-queue-header">
                <div class="active-queue-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h3>Antrian Aktif Hari Ini</h3>
            </div>
            <div class="queue-details">
                <div class="queue-item">
                    <i class="fas fa-calendar"></i>
                    <span><?php echo date('d F Y', strtotime($active_queue['tanggal'])); ?></span>
                </div>
                <div class="queue-item">
                    <i class="fas fa-clinic-medical"></i>
                    <span><?php echo htmlspecialchars($active_queue['nama_poli']); ?></span>
                </div>
                <div class="queue-item">
                    <i class="fas fa-user-md"></i>
                    <span><?php echo htmlspecialchars($active_queue['nama_dokter']); ?></span>
                </div>
                <div class="queue-item">
                    <i class="fas fa-clock"></i>
                    <span><?php echo date('H:i', strtotime($active_queue['jam_mulai'])); ?> - <?php echo date('H:i', strtotime($active_queue['jam_selesai'])); ?></span>
                </div>
            </div>
            <div class="queue-number">
                <p>Nomor Antrian Anda</p>
                <h2><?php echo htmlspecialchars($active_queue['no_antrian']); ?></h2>
                <span class="queue-status status-<?php echo $active_queue['status']; ?>">
                    <?php 
                    $status_labels = [
                        'menunggu' => 'Menunggu',
                        'dipanggil' => 'Dipanggil',
                        'selesai' => 'Selesai',
                        'batal' => 'Dibatalkan'
                    ];
                    echo $status_labels[$active_queue['status']] ?? $active_queue['status'];
                    ?>
                </span>
            </div>
        </div>
        <?php endif; ?>

        <!-- Daftar Semua Antrian -->
        <h2 class="section-title">Daftar Antrian Saya</h2>
        <div class="menu-grid">
            <?php if (!empty($all_queues)): ?>
                <?php foreach ($all_queues as $queue): ?>
                <div class="card" style="border-left: 4px solid <?php 
                    echo $queue['status'] == 'menunggu' ? 'var(--warning)' : 
                         ($queue['status'] == 'dipanggil' ? 'var(--success)' : 
                         ($queue['status'] == 'selesai' ? 'var(--primary)' : 'var(--danger)')); 
                ?>;">
                    <div class="card-icon" style="background: <?php 
                        echo $queue['status'] == 'menunggu' ? 'rgba(245, 158, 11, 0.1)' : 
                             ($queue['status'] == 'dipanggil' ? 'rgba(16, 185, 129, 0.1)' : 
                             ($queue['status'] == 'selesai' ? 'rgba(59, 130, 246, 0.1)' : 'rgba(239, 68, 68, 0.1)')); 
                    ?>; color: <?php 
                        echo $queue['status'] == 'menunggu' ? 'var(--warning)' : 
                             ($queue['status'] == 'dipanggil' ? 'var(--success)' : 
                             ($queue['status'] == 'selesai' ? 'var(--primary)' : 'var(--danger)')); 
                    ?>;">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3 class="card-title"><?php echo date('d F Y', strtotime($queue['tanggal'])); ?></h3>
                    <p class="card-description">
                        <strong><?php echo htmlspecialchars($queue['nama_poli']); ?></strong><br>
                        Dokter: <?php echo htmlspecialchars($queue['nama_dokter']); ?><br>
                        Antrian: <?php echo htmlspecialchars($queue['no_antrian']); ?>
                    </p>
                    <span class="queue-status status-<?php echo $queue['status']; ?>">
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
                <?php endforeach; ?>
            <?php else: ?>
            <div class="active-queue" style="border-left-color: var(--warning); grid-column: span 2;">
                <div class="active-queue-header">
                    <div class="active-queue-icon" style="background: rgba(245, 158, 11, 0.1); color: var(--warning);">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <h3>Belum Ada Antrian</h3>
                </div>
                <p style="text-align: center; padding: 2rem; color: var(--text-gray);">
                    Anda belum memiliki antrian. Silakan daftar poli untuk membuat antrian baru.
                </p>
            </div>
            <?php endif; ?>
        </div>

        <h2 class="section-title">Menu Utama</h2>
        <div class="menu-grid">
            <a href="Poli/daftar_poli.php" class="card">
                <div class="card-icon blue">
                    <i class="fas fa-calendar-plus"></i>
                </div>
                <h3 class="card-title">Daftar Poli</h3>
                <p class="card-description">Daftarkan diri untuk pemeriksaan kesehatan</p>
            </a>
            <a href="Antrian/cek_antrian.php" class="card">
                <div class="card-icon green">
                    <i class="fas fa-list-ol"></i>
                </div>
                <h3 class="card-title">Cek Antrian</h3>
                <p class="card-description">Lihat status antrian dan nomor antrian Anda</p>
            </a>
            <a href="Riwayat/riwayat_kunjungan.php" class="card">
                <div class="card-icon orange">
                    <i class="fas fa-history"></i>
                </div>
                <h3 class="card-title">Riwayat Kunjungan</h3>
                <p class="card-description">Lihat riwayat pemeriksaan kesehatan Anda</p>
            </a>
            <a href="Profil/profil.php" class="card">
                <div class="card-icon purple">
                    <i class="fas fa-user"></i>
                </div>
                <h3 class="card-title">Profil Saya</h3>
                <p class="card-description">Kelola informasi pribadi dan akun Anda</p>
            </a>
        </div>

        <div class="info-box">
            <h3>
                <i class="fas fa-info-circle"></i>
                Informasi Klinik
            </h3>
            <div class="info-list">
                <div class="info-item">
                    <span class="info-label">Jam Operasional</span>
                    <span class="info-value"><?php echo htmlspecialchars($clinic_info['jam_operasional']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Telepon</span>
                    <span class="info-value"><?php echo htmlspecialchars($clinic_info['telepon']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Alamat</span>
                    <span class="info-value"><?php echo htmlspecialchars($clinic_info['alamat']); ?></span>
                </div>
            </div>
        </div>
    </main>
</body>
</html>