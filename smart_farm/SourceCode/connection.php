<?php
$host = 'localhost';
$port = '3306';
$user = 'root';
$pass = '';
$data = 'smart_farm';

$connection = mysqli_connect($host, $user, $pass, $data, $port);

if (!$connection) {
    die('Database connection failed: ' . mysqli_connect_error());
}

mysqli_set_charset($connection, 'utf8mb4');
