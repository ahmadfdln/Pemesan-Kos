<?php
// Mulai sesi untuk menangani pesan error
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php'; 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Heaven Indekos</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts - Poppins (disamakan dengan halaman registrasi) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f4f8; /* Warna background lembut */
        }
        /* Style untuk container input dengan ikon */
        .form-input-container {
            position: relative;
        }
        /* Style untuk ikon di dalam input */
        .form-input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af; /* gray-400 */
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="flex flex-col md:flex-row bg-white rounded-2xl shadow-2xl w-full max-w-5xl mx-auto overflow-hidden">
            
            <!-- Kolom Kiri - Gambar & Welcome Text (dibuat konsisten dengan registrasi) -->
            <div class="hidden md:flex md:w-1/2 bg-gradient-to-br from-indigo-500 to-purple-600 p-12 text-white flex-col justify-center items-center text-center">
                <h1 class="text-4xl font-bold mb-4">Selamat Datang Kembali!</h1>
                <p class="mb-8 max-w-sm">Masuk untuk melanjutkan petualangan Anda dalam mencari atau menyewakan kos impian.</p>
                <!-- Menggunakan placeholder yang relevan dengan tema -->
                <img src="image/bg.jpg"
                     alt="Ilustrasi Login"
                     class="rounded-lg shadow-lg w-full h-auto object-cover max-w-xs">
            </div>

            <!-- Kolom Kanan - Form Login -->
            <div class="w-full md:w-1/2 p-8 md:p-12 flex flex-col justify-center">
                <h2 class="text-3xl font-bold text-gray-800 mb-2 text-center">Login ke Akun Anda</h2>
                <p class="text-gray-500 mb-6 text-center">Silakan masukkan detail akun Anda.</p>

                <!-- Menampilkan Notifikasi Error Login -->
                <?php if (isset($_SESSION['login_error'])): ?>
                    <div class="flex items-center bg-red-100 border-l-4 border-red-500 text-red-800 p-4 mb-4 rounded-md" role="alert">
                        <i class="fas fa-exclamation-triangle fa-lg mr-3"></i>
                        <p><?= $_SESSION['login_error']; ?></p>
                        <?php unset($_SESSION['login_error']); // Hapus pesan setelah ditampilkan ?>
                    </div>
                <?php endif; ?>
                
                <form action="proses_login.php" method="POST" class="space-y-5">
                    <div class="form-input-container">
                        <i class="fas fa-user form-input-icon"></i>
                        <input type="text" name="username" placeholder="Username" required class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    </div>
                    <div class="form-input-container">
                        <i class="fas fa-lock form-input-icon"></i>
                        <input type="password" name="password" placeholder="Kata Sandi" required class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    </div>
                    
                    <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold py-3 rounded-lg hover:opacity-90 transition duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        Login
                    </button>
                </form>

                <p class="text-center text-gray-600 mt-6">
                    Belum punya akun?
                    <a href="registrasi.php" class="text-indigo-600 hover:underline font-semibold transition">Daftar di sini</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
