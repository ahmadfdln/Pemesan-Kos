<?php
session_start();
include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validasi input
    if (empty($username) || empty($password)) {
        $_SESSION['pesan_error'] = "Username dan password wajib diisi.";
        header("Location: login.php");
        exit();
    }

    // Ambil data pengguna dari database
    $query = "SELECT * FROM pengguna WHERE nama_pengguna = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        // Verifikasi password
        if (password_verify($password, $user['kata_sandi'])) {
            // Set session login
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $user['id_pengguna'];
            $_SESSION['tipe_akun'] = $user['tipe_akun'];
            $_SESSION['full_name'] = $user['nama_lengkap'];

            // Redirect berdasarkan tipe akun
            if ($user['tipe_akun'] === 'admin') {
                header("Location: dashboard_admin.php");
            } elseif ($user['tipe_akun'] === 'pemilik') {
                header("Location: home_pemilik.php");
            } else {
                header("Location: home_penyewa.php");
            }
            exit;
        } else {
            $_SESSION['pesan_error'] = "Password salah.";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['pesan_error'] = "Akun tidak ditemukan.";
        header("Location: login.php");
        exit();
    }
} else {
    // Jika file diakses langsung tanpa POST
    header("Location: login.php");
    exit();
}
?>
