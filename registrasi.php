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
    <title>Registrasi - Heaven Indekos</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
    <div class="flex flex-col md:flex-row bg-white rounded-lg shadow-xl w-full max-w-5xl mx-auto overflow-hidden transform transition duration-500 hover:scale-[1.01]">
        
        <!-- Form Registrasi -->
        <div class="md:w-1/2 p-8 md:p-12 flex flex-col justify-center">
            <h2 class="text-3xl md:text-4xl font-bold text-center text-gray-800 mb-6">Daftar Akun Baru</h2>

            <!-- ALERT SECTION -->
            <?php if (isset($_SESSION['register_error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    <?php 
                        echo $_SESSION['register_error']; 
                        unset($_SESSION['register_error']); 
                    ?>
                </div>
            <?php elseif (isset($_SESSION['registration_success_message'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    <?php 
                        echo $_SESSION['registration_success_message']; 
                        unset($_SESSION['registration_success_message']); 
                    ?>
                </div>
            <?php endif; ?>
            
            <form action="proses_registrasi.php" method="POST" class="space-y-4">
                <div>
                    <label for="nama_lengkap" class="sr-only">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" id="nama_lengkap" placeholder="Nama Lengkap" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="username" class="sr-only">Username</label>
                    <input type="text" name="username" id="username" placeholder="Username" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="email" class="sr-only">Email</label>
                    <input type="email" name="email" id="email" placeholder="Email" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="password" class="sr-only">Kata Sandi</label>
                    <input type="password" name="password" id="password" placeholder="Kata Sandi" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="tipe_akun" class="block text-sm font-medium text-gray-700 mb-1">Daftar Sebagai:</label>
                    <select name="tipe_akun" id="tipe_akun" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-md bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="penyewa">Penyewa</option>
                        <option value="pemilik">Pemilik</option>
                    </select>
                </div>
                <button type="submit"
                    class="w-full bg-blue-600 text-white font-semibold py-3 rounded-md hover:bg-blue-700 transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                    Daftar <i class="fas fa-user-plus ml-2"></i>
                </button>
            </form>

            <p class="text-center text-gray-600 mt-6">
                Sudah punya akun?
                <a href="login.php" class="text-blue-600 hover:text-blue-800 font-medium transition duration-300">Login di sini</a>
            </p>
        </div>

        <!-- Gambar Kanan -->
        <div class="md:w-1/2 bg-blue-600 flex items-center justify-center p-6 md:p-12">
            <img src="https://placehold.co/600x400/4A90E2/FFFFFF?text=Temukan+Kost+Impianmu"
                alt="Ilustrasi Registrasi Indekos"
                class="rounded-lg shadow-lg w-full h-auto object-cover max-h-96 md:max-h-full">
        </div>
    </div>
</body>
</html>
