<?php
include '../koneksi.php';
session_start();

// Cek login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Ambil data dari form
$id_penyewa = $_SESSION['username']; // pastikan ini ID, bukan username literal
$id_kost = $_POST['id_kost'] ?? '';
$tanggal_masuk = date('Y-m-d'); // bisa diganti dengan input dari user
$tanggal_keluar = date('Y-m-d', strtotime("+30 days")); // default sebulan

// Ambil info kost dari tabel
$queryKost = mysqli_query($koneksi, "SELECT * FROM kamar_kos WHERE id = '$id_kost'");
$dataKost = mysqli_fetch_assoc($queryKost);

if ($dataKost) {
    $nama_kost = $dataKost['nama_kost'];
    $harga = $dataKost['harga'];

    // Simpan ke pemesanan
    $insert = mysqli_query($koneksi, "INSERT INTO pemesanan 
        (id_penyewa, nama_kost, harga, tanggal_masuk, tanggal_keluar, status)
        VALUES ('$id_penyewa', '$nama_kost', '$harga', '$tanggal_masuk', '$tanggal_keluar', 'Menunggu Pembayaran')");

    if ($insert) {
        echo "<script>alert('Pemesanan berhasil! Silakan unggah bukti pembayaran.'); window.location='riwayat_pemesanan.php';</script>";
    } else {
        echo "Gagal menyimpan data: " . mysqli_error($koneksi);
    }
} else {
    echo "Kost tidak ditemukan.";
}
?>
