<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

include '../../konfigurasi/koneksi.php';

$pesan = '';
$id = 0;
$nama = '';
$spesialisasi = '';
$no_hp = '';
$email = '';
$status = 'active';
$poli_id_selected = 0;
$jadwal_list = []; // untuk menampung jadwal saat ini

// AMBIL DATA DOKTER JIKA ADA ID DI URL
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $query = mysqli_query($conn, "SELECT * FROM dokter WHERE id = $id");
    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        $nama = $data['nama'];
        $spesialisasi = $data['spesialisasi'];
        $no_hp = $data['no_hp'];
        $email = $data['email'] ?? '';
        $status = $data['status'];
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

// AMBIL DAFTAR POLI
$poli_result = mysqli_query($conn, "SELECT id, nama FROM poli WHERE status = 'active' ORDER BY nama ASC");

// CARI poli_id berdasarkan spesialisasi
$poli_id_selected = 0;
if ($spesialisasi) {
    $res = mysqli_query($conn, "SELECT id FROM poli WHERE nama = '" . mysqli_real_escape_string($conn, $spesialisasi) . "' AND status = 'active' LIMIT 1");
    if ($res && mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        $poli_id_selected = (int)$row['id'];
    }
}

// AMBIL JADWAL SAAT INI
$jadwal_result = mysqli_query($conn, "SELECT hari, jam_mulai, jam_selesai FROM jadwal_dokter WHERE dokter_id = $id ORDER BY FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')");
if ($jadwal_result) {
    while ($row = mysqli_fetch_assoc($jadwal_result)) {
        $jadwal_list[] = $row;
    }
}

