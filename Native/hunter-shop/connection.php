<?php

$hostname = "localhost";
$username = "root";
$password = "";
$database = "hunter_shop";

$conn = mysqli_connect($hostname,$username,$password,$database);

if(!$conn){
    die("Connection Failed: " . mysqli_connect_error());
}