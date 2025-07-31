<?php
session_start();
include 'koneksi.php';

// Cek apakah user admin
if (!isset($_SESSION['loggedin']) || $_SESSION['tipe_akun'] !== 'admin') {
    echo "Akses ditolak!";
    exit;
}

// Proses ubah tipe akun
if (isset($_POST['id_pengguna'], $_POST['tipe_akun_baru'])) {
    $id = intval($_POST['id_pengguna']);
    $tipe_baru = $_POST['tipe_akun_baru'];

    // Validasi tipe akun baru
    $allowed_roles = ['admin', 'pemilik', 'penghuni'];
    if (in_array($tipe_baru, $allowed_roles)) {
        $update = mysqli_query($koneksi, "UPDATE pengguna SET tipe_akun = '$tipe_baru' WHERE id_pengguna = $id");

        if ($update) {
            $_SESSION['pesan_sukses'] = "Tipe akun berhasil diperbarui.";
        } else {
            $_SESSION['pesan_error'] = "Gagal mengubah tipe akun.";
        }
    } else {
        $_SESSION['pesan_error'] = "Tipe akun tidak valid.";
    }
}
header("Location: kelola_pengguna.php");
exit;
