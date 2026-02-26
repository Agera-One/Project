<?php
session_start();
include '../../konfigurasi/koneksi.php';

/* ===== PROTEKSI PASIEN ===== */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pasien') {
    header("Location: ../../auth/login.php");
    exit;
}

$pasien_id = $_SESSION['pasien_id'] ?? 0;

if (empty($pasien_id)) {
    session_destroy();
    header("Location: ../../auth/login.php?error=invalid_session");
    exit;
}

$query_patient = "SELECT nama, no_rm FROM pasien WHERE id = ?";
$stmt = $conn->prepare($query_patient);
$stmt->bind_param("i", $pasien_id);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $poli_id = intval($_POST['poli_id'] ?? 0);
    $dokter_id = intval($_POST['dokter_id'] ?? 0);
    $tanggal = trim($_POST['tanggal'] ?? '');
    
    if (empty($poli_id) || empty($dokter_id) || empty($tanggal)) {
        $error = 'Semua field wajib diisi!';
    } elseif (strtotime($tanggal) < strtotime(date('Y-m-d'))) {
        $error = 'Tanggal tidak boleh kurang dari hari ini!';
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM pendaftaran WHERE pasien_id = ? AND tanggal = ?");
        $stmt->bind_param("is", $pasien_id, $tanggal);
        $stmt->execute();
        $count = $stmt->get_result()->fetch_assoc()['count'];
        
        if ($count > 0) {
            $error = 'Anda sudah memiliki antrian pada tanggal ini!';
        } else {
            $hari = date('l', strtotime($tanggal));
            $hari_map = [
                'Sunday' => 'Minggu',
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu'
            ];
            $hari_indo = $hari_map[$hari] ?? $hari;
            
            $stmt = $conn->prepare("SELECT * FROM jadwal_dokter WHERE dokter_id = ? AND hari = ?");
            $stmt->bind_param("is", $dokter_id, $hari_indo);
            $stmt->execute();
            $jadwal = $stmt->get_result()->fetch_assoc();
            
            if (!$jadwal) {
                $error = 'Dokter tidak tersedia pada tanggal tersebut!';
            } else {
                $stmt = $conn->prepare("SELECT MAX(no_antrian) as max_queue FROM pendaftaran WHERE tanggal = ?");
                $stmt->bind_param("s", $tanggal);
                $stmt->execute();
                $max_queue = $stmt->get_result()->fetch_assoc()['max_queue'];
                
                if ($max_queue) {
                    $queue_number = intval(substr($max_queue, 1)) + 1;
                    $no_antrian = 'A' . str_pad($queue_number, 3, '0', STR_PAD_LEFT);
                } else {
                    $no_antrian = 'A001';
                }
                
                // ✅ FIX: HAPUS kolom keluhan dari query (sesuai struktur tabel)
                $stmt = $conn->prepare("INSERT INTO pendaftaran (pasien_id, dokter_id, poli_id, tanggal, no_antrian, status) 
                                        VALUES (?, ?, ?, ?, ?, 'menunggu')");
                $stmt->bind_param("iiiss", $pasien_id, $dokter_id, $poli_id, $tanggal, $no_antrian);
                
               if ($stmt->execute()) {
    // Set pesan sukses di session untuk ditampilkan di dashboard
    $_SESSION['success_daftar_poli'] = "Pendaftaran berhasil! Nomor antrian Anda: $no_antrian";
    // Redirect langsung ke dashboard
    header('Location: ../dashboard.php');
    exit();
} else {
    $error = 'Terjadi kesalahan saat menyimpan data!';
}
            }
        }
    }
}

$policies = $conn->query("SELECT * FROM poli WHERE status = 'active' ORDER BY nama")->fetch_all(MYSQLI_ASSOC);

