<?php
// detail_kos.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'koneksi.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

$id_kost = isset($_GET['id']) ? intval($_GET['id']) : 0;
$kost_data = null;

if ($id_kost > 0) {
    $query_kost = "
        SELECT 
            k.*, 
            p.nama_lengkap AS nama_pemilik, 
            k.no_hp AS telp_pemilik 
        FROM 
            kost k
        LEFT JOIN 
            pengguna p ON k.id_pemilik = p.id_pengguna
        WHERE 
            k.id_kost = ? AND k.status = 'publish'
    ";
    
    $stmt = mysqli_prepare($koneksi, $query_kost);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id_kost);
        mysqli_stmt_execute($stmt);
        $result_kost = mysqli_stmt_get_result($stmt);

        if ($result_kost && mysqli_num_rows($result_kost) > 0) {
            $kost_data = mysqli_fetch_assoc($result_kost);
        }
        mysqli_stmt_close($stmt);
    } else {
        die("Error preparing statement: " . mysqli_error($koneksi));
    }
}

if (!$kost_data) {
    $_SESSION['pesan_error'] = "Kost tidak ditemukan atau tidak tersedia.";
    header("Location: home_penyewa.php");
    exit();
}

// Proses data untuk ditampilkan
$nama_kost = htmlspecialchars($kost_data['nama_kost']);
$alamat = htmlspecialchars($kost_data['alamat']);
$harga_per_bulan = number_format($kost_data['harga'], 0, ',', '.');
$harga_raw = $kost_data['harga'];
$nama_pemilik = htmlspecialchars($kost_data['nama_pemilik'] ?? 'Tidak diketahui');
$telp_pemilik = htmlspecialchars($kost_data['telp_pemilik'] ?? '-');

// Logika untuk memecah string gambar menjadi array
$foto_string = $kost_data['foto'] ?? '';
$gambar_array = [];
if (!empty($foto_string)) {
    $gambar_array = array_map('trim', explode(',', $foto_string));
}
// Jika tidak ada gambar, gunakan placeholder
if (empty($gambar_array) || empty($gambar_array[0])) {
    $gambar_array = ['https://placehold.co/800x600/e2e8f0/4a5568?text=Gambar+Kos'];
}

$fasilitas_raw = $kost_data['fasilitas'] ?? '';
$fasilitas_list = [];
if (!empty($fasilitas_raw)) {
    $fasilitas_list = array_map('trim', explode(',', $fasilitas_raw));
}

