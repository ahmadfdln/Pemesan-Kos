<?php
// home_penyewa.php

if (session_status() == PHP_SESSION_NONE) {
    $lifetime = 60 * 60 * 24 * 365 * 10; // 10 tahun
    session_set_cookie_params($lifetime);
    session_start();
}

if (!isset($_SESSION['loggedin']) || ($_SESSION['tipe_akun'] ?? null) !== 'penyewa') {
    header("Location: login.php");
    exit();
}

include 'koneksi.php';

$nama_lengkap = $_SESSION['full_name'] ?? 'Penyewa';
// --- PERBAIKAN 1: PASTIKAN user_id DIAMBIL DARI SESSION ---
// PENTING: Pastikan Anda menyimpan ID pengguna ke $_SESSION['user_id'] saat proses login berhasil.
$id_pengguna = $_SESSION['user_id'] ?? 0;

// Ambil semua data wishlist milik pengguna yang sedang login
$wishlist_kost_ids = [];
if ($id_pengguna > 0) {
    $wishlist_sql = "SELECT id_kost FROM wishlist WHERE id_pengguna = ?";
    $stmt_wishlist = mysqli_prepare($koneksi, $wishlist_sql);
    mysqli_stmt_bind_param($stmt_wishlist, "i", $id_pengguna);
    mysqli_stmt_execute($stmt_wishlist);
    $result_wishlist = mysqli_stmt_get_result($stmt_wishlist);
    while ($wishlist_row = mysqli_fetch_assoc($result_wishlist)) {
        $wishlist_kost_ids[] = $wishlist_row['id_kost'];
    }
    mysqli_stmt_close($stmt_wishlist);
}

// Mengambil data kost yang statusnya 'publish'.
$sql = "SELECT * FROM kost WHERE status = 'publish' ORDER BY id_kost DESC";
$result = mysqli_query($koneksi, $sql);