$jadwal_dokter = [];
$result = $conn->query("SELECT jd.*, d.nama as nama_dokter, p.nama as nama_poli 
                        FROM jadwal_dokter jd 
                        JOIN dokter d ON jd.dokter_id = d.id 
                        JOIN poli p ON jd.poli_id = p.id 
                        WHERE d.status = 'active' AND p.status = 'active'
                        ORDER BY jd.hari, jd.jam_mulai");
while ($row = $result->fetch_assoc()) {
    $jadwal_dokter[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Poli - Klinik Sehat</title>
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

        /* Page Header - BARU (MENGGANTIKAN BREADCRUMB) */
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

        .card-body {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
        }

        @media (max-width: 968px) {
            .card-body {
                grid-template-columns: 1fr;
            }
        }

        /* Form Group */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
        }

        /* Alert */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert i {
            font-size: 1.25rem;
        }

        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        /* Button */
        .btn-group {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--primary);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--text-gray);
            color: var(--white);
        }

        .btn-secondary:hover {
            background: #475569;
        }

        /* Poli List */
        .poli-list {
            display: grid;
            gap: 1rem;
        }

        .poli-item {
            padding: 1.25rem;
            border: 2px solid var(--border);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .poli-item:hover {
            border-color: var(--primary);
            background: #eff6ff;
        }

        .poli-item input[type="radio"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .poli-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .poli-info h3 {
            font-size: 1.125rem;
            color: var(--text-dark);
            margin-bottom: 0.25rem;
        }

        .poli-info p {
            font-size: 0.875rem;
            color: var(--text-gray);
        }

        /* Dokter List */
        .dokter-list {
            display: none;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid var(--border);
        }

        .dokter-list.show {
            display: block;
        }

        .dokter-item {
            padding: 1rem;
            border: 2px solid var(--border);
            border-radius: 8px;
            margin-bottom: 0.75rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .dokter-item:hover {
            border-color: var(--primary);
            background: #eff6ff;
        }

        .dokter-item input[type="radio"] {
            margin-right: 0.75rem;
            cursor: pointer;
        }

        .dokter-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .dokter-info h4 {
            font-size: 1rem;
            color: var(--text-dark);
            margin: 0;
        }

        .dokter-info small {
            color: var(--text-gray);
            font-size: 0.875rem;
        }

        /* Info Box */
        .info-box {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            margin-top: 1rem;
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

        .info-box p {
            color: var(--text-gray);
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .info-box ul {
            margin: 0.75rem 0 0 1.5rem;
            color: var(--text-gray);
            font-size: 0.9rem;
        }

        .info-box li {
            margin-bottom: 0.5rem;
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
        <!-- PAGE HEADER BARU (MENGGANTIKAN BREADCRUMB) -->
        <div class="page-header">
            <a href="../dashboard.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>

        <!-- Card -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-calendar-plus"></i> Daftar Poli</h2>
                <p style="margin: 0.5rem 0 0 0; color: var(--text-gray); font-size: 0.9rem;">
                    Pilih poli dan jadwal untuk pemeriksaan kesehatan Anda
                </p>
            </div>

            <div class="card-body">
                <!-- Form Pendaftaran -->
                <div>
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <div><?php echo $success; ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <div><?php echo htmlspecialchars($error); ?></div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <!-- Pilih Poli -->
                        <div class="form-group">
                            <label><i class="fas fa-clinic-medical"></i> Pilih Poli</label>
                            <div class="poli-list">
                                <?php foreach ($policies as $policy): ?>
                                <label class="poli-item" onclick="showDokter(<?php echo $policy['id']; ?>)">
                                    <input type="radio" name="poli_id" value="<?php echo $policy['id']; ?>" 
                                           required <?php echo (isset($_POST['poli_id']) && $_POST['poli_id'] == $policy['id']) ? 'checked' : ''; ?>>
                                    <div class="poli-icon">
                                        <i class="fas fa-stethoscope"></i>
                                    </div>
                                    <div class="poli-info">
                                        <h3><?php echo htmlspecialchars($policy['nama']); ?></h3>
                                        <p>Poli <?php echo htmlspecialchars($policy['nama']); ?></p>
                                    </div>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Pilih Dokter -->
                        <div class="form-group">
                            <label><i class="fas fa-user-md"></i> Pilih Dokter</label>
                            <div id="dokterContainer">
                                <?php foreach ($policies as $policy): ?>
                                <div class="dokter-list" id="dokterPoli<?php echo $policy['id']; ?>">
                                    <?php
                                    $dokter_poli = array_filter($jadwal_dokter, function($j) use ($policy) {
                                        return $j['poli_id'] == $policy['id'];
                                    });
                                    
                                    if (count($dokter_poli) > 0):
                                        foreach ($dokter_poli as $j):
                                    ?>
                                    <label class="dokter-item">
                                        <input type="radio" name="dokter_id" value="<?php echo $j['dokter_id']; ?>" 
                                               required <?php echo (isset($_POST['dokter_id']) && $_POST['dokter_id'] == $j['dokter_id']) ? 'checked' : ''; ?>>
                                        <div class="dokter-info">
                                            <h4><?php echo htmlspecialchars($j['nama_dokter']); ?></h4>
                                            <small><?php echo htmlspecialchars($j['hari']); ?>, <?php echo date('H:i', strtotime($j['jam_mulai'])); ?> - <?php echo date('H:i', strtotime($j['jam_selesai'])); ?></small>
                                        </div>
                                    </label>
                                    <?php 
                                        endforeach;
                                    else:
                                    ?>
                                    <p style="text-align: center; color: var(--text-gray); padding: 1rem;">
                                        <i class="fas fa-info-circle"></i> Tidak ada dokter tersedia untuk poli ini
                                    </p>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Tanggal Pemeriksaan (TANPA KELUHAN) -->
                        <div class="form-group">
                            <label><i class="fas fa-calendar"></i> Tanggal Pemeriksaan</label>
                            <input type="date" name="tanggal" required 
                                   min="<?php echo date('Y-m-d'); ?>" 
                                   max="<?php echo date('Y-m-d', strtotime('+7 days')); ?>"
                                   value="<?php echo isset($_POST['tanggal']) ? htmlspecialchars($_POST['tanggal']) : ''; ?>">
                            <small style="color: var(--text-gray); font-size: 0.85rem;">
                                Pilih tanggal antara hari ini hingga 7 hari ke depan
                            </small>
                        </div>

                        <!-- Tombol -->
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Daftar Sekarang
                            </button>
                            <a href="../dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Info Box -->
                <div>
                    <div class="info-box">
                        <h3><i class="fas fa-info-circle"></i> Informasi Penting</h3>
                        <p>Berikut adalah hal-hal yang perlu diperhatikan saat mendaftar poli:</p>
                        <ul>
                            <li><i class="fas fa-check-circle" style="color: var(--success); margin-right: 0.5rem;"></i> Pilih poli sesuai kebutuhan kesehatan Anda</li>
                            <li><i class="fas fa-check-circle" style="color: var(--success); margin-right: 0.5rem;"></i> Perhatikan jadwal dokter yang tersedia</li>
                            <li><i class="fas fa-check-circle" style="color: var(--success); margin-right: 0.5rem;"></i> Datang tepat waktu sesuai jadwal</li>
                            <li><i class="fas fa-check-circle" style="color: var(--success); margin-right: 0.5rem;"></i> Bawa kartu identitas dan nomor rekam medis</li>
                            <li><i class="fas fa-check-circle" style="color: var(--success); margin-right: 0.5rem;"></i> Nomor antrian akan diberikan setelah pendaftaran berhasil</li>
                        </ul>
                    </div>

                    <div class="info-box" style="margin-top: 1rem;">
                        <h3><i class="fas fa-clock"></i> Jam Operasional</h3>
                        <p style="margin-bottom: 0.75rem;">
                            <strong>Senin - Sabtu</strong><br>
                            08:00 - 20:00 WIB
                        </p>
                        <p style="color: var(--danger); font-weight: 600; margin: 0;">
                            <i class="fas fa-times-circle"></i> Minggu: Libur
                        </p>
                    </div>

                    <div class="info-box" style="margin-top: 1rem; background: linear-gradient(135deg, #eff6ff, #dbeafe);">
                        <h3 style="color: var(--primary);"><i class="fas fa-shield-alt"></i> Keamanan Data</h3>
                        <p style="margin: 0;">
                            Data pribadi Anda akan <strong>dirahasiakan</strong> dan hanya digunakan untuk keperluan pemeriksaan medis.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function showDokter(poliId) {
            document.querySelectorAll('.dokter-list').forEach(el => {
                el.classList.remove('show');
            });
            
            const dokterList = document.getElementById('dokterPoli' + poliId);
            if (dokterList) {
                dokterList.classList.add('show');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const selectedPoli = document.querySelector('input[name="poli_id"]:checked');
            if (selectedPoli) {
                showDokter(selectedPoli.value);
            }
        });
    </script>
</body>
</html>