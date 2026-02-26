<?php
session_start();
// CEK LOGIN (sesuaikan dengan sistemmu)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

// KONEKSI DATABASE
include '../../konfigurasi/koneksi.php';

// CEK AKSI HAPUS
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // CEK APAKAH PASIEN INI MASIH MEMILIKI ANTRIAN AKTIF
    $check_antrian = mysqli_query($conn, "SELECT id FROM pendaftaran WHERE pasien_id = $id AND status IN ('menunggu', 'sedang dilayani') LIMIT 1");
    if (mysqli_num_rows($check_antrian) > 0) {
        $_SESSION['error_message'] = "Gagal menghapus pasien! Pasien masih memiliki antrian aktif.";
        header("Location: ../Pasien/pasien.php");
        exit;
    }
    
    // PROSES HAPUS
    $delete_query = "DELETE FROM pasien WHERE id = $id";
    
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['success_message'] = "Data pasien berhasil dihapus!";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus data pasien: " . mysqli_error($conn);
    }
    
    // REDIRECT KE HALAMAN YANG BENAR
    header("Location: ../Pasien/pasien.php");
    exit;
}

// INISIALISASI VARIABEL
$search = $_GET['search'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 12; // Jumlah pasien per halaman
$offset = ($page - 1) * $limit;

// BANGUN QUERY DASAR
$sql = "SELECT * FROM pasien WHERE 1=1";

// TAMBAHKAN FILTER PENCARIAN
if ($search) {
    $search_term = mysqli_real_escape_string($conn, $search);
    $sql .= " AND (nama LIKE '%$search_term%' OR nik LIKE '%$search_term%' OR no_rm LIKE '%$search_term%')";
}

// HITUNG TOTAL DATA
$total_result = mysqli_query($conn, $sql);
$total_data = mysqli_num_rows($total_result);

// TAMBAHKAN PAGINASI
$sql .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";

// EKSEKUSI QUERY
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pasien - Klinik Sehat</title>
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
            max-width: 1400px;
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
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--border);
        }

        .page-header-left {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .page-title h2 {
            font-size: 1.75rem;
            color: var(--text-dark);
            margin-bottom: 0.25rem;
        }

        .page-title p {
            color: var(--text-gray);
            font-size: 0.875rem;
        }

        .page-header-right {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        /* Search and Filter */
        .toolbar {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow);
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
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
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-gray);
        }

        /* Button */
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
            white-space: nowrap;
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

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.8125rem;
        }

        .btn-danger {
            background: var(--danger);
            color: var(--white);
        }

        .btn-danger:hover {
            background: #dc2626;
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

        .btn-warning {
            background: var(--warning);
            color: var(--white);
        }

        .btn-warning:hover {
            background: #d97706;
        }

        /* Patient Cards */
        .patient-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        .patient-card {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .patient-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .patient-header {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--light);
        }

        .patient-avatar {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .patient-info {
            flex: 1;
        }

        .patient-name {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.25rem;
        }

        .patient-rm {
            font-size: 0.8125rem;
            color: var(--text-gray);
            font-weight: 600;
        }

        .patient-details {
            display: grid;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.875rem;
        }

        .detail-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: var(--light);
            color: var(--text-gray);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            flex-shrink: 0;
        }

        .detail-text {
            color: var(--text-dark);
        }

        .patient-actions {
            display: flex;
            gap: 0.5rem;
            padding-top: 1rem;
            border-top: 2px solid var(--light);
        }

        /* Alert Message */
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

        /* Pagination */
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
            text-decoration: none;
            color: var(--text-dark);
        }

        .page-item.active {
            background: var(--primary);
            color: var(--white);
        }

        .page-item:not(.active):hover {
            background: var(--light);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            background: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow);
            grid-column: 1 / -1;
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--text-gray);
            opacity: 0.5;
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            font-size: 1.25rem;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: var(--text-gray);
            margin-bottom: 1.5rem;
        }

        .empty-state a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .empty-state a:hover {
            text-decoration: underline;
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

            .page-header-right {
                width: 100%;
                flex-direction: column;
            }

            .page-header-right .btn {
                width: 100%;
                justify-content: center;
            }

            .toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .toolbar-actions {
                display: flex;
                gap: 0.75rem;
                width: 100%;
            }

            .toolbar-actions .btn {
                flex: 1;
            }

            .search-box {
                min-width: 100%;
            }

            .patient-grid {
                grid-template-columns: 1fr;
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
            </div>
        </div>
    </header>

    <!-- Main Container -->
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

        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-left">
                <div class="page-title">
                    <h2>Data Pasien</h2>
                    <p>Kelola data seluruh pasien yang terdaftar</p>
                </div>
            </div>
            <div class="page-header-right">
                <a href="../Pasien/tambah_pasien.php?step=1" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Pasien
                </a>
                  <a href="../Dashboard/dashboard.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="toolbar">
            <form method="GET" class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Cari pasien berdasarkan nama, NIK, atau No. RM..." value="<?= htmlspecialchars($search) ?>">
            </form>
            <div class="toolbar-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Cari
                </button>
                <a href="../Pasien/pasien.php" class="btn btn-outline">
                    <i class="fas fa-sync-alt"></i> Reset
                </a>
            </div>
        </div>

        <!-- Patient Grid -->
        <div class="patient-grid">
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="patient-card">
                        <div class="patient-header">
                            <div class="patient-avatar"><?= strtoupper(substr($row['nama'], 0, 2)) ?></div>
                            <div class="patient-info">
                                <div class="patient-name"><?= htmlspecialchars($row['nama']) ?></div>
                                <div class="patient-rm">No. RM: <?= htmlspecialchars($row['no_rm']) ?></div>
                            </div>
                        </div>
                        <div class="patient-details">
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-id-card"></i>
                                </div>
                                <div class="detail-text"><?= htmlspecialchars($row['nik']) ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="detail-text"><?= htmlspecialchars($row['no_hp'] ?: '-') ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-birthday-cake"></i>
                                </div>
                                <div class="detail-text"><?= date('d F Y', strtotime($row['tgl_lahir'])) ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="detail-text"><?= htmlspecialchars(substr($row['alamat'], 0, 30) . (strlen($row['alamat']) > 30 ? '...' : '')) ?></div>
                            </div>
                        </div>
                        <div class="patient-actions">
                            <a href="../Pasien/edit_pasien.php?id=<?= $row['id'] ?>" class="btn btn-outline btn-sm" style="flex: 1;">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <?php
                            // CEK APAKAH PASIEN INI MASIH MEMILIKI ANTRIAN AKTIF
                            $check_antrian = mysqli_query($conn, "SELECT id FROM pendaftaran WHERE pasien_id = {$row['id']} AND status IN ('menunggu', 'sedang dilayani') LIMIT 1");
                            $has_active_antrian = mysqli_num_rows($check_antrian) > 0;
                            ?>
                            <?php if ($has_active_antrian): ?>
                                <button class="btn btn-warning btn-sm" title="Tidak bisa dihapus karena masih memiliki antrian aktif" disabled>
                                    <i class="fas fa-ban"></i>
                                </button>
                            <?php else: ?>
                                <!-- HAPUS TANPA JS - langsung redirect -->
                                <a href="../Pasien/pasien.php?action=delete&id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data pasien <?= addslashes($row['nama']) ?>?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h3>Tidak Ada Data Pasien</h3>
                    <p>Belum ada pasien yang terdaftar di klinik.</p>
                    <a href="../Pasien/tambah_pasien.php?step=1">
                        <i class="fas fa-plus"></i> Tambah Pasien Baru
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
                
                if ($start_page > 1) {
                    echo '<a href="../Pasien/pasien.php?page=1&search=' . urlencode($search) . '" class="page-item">1</a>';
                    if ($start_page > 2) {
                        echo '<span class="page-item">...</span>';
                    }
                }
                
                for ($i = $start_page; $i <= $end_page; $i++) {
                    $active_class = ($i == $page) ? 'active' : '';
                    echo '<a href="../Pasien/pasien.php?page=' . $i . '&search=' . urlencode($search) . '" class="page-item ' . $active_class . '">' . $i . '</a>';
                }
                
                if ($end_page < $total_pages) {
                    if ($end_page < $total_pages - 1) {
                        echo '<span class="page-item">...</span>';
                    }
                    echo '<a href="../Pasien/pasien.php?page=' . $total_pages . '&search=' . urlencode($search) . '" class="page-item">' . $total_pages . '</a>';
                }
                ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>