// PROSES FORM EDIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id         = (int)$_POST['id'];
    $nama       = trim($_POST['nama'] ?? '');
    $poli_id    = (int)($_POST['poli_id'] ?? 0);
    $no_hp      = trim($_POST['no_hp'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $status     = in_array($_POST['status'] ?? 'active', ['active', 'inactive']) ? $_POST['status'] : 'active';

    $hari_list      = $_POST['hari'] ?? [];
    $jam_mulai_list = $_POST['jam_mulai'] ?? [];
    $jam_selesai_list = $_POST['jam_selesai'] ?? [];

    // AMBIL NAMA POLI BARU
    $spesialisasi_baru = '';
    if ($poli_id > 0) {
        $q_poli = mysqli_query($conn, "SELECT nama FROM poli WHERE id = $poli_id AND status = 'active' LIMIT 1");
        if ($q_poli && mysqli_num_rows($q_poli) > 0) {
            $poli_data = mysqli_fetch_assoc($q_poli);
            $spesialisasi_baru = $poli_data['nama'];
        }
    }

    if (!$nama || !$spesialisasi_baru || !$no_hp) {
        $pesan = "Nama, Poli/Spesialisasi, dan No. HP wajib diisi!";
    } else {
        // CEK DUPLIKASI
        $cek_dokter = mysqli_query($conn,
            "SELECT id FROM dokter 
             WHERE nama = '" . mysqli_real_escape_string($conn, $nama) . "' 
             AND spesialisasi = '" . mysqli_real_escape_string($conn, $spesialisasi_baru) . "' 
             AND id != $id 
             LIMIT 1"
        );
        if (mysqli_num_rows($cek_dokter) > 0) {
            $pesan = "Dokter dengan nama dan spesialisasi yang sama sudah terdaftar!";
        } else {
            // UPDATE DATA DOKTER
            $update = mysqli_query($conn,
                "UPDATE dokter SET 
                    nama = '" . mysqli_real_escape_string($conn, $nama) . "',
                    spesialisasi = '" . mysqli_real_escape_string($conn, $spesialisasi_baru) . "',
                    no_hp = '" . mysqli_real_escape_string($conn, $no_hp) . "',
                    email = '" . mysqli_real_escape_string($conn, $email) . "',
                    status = '$status'
                 WHERE id = $id"
            );

            if ($update) {
                // HAPUS JADWAL LAMA
                mysqli_query($conn, "DELETE FROM jadwal_dokter WHERE dokter_id = $id");

                $error_jadwal = false;
                for ($i = 0; $i < count($hari_list); $i++) {
                    $hari = trim($hari_list[$i] ?? '');
                    $jam_mulai = trim($jam_mulai_list[$i] ?? '');
                    $jam_selesai = trim($jam_selesai_list[$i] ?? '');

                    if (!$hari || !$jam_mulai || !$jam_selesai) continue;

                    $insert_jadwal = mysqli_query($conn,
                        "INSERT INTO jadwal_dokter (dokter_id, poli_id, hari, jam_mulai, jam_selesai) 
                         VALUES (
                             $id,
                             $poli_id,
                             '" . mysqli_real_escape_string($conn, $hari) . "',
                             '" . mysqli_real_escape_string($conn, $jam_mulai) . "',
                             '" . mysqli_real_escape_string($conn, $jam_selesai) . "'
                         )"
                    );

                    if (!$insert_jadwal) {
                        $error_jadwal = true;
                        break;
                    }
                }

                if (!$error_jadwal) {
                    $_SESSION['success_message'] = "Data dokter dan jadwal berhasil diperbarui!";
                    header("Location: ../Dokter/dokter.php");
                    exit;
                } else {
                    // Opsional: rollback update dokter? (tidak dilakukan karena data dokter tetap valid)
                    $pesan = "Gagal menyimpan salah satu jadwal dokter.";
                }
            } else {
                $pesan = "Gagal memperbarui data: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Dokter - Klinik Sehat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Sama persis seperti di tambah_dokter.php */
        * { margin:0; padding:0; box-sizing:border-box; }
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
            max-width: 1200px;
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
        .card {
            background: var(--white);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: var(--shadow);
        }
        .card-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--light);
        }
        .card-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        .card-header h3 {
            font-size: 1.125rem;
            color: var(--text-dark);
        }
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }
        .form-group label .required {
            color: var(--danger);
        }
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        select.form-control {
            cursor: pointer;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .form-row-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1rem;
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
        .btn-warning {
            background: var(--warning);
            color: var(--white);
        }
        .btn-warning:hover {
            background: #e08f00;
        }
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
            padding-top: 0rem;
            border-top: 2px solid var(--light);
        }
        .btn-submit {
            flex: 1;
        }
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        .jadwal-section {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid var(--light);
        }
        .jadwal-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 1rem;
        }
        .jadwal-item {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 1rem;
            margin-bottom: 1rem;
            padding: 1rem;
            background: var(--light);
            border-radius: 8px;
        }
        .jadwal-item:last-child {
            margin-bottom: 0;
        }
        .jadwal-item .remove-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--danger);
            color: var(--white);
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .jadwal-item .remove-btn:hover {
            background: #dc2626;
        }
        .add-jadwal-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--secondary);
            color: var(--white);
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .add-jadwal-btn:hover {
            background: #475569;
        }
        @media (max-width: 768px) {
            .header-container { padding: 1rem; }
            .container { padding: 1rem; }
            .page-header { flex-direction: column; align-items: flex-start; }
            .form-row, .form-row-3 { grid-template-columns: 1fr; }
            .jadwal-item { grid-template-columns: 1fr 1fr 1fr; gap: 0.5rem; }
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
                    <div class="name"><?= $_SESSION['nama'] ?? 'Admin Klinik' ?></div>
                    <div class="role"><?= $_SESSION['role'] ?? 'Staff Administrator' ?></div>
                </div>
                <div class="user-avatar"><i class="fas fa-user"></i></div>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="page-header">
            <div class="page-title">
                <h2>Edit Data Dokter</h2>
                <div class="page-subtitle">Perbarui data dokter dan jadwal praktiknya</div>
            </div>
            <a href="../Dokter/dokter.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Kembali ke Data Dokter
            </a>
        </div>

        <?php if ($pesan): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= htmlspecialchars($pesan) ?></span>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <div class="card-icon">
                    <i class="fas fa-user-edit"></i>
                </div>
                <h3>Perbarui Data Dokter</h3>
            </div>
            <form method="POST">
                <input type="hidden" name="id" value="<?= (int)$id ?>">

                <div class="form-group">
                    <label for="nama">Nama Lengkap Dokter <span class="required">*</span></label>
                    <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($nama) ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="poli_id">Poli/Spesialisasi <span class="required">*</span></label>
                        <select class="form-control" id="poli_id" name="poli_id" required>
                            <option value="">-- Pilih Poli/Spesialisasi --</option>
                            <?php if ($poli_result && mysqli_num_rows($poli_result) > 0): ?>
                                <?php while ($poli = mysqli_fetch_assoc($poli_result)): ?>
                                    <option value="<?= (int)$poli['id'] ?>" <?= ((int)$poli['id'] === $poli_id_selected) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($poli['nama']) ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="no_hp">No. HP <span class="required">*</span></label>
                        <input type="tel" class="form-control" id="no_hp" name="no_hp" value="<?= htmlspecialchars($no_hp) ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email) ?>">
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="active" <?= ($status == 'active') ? 'selected' : '' ?>>Aktif</option>
                            <option value="inactive" <?= ($status == 'inactive') ? 'selected' : '' ?>>Tidak Aktif</option>
                        </select>
                    </div>
                </div>

                <!-- Bagian Jadwal Praktik -->
                <div class="jadwal-section">
                    <div class="jadwal-title">Jadwal Praktik</div>
                    <div id="jadwal-container">
                        <?php if (!empty($jadwal_list)): ?>
                            <?php foreach ($jadwal_list as $j): ?>
                                <div class="jadwal-item">
                                    <select class="form-control" name="hari[]" required>
                                        <option value="">-- Pilih Hari --</option>
                                        <option value="Senin" <?= ($j['hari'] == 'Senin') ? 'selected' : '' ?>>Senin</option>
                                        <option value="Selasa" <?= ($j['hari'] == 'Selasa') ? 'selected' : '' ?>>Selasa</option>
                                        <option value="Rabu" <?= ($j['hari'] == 'Rabu') ? 'selected' : '' ?>>Rabu</option>
                                        <option value="Kamis" <?= ($j['hari'] == 'Kamis') ? 'selected' : '' ?>>Kamis</option>
                                        <option value="Jumat" <?= ($j['hari'] == 'Jumat') ? 'selected' : '' ?>>Jumat</option>
                                        <option value="Sabtu" <?= ($j['hari'] == 'Sabtu') ? 'selected' : '' ?>>Sabtu</option>
                                        <option value="Minggu" <?= ($j['hari'] == 'Minggu') ? 'selected' : '' ?>>Minggu</option>
                                    </select>
                                    <input type="time" class="form-control" name="jam_mulai[]" value="<?= htmlspecialchars($j['jam_mulai']) ?>" required>
                                    <input type="time" class="form-control" name="jam_selesai[]" value="<?= htmlspecialchars($j['jam_selesai']) ?>" required>
                                    <div class="remove-btn" onclick="removeJadwal(this)">
                                        <i class="fas fa-trash"></i>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="jadwal-item">
                                <select class="form-control" name="hari[]" required>
                                    <option value="">-- Pilih Hari --</option>
                                    <option value="Senin">Senin</option>
                                    <option value="Selasa">Selasa</option>
                                    <option value="Rabu">Rabu</option>
                                    <option value="Kamis">Kamis</option>
                                    <option value="Jumat">Jumat</option>
                                    <option value="Sabtu">Sabtu</option>
                                    <option value="Minggu">Minggu</option>
                                </select>
                                <input type="time" class="form-control" name="jam_mulai[]" required>
                                <input type="time" class="form-control" name="jam_selesai[]" required>
                                <div class="remove-btn" onclick="removeJadwal(this)">
                                    <i class="fas fa-trash"></i>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="add-jadwal-btn" onclick="addJadwal()">
                        <i class="fas fa-plus"></i> Tambah Jadwal
                    </button>
                </div>

                <div class="form-actions">
                    <a href="../Dokter/dokter.php" class="btn btn-outline">
                        <i class="fas fa-times"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-warning btn-submit">
                        <i class="fas fa-save"></i> Update Data Dokter & Jadwal
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        function addJadwal() {
            const container = document.getElementById('jadwal-container');
            const newItem = document.createElement('div');
            newItem.className = 'jadwal-item';
            newItem.innerHTML = `
                <select class="form-control" name="hari[]" required>
                    <option value="">-- Pilih Hari --</option>
                    <option value="Senin">Senin</option>
                    <option value="Selasa">Selasa</option>
                    <option value="Rabu">Rabu</option>
                    <option value="Kamis">Kamis</option>
                    <option value="Jumat">Jumat</option>
                    <option value="Sabtu">Sabtu</option>
                    <option value="Minggu">Minggu</option>
                </select>
                <input type="time" class="form-control" name="jam_mulai[]" required>
                <input type="time" class="form-control" name="jam_selesai[]" required>
                <div class="remove-btn" onclick="removeJadwal(this)">
                    <i class="fas fa-trash"></i>
                </div>
            `;
            container.appendChild(newItem);
        }

        function removeJadwal(button) {
            const item = button.parentNode;
            if (document.querySelectorAll('.jadwal-item').length > 1) {
                item.remove();
            } else {
                alert('Minimal harus ada 1 jadwal!');
            }
        }
    </script>
</body>
</html>