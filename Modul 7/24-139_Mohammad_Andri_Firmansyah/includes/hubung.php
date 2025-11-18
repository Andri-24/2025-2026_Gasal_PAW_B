<?php
$servername = "localhost";
$name = "root";
$password = "";
$dbname = "penjualan2";

$conn = mysqli_connect($servername, $name, $password, $dbname);

if (!$conn){
    die("Koneksi gagal: " . mysqli_connect_error());
}

?>