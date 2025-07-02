<?php
// dashboard_admin.php

include '../koneksi.php'; // Sesuaikan path jika dashboard_admin.php di subfolder

// Pastikan session sudah dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah pengguna sudah login dan memiliki tipe_akun 'admin'
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || ($_SESSION['tipe_akun'] ?? '') !== 'admin') {
    // Jika tidak login atau bukan admin, redirect ke halaman login
    header("Location: ../login.php");
    exit();
}

// Ambil username admin dari session
$admin_username = $_SESSION['username'] ?? 'Admin';

// Query untuk mengambil data statistik
// Pastikan nama tabel dan kolom sesuai dengan database Anda
$jumlah_pengguna = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM pengguna"));
$jumlah_pemilik  = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM pengguna WHERE tipe_akun='pemilik'"));
$jumlah_admin    = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM pengguna WHERE tipe_akun='admin'"));
$jumlah_penyewa  = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM pengguna WHERE tipe_akun='penyewa'")); // Tambahan untuk penyewa

// Asumsi tabel 'kamar_kos' dari diskusi sebelumnya, bukan 'kost'
// Jika nama tabel Anda adalah 'kost', ganti 'kamar_kos' menjadi 'kost'
$jumlah_kost     = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM kamar_kos"));
$kost_tersedia   = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM kamar_kos WHERE status_ketersediaan='Tersedia'")); // Menggunakan status_ketersediaan
$kost_tidak_tersedia = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM kamar_kos WHERE status_ketersediaan='Tidak Tersedia'")); // Menggunakan status_ketersediaan

$bookingan       = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM pemesanan"));
$penilaian       = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM penilaian")); // Asumsi tabel penilaian ada
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Heaven Indekos</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5; /* Warna latar belakang abu-abu terang */
        }
    </style>
