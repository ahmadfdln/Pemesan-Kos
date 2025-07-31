<?php include 'koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - Heaven Indekos</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa; /* Warna latar belakang terang */
        }
        /* Custom animation for hero background */
        @keyframes zoomPan {
            0% {
                transform: scale(1) translateX(0);
            }
            50% {
                transform: scale(1.05) translateX(5%);
            }
            100% {
                transform: scale(1) translateX(0);
            }
        }
        .hero-bg-animated {
            animation: zoomPan 30s infinite alternate ease-in-out;
        }
    </style>
</head>
<body class="flex flex-col min-h-screen">
    <!-- Header/Navbar -->
    <header class="bg-white shadow-lg py-4">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <a href="index.php" class="text-3xl font-extrabold text-blue-700 rounded-md p-2">Heaven Indekos</a>
            <nav class="hidden md:flex space-x-6">
                <a href="index.php" class="text-gray-700 hover:text-blue-600 font-medium px-4 py-2 rounded-md transition duration-300 ease-in-out">Beranda</a>
                <a href="#features" class="text-gray-700 hover:text-blue-600 font-medium px-4 py-2 rounded-md transition duration-300 ease-in-out">Fitur</a>
                <a href="#gallery" class="text-gray-700 hover:text-blue-600 font-medium px-4 py-2 rounded-md transition duration-300 ease-in-out">Galeri</a>
                <a href="login.php" class="bg-blue-600 text-white font-semibold px-6 py-2 rounded-full hover:bg-blue-700 transition duration-300 ease-in-out shadow-md">Login</a>
            </nav>
            <!-- Mobile Menu Button -->
            <button class="md:hidden text-gray-700 text-2xl focus:outline-none">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative text-white py-20 md:py-32 flex-grow flex items-center justify-center overflow-hidden">
        <!-- Gambar latar belakang placeholder dengan animasi -->
        <!-- URL gambar placeholder telah diperbarui untuk mencerminkan latar belakang gambar kost -->
        <img src="image/bg.jpg" alt="Latar Belakang Indekos" class="absolute inset-0 w-full h-full object-cover z-0 hero-bg-animated">
        <!-- Overlay untuk kegelapan gambar latar belakang -->
        <div class="absolute inset-0 bg-black opacity-50 z-10"></div>
        
        <div class="container mx-auto px-4 text-center relative z-20">
            <h1 class="text-4xl md:text-6xl font-extrabold leading-tight mb-4 animate-fade-in-up">
                Temukan Kost Terbaik Impianmu
            </h1>
            <p class="text-lg md:text-xl mb-8 opacity-90 animate-fade-in-up delay-200 max-w-2xl mx-auto">
                Mau cari kost yang nyaman, aman, dan strategis? Dapatkan infonya dan langsung sewa di Heaven Indekos.
            </p>
            <a href="#" class="bg-white text-blue-700 font-bold px-8 py-4 rounded-full shadow-xl hover:bg-gray-100 hover:scale-105 transition duration-300 ease-in-out inline-block animate-fade-in-up delay-400 transform hover:-translate-y-1">
                Mulai Cari Kost Sekarang <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </section>

    <!-- Featured Amenities Section -->
    <section id="features" class="py-16 bg-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-10">Fitur Unggulan Kami</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-gray-50 p-6 rounded-lg shadow-md hover:shadow-xl transition duration-300 ease-in-out transform hover:-translate-y-2">
                    <div class="text-blue-600 text-5xl mb-4"><i class="fas fa-wifi"></i></div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Internet Cepat</h3>
                    <p class="text-gray-600">Nikmati koneksi internet super cepat untuk semua kebutuhan online Anda.</p>
                </div>
                <!-- Feature 2 -->
                <div class="bg-gray-50 p-6 rounded-lg shadow-md hover:shadow-xl transition duration-300 ease-in-out transform hover:-translate-y-2">
                    <div class="text-blue-600 text-5xl mb-4"><i class="fas fa-shield-alt"></i></div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Keamanan 24/7</h3>
                    <p class="text-gray-600">Keamanan terjamin dengan pengawasan 24 jam dan akses terbatas.</p>
                </div>
                <!-- Feature 3 -->
                <div class="bg-gray-50 p-6 rounded-lg shadow-md hover:shadow-xl transition duration-300 ease-in-out transform hover:-translate-y-2">
                    <div class="text-blue-600 text-5xl mb-4"><i class="fas fa-bed"></i></div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Kamar Nyaman</h3>
                    <p class="text-gray-600">Kamar bersih, luas, dan dilengkapi fasilitas modern untuk kenyamanan Anda.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Kost Gallery Section -->
    <section id="gallery" class="py-16 bg-gray-100">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-10">Galeri Kost Pilihan</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Kost Card 1 -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden transform hover:scale-105 transition duration-300 ease-in-out">
                    <img src="image/cartd1.jpg" alt="Kamar Kost 1" class="w-full h-48 object-cover">
                    <div class="p-6 text-left">
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">Kost Melati Indah</h3>
                        <p class="text-gray-600 mb-4">Lokasi strategis dekat kampus dan pusat perbelanjaan.</p>
                        <span class="inline-block bg-blue-100 text-blue-800 text-xs font-semibold px-3 py-1 rounded-full"><i class="fas fa-map-marker-alt mr-1"></i> Blang Bintatang</span>
                    </div>
                </div>
                <!-- Kost Card 2 -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden transform hover:scale-105 transition duration-300 ease-in-out">
                    <img src="image/card2.jpg" alt="Kamar Kost 2" class="w-full h-48 object-cover">
                    <div class="p-6 text-left">
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">Kost Anggrek Residence</h3>
                        <p class="text-gray-600 mb-4">Fasilitas lengkap dengan area komunal yang nyaman.</p>
                        <span class="inline-block bg-blue-100 text-blue-800 text-xs font-semibold px-3 py-1 rounded-full"><i class="fas fa-map-marker-alt mr-1"></i> Blang Bintatang</span>
                    </div>
                </div>
                <!-- Kost Card 3 -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden transform hover:scale-105 transition duration-300 ease-in-out">
                    <img src="image/card3.jpg" alt="Kamar Kost 3" class="w-full h-48 object-cover">
                    <div class="p-6 text-left">
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">Kost Cempaka Asri</h3>
                        <p class="text-gray-600 mb-4">Lingkungan tenang dan aman, cocok untuk mahasiswa.</p>
                        <span class="inline-block bg-blue-100 text-blue-800 text-xs font-semibold px-3 py-1 rounded-full"><i class="fas fa-map-marker-alt mr-1"></i> Blang Bintatang</span>
                    </div>
                </div>
            </div>
            <a href="#" class="mt-10 inline-block bg-blue-600 text-white font-semibold px-8 py-3 rounded-full hover:bg-blue-700 transition duration-300 ease-in-out shadow-md">Lihat Semua Kost <i class="fas fa-arrow-right ml-2"></i></a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-6 mt-auto">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; <?php echo date("Y"); ?> Heaven Indekos. Semua Hak Dilindungi.</p>
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
