<?php
// koneksi.php

$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_kos";

$koneksi = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
