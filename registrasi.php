<?php
include 'koneksi.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Buat Akun Heaven Indekos</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts - Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f4f8; /* Warna background lebih lembut */
        }
        .form-input-container {
            position: relative;
        }
        .form-input-icon {
            position: absolute;
            left: 1rem; /* Posisi ikon dari kiri */
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af; /* gray-400 */
            pointer-events: none; /* Agar ikon tidak bisa diklik */
        }
        /* PERBAIKAN: Kelas .form-input dihapus dari sini karena kita akan menggunakan kelas Tailwind langsung */
    </style>
</head>
<body>
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="flex flex-col md:flex-row bg-white rounded-2xl shadow-2xl w-full max-w-5xl mx-auto overflow-hidden">
            
            <!-- Kolom Kiri - Gambar & Welcome Text -->
            <div class="hidden md:flex md:w-1/2 bg-gradient-to-br from-indigo-500 to-purple-600 p-12 text-white flex-col justify-center items-center text-center">
                <h1 class="text-4xl font-bold mb-4">Selamat Datang!</h1>
                <p class="mb-8 max-w-sm">Temukan kos impian Anda atau sewakan properti Anda dengan mudah bersama Heaven Indekos.</p>
                <img src="image/bg.jpg"
                     alt="Ilustrasi Indekos"
                     class="rounded-lg shadow-lg w-full h-auto object-cover max-w-xs">
            </div>

            <!-- Kolom Kanan - Form Registrasi -->
            <div class="w-full md:w-1/2 p-8 md:p-12 flex flex-col justify-center">
                <h2 class="text-3xl font-bold text-gray-800 mb-2 text-center">Buat Akun Anda</h2>
                <p class="text-gray-500 mb-6 text-center">Hanya butuh beberapa langkah untuk memulai.</p>

                <!-- Tampilan Notifikasi Ditingkatkan -->
                <?php if (isset($_SESSION['register_error'])): ?>
                    <div class="flex items-center bg-red-100 border-l-4 border-red-500 text-red-800 p-4 mb-4 rounded-md" role="alert">
                        <i class="fas fa-exclamation-triangle fa-lg mr-3"></i>
                        <p><?= $_SESSION['register_error']; ?></p>
                        <?php unset($_SESSION['register_error']); ?>
                    </div>
                <?php elseif (isset($_SESSION['registration_success_message'])): ?>
                    <div class="flex items-center bg-green-100 border-l-4 border-green-500 text-green-800 p-4 mb-4 rounded-md" role="alert">
                        <i class="fas fa-check-circle fa-lg mr-3"></i>
                        <p><?= $_SESSION['registration_success_message']; ?></p>
                        <?php unset($_SESSION['registration_success_message']); ?>
                    </div>
                <?php endif; ?>
                
                <form action="proses_registrasi.php" method="POST" class="space-y-5">
                    <div class="form-input-container">
                        <i class="fas fa-user form-input-icon"></i>
                        <!-- PERBAIKAN: Menggunakan pl-12 (padding-left: 3rem) untuk memberi ruang bagi ikon dan pr-4 untuk padding kanan -->
                        <input type="text" name="nama_lengkap" placeholder="Nama Lengkap" required class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    </div>
                    <div class="form-input-container">
                        <i class="fas fa-at form-input-icon"></i>
                        <!-- PERBAIKAN: Menggunakan pl-12 (padding-left: 3rem) untuk memberi ruang bagi ikon dan pr-4 untuk padding kanan -->
                        <input type="text" name="username" placeholder="Username" required class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    </div>
                    <div class="form-input-container">
                        <i class="fas fa-envelope form-input-icon"></i>
                        <!-- PERBAIKAN: Menggunakan pl-12 (padding-left: 3rem) untuk memberi ruang bagi ikon dan pr-4 untuk padding kanan -->
                        <input type="email" name="email" placeholder="Alamat Email" required class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    </div>
                    <div class="form-input-container">
                        <i class="fas fa-lock form-input-icon"></i>
                        <!-- PERBAIKAN: Menggunakan pl-12 (padding-left: 3rem) untuk memberi ruang bagi ikon dan pr-4 untuk padding kanan -->
                        <input type="password" name="password" placeholder="Kata Sandi" required class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    </div>
                    
                    <!-- Kolom Jenis Kelamin & Tipe Akun -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="jenis_kelamin" class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin</label>
                            <select name="jenis_kelamin" id="jenis_kelamin" required class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 focus:outline-none cursor-not-allowed">
                                <option value="Perempuan" selected>Perempuan</option>
                            </select>
                        </div>
                        <div>
                            <label for="tipe_akun" class="block text-sm font-medium text-gray-700 mb-1">Daftar Sebagai</label>
                            <select name="tipe_akun" id="tipe_akun" required class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                                <option value="penyewa">Penyewa</option>
                                <option value="pemilik">Pemilik</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold py-3 rounded-lg hover:opacity-90 transition duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        Daftar Sekarang
                    </button>
                </form>

                <p class="text-center text-gray-600 mt-6">
                    Sudah punya akun?
                    <a href="login.php" class="text-indigo-600 hover:underline font-semibold transition">Login di sini</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
