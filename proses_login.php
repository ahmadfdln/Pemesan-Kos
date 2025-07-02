<?php
// proses_login.php

// Sertakan file koneksi database
include 'koneksi.php';

// Pastikan session sudah dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah koneksi berhasil
if (!isset($koneksi) || $koneksi === null) {
    $_SESSION['login_error'] = "Terjadi masalah koneksi database. Mohon periksa konfigurasi database Anda di koneksi.php dan pastikan server MySQL berjalan.";
    header("Location: login.php");
    exit();
}

// Proses hanya jika request-nya POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $username_input = $_POST['username'];
    $password_input = $_POST['password'];

    // Lindungi dari SQL Injection
    $username_input = mysqli_real_escape_string($koneksi, $username_input);

    // Ambil data pengguna
    $query = "SELECT id_pengguna, nama_pengguna, kata_sandi, tipe_akun FROM pengguna WHERE nama_pengguna='$username_input'";
    $result = mysqli_query($koneksi, $query);

    // Periksa apakah pengguna ditemukan
    if (mysqli_num_rows($result) == 1) {
        $user_data = mysqli_fetch_assoc($result);
        $hashed_password_from_db = $user_data['kata_sandi'];

        // Verifikasi kata sandi
        if (password_verify($password_input, $hashed_password_from_db)) {
            // Login berhasil
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $user_data['nama_pengguna'];
            $_SESSION['user_id'] = $user_data['id_pengguna'];
            $_SESSION['tipe_akun'] = $user_data['tipe_akun'];

            // Tambahkan id_penyewa jika akun adalah penyewa
            if ($user_data['tipe_akun'] == 'penyewa') {
                $_SESSION['id_penyewa'] = $user_data['id_pengguna']; // <-- Tambahan penting ini!
                header("Location: home_penyewa.php");
            } else if ($user_data['tipe_akun'] == 'pemilik') {
                header("Location: home_pemilik.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            // Password salah
            $_SESSION['login_error'] = "Username atau kata sandi salah.";
            header("Location: login.php");
            exit();
        }
    } else {
        // Pengguna tidak ditemukan
        $_SESSION['login_error'] = "Username atau kata sandi salah.";
        header("Location: login.php");
        exit();
    }
} else {
    // Jika bukan POST
    header("Location: login.php");
    exit();
}

// Tutup koneksi
mysqli_close($koneksi);
?>
