<?php
// detail_pemesanan_admin.php (Khusus untuk Admin)

// Memulai sesi jika belum ada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'koneksi.php';

// Cek otorisasi: pastikan yang mengakses adalah admin
if (!isset($_SESSION['loggedin']) || ($_SESSION['tipe_akun'] ?? null) !== 'admin') {
    header("Location: login.php");
    exit();
}

// Ambil ID pemesanan dari URL
$id_pemesanan = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_pemesanan <= 0) {
    $_SESSION['flash_message'] = "ID Pemesanan tidak valid.";
    header("Location: dashboard_admin.php?page=pemesanan");
    exit();
}

// --- Logika untuk mengambil data detail pemesanan ---
// PERBAIKAN: Kolom no_telepon dihapus dari query untuk mencegah error jika tidak ada.
// Jika Anda sudah menambahkan kolom no_telepon ke tabel pengguna, Anda bisa menambahkannya kembali di sini.
$query = "
    SELECT 
        p.*, 
        k.alamat AS alamat_kost, 
        pemilik.nama_lengkap AS nama_pemilik,
        penyewa.nama_lengkap AS nama_penyewa
    FROM 
        pemesanan p
    LEFT JOIN 
        kost k ON p.id_kost = k.id_kost
    LEFT JOIN 
        pengguna pemilik ON k.id_pemilik = pemilik.id_pengguna
    LEFT JOIN
        pengguna penyewa ON p.id_penyewa = penyewa.id_pengguna
    WHERE 
        p.id = ?
";
$stmt = mysqli_prepare($koneksi, $query);

// --- PERBAIKAN: Tambahkan pengecekan error setelah mysqli_prepare ---
if ($stmt === false) {
    // Ini akan menampilkan error SQL yang sebenarnya untuk debugging
    die('Error dalam mempersiapkan query: ' . mysqli_error($koneksi));
}


mysqli_stmt_bind_param($stmt, "i", $id_pemesanan);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$pemesanan = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Jika pemesanan tidak ditemukan, redirect kembali
if (!$pemesanan) {
    $_SESSION['flash_message'] = "Detail pemesanan tidak ditemukan.";
    header("Location: dashboard_admin.php?page=pemesanan");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pemesanan #<?= $pemesanan['id'] ?> - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style> body { font-family: 'Inter', sans-serif; background-color: #f9fafb; } </style>
</head>
<body class="flex flex-col min-h-screen">

    <header class="bg-white shadow-sm py-4">
        <div class="container mx-auto px-6 flex justify-between items-center">
            <h1 class="text-xl font-semibold text-gray-800">Detail Pemesanan #<?= htmlspecialchars($pemesanan['id']) ?></h1>
            <a href="dashboard_admin.php?page=pemesanan" class="text-indigo-600 hover:text-indigo-800 font-medium"><i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar Pesanan</a>
        </div>
    </header>

    <main class="flex-grow">
        <div class="container mx-auto px-6 py-8">
            <div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-md">
                
                <!-- Header Detail -->
                <div class="flex flex-wrap justify-between items-start mb-6 pb-6 border-b gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($pemesanan['nama_kost']) ?></h2>
                        <p class="text-gray-500 mt-1">Dipesan oleh: <strong class="text-gray-700"><?= htmlspecialchars($pemesanan['nama_penyewa']) ?></strong></p>
                        <!-- PERBAIKAN: Hapus tampilan no telepon jika tidak ada di query -->
                        <!-- <p class="text-gray-500">No. Telepon: <strong class="text-gray-700"><?= htmlspecialchars($pemesanan['telepon_penyewa'] ?? 'Tidak ada') ?></strong></p> -->
                    </div>
                    <div class="text-right">
                        <p class="text-gray-500">Status Saat Ini</p>
                        <span class="mt-1 inline-block px-4 py-2 text-sm font-semibold rounded-full 
                            <?php 
                                if ($pemesanan['status'] == 'Dikonfirmasi') echo 'bg-green-100 text-green-800';
                                elseif ($pemesanan['status'] == 'Ditolak') echo 'bg-red-100 text-red-800';
                                else echo 'bg-yellow-100 text-yellow-800';
                            ?>">
                            <?= htmlspecialchars($pemesanan['status']) ?>
                        </span>
                    </div>
                </div>

                <!-- Grid Detail -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Kolom Kiri: Detail Sewa & Pembayaran -->
                    <div>
                        <h3 class="font-semibold text-lg text-gray-800 mb-4">Informasi Pemesanan</h3>
                        <div class="space-y-3 text-sm">
                            <p><strong class="w-32 inline-block text-gray-500">Tanggal Masuk</strong> : <?= date('d F Y', strtotime($pemesanan['tanggal_masuk'])) ?></p>
                            <p><strong class="w-32 inline-block text-gray-500">Tanggal Keluar</strong> : <?= date('d F Y', strtotime($pemesanan['tanggal_keluar'])) ?></p>
                            <p><strong class="w-32 inline-block text-gray-500">Total Tagihan</strong> : <span class="font-bold text-lg text-indigo-600">Rp <?= number_format($pemesanan['total_harga'], 0, ',', '.') ?></span></p>
                            <p><strong class="w-32 inline-block text-gray-500">Metode Bayar</strong> : <?= htmlspecialchars($pemesanan['metode_pembayaran'] ?? 'N/A') ?></p>
                            <p><strong class="w-32 inline-block text-gray-500">Tanggal Pesan</strong> : <?= date('d F Y, H:i', strtotime($pemesanan['tanggal_pemesanan'])) ?></p>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Bukti Pembayaran -->
                    <div>
                        <h3 class="font-semibold text-lg text-gray-800 mb-4">Bukti Pembayaran</h3>
                        <?php if (!empty($pemesanan['bukti_pembayaran'])): ?>
                            <a href="uploads/bukti/<?= htmlspecialchars($pemesanan['bukti_pembayaran']) ?>" target="_blank">
                                <img src="uploads/bukti/<?= htmlspecialchars($pemesanan['bukti_pembayaran']) ?>" alt="Bukti Pembayaran" class="w-full max-w-xs object-cover rounded-lg border hover:shadow-lg transition-shadow duration-200">
                            </a>
                            <p class="text-xs text-gray-500 mt-2">Klik gambar untuk melihat ukuran penuh.</p>
                        <?php else: ?>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                                <i class="fas fa-file-invoice-dollar fa-3x text-gray-400"></i>
                                <p class="mt-2 text-gray-500">Penyewa belum mengupload bukti pembayaran.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Aksi Admin -->
                <?php if ($pemesanan['status'] === 'Menunggu Konfirmasi'): ?>
                <div class="mt-8 border-t pt-6 text-center">
                    <h3 class="font-semibold text-lg text-gray-800 mb-4">Tindakan Admin</h3>
                    <div class="flex justify-center space-x-4">
                        <a href="proses_pemesanan.php?aksi=konfirmasi&id=<?= $pemesanan['id'] ?>" class="bg-green-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-green-700 transition duration-200">
                            <i class="fas fa-check mr-2"></i>Konfirmasi Pesanan
                        </a>
                        <a href="proses_pemesanan.php?aksi=batalkan&id=<?= $pemesanan['id'] ?>" class="bg-red-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-red-700 transition duration-200">
                            <i class="fas fa-times mr-2"></i>Batalkan Pesanan
                        </a>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </main>

    <footer class="bg-white py-4 mt-auto border-t">
        <div class="container mx-auto text-center text-sm text-gray-500"><p>&copy; <?= date("Y"); ?> Heaven Indekos. Admin Panel.</p></div>
    </footer>
</body>
</html>