</head>
<body class="flex flex-col min-h-screen">
    <!-- Header/Navbar -->
    <header class="bg-blue-700 shadow-lg py-4">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <a href="dashboard_admin.php" class="text-3xl font-extrabold text-white rounded-md p-2">Admin Panel</a>
            <nav class="hidden md:flex items-center space-x-6">
                <a href="manage_users.php" class="text-blue-200 hover:text-white font-medium px-4 py-2 rounded-md transition duration-300 ease-in-out">Manajemen Pengguna</a>
                <a href="manage_kost.php" class="text-blue-200 hover:text-white font-medium px-4 py-2 rounded-md transition duration-300 ease-in-out">Manajemen Kos</a>
                <a href="manage_bookings.php" class="text-blue-200 hover:text-white font-medium px-4 py-2 rounded-md transition duration-300 ease-in-out">Manajemen Pemesanan</a>
                <a href="../logout.php" class="bg-red-500 text-white font-semibold px-6 py-2 rounded-full hover:bg-red-600 transition duration-300 ease-in-out shadow-md">Logout <i class="fas fa-sign-out-alt ml-2"></i></a>
            </nav>
            <!-- Mobile Menu Button (implement with JS if needed) -->
            <button class="md:hidden text-white text-2xl focus:outline-none">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>

    <!-- Welcome Section -->
    <section class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-16 md:py-20 text-center">
        <div class="container mx-auto px-4">
            <h1 class="text-4xl md:text-5xl font-extrabold leading-tight mb-4">
                Selamat Datang, <span class="text-yellow-300"><?php echo htmlspecialchars($admin_username); ?></span>!
            </h1>
            <p class="text-lg md:text-xl opacity-90 max-w-2xl mx-auto">
                Ringkasan dan kontrol penuh atas Heaven Indekos.
            </p>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="py-16 bg-gray-100 flex-grow">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-12 text-center">Ringkasan Statistik</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Card: Total Pengguna -->
                <div class="bg-white rounded-lg shadow-md p-6 text-center transform hover:scale-105 transition duration-300 ease-in-out">
                    <div class="text-blue-600 text-5xl mb-4"><i class="fas fa-users"></i></div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Total Pengguna</h3>
                    <p class="text-4xl font-bold text-blue-700"><?= $jumlah_pengguna ?></p>
                </div>

                <!-- Card: Pemilik Kost -->
                <div class="bg-white rounded-lg shadow-md p-6 text-center transform hover:scale-105 transition duration-300 ease-in-out">
                    <div class="text-green-600 text-5xl mb-4"><i class="fas fa-user-tie"></i></div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Pemilik Kost</h3>
                    <p class="text-4xl font-bold text-green-700"><?= $jumlah_pemilik ?></p>
                </div>

                <!-- Card: Penyewa -->
                <div class="bg-white rounded-lg shadow-md p-6 text-center transform hover:scale-105 transition duration-300 ease-in-out">
                    <div class="text-purple-600 text-5xl mb-4"><i class="fas fa-user-tag"></i></div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Penyewa</h3>
                    <p class="text-4xl font-bold text-purple-700"><?= $jumlah_penyewa ?></p>
                </div>

                <!-- Card: Admin -->
                <div class="bg-white rounded-lg shadow-md p-6 text-center transform hover:scale-105 transition duration-300 ease-in-out">
                    <div class="text-red-600 text-5xl mb-4"><i class="fas fa-user-shield"></i></div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Admin</h3>
                    <p class="text-4xl font-bold text-red-700"><?= $jumlah_admin ?></p>
                </div>

                <!-- Card: Total Kost -->
                <div class="bg-white rounded-lg shadow-md p-6 text-center transform hover:scale-105 transition duration-300 ease-in-out">
                    <div class="text-yellow-600 text-5xl mb-4"><i class="fas fa-building"></i></div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Total Kost</h3>
                    <p class="text-4xl font-bold text-yellow-700"><?= $jumlah_kost ?></p>
                </div>

                <!-- Card: Kost Tersedia -->
                <div class="bg-white rounded-lg shadow-md p-6 text-center transform hover:scale-105 transition duration-300 ease-in-out">
                    <div class="text-teal-600 text-5xl mb-4"><i class="fas fa-house-chimney-medical"></i></div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Kost Tersedia</h3>
                    <p class="text-4xl font-bold text-teal-700"><?= $kost_tersedia ?></p>
                </div>

                <!-- Card: Kost Tidak Tersedia -->
                <div class="bg-white rounded-lg shadow-md p-6 text-center transform hover:scale-105 transition duration-300 ease-in-out">
                    <div class="text-orange-600 text-5xl mb-4"><i class="fas fa-house-circle-xmark"></i></div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Kost Tidak Tersedia</h3>
                    <p class="text-4xl font-bold text-orange-700"><?= $kost_tidak_tersedia ?></p>
                </div>

                <!-- Card: Jumlah Pemesanan -->
                <div class="bg-white rounded-lg shadow-md p-6 text-center transform hover:scale-105 transition duration-300 ease-in-out">
                    <div class="text-indigo-600 text-5xl mb-4"><i class="fas fa-receipt"></i></div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Jumlah Pemesanan</h3>
                    <p class="text-4xl font-bold text-indigo-700"><?= $bookingan ?></p>
                </div>

                <!-- Card: Jumlah Penilaian -->
                <div class="bg-white rounded-lg shadow-md p-6 text-center transform hover:scale-105 transition duration-300 ease-in-out">
                    <div class="text-pink-600 text-5xl mb-4"><i class="fas fa-star"></i></div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Jumlah Penilaian</h3>
                    <p class="text-4xl font-bold text-pink-700"><?= $penilaian ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-6 mt-auto">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; <?php echo date("Y"); ?> Heaven Indekos Admin. Semua Hak Dilindungi.</p>
            <div class="flex justify-center space-x-4 mt-3">
                <a href="#" class="text-gray-400 hover:text-white transition duration-300 ease-in-out"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="text-gray-400 hover:text-white transition duration-300 ease-in-out"><i class="fab fa-twitter"></i></a>
                <a href="#" class="text-gray-400 hover:text-white transition duration-300 ease-in-out"><i class="fab fa-instagram"></i></a>
                <a href="#" class="text-gray-400 hover:text-white transition duration-300 ease-in-out"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>
    </footer>
</body>
</html>
