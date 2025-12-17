<?php
require_once 'connection.php';

// Proses tambah data
if (isset($_POST['action'])) {
    if ($_POST['action'] == 'add') {

        // Ambil data dari form 
        $code = isset($_POST['code']) ? trim($_POST['code']) : '';
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $unit = isset($_POST['unit']) ? trim($_POST['unit']) : '';
        $price = isset($_POST['price']) ? trim($_POST['price']) : '';
        $image = isset($_FILES['image']['name']) ? $_FILES['image']['name'] : '';

        // Validasi input
        if (empty($code) || empty($name) || empty($unit) || empty($price) || empty($image)) {
            echo "Error: All fields are required.";
            exit;
        }

        // Upload gambar
        $target_dir = __DIR__ . "/../Assets/Images/";
        
        // Buat folder jika belum ada
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        
        // Debug: Cek permission folder
        if (!is_writable($target_dir)) {
            chmod($target_dir, 0755);
        }
        
        // Validasi tipe file
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            echo "Error: Only image files are allowed (JPEG, PNG, GIF, WebP). Type received: " . $_FILES['image']['type'];
            exit;
        }
        
        // Generate nama file unik
        $ext = pathinfo($image, PATHINFO_EXTENSION);
        $image = uniqid('produk_') . '.' . strtolower($ext);
        $target_file = $target_dir . $image;
        $tmp_file = $_FILES['image']['tmp_name'];
        
        // Debug: Cek file upload error
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $upload_errors = [
                UPLOAD_ERR_OK => 'No error',
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
                UPLOAD_ERR_PARTIAL => 'File partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Temporary folder missing',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by extension',
            ];
            echo "Error: " . $upload_errors[$_FILES['image']['error']];
            exit;
        }
        
        // Debug: Cek tmp file ada
        if (!is_uploaded_file($tmp_file)) {
            echo "Error: Invalid file upload.";
            exit;
        }
        
        if (!move_uploaded_file($tmp_file, $target_file)) {
            echo "Error uploading file to: " . $target_file . " | Temp file: " . $tmp_file . " | Writable: " . (is_writable($target_dir) ? 'Yes' : 'No');
            exit;
        }

        // Query insert data ke database menggunakan prepared statement
        $query = "INSERT INTO produk (kode, nama, satuan, harga, gambar) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($connection, $query);
        
        if (!$stmt) {
            echo "Error: " . mysqli_error($connection);
            exit;
        }
        
        mysqli_stmt_bind_param($stmt, 'ssdss', $code, $name, $unit, $price, $image);
        
        if (mysqli_stmt_execute($stmt)) {
            header("Location: index/index.php");
            exit;
        } else {
            echo "Error: " . mysqli_error($connection);
            exit;
        }
    } elseif ($_POST['action'] === 'edit') {
        // Ambil data dari form
        $id = intval($_POST['id']);
        $code = isset($_POST['code']) ? trim($_POST['code']) : '';
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $unit = isset($_POST['unit']) ? trim($_POST['unit']) : '';
        $price = isset($_POST['price']) ? trim($_POST['price']) : '';

        // Ambil data gambar lama dari database menggunakan prepared statement
        $queryShow = "SELECT gambar FROM produk WHERE id = ?";
        $stmt = mysqli_prepare($connection, $queryShow);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        
        if (!$result) {
            echo "Error: Product not found.";
            exit;
        }
        
        $imagePath = __DIR__ . "/../Assets/Images/" . $result['gambar'];

        // Cek apakah ada file gambar yang diupload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $image_ext = $_FILES['image']['name'];
            $target_dir = __DIR__ . "/../Assets/Images/";
            
            // Buat folder jika belum ada
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            
            // Debug: Cek permission folder
            if (!is_writable($target_dir)) {
                chmod($target_dir, 0755);
            }
            
            // Validasi tipe file
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($_FILES['image']['type'], $allowed_types)) {
                echo "Error: Only image files are allowed (JPEG, PNG, GIF, WebP). Type received: " . $_FILES['image']['type'];
                exit;
            }
            
            // Generate nama file unik
            $ext = pathinfo($image_ext, PATHINFO_EXTENSION);
            $image = uniqid('produk_') . '.' . strtolower($ext);
            $target_file = $target_dir . $image;
            $tmp_file = $_FILES['image']['tmp_name'];
            
            // Debug: Cek tmp file ada
            if (!is_uploaded_file($tmp_file)) {
                echo "Error: Invalid file upload.";
                exit;
            }
            
            if (!move_uploaded_file($tmp_file, $target_file)) {
                echo "Error uploading file to: " . $target_file . " | Temp file: " . $tmp_file . " | Writable: " . (is_writable($target_dir) ? 'Yes' : 'No');
                exit;
            }

            // Hapus file gambar lama dari server
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }

            // Update data termasuk gambar
            $query = "UPDATE produk SET kode=?, nama=?, satuan=?, harga=?, gambar=? WHERE id=?";
            $stmt = mysqli_prepare($connection, $query);
            mysqli_stmt_bind_param($stmt, 'ssdssi', $code, $name, $unit, $price, $image, $id);
            
            if (!mysqli_stmt_execute($stmt)) {
                echo "Error: " . mysqli_error($connection);
            } else {
                header("Location: index/index.php");
                exit;
            }
        } else {
            // Update data tanpa mengubah gambar
            $query = "UPDATE produk SET kode=?, nama=?, satuan=?, harga=? WHERE id=?";
            $stmt = mysqli_prepare($connection, $query);
            mysqli_stmt_bind_param($stmt, 'ssdsi', $code, $name, $unit, $price, $id);
            
            if (!mysqli_stmt_execute($stmt)) {
                echo "Error: " . mysqli_error($connection);
            } else {
                header("Location: index/index.php");
                exit;
            }
        }
    }
}


// Proses hapus data
if (isset($_GET['remove'])) {
    $id = intval($_GET['remove']);

    // Hapus file gambar terkait dari server menggunakan prepared statement
    $queryShow = "SELECT gambar FROM produk WHERE id = ?";
    $stmt = mysqli_prepare($connection, $queryShow);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    
    if ($result) {
        $imagePath = __DIR__ . "/../Assets/Images/" . $result['gambar'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    // Hapus data dari database
    $query = "DELETE FROM produk WHERE id = ?";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: index/index.php");
        exit;
    } else {
        echo "Error: " . mysqli_error($connection);
    }
}