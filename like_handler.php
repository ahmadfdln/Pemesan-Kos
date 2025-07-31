<?php
// like_handler.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';

header('Content-Type: application/json');

// Pastikan pengguna sudah login dan memiliki user_id di session
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Anda harus login untuk menyukai item.']);
    exit();
}

// Ambil data dari permintaan POST
$id_kost = isset($_POST['id_kost']) ? (int)$_POST['id_kost'] : 0;
$id_pengguna = (int)$_SESSION['user_id'];

if ($id_kost > 0 && $id_pengguna > 0) {
    // Cek apakah item sudah ada di wishlist
    $check_sql = "SELECT id FROM wishlist WHERE id_pengguna = ? AND id_kost = ?";
    $stmt_check = mysqli_prepare($koneksi, $check_sql);
    mysqli_stmt_bind_param($stmt_check, "ii", $id_pengguna, $id_kost);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);

    if (mysqli_num_rows($result_check) > 0) {
        // Jika sudah ada, hapus dari wishlist (unlike)
        $delete_sql = "DELETE FROM wishlist WHERE id_pengguna = ? AND id_kost = ?";
        $stmt_delete = mysqli_prepare($koneksi, $delete_sql);
        mysqli_stmt_bind_param($stmt_delete, "ii", $id_pengguna, $id_kost);
        if (mysqli_stmt_execute($stmt_delete)) {
            echo json_encode(['status' => 'success', 'action' => 'unliked']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus dari wishlist.']);
        }
    } else {
        // Jika belum ada, tambahkan ke wishlist (like)
        $insert_sql = "INSERT INTO wishlist (id_pengguna, id_kost) VALUES (?, ?)";
        $stmt_insert = mysqli_prepare($koneksi, $insert_sql);
        mysqli_stmt_bind_param($stmt_insert, "ii", $id_pengguna, $id_kost);
        if (mysqli_stmt_execute($stmt_insert)) {
            echo json_encode(['status' => 'success', 'action' => 'liked']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menambahkan ke wishlist.']);
        }
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak valid.']);
}
?>
