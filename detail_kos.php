<?php
// detail_kos.php

include 'koneksi.php'; // Sesuaikan path jika detail_kos.php di subfolder

// Pastikan session sudah dimulai dari koneksi.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah pengguna sudah login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php"); // Redirect ke halaman login jika belum login
    exit();
}

// Ambil ID kamar dari parameter URL
$id_kamar = isset($_GET['id']) ? intval($_GET['id']) : 0;

$kamar_data = null;
if ($id_kamar > 0) {
    // Query untuk mengambil data kamar berdasarkan ID
    // GANTI 'kamar_kos' dengan nama tabel kamar Anda jika berbeda
    $query_kamar = "SELECT * FROM kamar_kos WHERE id_kamar = $id_kamar";
    $result_kamar = mysqli_query($koneksi, $query_kamar);

    if ($result_kamar && mysqli_num_rows($result_kamar) > 0) {
        $kamar_data = mysqli_fetch_assoc($result_kamar);
    }
}

// Jika kamar tidak ditemukan, redirect atau tampilkan pesan error
if (!$kamar_data) {
    // Anda bisa mengarahkan kembali ke home_penyewa.php dengan pesan error
    $_SESSION['error_message'] = "Kamar kos tidak ditemukan.";
    header("Location: home_penyewa.php");
    exit();
}

// Data kamar yang akan ditampilkan
$nama_kamar = htmlspecialchars($kamar_data['nama_kamar']);
$lokasi = htmlspecialchars($kamar_data['lokasi']);
$deskripsi = htmlspecialchars($kamar_data['deskripsi']);
$harga_per_bulan = number_format($kamar_data['harga_per_bulan'], 0, ',', '.'); // Format harga
$harga_raw = $kamar_data['harga_per_bulan']; // Harga mentah untuk perhitungan
$status_ketersediaan = htmlspecialchars($kamar_data['status_ketersediaan']);
$fasilitas_raw = $kamar_data['fasilitas']; // Asumsi fasilitas disimpan sebagai string JSON atau koma-separated
$gambar_url = htmlspecialchars($kamar_data['gambar_url']); // URL gambar utama

// Contoh parsing fasilitas (jika disimpan sebagai JSON string)
$fasilitas_list = [];
if (!empty($fasilitas_raw)) {
    $decoded_fasilitas = json_decode($fasilitas_raw, true);
    if (is_array($decoded_fasilitas)) {
        $fasilitas_list = $decoded_fasilitas;
    } else {
        // Jika bukan JSON, coba pisahkan dengan koma
        $fasilitas_list = array_map('trim', explode(',', $fasilitas_raw));
    }
}

// Tentukan warna status ketersediaan
$status_color = ($status_ketersediaan == 'Tersedia') ? 'text-green-600' : 'text-red-600';
$status_icon = ($status_ketersediaan == 'Tersedia') ? 'fas fa-check-circle' : 'fas fa-times-circle';