?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Penyewa - Heaven Indekos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />
    <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .card-carousel-container .swiper-button-next, .card-carousel-container .swiper-button-prev {
            color: #fff; background-color: rgba(0, 0, 0, 0.4); width: 32px; height: 32px; border-radius: 50%;
            opacity: 0; transition: opacity 0.3s ease; transform: scale(0.8);
        }
        .card-carousel-container:hover .swiper-button-next, .card-carousel-container:hover .swiper-button-prev { opacity: 1; }
        .card-carousel-container .swiper-button-next::after, .card-carousel-container .swiper-button-prev::after { font-size: 14px; font-weight: bold; }
        .card-carousel-container .swiper-pagination-bullet { background-color: rgba(255, 255, 255, 0.7); }
        .card-carousel-container .swiper-pagination-bullet-active { background-color: #fff; }
        @keyframes fadeInDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade-in-down { animation: fadeInDown 0.8s ease-out forwards; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade-in-up { animation: fadeInUp 0.8s ease-out forwards; }
    </style>
</head>
<body class="flex flex-col min-h-screen">

    <!-- Navbar -->
    <header class="bg-white shadow-md py-4 sticky top-0 z-30">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <a href="home_penyewa.php" class="text-2xl font-black text-blue-700 tracking-tighter">HeavenIndekos</a>
            <nav class="hidden md:flex items-center space-x-2">
                <a href="wishlist.php" class="text-gray-600 hover:bg-gray-100 font-medium px-4 py-2 rounded-lg">Wishlist</a>
                <a href="riwayat_pemesanan.php" class="text-gray-600 hover:bg-gray-100 font-medium px-4 py-2 rounded-lg">Riwayat</a>
                <a href="pengaturan.php" class="text-gray-600 hover:bg-gray-100 font-medium px-4 py-2 rounded-lg">Pengaturan</a>
                <a href="logout.php" class="bg-red-500 text-white font-semibold px-5 py-2 rounded-lg hover:bg-red-600 transition-colors">Logout</a>
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
    <section id="available-rooms" class="py-12 bg-gray-50 flex-grow">
        <div class="container mx-auto px-4">
            <h2 class="text-2xl font-bold text-gray-800 mb-8">Pilihan Kost Tersedia</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <?php
                            $harga = number_format($row['harga'], 0, ',', '.');
                            $nama_kost_pendek = htmlspecialchars(substr($row['nama_kost'], 0, 25)) . (strlen($row['nama_kost']) > 25 ? '...' : '');
                            $foto_string = $row['foto'] ?? '';
                            $gambar_array = [];
                            if (!empty($foto_string)) {
                                $gambar_array = array_map('trim', explode(',', $foto_string));
                            }
                            if (empty($gambar_array) || empty($gambar_array[0])) {
                                $gambar_array = ['https://placehold.co/600x400/e2e8f0/4a5568?text=Gambar+Kos'];
                            }
                            
                            // Tentukan status like untuk setiap kartu
                            $is_liked = in_array($row['id_kost'], $wishlist_kost_ids);
                            $like_icon_class = $is_liked ? 'fas' : 'far';
                            $like_text_color = $is_liked ? 'text-red-500' : 'text-gray-700';
                        ?>

                        <div class="group bg-white rounded-xl shadow-md overflow-hidden flex flex-col">
                            <div class="relative card-carousel-container">
                                <div class="swiper h-48">
                                    <div class="swiper-wrapper">
                                        <?php foreach ($gambar_array as $gambar_item): ?>
                                            <div class="swiper-slide">
                                                <a href="detail_kos.php?id=<?= $row['id_kost']; ?>">
                                                    <img src="<?= strpos($gambar_item, 'https://') === 0 ? $gambar_item : 'uploads/' . htmlspecialchars($gambar_item) ?>" alt="Gambar <?= htmlspecialchars($row['nama_kost']); ?>" class="w-full h-full object-cover">
                                                </a>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="swiper-pagination"></div>
                                </div>
                                <div class="swiper-button-prev"></div>
                                <div class="swiper-button-next"></div>
                                
                                <button class="like-button absolute top-3 right-3 bg-white/70 backdrop-blur-sm h-9 w-9 rounded-full flex items-center justify-center <?= $like_text_color ?> hover:bg-white transition z-10" data-kost-id="<?= $row['id_kost'] ?>">
                                    <i class="<?= $like_icon_class ?> fa-heart text-lg"></i>
                                </button>
                            </div>
                            
                            <div class="p-4 flex-grow flex flex-col">
                                <p class="text-xs font-semibold text-blue-600">Coliving</p>
                                <h3 class="text-base font-bold text-gray-800 mt-1 truncate" title="<?= htmlspecialchars($row['nama_kost']); ?>">
                                    <a href="detail_kos.php?id=<?= $row['id_kost']; ?>" class="hover:underline"><?= $nama_kost_pendek; ?></a>
                                </h3>
                                <p class="text-sm text-gray-500 mt-1 flex items-start">
                                    <i class="fas fa-map-marker-alt w-4 mt-1 mr-2 text-gray-400"></i>
                                    <span><?= htmlspecialchars($row['alamat']); ?></span>
                                </p>
                                <div class="mt-4 pt-4 border-t border-gray-100 flex-grow flex flex-col justify-end">
                                    <p class="text-xs text-gray-500">mulai dari</p>
                                    <p class="text-lg font-extrabold text-gray-900">
                                        Rp <?= $harga; ?> <span class="font-normal text-sm text-gray-500">/bulan</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="col-span-full text-center text-gray-500 mt-8">Saat ini belum ada kost yang tersedia. Silakan cek kembali nanti.</p>
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

    <!-- Script untuk Like & Carousel -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Inisialisasi Carousel
            const carousels = document.querySelectorAll('.card-carousel-container');
            carousels.forEach(container => {
                new Swiper(container.querySelector('.swiper'), {
                    loop: true,
                    navigation: {
                        nextEl: container.querySelector('.swiper-button-next'),
                        prevEl: container.querySelector('.swiper-button-prev'),
                    },
                    pagination: {
                        el: container.querySelector('.swiper-pagination'),
                        clickable: true,
                    },
                });
            });

            // --- PERBAIKAN 2: LOGIKA TOMBOL LIKE ---
            const likeButtons = document.querySelectorAll('.like-button');
            likeButtons.forEach(button => {
                button.addEventListener('click', function(event) {
                    // Mencegah link di belakang tombol ikut ter-klik
                    event.preventDefault(); 
                    event.stopPropagation();

                    const kostId = this.dataset.kostId;
                    const icon = this.querySelector('i');

                    // Kirim permintaan ke backend
                    fetch('like_handler.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded', },
                        body: 'id_kost=' + kostId
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Ubah tampilan tombol berdasarkan respons dari server
                            if (data.action === 'liked') {
                                icon.classList.remove('far'); // Ikon kosong
                                icon.classList.add('fas');    // Ikon penuh
                                button.classList.add('text-red-500');
                                button.classList.remove('text-gray-700');
                            } else { // unliked
                                icon.classList.remove('fas');    // Ikon penuh
                                icon.classList.add('far');     // Ikon kosong
                                button.classList.remove('text-red-500');
                                button.classList.add('text-gray-700');
                            }
                        } else {
                            // Tampilkan pesan error jika ada masalah (misal: belum login)
                            alert(data.message || 'Terjadi kesalahan.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan koneksi. Silakan coba lagi.');
                    });
                });
            });
        });
    </script>
</body>
</html>
