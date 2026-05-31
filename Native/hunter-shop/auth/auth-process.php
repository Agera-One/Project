<?php

session_start();
include "../connection.php";

if (isset($_POST["login"])) {
    $email    = $_POST["email"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["id"];
            header("Location: ../dashboard/dashboard.html");
            exit();
        } else {
            echo '<script>alert("Incorrect password. Please try again.")</script>';
        }
    } else {
        echo '<script>alert("Email not found. Please register first.")</script>';
    }
}

if (isset($_POST["register"])) {
    $username = $_POST["username"];
    $email    = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $checkEmailQuery = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $checkEmailQuery);

    if (mysqli_num_rows($result) > 0) {
        echo '<script>alert("Email already exists. Please use a different email.")</script>';
        echo '<script>window.location.href = "register.php";</script>';
    } else {
        $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";

        if (mysqli_query($conn, $sql)) {
            echo '<script>alert("Registration successful. Please log in.")</script>';
            echo '<script>window.location.href = "login.html";</script>';
        } else {
            echo '<script>alert("Error occurred during registration.")</script>';
        }
    }
    
}

if (isset($_POST["logout"])) {
    session_destroy();
    header("Location: login.html");
    exit();
}