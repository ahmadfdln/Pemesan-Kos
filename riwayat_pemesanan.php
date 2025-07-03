<?php
include 'koneksi.php';
session_start();

// Pastikan pengguna sudah login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

// Gunakan 'user_id' (angka) bukan 'username' (teks)
$id_penyewa = $_SESSION['user_id']; 

// --- PERBAIKAN: Mengurutkan berdasarkan 'id' karena 'tanggal_pemesanan' tidak ada ---
$query_str = "SELECT * FROM pemesanan WHERE id_penyewa = ? ORDER BY id DESC";
$stmt = mysqli_prepare($koneksi, $query_str);

// Tambahkan pengecekan untuk mencegah error fatal
if ($stmt === false) {
    die("Error preparing statement: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, "i", $id_penyewa);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pemesanan - Heaven Indekos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto mt-10 p-6">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-bold text-gray-800">Riwayat Pemesanan Anda</h2>
                <a href="home_penyewa.php" class="text-blue-600 hover:text-blue-800 font-medium">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Beranda
                </a>
            </div>

            <?php if (isset($_SESSION['pesan_sukses'])): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                    <p class="font-bold">Sukses!</p>
                    <p><?= $_SESSION['pesan_sukses'] ?></p>
                </div>
                <?php unset($_SESSION['pesan_sukses']); ?>
            <?php endif; ?>

            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-200 text-sm text-left">
                    <thead class="bg-gray-100 text-gray-600 uppercase">
                        <tr>
                            <th class="px-4 py-3">Nama Kost</th>
                            <th class="px-4 py-3">Tanggal Masuk</th>
                            <th class="px-4 py-3">Tanggal Keluar</th>
                            <th class="px-4 py-3">Harga</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white text-gray-700 divide-y divide-gray-200">
                        <?php if ($result && mysqli_num_rows($result) > 0): ?>
                            <?php while ($data = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td class="px-4 py-3 font-medium"><?= htmlspecialchars($data['nama_kost']) ?></td>
                                    <td class="px-4 py-3"><?= date('d M Y', strtotime($data['tanggal_masuk'])) ?></td>
                                    <td class="px-4 py-3"><?= date('d M Y', strtotime($data['tanggal_keluar'])) ?></td>
                                    <td class="px-4 py-3">Rp <?= number_format($data['harga'], 0, ',', '.') ?></td>
                                    <td class="px-4 py-3">
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full 
                                            <?= ($data['status'] == 'Dikonfirmasi') ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                            <?= htmlspecialchars($data['status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="detail_pemesanan.php?id=<?= $data['id'] ?>" class="text-blue-600 hover:underline">Lihat Detail</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-10 text-gray-500">
                                    <i class="fas fa-box-open fa-3x mb-3"></i>
                                    <p>Anda belum memiliki riwayat pemesanan.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