$user_id = $_SESSION['user_id'] ?? null;

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Kost - <?php echo $nama_kost; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Library Swiper.js -->
    <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />
    <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .modal-container { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; }
        .modal-content { background-color: #fefefe; margin: auto; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); width: 90%; max-width: 500px; position: relative; animation: fadeIn 0.3s ease-out; }
        .close-button { color: #aaa; position: absolute; top: 1rem; right: 1.5rem; font-size: 28px; font-weight: bold; }
        .close-button:hover, .close-button:focus { color: black; text-decoration: none; cursor: pointer; }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }

        /* Style untuk Carousel di Halaman Detail */
        .detail-carousel .swiper-button-next,
        .detail-carousel .swiper-button-prev {
            color: #fff;
            background-color: rgba(0, 0, 0, 0.3);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            transition: background-color 0.3s ease;
        }
        .detail-carousel .swiper-button-next:hover,
        .detail-carousel .swiper-button-prev:hover {
            background-color: rgba(0, 0, 0, 0.5);
        }
        .detail-carousel .swiper-button-next::after,
        .detail-carousel .swiper-button-prev::after {
            font-size: 18px;
        }
        .detail-carousel .swiper-pagination-bullet-active {
            background-color: #fff;
        }
    </style>
</head>
<body class="flex flex-col min-h-screen">
    <header class="bg-white shadow-lg py-4">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <a href="home_penyewa.php" class="text-3xl font-extrabold text-blue-700">Heaven Indekos</a>
            <a href="home_penyewa.php" class="text-gray-600 hover:text-blue-600 font-medium"><i class="fas fa-arrow-left mr-2"></i>Kembali</a>
        </div>
    </header>

    <main class="flex-grow py-12 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="bg-white rounded-lg shadow-xl overflow-hidden p-6 md:p-8">
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 items-start">
                    
                    <div class="lg:col-span-3 relative rounded-lg overflow-hidden shadow-md detail-carousel">
                        <div class="swiper h-64 md:h-96">
                            <div class="swiper-wrapper">
                                <?php foreach ($gambar_array as $gambar_item): ?>
                                    <div class="swiper-slide">
                                        <img src="<?= strpos($gambar_item, 'https://') === 0 ? $gambar_item : 'uploads/' . htmlspecialchars($gambar_item) ?>" 
                                             alt="Gambar <?php echo $nama_kost; ?>" 
                                             class="w-full h-full object-cover">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="swiper-pagination"></div>
                            <div class="swiper-button-prev"></div>
                            <div class="swiper-button-next"></div>
                        </div>
                    </div>

                    <div class="lg:col-span-2">
                        <h1 class="text-4xl font-extrabold text-gray-800 mb-4"><?php echo $nama_kost; ?></h1>
                        
                        <div class="bg-blue-50 p-4 rounded-lg mb-6">
                            <div class="text-blue-800 text-3xl font-bold">
                                Rp <?php echo $harga_per_bulan; ?><span class="text-lg font-normal text-gray-600">/bulan</span>
                            </div>
                            <div class="mt-2 text-lg font-semibold text-green-600">
                                <i class="fas fa-check-circle mr-2"></i> Tersedia
                            </div>
                        </div>

                        <h2 class="text-xl font-bold text-gray-800 mb-3">Detail Informasi</h2>
                        <div class="space-y-3 text-gray-700 mb-6 border-t pt-4">
                            <p class="flex items-start"><i class="fas fa-map-marker-alt w-5 mr-3 mt-1 text-blue-600"></i> <span class="font-semibold">Alamat:</span><span class="ml-2"><?php echo $alamat; ?></span></p>
                            <p class="flex items-center"><i class="fas fa-user-tie w-5 mr-3 text-blue-600"></i> <span class="font-semibold">Pemilik:</span><span class="ml-2"><?php echo $nama_pemilik; ?></span></p>
                            <p class="flex items-center"><i class="fas fa-phone w-5 mr-3 text-blue-600"></i> <span class="font-semibold">Telepon:</span><span class="ml-2"><?php echo $telp_pemilik; ?></span></p>
                        </div>
                        
                        <h2 class="text-xl font-bold text-gray-800 mb-3">Fasilitas</h2>
                        <div class="mb-8 border-t pt-4">
                            <?php if (!empty($fasilitas_list)): ?>
                                <ul class="grid grid-cols-2 gap-x-6 gap-y-3 text-gray-700">
                                    <?php foreach ($fasilitas_list as $item): ?>
                                        <li class="flex items-center"><i class="fas fa-check-circle text-green-500 mr-2"></i> <?= htmlspecialchars($item) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-gray-500">Tidak ada data fasilitas untuk kost ini.</p>
                            <?php endif; ?>
                        </div>

                        <button id="openPaymentModal" class="w-full bg-blue-600 text-white font-semibold py-4 rounded-lg hover:bg-blue-700 transition duration-300 ease-in-out shadow-lg hover:shadow-xl text-xl">
                            Sewa Sekarang <i class="fas fa-paper-plane ml-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-gray-800 text-white py-6 mt-auto">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; <?php echo date("Y"); ?> Heaven Indekos. Semua Hak Dilindungi.</p>
        </div>
    </footer>

    <!-- Modal Pembayaran -->
    <div id="paymentModal" class="modal-container">
        <div class="modal-content">
            <span class="close-button" id="closePaymentModal">&times;</span>
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Formulir Sewa Kost</h2>
            <form action="proses_sewa.php" method="POST" class="space-y-4">
                <input type="hidden" name="id_kost" value="<?php echo $id_kost; ?>">
                <input type="hidden" name="id_penyewa" value="<?php echo $user_id; ?>">
                <input type="hidden" name="nama_kost" value="<?php echo $nama_kost; ?>">
                <input type="hidden" name="harga" value="<?php echo $harga_raw; ?>">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kost yang Disewa:</label>
                    <input type="text" value="<?php echo $nama_kost; ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
                </div>
                <div>
                    <label for="tanggal_masuk" class="block text-sm font-medium text-gray-700">Tanggal Mulai Sewa:</label>
                    <input type="date" name="tanggal_masuk" id="tanggal_masuk" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" min="<?php echo date('Y-m-d'); ?>">
                </div>
                <div>
                    <label for="durasi_sewa" class="block text-sm font-medium text-gray-700">Durasi Sewa (Bulan):</label>
                    <input type="number" name="durasi_sewa" id="durasi_sewa" min="1" value="1" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" oninput="calculateTotal()">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Total Harga:</label>
                    <input type="text" id="total_harga" value="Rp <?php echo $harga_per_bulan; ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-lg font-bold text-blue-700" readonly>
                </div>
                <div>
                    <label for="metode_pembayaran" class="block text-sm font-medium text-gray-700">Metode Pembayaran:</label>
                    <select name="metode_pembayaran" id="metode_pembayaran" required class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" onchange="updatePaymentInfo()">
                        <option value="BCA">Transfer Bank (BCA)</option>
                        <option value="GoPay">E-Wallet (GoPay)</option>
                        <option value="OVO">E-Wallet (OVO)</option>
                        <option value="VA_Mandiri">Virtual Account (Mandiri)</option>
                    </select>
                </div>
                <div id="payment-details">
                    <label id="payment-label" for="payment-number" class="block text-sm font-medium text-gray-700">Nomor Tujuan:</label>
                    <div class="flex items-center">
                        <input type="text" id="payment-number" value="" class="w-full px-3 py-2 border border-gray-300 rounded-l-md bg-gray-100" readonly>
                        <button type="button" onclick="copyToClipboard('payment-number')" class="bg-gray-200 px-4 py-2 rounded-r-md hover:bg-gray-300 transition">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <p id="copy-feedback" class="text-xs text-green-600 mt-1 h-4"></p>
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-3 rounded-md hover:bg-blue-700 transition duration-300 ease-in-out shadow-md">
                    Konfirmasi Sewa
                </button>
            </form>
        </div>
    </div>

    <!-- PERBAIKAN SCRIPT DI SINI -->
    <script>
        // 1. Bungkus inisialisasi Swiper di dalam DOMContentLoaded
        document.addEventListener('DOMContentLoaded', function () {
            const swiper = new Swiper('.detail-carousel .swiper', {
                loop: true,
                navigation: {
                    // 2. Buat selector lebih spesifik
                    nextEl: '.detail-carousel .swiper-button-next',
                    prevEl: '.detail-carousel .swiper-button-prev',
                },
                pagination: {
                    el: '.detail-carousel .swiper-pagination',
                    clickable: true,
                },
            });
        });

        // Sisa script untuk modal (sudah benar, tidak perlu diubah)
        const modal = document.getElementById("paymentModal");
        const btn = document.getElementById("openPaymentModal");
        const span = document.getElementById("closePaymentModal");

        const paymentData = {
            'BCA': { label: 'Nomor Rekening Tujuan (BCA)', number: '7201159363' },
            'GoPay': { label: 'Nomor E-Wallet (GoPay)', number: '081234567890' },
            'OVO': { label: 'Nomor E-Wallet (OVO)', number: '089876543210' },
            'VA_Mandiri': { label: 'Nomor Virtual Account (Mandiri)', number: '88088123456789' }
        };

        if (btn) {
            btn.onclick = function() {
                modal.style.display = "flex";
                calculateTotal();
                updatePaymentInfo();
            }
        }
        if(span) {
            span.onclick = function() { modal.style.display = "none"; }
        }
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
        function calculateTotal() {
            const hargaPerBulan = parseFloat(document.querySelector('input[name="harga"]').value);
            const durasiSewa = parseInt(document.getElementById('durasi_sewa').value);
            
            if (!isNaN(hargaPerBulan) && !isNaN(durasiSewa) && durasiSewa > 0) {
                const total = hargaPerBulan * durasiSewa;
                document.getElementById('total_harga').value = 'Rp ' + total.toLocaleString('id-ID');
            } else {
                document.getElementById('total_harga').value = 'Rp 0';
            }
        }
        function updatePaymentInfo() {
            const selectedMethod = document.getElementById('metode_pembayaran').value;
            const info = paymentData[selectedMethod];
            
            if (info) {
                document.getElementById('payment-label').textContent = info.label;
                document.getElementById('payment-number').value = info.number;
                document.getElementById('payment-details').style.display = 'block';
            } else {
                document.getElementById('payment-details').style.display = 'none';
            }
        }
        function copyToClipboard(elementId) {
            const input = document.getElementById(elementId);
            const feedback = document.getElementById('copy-feedback');
            
            const textarea = document.createElement('textarea');
            textarea.value = input.value;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);

            feedback.textContent = 'Nomor berhasil disalin!';
            setTimeout(() => {
                feedback.textContent = '';
            }, 2000);
        }
    </script>
</body> 
</html>
