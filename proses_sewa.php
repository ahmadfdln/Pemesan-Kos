<?php
include 'koneksi.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_penyewa = $_POST['id_penyewa'] ?? null;
    $nama_kost = $_POST['nama_kost'] ?? null;
    $harga = $_POST['harga'] ?? null;
    $tanggal_masuk = $_POST['tanggal_masuk'] ?? null;
    $tanggal_keluar = $_POST['tanggal_keluar'] ?? null;
    $metode_pembayaran = $_POST['metode_pembayaran'] ?? null;

    if (!$id_penyewa || !$nama_kost || !$harga || !$tanggal_masuk || !$tanggal_keluar || !$metode_pembayaran) {
        echo "❌ Data tidak valid.";
        exit();
    }

    $query = "INSERT INTO pemesanan (
        id_penyewa, nama_kost, harga, tanggal_masuk, tanggal_keluar, status, metode_pembayaran
    ) VALUES (
        '$id_penyewa', '$nama_kost', '$harga', '$tanggal_masuk', '$tanggal_keluar', 'Menunggu Konfirmasi', '$metode_pembayaran'
    )";

    if (mysqli_query($koneksi, $query)) {
        echo "✅ Pemesanan berhasil! <a href='riwayat_pemesanan.php'>Lihat Riwayat</a>";
    } else {
        echo "❌ Terjadi kesalahan saat memproses pesanan: " . mysqli_error($koneksi);
    }
} else {
    echo "Akses tidak valid.";
}
?>