// Ambil user_id dari session untuk form pemesanan
$user_id = $_SESSION['user_id'] ?? null;

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Kos - <?php echo $nama_kamar; ?> - Heaven Indekos</title>
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
        /* Style untuk modal */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1000; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
            display: flex; /* Use flexbox for centering */
            align-items: center; /* Center vertically */
            justify-content: center; /* Center horizontally */
        }
        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 500px;
            position: relative;
            animation: fadeIn 0.3s ease-out;
        }
        .close-button {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            position: absolute;
            top: 10px;
            right: 20px;
        }
        .close-button:hover,
        .close-button:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
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

    <!-- Main Content - Room Details -->
    <main class="flex-grow py-12 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="bg-white rounded-lg shadow-xl overflow-hidden p-6 md:p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                    <!-- Room Image Section -->
                    <div class="relative rounded-lg overflow-hidden shadow-md">
                        <img 
                            src="<?php echo !empty($gambar_url) ? $gambar_url : 'https://placehold.co/800x600/CCCCCC/333333?text=Gambar+Kos+Tidak+Tersedia'; ?>" 
                            alt="Gambar <?php echo $nama_kamar; ?>" 
                            class="w-full h-64 md:h-96 object-cover"
                            onerror="this.onerror=null;this.src='https://placehold.co/800x600/CCCCCC/333333?text=Gambar+Kos+Tidak+Tersedia';"
                        >
                        <!-- Optional: Gallery indicators/buttons if multiple images -->
                        <!-- <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex space-x-2">
                            <span class="w-3 h-3 bg-white rounded-full opacity-75"></span>
                            <span class="w-3 h-3 bg-gray-400 rounded-full opacity-75"></span>
                            <span class="w-3 h-3 bg-gray-400 rounded-full opacity-75"></span>
                        </div> -->
                    </div>

                    <!-- Room Info Section -->
                    <div>
                        <h1 class="text-4xl font-extrabold text-gray-800 mb-4"><?php echo $nama_kamar; ?></h1>
                        <p class="text-gray-600 text-lg mb-4 flex items-center"><i class="fas fa-map-marker-alt mr-2 text-blue-600"></i> <?php echo $lokasi; ?></p>
                        
                        <div class="bg-blue-50 p-4 rounded-lg mb-6 flex items-center justify-between">
                            <div class="text-blue-800 text-2xl font-bold">
                                <i class="fas fa-dollar-sign mr-2"></i> Rp <?php echo $harga_per_bulan; ?><span class="text-lg font-normal text-gray-600">/bulan</span>
                            </div>
                            <div class="text-lg font-semibold <?php echo $status_color; ?>">
                                <i class="<?php echo $status_icon; ?> mr-2"></i> <?php echo $status_ketersediaan; ?>
                            </div>
                        </div>

                        <h2 class="text-2xl font-bold text-gray-800 mb-3">Deskripsi</h2>
                        <p class="text-gray-700 leading-relaxed mb-6"><?php echo $deskripsi; ?></p>

                        <h2 class="text-2xl font-bold text-gray-800 mb-3">Fasilitas</h2>
                        <ul class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-gray-700 mb-8">
                            <?php if (!empty($fasilitas_list)): ?>
                                <?php foreach ($fasilitas_list as $fasilitas_item): ?>
                                    <li class="flex items-center"><i class="fas fa-check-circle text-green-500 mr-2"></i> <?php echo htmlspecialchars($fasilitas_item); ?></li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="text-gray-500">Tidak ada fasilitas yang tercantum.</li>
                            <?php endif; ?>
                        </ul>

                        <?php if ($status_ketersediaan == 'Tersedia'): ?>
                            <button id="openPaymentModal" class="w-full bg-blue-600 text-white font-semibold py-4 rounded-lg hover:bg-blue-700 transition duration-300 ease-in-out shadow-lg hover:shadow-xl text-xl">
                                Sewa Sekarang <i class="fas fa-paper-plane ml-2"></i>
                            </button>
                        <?php else: ?>
                            <button class="w-full bg-gray-400 text-white font-semibold py-4 rounded-lg cursor-not-allowed text-xl" disabled>
                                Tidak Tersedia <i class="fas fa-times-circle ml-2"></i>
                            </button>
                        <?php endif; ?>

                        <a href="home_penyewa.php" class="block text-center mt-4 text-blue-600 hover:text-blue-800 font-medium transition duration-300 ease-in-out">
                            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Kamar
                        </a>
                    </div>
                </div>
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

    <!-- Payment Modal -->
    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <span class="close-button" id="closePaymentModal">&times;</span>
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Formulir Sewa Kamar</h2>
            <form action="proses_sewa.php" method="POST" class="space-y-4">
                <input type="hidden" name="id_kamar" value="<?php echo $id_kamar; ?>">
                <input type="hidden" name="id_pengguna" value="<?php echo $user_id; ?>">
                <input type="hidden" name="harga_per_bulan" value="<?php echo $harga_raw; ?>">

                <div>
                    <label for="nama_kamar_modal" class="block text-sm font-medium text-gray-700">Kamar yang Disewa:</label>
                    <input type="text" id="nama_kamar_modal" value="<?php echo $nama_kamar; ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed" readonly>
                </div>

                <div>
                    <label for="durasi_sewa" class="block text-sm font-medium text-gray-700">Durasi Sewa (Bulan):</label>
                    <input 
                        type="number" 
                        name="durasi_sewa" 
                        id="durasi_sewa" 
                        min="1" 
                        value="1" 
                        required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        oninput="calculateTotal()"
                    >
                </div>

                <div>
                    <label for="total_harga" class="block text-sm font-medium text-gray-700">Total Harga:</label>
                    <input type="text" id="total_harga" value="Rp <?php echo $harga_per_bulan; ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed text-lg font-bold text-blue-700" readonly>
                </div>

                <div class="pt-4 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Metode Pembayaran (Simulasi)</h3>
                    <div class="space-y-2">
                        <label class="inline-flex items-center">
                            <input type="radio" name="metode_pembayaran" value="Transfer Bank" class="form-radio text-blue-600" checked>
                            <span class="ml-2 text-gray-700">Transfer Bank</span>
                        </label>
                        <label class="inline-flex items-center ml-4">
                            <input type="radio" name="metode_pembayaran" value="E-Wallet" class="form-radio text-blue-600">
                            <span class="ml-2 text-gray-700">E-Wallet</span>
                        </label>
                    </div>
                </div>
                
                <button 
                    type="submit" 
                    class="w-full bg-blue-600 text-white font-semibold py-3 rounded-md hover:bg-blue-700 transition duration-300 ease-in-out shadow-md hover:shadow-lg"
                >
                    Konfirmasi Sewa & Bayar
                </button>
            </form>
        </div>
    </div>

    <script>
        // Get the modal
        var modal = document.getElementById("paymentModal");

        // Get the button that opens the modal
        var btn = document.getElementById("openPaymentModal");

        // Get the <span> element that closes the modal
        var span = document.getElementById("closePaymentModal");

        // When the user clicks the button, open the modal 
        if (btn) { // Check if the button exists (only if status is 'Tersedia')
            btn.onclick = function() {
                modal.style.display = "flex"; // Use flex to center
                calculateTotal(); // Calculate initial total when modal opens
            }
        }

        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // Function to calculate total price based on duration
        function calculateTotal() {
            const hargaPerBulan = parseFloat(document.querySelector('input[name="harga_per_bulan"]').value);
            const durasiSewa = parseInt(document.getElementById('durasi_sewa').value);
            
            if (!isNaN(hargaPerBulan) && !isNaN(durasiSewa) && durasiSewa > 0) {
                const total = hargaPerBulan * durasiSewa;
                document.getElementById('total_harga').value = 'Rp ' + total.toLocaleString('id-ID');
            } else {
                document.getElementById('total_harga').value = 'Rp 0';
            }
        }
    </script>
</body>
</html>
