<?php
// proses_registrasi.php

// Sertakan file koneksi database
include 'koneksi.php';

// Mulai session jika belum
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Pastikan koneksi ke database berhasil
if (!isset($koneksi) || !$koneksi) {
    $_SESSION['register_error'] = "Gagal koneksi ke database.";
    header("Location: registrasi.php");
    exit();
}

// Proses jika form dikirim melalui POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Ambil dan bersihkan data dari form
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    // Variabel $username di PHP tetap, tapi akan dimasukkan ke kolom 'nama_pengguna'
    $username     = mysqli_real_escape_string($koneksi, $_POST['username']);
    $email        = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password     = $_POST['password'];
    $tipe_akun    = mysqli_real_escape_string($koneksi, $_POST['tipe_akun']);
    $jenis_kelamin = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin']);

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // --- PERBAIKAN: Menggunakan nama kolom 'nama_pengguna' dari database ---
    $cek = mysqli_query($koneksi, "SELECT * FROM pengguna WHERE nama_pengguna='$username' OR email='$email'");
    
    if ($cek === false) {
        $_SESSION['register_error'] = "Terjadi kesalahan saat memeriksa data. " . mysqli_error($koneksi);
        header("Location: registrasi.php");
        exit();
    }

    if (mysqli_num_rows($cek) > 0) {
        $_SESSION['register_error'] = "Username atau email sudah digunakan.";
        header("Location: registrasi.php");
        exit();
    }

    // --- PERBAIKAN: Menggunakan nama kolom yang benar ('nama_pengguna', 'kata_sandi') untuk INSERT ---
    $query = "INSERT INTO pengguna (nama_lengkap, nama_pengguna, email, kata_sandi, jenis_kelamin, tipe_akun) 
              VALUES ('$nama_lengkap', '$username', '$email', '$hashed_password', '$jenis_kelamin', '$tipe_akun')";

    if (mysqli_query($koneksi, $query)) {
        $_SESSION['registration_success_message'] = "Registrasi berhasil! Silakan login.";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['register_error'] = "Terjadi kesalahan saat registrasi. " . mysqli_error($koneksi);
        header("Location: registrasi.php");
        exit();
    }
} else {
    // Redirect jika akses langsung
    header("Location: registrasi.php");
    exit();
}
?>
