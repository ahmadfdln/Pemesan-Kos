<?php
// proses_konfirmasi.php (Versi AJAX)

include 'session_handler.php';
include 'koneksi.php';

// Atur header untuk output JSON
header('Content-Type: application/json');

// Cek otorisasi
if (!isset($_SESSION['loggedin']) || ($_SESSION['tipe_akun'] ?? null) !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Sesi tidak valid.']);
    exit();
}

// Ambil data dari URL
$id = $_GET['id'] ?? 0;
$aksi = $_GET['aksi'] ?? '';

if (empty($id) || !in_array($aksi, ['konfirmasi', 'tolak'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID atau aksi tidak valid.']);
    exit();
}

// Tentukan status baru berdasarkan aksi
$new_status = '';
if ($aksi === 'konfirmasi') {
    $new_status = 'Dikonfirmasi';
} elseif ($aksi === 'tolak') {
    $new_status = 'Ditolak';
}

// Update database
// Gunakan prepared statements untuk keamanan
$stmt = $koneksi->prepare("UPDATE pemesanan SET status = ? WHERE id = ?");
$stmt->bind_param("si", $new_status, $id);

if ($stmt->execute()) {
    // Jika berhasil
    $response = [
        'status' => 'success',
        'message' => 'Pemesanan berhasil ' . $new_status . '.',
        'new_status' => $new_status
    ];
    echo json_encode($response);
} else {
    // Jika gagal
    $response = [
        'status' => 'error',
        'message' => 'Gagal memperbarui status pemesanan.'
    ];
    echo json_encode($response);
}

$stmt->close();
$koneksi->close();
?>
