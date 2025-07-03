<?php
// proses_login.php

// Mulai session jika belum ada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Sertakan file koneksi
include 'koneksi.php';

// Pastikan request adalah POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit();
}

// Ambil dan bersihkan input
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

// Validasi dasar: pastikan input tidak kosong
if (empty($username) || empty($password)) {
    $_SESSION['login_error'] = "Username dan password tidak boleh kosong.";
    header("Location: login.php");
    exit();
}

// Gunakan prepared statement untuk keamanan dari SQL Injection
$query = "SELECT id_pengguna, nama_pengguna, kata_sandi, tipe_akun, nama_lengkap FROM pengguna WHERE nama_pengguna = ?";
$stmt = mysqli_prepare($koneksi, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        // Verifikasi password
        if (password_verify($password, $user['kata_sandi'])) {
            // Login berhasil, set session
            session_regenerate_id(true); // Mencegah session fixation
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $user['id_pengguna'];
            $_SESSION['username'] = $user['nama_pengguna'];
            $_SESSION['full_name'] = $user['nama_lengkap'];
            $_SESSION['tipe_akun'] = $user['tipe_akun'];

            // --- PENGARAHAN BERDASARKAN PERAN (ROLE) ---
            if ($user['tipe_akun'] == 'admin') {
                // Jika admin, arahkan ke dasbor admin gabungan
                header("Location: dashboard_admin.php");
            } elseif ($user['tipe_akun'] == 'pemilik') {
                // Jika pemilik, arahkan ke dasbor khusus pemilik
                header("Location: home_pemilik.php");
            } elseif ($user['tipe_akun'] == 'penyewa') {
                // Jika penyewa, arahkan ke beranda penyewa
                header("Location: home_penyewa.php");
            } else {
                // Fallback jika peran tidak dikenali
                header("Location: login.php");
            }
            exit();

        } else {
            // Password salah
            $_SESSION['login_error'] = "Username atau password salah.";
            header("Location: login.php");
            exit();
        }
    } else {
        // Pengguna tidak ditemukan
        $_SESSION['login_error'] = "Username atau password salah.";
        header("Location: login.php");
        exit();
    }
    mysqli_stmt_close($stmt);
} else {
    // Gagal menyiapkan statement
    $_SESSION['login_error'] = "Terjadi kesalahan pada server.";
    header("Location: login.php");
    exit();
}

mysqli_close($koneksi);
?>
