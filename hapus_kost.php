<?php
// hapus_kost.php
include 'session_handler.php';
include 'koneksi.php';

// 1. Cek otorisasi, hanya admin yang boleh menghapus
if (!isset($_SESSION['loggedin']) || ($_SESSION['tipe_akun'] ?? null) !== 'admin') {
    header("Location: login.php");
    exit();
}

// 2. Ambil dan validasi ID kost dari URL
$id_kost = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_kost <= 0) {
    $_SESSION['flash_message'] = "Error: ID kost tidak valid.";
    header("Location: dashboard_admin.php?page=kost");
    exit();
}

// Variabel untuk menyimpan nama file foto
$nama_foto = null;

// Mulai transaksi database
mysqli_begin_transaction($koneksi);

try {
    // Langkah A: Ambil nama file foto sebelum menghapus data kost
    $query_get_foto = "SELECT foto FROM kost WHERE id_kost = ?";
    $stmt_get = mysqli_prepare($koneksi, $query_get_foto);
    if (!$stmt_get) throw new Exception("Gagal mempersiapkan query untuk mengambil foto.");
    
    mysqli_stmt_bind_param($stmt_get, "i", $id_kost);
    mysqli_stmt_execute($stmt_get);
    $result_get = mysqli_stmt_get_result($stmt_get);
    if ($data = mysqli_fetch_assoc($result_get)) {
        $nama_foto = $data['foto'];
    }
    mysqli_stmt_close($stmt_get);

    // Langkah B: Hapus semua data pemesanan yang terkait dengan kost ini
    $query_delete_pemesanan = "DELETE FROM pemesanan WHERE id_kost = ?";
    $stmt_pemesanan = mysqli_prepare($koneksi, $query_delete_pemesanan);
    if (!$stmt_pemesanan) throw new Exception("Gagal mempersiapkan query untuk menghapus pemesanan terkait.");
    
    mysqli_stmt_bind_param($stmt_pemesanan, "i", $id_kost);
    mysqli_stmt_execute($stmt_pemesanan);
    mysqli_stmt_close($stmt_pemesanan);

    // Langkah C: Hapus data kost itu sendiri
    $query_delete_kost = "DELETE FROM kost WHERE id_kost = ?";
    $stmt_kost = mysqli_prepare($koneksi, $query_delete_kost);
    if (!$stmt_kost) throw new Exception("Gagal mempersiapkan query untuk menghapus kost.");

    mysqli_stmt_bind_param($stmt_kost, "i", $id_kost);
    mysqli_stmt_execute($stmt_kost);

    $affected_rows = mysqli_stmt_affected_rows($stmt_kost);
    mysqli_stmt_close($stmt_kost);

    if ($affected_rows > 0) {
        // Jika semua query database berhasil, commit transaksi
        mysqli_commit($koneksi);

        // Langkah D: Hapus file gambar dari server SETELAH commit berhasil
        if (!empty($nama_foto)) {
            $path_foto = 'uploads/' . $nama_foto; // Sesuaikan path jika berbeda
            if (file_exists($path_foto)) {
                unlink($path_foto);
            }
        }
        $_SESSION['flash_message'] = "Data kost dan pemesanan terkait berhasil dihapus.";
    } else {
        throw new Exception("Data kost tidak ditemukan atau sudah dihapus.");
    }

} catch (Exception $e) {
    // Jika terjadi error, batalkan semua perubahan
    mysqli_rollback($koneksi);
    $_SESSION['flash_message'] = "Error: " . $e->getMessage();
}

mysqli_close($koneksi);

// 5. Redirect kembali ke halaman manajemen kost
header("Location: dashboard_admin.php?page=kost");
exit();
?>
