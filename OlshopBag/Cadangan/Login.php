<?php
    session_start();
    $a_email = "ageraone@gmail.com";
    $a_password = password_hash("123", PASSWORD_DEFAULT);

    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        $email = $_POST['email'];
        $password = $_POST['password'];
        if ($email == $a_email) {
            if (password_verify($password, $a_password)) {
                $_SESSION['email'] = $a_email;
                header('Location: Home.php');
                exit;
            } else {
                echo "
                <script>
                    alert('Password tidak sesuai');
                    window.location.href = 'index.php'; 
                </script>";
                exit;
            }
        } else {
            echo "
            <script>
                alert('Email tidak sesuai');
                window.location.href = 'index.php'; 
            </script>";
            exit;
        }
    }
?>