<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

include '../../konfigurasi/koneksi.php';

// Ambil ID dari URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID kunjungan tidak valid.");
}
$id = (int)$_GET['id'];

// Ambil data kunjungan
$query = "
    SELECT 
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
    WHERE p.id = $id AND p.status = 'selesai'
    LIMIT 1
";

$result = mysqli_query($conn, $query);
if (!$result || mysqli_num_rows($result) === 0) {
    die("Data kunjungan tidak ditemukan.");
}
$data = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Kunjungan - <?= htmlspecialchars($data['nama_pasien']) ?> - Klinik Sehat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --primary: #2563eb;
            --text-dark: #1e293b;
            --text-gray: #64748b;
            --border: #e2e8f0;
            --light: #f8fafc;
            --white: #ffffff;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--white);
            color: var(--text-dark);
            line-height: 1.6;
            padding: 2rem;
        }
        .header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .logo {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }
        .logo i {
            font-size: 2rem;
            color: var(--primary);
        }
        .clinic-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-dark);
        }
        .clinic-tagline {
            font-size: 0.875rem;
            color: var(--text-gray);
        }
        .document-title {
            text-align: center;
            font-size: 1.25rem;
            font-weight: 700;
            margin: 1.5rem 0;
            color: var(--primary);
        }
        .patient-info {
            background: var(--light);
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        .info-item {
            display: flex;
            flex-direction: column;
        }
        .info-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-gray);
            text-transform: uppercase;
            margin-bottom: 0.25rem;
        }
        .info-value {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-dark);
        }
        .visit-details {
            background: var(--light);
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px dashed var(--border);
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: var(--text-gray);
        }
        .detail-value {
            font-weight: 600;
            color: var(--text-dark);
        }
        .footer {
            text-align: center;
            margin-top: 2rem;
            color: var(--text-gray);
            font-size: 0.875rem;
        }
        .back-link {
            display: inline-block;
            margin-top: 1.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--light);
            color: var(--text-dark);
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
        }
        .back-link:hover {
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="logo">
            <i class="fas fa-hospital"></i>
            <div>
                <div class="clinic-name">Klinik Sehat</div>
                <div class="clinic-tagline">Sistem Manajemen Klinik</div>
            </div>
        </div>
        <div class="document-title">DETAIL KUNJUNGAN PASIEN</div>
    </div>

    <!-- Informasi Pasien -->
    <div class="patient-info">
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Nama Pasien</div>
                <div class="info-value"><?= htmlspecialchars($data['nama_pasien']) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Nomor Rekam Medis</div>
                <div class="info-value"><?= htmlspecialchars($data['no_rm']) ?></div>
            </div>
        </div>
    </div>

    <!-- Detail Kunjungan -->
    <div class="visit-details">
        <div class="detail-row">
            <div class="detail-label">Tanggal Kunjungan</div>
            <div class="detail-value"><?= date('d F Y', strtotime($data['tanggal'])) ?></div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Nomor Antrian</div>
            <div class="detail-value"><?= htmlspecialchars($data['no_antrian']) ?></div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Poliklinik</div>
            <div class="detail-value"><?= htmlspecialchars($data['nama_poli']) ?></div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Dokter Pemeriksa</div>
            <div class="detail-value"><?= htmlspecialchars($data['nama_dokter']) ?></div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Ditampilkan pada: <?= date('d F Y H:i') ?></p>
    </div>

    <!-- Tombol Kembali -->
    <div style="text-align: center;">
        <a href="../Riwayat/riwayat.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Kembali ke Riwayat Kunjungan
        </a>
    </div>
</body>
</html>