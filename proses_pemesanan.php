<?php
// proses_pemesanan.php (Untuk Aksi Admin)
include 'session_handler.php'; // Seharusnya ada di folder yang sama
include 'koneksi.php';        // Path ini sudah diperbaiki (tanpa ../)

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['loggedin']) || ($_SESSION['tipe_akun'] ?? null) !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    exit('Akses ditolak.');
}

// Validasi input dari URL (menggunakan $_GET)
if (!isset($_GET['id']) || !isset($_GET['aksi'])) {
    $_SESSION['flash_message'] = "Error: Aksi atau ID tidak valid.";
    header("Location: dashboard_admin.php?page=pemesanan");
    exit();
}

$id_pemesanan = (int)$_GET['id'];
$aksi = $_GET['aksi'];
$status_baru = '';

// Tentukan status baru berdasarkan aksi dari URL
if ($aksi === 'konfirmasi') {
    $status_baru = 'Dikonfirmasi';
} elseif ($aksi === 'batalkan') {
    $status_baru = 'Ditolak';
} else {
    // Jika aksi tidak dikenali, redirect kembali
    $_SESSION['flash_message'] = "Error: Aksi tidak dikenali.";
    header("Location: dashboard_admin.php?page=pemesanan");
    exit();
}

// Gunakan prepared statement untuk keamanan
$query = "UPDATE pemesanan SET status = ? WHERE id = ?";
$stmt = mysqli_prepare($koneksi, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "si", $status_baru, $id_pemesanan);
    
    if (mysqli_stmt_execute($stmt)) {
        // Jika berhasil, siapkan pesan sukses dan redirect
        $_SESSION['flash_message'] = "Status pesanan #$id_pemesanan berhasil diubah menjadi '$status_baru'.";
    } else {
        // Jika gagal
        $_SESSION['flash_message'] = "Error: Gagal mengubah status pesanan. " . mysqli_error($koneksi);
    }
    
    mysqli_stmt_close($stmt);
} else {
    $_SESSION['flash_message'] = "Error: Gagal mempersiapkan query. " . mysqli_error($koneksi);
}

mysqli_close($koneksi);

// Redirect kembali ke halaman pemesanan setelah semua logika selesai
header("Location: dashboard_admin.php?page=pemesanan");
exit();
?>
