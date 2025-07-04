<?php
// home_penyewa.php

// Mulai session jika belum ada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Sertakan file koneksi database
include 'koneksi.php';

// Cek apakah pengguna sudah login DAN perannya adalah 'penyewa'
if (!isset($_SESSION['loggedin']) || $_SESSION['tipe_akun'] !== 'penyewa') {
    header("Location: login.php");
    exit();
}

// Ambil nama lengkap dari session untuk sapaan yang lebih personal
$nama_lengkap = $_SESSION['full_name'] ?? 'Penyewa';

// --- PERBAIKAN LOGIKA PENGAMBILAN DATA ---
// 1. Mengambil dari tabel 'kost' (bukan 'kamar_kos')
// 2. Hanya mengambil kost yang statusnya 'publish'
$sql = "SELECT * FROM kost WHERE status = 'publish' ORDER BY id_kost DESC";
$result = mysqli_query($koneksi, $sql);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Penyewa - Heaven Indekos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        @keyframes fadeInDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade-in-down { animation: fadeInDown 0.8s ease-out forwards; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade-in-up { animation: fadeInUp 0.8s ease-out forwards; }
    </style>
</head>
<body class="flex flex-col min-h-screen">

    <!-- Navbar -->
    <header class="bg-white shadow-lg py-4 sticky top-0 z-20">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <a href="home_penyewa.php" class="text-3xl font-extrabold text-blue-700">Heaven Indekos</a>
            <nav class="hidden md:flex items-center space-x-6">
                <a href="riwayat_pemesanan.php" class="text-gray-700 hover:text-blue-600 font-medium px-4 py-2 rounded-md">Riwayat Pemesanan</a>
                <a href="pengaturan.php" class="text-gray-700 hover:text-blue-600 font-medium px-4 py-2 rounded-md">Pengaturan</a>
                <a href="logout.php" class="bg-red-500 text-white font-semibold px-6 py-2 rounded-full hover:bg-red-600">Logout <i class="fas fa-sign-out-alt ml-2"></i></a>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-20 md:py-32 text-center overflow-hidden">
        <div class="absolute inset-0 z-0 opacity-20">
            <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none"><circle cx="20" cy="20" r="15" fill="currentColor" class="text-blue-400 opacity-75"></circle><circle cx="80" cy="50" r="20" fill="currentColor" class="text-indigo-400 opacity-75"></circle><rect x="50" y="10" width="10" height="10" rx="2" fill="currentColor" class="text-blue-300 opacity-75"></rect></svg>
        </div>
        <div class="container mx-auto px-4 relative z-10">
            <h1 class="text-4xl md:text-5xl font-extrabold leading-tight mb-4 animate-fade-in-down">Selamat Datang, <span class="text-yellow-300"><?php echo htmlspecialchars($nama_lengkap); ?></span>!</h1>
            <p class="text-lg md:text-xl opacity-90 max-w-2xl mx-auto mb-8 animate-fade-in-down" style="animation-delay: 0.2s;">Temukan kamar kost impian Anda dan kelola pemesanan dengan mudah di sini.</p>
            <a href="#available-rooms" class="bg-white text-blue-700 font-bold px-8 py-4 rounded-full shadow-xl hover:bg-gray-100 hover:scale-105 transition duration-300 ease-in-out inline-block animate-fade-in-up" style="animation-delay: 0.4s;">Mulai Cari Kost <i class="fas fa-arrow-down ml-2"></i></a>
        </div>
    </section>

    <!-- Kamar Tersedia -->
    <section id="available-rooms" class="py-16 bg-gray-100 flex-grow">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-12">Pilihan Kost Tersedia untuk Anda</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <?php
                            // --- PENYESUAIAN DENGAN KOLOM TABEL 'kost' ---
                            $harga = number_format($row['harga'], 0, ',', '.');
                            // Menggunakan gambar dari folder 'uploads' jika ada, jika tidak pakai placeholder
                            $gambar = !empty($row['foto']) ? 'uploads/' . htmlspecialchars($row['foto']) : 'https://placehold.co/600x400/e2e8f0/4a5568?text=Gambar+Kos';
                        ?>
                        <div class="bg-white rounded-xl shadow-lg overflow-hidden transition transform hover:scale-[1.02]">
                            <img src="<?= $gambar ?>" alt="Gambar <?= htmlspecialchars($row['nama_kost']); ?>" class="w-full h-48 object-cover">
                            <div class="p-6 text-left">
                                <h3 class="text-xl font-semibold text-gray-800 mb-2"><?= htmlspecialchars($row['nama_kost']); ?></h3>
                                <p class="text-gray-600 mb-4 text-sm"><?= htmlspecialchars(substr($row['alamat'], 0, 100)); ?>...</p>
                                <div class="flex justify-between items-center mb-4 text-gray-700">
                                    <span class="text-lg text-blue-700 font-medium">Rp <?= $harga; ?><span class="text-sm text-gray-500">/bulan</span></span>
                                    <span class="font-semibold text-green-600"><i class="fas fa-circle mr-1 text-xs"></i> Tersedia</span>
                                </div>
                                <a href="detail_kos.php?id=<?= $row['id_kost']; ?>" class="w-full block text-center bg-blue-600 text-white font-semibold py-3 rounded-lg hover:bg-blue-700">Lihat Detail <i class="fas fa-chevron-right ml-1"></i></a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="col-span-full text-gray-500">Saat ini belum ada kost yang tersedia. Silakan cek kembali nanti.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-6 mt-auto">
        <div class="container mx-auto text-center">
            <p>&copy; <?= date("Y"); ?> Heaven Indekos. Semua Hak Dilindungi.</p>
        </div>
    </footer>
</body>
</html>
