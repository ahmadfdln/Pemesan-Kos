<?php
// proses_sewa.php

include 'koneksi.php';
session_start();

// 1. Cek apakah pengguna sudah login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['pesan_error'] = "Anda harus login untuk menyewa.";
    header("Location: login.php");
    exit();
}

// 2. Pastikan metode request adalah POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // Redirect atau tampilkan pesan error jika bukan POST
    header("Location: home_penyewa.php");
    exit();
}

// 3. Ambil dan validasi data dari form
$id_kost = $_POST['id_kost'] ?? null;
$id_penyewa = $_POST['id_penyewa'] ?? null;
$durasi_sewa = $_POST['durasi_sewa'] ?? null;
$tanggal_masuk_str = $_POST['tanggal_masuk'] ?? null;
$metode_pembayaran = $_POST['metode_pembayaran'] ?? null;

if (!$id_kost || !$id_penyewa || !$durasi_sewa || !$tanggal_masuk_str || !$metode_pembayaran) {
    $_SESSION['pesan_error'] = "Data pemesanan tidak lengkap. Silakan coba lagi.";
    // Redirect kembali ke halaman detail dengan ID kost jika ada
    header("Location: detail_kos.php?id=" . ($id_kost ?? ''));
    exit();
}

// 4. Ambil data kost dari database untuk validasi dan keamanan
$query_cek_kost = "SELECT nama_kost, harga FROM kost WHERE id_kost = ? AND status = 'publish'";
$stmt_cek = mysqli_prepare($koneksi, $query_cek_kost);
mysqli_stmt_bind_param($stmt_cek, "i", $id_kost);
mysqli_stmt_execute($stmt_cek);
$result_cek = mysqli_stmt_get_result($stmt_cek);

if (mysqli_num_rows($result_cek) == 0) {
    $_SESSION['pesan_error'] = "Kost tidak tersedia atau tidak ditemukan.";
    header("Location: home_penyewa.php");
    exit();
}

$data_kost_server = mysqli_fetch_assoc($result_cek);
$nama_kost_server = $data_kost_server['nama_kost'];
$harga_server = $data_kost_server['harga'];
mysqli_stmt_close($stmt_cek);

// 5. Lakukan kalkulasi di server
$total_harga = (int)$durasi_sewa * (int)$harga_server;
$tanggal_masuk = new DateTime($tanggal_masuk_str);
$tanggal_keluar = clone $tanggal_masuk;
$tanggal_keluar->add(new DateInterval("P{$durasi_sewa}M"));

// Mengambil tanggal dan waktu saat ini sesuai zona waktu Jakarta (WIB)
// Pastikan kolom 'tanggal_pemesanan' di database Anda bertipe DATETIME atau TIMESTAMP
$tanggal_pemesanan = (new DateTime('now', new DateTimeZone('Asia/Jakarta')))->format('Y-m-d H:i:s');

// 6. Siapkan dan eksekusi query INSERT
$query_insert = "INSERT INTO pemesanan (id_penyewa, id_kost, nama_kost, harga, total_harga, tanggal_masuk, tanggal_keluar, status, metode_pembayaran, tanggal_pemesanan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt_insert = mysqli_prepare($koneksi, $query_insert);

$status_pemesanan = 'Menunggu Konfirmasi';
$tanggal_masuk_db = $tanggal_masuk->format('Y-m-d');
$tanggal_keluar_db = $tanggal_keluar->format('Y-m-d');

// Tipe data pada bind_param sudah sesuai (i untuk integer, s untuk string)
mysqli_stmt_bind_param($stmt_insert, "iisiisssss", 
    $id_penyewa, 
    $id_kost, 
    $nama_kost_server, 
    $harga_server,
    $total_harga,
    $tanggal_masuk_db, 
    $tanggal_keluar_db, 
    $status_pemesanan, 
    $metode_pembayaran,
    $tanggal_pemesanan
);

if (mysqli_stmt_execute($stmt_insert)) {
    // Jika pemesanan berhasil, ubah status kost menjadi 'draft'
    $query_update_kost = "UPDATE kost SET status = 'draft' WHERE id_kost = ?";
    $stmt_update = mysqli_prepare($koneksi, $query_update_kost);
    mysqli_stmt_bind_param($stmt_update, "i", $id_kost);
    mysqli_stmt_execute($stmt_update);
    mysqli_stmt_close($stmt_update);

    $_SESSION['pesan_sukses'] = "Pemesanan berhasil! Silakan cek riwayat pemesanan untuk detail dan pembayaran.";
    header("Location: riwayat_pemesanan.php");
    exit();
} else {
    // Jika gagal, kirim pesan error yang lebih spesifik
    $_SESSION['pesan_error'] = "Gagal memproses pesanan: " . mysqli_stmt_error($stmt_insert);
    header("Location: detail_kos.php?id=" . $id_kost);
    exit();
}

mysqli_stmt_close($stmt_insert);
?>
