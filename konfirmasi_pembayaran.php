<?php
// konfirmasi_pembayaran.php

include 'koneksi.php'; // Sesuaikan path jika konfirmasi_pembayaran.php di subfolder

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah pengguna sudah login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php"); // Redirect ke halaman login jika belum login
    exit();
}

// Ambil data konfirmasi dari session
$konfirmasi_data = $_SESSION['konfirmasi_pemesanan'] ?? null;

// Jika tidak ada data konfirmasi di session, redirect kembali
if (!$konfirmasi_data) {
    header("Location: home_penyewa.php");
    exit();
}

// Hapus data konfirmasi dari session setelah diambil
unset($_SESSION['konfirmasi_pemesanan']);

$nama_kamar = htmlspecialchars($konfirmasi_data['nama_kamar']);
$lokasi_kamar = htmlspecialchars($konfirmasi_data['lokasi_kamar']);
$durasi_sewa = htmlspecialchars($konfirmasi_data['durasi_sewa']);
$total_harga = number_format($konfirmasi_data['total_harga'], 0, ',', '.');
$metode_pembayaran = htmlspecialchars($konfirmasi_data['metode_pembayaran']);
$id_pemesanan = htmlspecialchars($konfirmasi_data['id_pemesanan']);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pembayaran - Heaven Indekos</title>
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
    </style>
</head>
<body class="flex flex-col min-h-screen">
    <!-- Header/Navbar -->
    <header class="bg-white shadow-lg py-4">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <a href="home_penyewa.php" class="text-3xl font-extrabold text-blue-700 rounded-md p-2">Heaven Indekos</a>
            <nav class="hidden md:flex items-center space-x-6">
                <a href="riwayat_pemesanan.php" class="text-gray-700 hover:text-blue-600 font-medium px-4 py-2 rounded-md transition duration-300 ease-in-out">Riwayat Pemesanan</a>
                <a href="pengaturan.php" class="text-gray-700 hover:text-blue-600 font-medium px-4 py-2 rounded-md transition duration-300 ease-in-out">Pengaturan</a>
                <a href="../logout.php" class="bg-red-500 text-white font-semibold px-6 py-2 rounded-full hover:bg-red-600 transition duration-300 ease-in-out shadow-md">Logout <i class="fas fa-sign-out-alt ml-2"></i></a>
            </nav>
            <!-- Mobile Menu Button (implement with JS if needed) -->
            <button class="md:hidden text-gray-700 text-2xl focus:outline-none">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>

    <!-- Main Content - Payment Confirmation -->
    <main class="flex-grow py-12 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="bg-white rounded-lg shadow-xl overflow-hidden p-6 md:p-8 text-center">
                <div class="text-green-500 text-6xl mb-6">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Pemesanan Berhasil Dibuat!</h1>
                <p class="text-lg text-gray-700 mb-8 max-w-2xl mx-auto">
                    Pesanan Anda untuk kamar **<?php echo $nama_kamar; ?>** telah berhasil dibuat. Silakan selesaikan pembayaran Anda.
                </p>

                <div class="bg-blue-50 p-6 rounded-lg mb-8 text-left">
                    <h2 class="text-xl font-bold text-blue-800 mb-4">Detail Pesanan Anda:</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-gray-700">
                        <div><strong class="text-gray-900">ID Pesanan:</strong> #<?php echo $id_pemesanan; ?></div>
                        <div><strong class="text-gray-900">Kamar:</strong> <?php echo $nama_kamar; ?> (<?php echo $lokasi_kamar; ?>)</div>
                        <div><strong class="text-gray-900">Durasi Sewa:</strong> <?php echo $durasi_sewa; ?> Bulan</div>
                        <div><strong class="text-gray-900">Metode Pembayaran:</strong> <?php echo $metode_pembayaran; ?></div>
                        <div class="col-span-1 sm:col-span-2 text-2xl font-bold text-blue-700 mt-4">
                            Total Harga: Rp <?php echo $total_harga; ?>
                        </div>
                    </div>
                </div>

                <div class="bg-yellow-50 p-6 rounded-lg mb-8 text-left">
                    <h2 class="text-xl font-bold text-yellow-800 mb-4"><i class="fas fa-info-circle mr-2"></i> Instruksi Pembayaran</h2>
                    <?php if ($metode_pembayaran == 'Transfer Bank'): ?>
                        <p class="text-gray-700 mb-2">Silakan transfer sejumlah **Rp <?php echo $total_harga; ?>** ke rekening berikut:</p>
                        <ul class="list-disc list-inside text-gray-700 mb-4">
                            <li>**Bank:** BCA</li>
                            <li>**Nomor Rekening:** 123-456-7890</li>
                            <li>**Atas Nama:** PT Heaven Indekos</li>
                        </ul>
                        <p class="text-gray-700">Mohon lakukan pembayaran dalam waktu 24 jam. Setelah transfer, konfirmasikan pembayaran Anda melalui halaman Riwayat Pemesanan.</p>
                    <?php elseif ($metode_pembayaran == 'E-Wallet'): ?>
                        <p class="text-gray-700 mb-2">Silakan bayar sejumlah **Rp <?php echo $total_harga; ?>** melalui E-Wallet pilihan Anda:</p>
                        <ul class="list-disc list-inside text-gray-700 mb-4">
                            <li>**OVO/Gopay/Dana:** 0812-3456-7890 (a/n Heaven Indekos)</li>
                            <li>Scan QR Code (jika tersedia)</li>
                        </ul>
                        <p class="text-gray-700">Pastikan jumlah yang ditransfer sesuai. Konfirmasi pembayaran Anda melalui halaman Riwayat Pemesanan.</p>
                    <?php else: ?>
                        <p class="text-gray-700">Instruksi pembayaran untuk metode ini akan diberikan setelah tim kami memproses pesanan Anda.</p>
                    <?php endif; ?>
                </div>

                <a href="riwayat_pemesanan.php" class="inline-block bg-blue-600 text-white font-semibold px-8 py-3 rounded-full hover:bg-blue-700 transition duration-300 ease-in-out shadow-md">
                    Lihat Riwayat Pemesanan <i class="fas fa-history ml-2"></i>
                </a>
                <a href="home_penyewa.php" class="inline-block bg-gray-200 text-gray-800 font-semibold px-8 py-3 rounded-full hover:bg-gray-300 transition duration-300 ease-in-out shadow-md ml-4">
                    Kembali ke Beranda <i class="fas fa-home ml-2"></i>
                </a>
            </div>
        </div>
    </main>

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
