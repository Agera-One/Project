<?php
session_start();
include '../konfigurasi/koneksi.php';

// Cek apakah sudah login
if (isset($_SESSION['role'])) {
    $role = strtolower(trim($_SESSION['role']));
    
    switch ($role) {
        case 'admin':
            header('Location: ../mimin/Dashboard/dashboard.php');
            break;
        case 'pasien':
            header('Location: ../pasien/dashboard.php');
            break;
        default:
            session_destroy();
            header('Location: login.php');
    }
    exit();
}

// Cek method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

// Ambil data dari form
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validasi input
if (empty($email) || empty($password)) {
    header('Location: login.php?error=kosong');
    exit();
}

// Cek user di database
$stmt = $conn->prepare("SELECT u.id, u.email, u.password, u.role, p.id as pasien_id, p.nama, p.no_rm 
                        FROM users u 
                        LEFT JOIN pasien p ON u.id = p.user_id 
                        WHERE u.email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // VERIFIKASI PASSWORD
    if (password_verify($password, $user['password'])) {
        // Normalisasi role
        $role = strtolower(trim($user['role']));
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $role;
        
        // Debug (hapus setelah fix)
        error_log("LOGIN: Email={$user['email']}, Role={$role}");
        
        // Redirect berdasarkan role
        if ($role === 'pasien') {
            if ($user['pasien_id']) {
                $_SESSION['pasien_id'] = $user['pasien_id'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['no_rm'] = $user['no_rm'];
                header('Location: ../pasien/dashboard.php');
            } else {
                session_destroy();
                header('Location: login.php?error=user_not_found');
            }
        } 
        elseif ($role === 'admin') {
            header('Location: ../mimin/Dashboard/dashboard.php');
        }
        else {
            session_destroy();
            header('Location: login.php?error=access_denied');
        }
        
        exit();
    } else {
        header('Location: login.php?error=password');
        exit();
    }
} else {
    header('Location: login.php?error=email');
    exit();
}

$conn->close();
?>