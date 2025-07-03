<?php
// hapus_pengguna.php

include 'koneksi.php';
session_start();

// 1. Cek otorisasi, hanya admin yang boleh menghapus
if (!isset($_SESSION['loggedin']) || $_SESSION['tipe_akun'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 2. Ambil dan validasi ID pengguna dari URL
$id_pengguna = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_pengguna <= 0) {
    $_SESSION['pesan_error'] = "ID pengguna tidak valid.";
    // PERBAIKAN: Arahkan kembali ke dasbor admin yang benar
    header("Location: dashboard_admin.php");
    exit();
}

// 3. Jangan biarkan admin menghapus akunnya sendiri
if ($id_pengguna == $_SESSION['user_id']) {
    $_SESSION['pesan_error'] = "Anda tidak dapat menghapus akun Anda sendiri.";
    // PERBAIKAN: Arahkan kembali ke dasbor admin yang benar
    header("Location: dashboard_admin.php");
    exit();
}

// 4. Hapus pengguna dari database menggunakan prepared statement
$query_delete = "DELETE FROM pengguna WHERE id_pengguna = ?";
$stmt = mysqli_prepare($koneksi, $query_delete);

if ($stmt === false) {
    die("Error preparing delete statement: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, "i", $id_pengguna);

if (mysqli_stmt_execute($stmt)) {
    // Jika berhasil
    $_SESSION['pesan_sukses'] = "Pengguna berhasil dihapus.";
} else {
    // Jika gagal, cek error spesifik untuk foreign key
    if (mysqli_errno($koneksi) == 1451) {
        $_SESSION['pesan_error'] = "Gagal menghapus. Pengguna ini (pemilik) masih memiliki data kost yang terdaftar. Hapus data kost milik pengguna ini terlebih dahulu.";
    } else {
        // Jika error lain
        $_SESSION['pesan_error'] = "Gagal menghapus pengguna: " . mysqli_stmt_error($stmt);
    }
}

mysqli_stmt_close($stmt);

// 5. Redirect kembali ke halaman dasbor admin yang benar
header("Location: dashboard_admin.php");
exit();

?>
