<?php
// proses_konfirmasi.php

include 'koneksi.php';
session_start();

// 1. Cek otorisasi, hanya admin yang boleh mengakses
if (!isset($_SESSION['loggedin']) || $_SESSION['tipe_akun'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 2. Ambil dan validasi parameter dari URL
$id_pemesanan = isset($_GET['id']) ? intval($_GET['id']) : 0;
$aksi = isset($_GET['aksi']) ? $_GET['aksi'] : '';

if ($id_pemesanan <= 0 || !in_array($aksi, ['konfirmasi', 'tolak'])) {
    $_SESSION['pesan_error'] = "Aksi tidak valid.";
    header("Location: dashboard_admin.php");
    exit();
}

// Tentukan status baru berdasarkan aksi
$status_baru = '';
if ($aksi == 'konfirmasi') {
    $status_baru = 'Dikonfirmasi';
} elseif ($aksi == 'tolak') {
    $status_baru = 'Ditolak';
}

// 3. Update status pemesanan di database
$query_update = "UPDATE pemesanan SET status = ? WHERE id = ?";
$stmt = mysqli_prepare($koneksi, $query_update);
mysqli_stmt_bind_param($stmt, "si", $status_baru, $id_pemesanan);

if (mysqli_stmt_execute($stmt)) {
    // --- PERBAIKAN ---
    // Logika untuk mengembalikan status kost saat ditolak dihapus sementara
    // karena tabel 'pemesanan' Anda tidak memiliki kolom 'id_kost'.
    //
    // CATATAN: Jika Anda menolak pemesanan, Anda harus mengubah status kost
    // kembali menjadi 'publish' secara manual melalui menu Manajemen Kost.
    // Untuk fungsionalitas penuh, sangat disarankan untuk menambahkan
    // kolom 'id_kost' ke tabel 'pemesanan'.
    
    $_SESSION['pesan_sukses'] = "Status pemesanan berhasil diperbarui.";
} else {
    $_SESSION['pesan_error'] = "Gagal memperbarui status pemesanan.";
}

mysqli_stmt_close($stmt);

// 4. Redirect kembali ke dasbor admin
header("Location: dashboard_admin.php");
exit();

?>
