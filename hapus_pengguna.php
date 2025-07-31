<?php
// hapus_pengguna.php (Versi Perbaikan dengan Cascading Delete)
include 'session_handler.php'; // PENTING: Untuk memulai dan menangani session
include 'koneksi.php';

// 1. Cek otorisasi, hanya admin yang boleh menghapus
if (!isset($_SESSION['loggedin']) || ($_SESSION['tipe_akun'] ?? null) !== 'admin') {
    header("Location: login.php");
    exit();
}

// 2. Ambil dan validasi ID pengguna dari URL
$id_pengguna = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_pengguna <= 0) {
    $_SESSION['flash_message'] = "Error: ID pengguna tidak valid.";
    header("Location: dashboard_admin.php?page=pengguna");
    exit();
}

// 3. Jangan biarkan admin menghapus akunnya sendiri
if ($id_pengguna == $_SESSION['user_id']) {
    $_SESSION['flash_message'] = "Error: Anda tidak dapat menghapus akun Anda sendiri.";
    header("Location: dashboard_admin.php?page=pengguna");
    exit();
}

// 4. PERUBAHAN: Gunakan Transaksi untuk memastikan integritas data
mysqli_begin_transaction($koneksi);

try {
    // Langkah 1: Hapus semua data pemesanan yang terkait dengan penyewa ini.
    // Ini akan menyelesaikan masalah foreign key constraint dari tabel 'pemesanan'.
    $query_delete_pemesanan = "DELETE FROM pemesanan WHERE id_penyewa = ?";
    $stmt_pemesanan = mysqli_prepare($koneksi, $query_delete_pemesanan);
    if ($stmt_pemesanan === false) {
        throw new Exception("Gagal mempersiapkan query untuk menghapus pemesanan terkait.");
    }
    mysqli_stmt_bind_param($stmt_pemesanan, "i", $id_pengguna);
    mysqli_stmt_execute($stmt_pemesanan);
    mysqli_stmt_close($stmt_pemesanan);

    // Langkah 2: Hapus pengguna itu sendiri.
    // Ini mungkin masih gagal jika pengguna adalah 'pemilik' yang punya data kost.
    $query_delete_pengguna = "DELETE FROM pengguna WHERE id_pengguna = ?";
    $stmt_pengguna = mysqli_prepare($koneksi, $query_delete_pengguna);
    if ($stmt_pengguna === false) {
        throw new Exception("Gagal mempersiapkan query untuk menghapus pengguna.");
    }
    mysqli_stmt_bind_param($stmt_pengguna, "i", $id_pengguna);
    
    if (!mysqli_stmt_execute($stmt_pengguna)) {
        // Jika eksekusi gagal, kemungkinan karena pengguna adalah 'pemilik'
        throw new Exception("Gagal menghapus. Pengguna ini mungkin seorang pemilik yang masih memiliki data kost. Hapus data kost terlebih dahulu.");
    }

    $affected_rows = mysqli_stmt_affected_rows($stmt_pengguna);
    mysqli_stmt_close($stmt_pengguna);

    if ($affected_rows > 0) {
        // Jika semua berhasil, commit transaksi
        mysqli_commit($koneksi);
        $_SESSION['flash_message'] = "Pengguna dan semua data pemesanan terkait berhasil dihapus.";
    } else {
        throw new Exception("Pengguna tidak ditemukan atau sudah dihapus sebelumnya.");
    }

} catch (Exception $e) {
    // Jika ada error di salah satu langkah, batalkan semua perubahan (rollback)
    mysqli_rollback($koneksi);
    $_SESSION['flash_message'] = $e->getMessage();
}

mysqli_close($koneksi);

// 5. Redirect kembali ke halaman manajemen pengguna
header("Location: dashboard_admin.php?page=pengguna");
exit();
?>
