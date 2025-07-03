<?php
// hapus_kamar.php

include 'koneksi.php';
session_start();

// 1. Cek otorisasi (hanya admin atau pemilik yang boleh menghapus)
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['tipe_akun'], ['pemilik', 'admin'])) {
    header("Location: login.php");
    exit();
}

// 2. Ambil dan validasi ID dari URL
$id_kost = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_kost <= 0) {
    $_SESSION['pesan_error'] = "ID kost tidak valid.";
    // PERBAIKAN: Arahkan kembali ke dasbor admin yang benar
    header("Location: dashboard_admin.php"); 
    exit();
}

// 3. Hapus file gambar terkait dari folder 'uploads'
$query_get_foto = "SELECT foto FROM kost WHERE id_kost = ?";
$stmt_get = mysqli_prepare($koneksi, $query_get_foto);
mysqli_stmt_bind_param($stmt_get, "i", $id_kost);
mysqli_stmt_execute($stmt_get);
$result_get = mysqli_stmt_get_result($stmt_get);

if ($data = mysqli_fetch_assoc($result_get)) {
    $nama_foto = $data['foto'];
    if (!empty($nama_foto)) {
        $path_foto = 'uploads/' . $nama_foto;
        if (file_exists($path_foto)) {
            unlink($path_foto); // Hapus file dari server
        }
    }
}
mysqli_stmt_close($stmt_get);


// 4. Hapus data kost dari database
$query_delete = "DELETE FROM kost WHERE id_kost = ?";
$stmt_delete = mysqli_prepare($koneksi, $query_delete);

if ($stmt_delete === false) {
    die("Error preparing delete statement: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt_delete, "i", $id_kost);

if (mysqli_stmt_execute($stmt_delete)) {
    $_SESSION['pesan_sukses'] = "Data kost berhasil dihapus.";
} else {
    // Cek error spesifik untuk foreign key (jika kost masih punya data pemesanan/penilaian)
    if (mysqli_errno($koneksi) == 1451) {
        $_SESSION['pesan_error'] = "Gagal menghapus. Kost ini memiliki data pemesanan atau penilaian yang terkait. Hapus data terkait terlebih dahulu.";
    } else {
        $_SESSION['pesan_error'] = "Gagal menghapus data kost: " . mysqli_stmt_error($stmt_delete);
    }
}
mysqli_stmt_close($stmt_delete);

// 5. Redirect kembali ke halaman dasbor admin yang benar
header("Location: dashboard_admin.php");
exit();
?>
