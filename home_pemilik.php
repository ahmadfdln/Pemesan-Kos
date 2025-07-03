<?php
// home_pemilik.php

// Mulai session jika belum ada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Sertakan file koneksi database
include 'koneksi.php';

// Cek apakah pengguna sudah login DAN perannya adalah 'pemilik'
if (!isset($_SESSION['loggedin']) || $_SESSION['tipe_akun'] !== 'pemilik') {
    header("Location: login.php");
    exit();
}

// Ambil data pemilik dari session
$id_pemilik = $_SESSION['user_id'];
$nama_pemilik = $_SESSION['full_name'] ?? 'Pemilik';

// Inisialisasi variabel statistik
$total_kamar = 0;
$kamar_terisi = 0; // Dalam konteks Anda, ini bisa berarti 'draft' atau status lain
$kamar_kosong = 0; // Dalam konteks Anda, ini bisa berarti 'publish'

// --- PENYESUAIAN DENGAN TABEL 'kost' ---

// Query untuk mengambil data statistik KHUSUS UNTUK PEMILIK INI
// Menggunakan tabel 'kost'
$query_total_kamar = "SELECT COUNT(*) AS total FROM kost WHERE id_pemilik = ?";
$stmt = mysqli_prepare($koneksi, $query_total_kamar);

if ($stmt === false) {
    die("Error preparing statement (total_kamar): " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, "i", $id_pemilik);
mysqli_stmt_execute($stmt);
$result_total = mysqli_stmt_get_result($stmt);
if ($result_total) {
    $total_kamar = mysqli_fetch_assoc($result_total)['total'] ?? 0;
}
mysqli_stmt_close($stmt);

// Menggunakan kolom 'status' = 'publish' sebagai kamar yang tersedia/kosong
$query_kamar_kosong = "SELECT COUNT(*) AS total FROM kost WHERE id_pemilik = ? AND status = 'publish'";
$stmt = mysqli_prepare($koneksi, $query_kamar_kosong);

if ($stmt === false) {
    die("Error preparing statement (kamar_kosong): " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, "i", $id_pemilik);
mysqli_stmt_execute($stmt);
$result_kosong = mysqli_stmt_get_result($stmt);
if ($result_kosong) {
    $kamar_kosong = mysqli_fetch_assoc($result_kosong)['total'] ?? 0;
}
mysqli_stmt_close($stmt);

// Kamar terisi/draft adalah total dikurangi yang publish
$kamar_terisi = $total_kamar - $kamar_kosong;


// Query untuk mengambil daftar kamar milik pemilik ini dari tabel 'kost'
$query_daftar_kamar = "SELECT * FROM kost WHERE id_pemilik = ?";
$stmt = mysqli_prepare($koneksi, $query_daftar_kamar);

if ($stmt === false) {
    die("Error preparing statement (daftar_kamar): " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, "i", $id_pemilik);
mysqli_stmt_execute($stmt);
$daftar_kamar_result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pemilik - Heaven Indekos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
        }
    </style>
</head>
<body class="flex flex-col min-h-screen">

    <!-- Header/Navbar -->
    <header class="bg-white shadow-md py-4">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <a href="home_pemilik.php" class="text-2xl font-bold text-gray-800">Dashboard Pemilik</a>
            <nav class="hidden md:flex items-center space-x-6">
                <a href="kelola_kamar.php" class="text-gray-600 hover:text-blue-600 font-medium">Kelola Kost</a>
                <a href="data_penyewa.php" class="text-gray-600 hover:text-blue-600 font-medium">Data Penyewa</a>
                <a href="logout.php" class="bg-red-500 text-white font-semibold px-5 py-2 rounded-lg hover:bg-red-600">Logout</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
        <div class="container mx-auto px-4 py-8">
            <!-- Welcome Message -->
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white p-8 rounded-lg shadow-lg mb-8">
                <h1 class="text-3xl font-bold">Selamat Datang, <?php echo htmlspecialchars($nama_pemilik); ?>!</h1>
                <p class="mt-2 text-blue-100">Kelola semua properti Anda dengan mudah di satu tempat.</p>
            </div>

            <!-- Statistics Section -->
            <section class="mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Ringkasan Properti Anda</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Card: Total Kost -->
                    <div class="bg-white rounded-lg shadow p-6 flex items-center space-x-4">
                        <div class="bg-blue-100 text-blue-600 p-4 rounded-full"><i class="fas fa-building fa-2x"></i></div>
                        <div>
                            <h3 class="text-gray-500">Total Kost</h3>
                            <p class="text-3xl font-bold text-gray-800"><?= $total_kamar ?></p>
                        </div>
                    </div>
                    <!-- Card: Kost Terisi/Draft -->
                    <div class="bg-white rounded-lg shadow p-6 flex items-center space-x-4">
                        <div class="bg-red-100 text-red-600 p-4 rounded-full"><i class="fas fa-bed fa-2x"></i></div>
                        <div>
                            <h3 class="text-gray-500">Kost Terisi / Draft</h3>
                            <p class="text-3xl font-bold text-gray-800"><?= $kamar_terisi ?></p>
                        </div>
                    </div>
                    <!-- Card: Kost Tersedia/Publish -->
                    <div class="bg-white rounded-lg shadow p-6 flex items-center space-x-4">
                        <div class="bg-green-100 text-green-600 p-4 rounded-full"><i class="fas fa-door-open fa-2x"></i></div>
                        <div>
                            <h3 class="text-gray-500">Kost Tersedia</h3>
                            <p class="text-3xl font-bold text-gray-800"><?= $kamar_kosong ?></p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Daftar Kamar Section -->
            <section>
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold text-gray-800">Daftar Kost Anda</h2>
                    <a href="tambah_kamar.php" class="bg-blue-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-plus mr-2"></i>Tambah Kost
                    </a>
                </div>
                <div class="bg-white rounded-lg shadow overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="p-4 font-semibold text-gray-600">Nama Kost</th>
                                <th class="p-4 font-semibold text-gray-600">Harga/Bulan</th>
                                <th class="p-4 font-semibold text-gray-600">Status</th>
                                <th class="p-4 font-semibold text-gray-600">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php if ($daftar_kamar_result && mysqli_num_rows($daftar_kamar_result) > 0): ?>
                                <?php while ($kamar = mysqli_fetch_assoc($daftar_kamar_result)): ?>
                                    <tr>
                                        <td class="p-4"><?= htmlspecialchars($kamar['nama_kost']) ?></td>
                                        <td class="p-4">Rp <?= number_format($kamar['harga'], 0, ',', '.') ?></td>
                                        <td class="p-4">
                                            <?php
                                                $status = $kamar['status'];
                                                $status_class = ($status === 'publish') ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
                                                echo "<span class='px-3 py-1 text-sm font-medium rounded-full $status_class'>" . ucfirst($status) . "</span>";
                                            ?>
                                        </td>
                                        <td class="p-4 flex space-x-2">
                                            <a href="edit_kamar.php?id=<?= $kamar['id_kost'] ?>" class="text-blue-600 hover:text-blue-800" title="Edit"><i class="fas fa-edit"></i></a>
                                            <a href="hapus_kamar.php?id=<?= $kamar['id_kost'] ?>" class="text-red-600 hover:text-red-800" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus kost ini?')"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="p-4 text-center text-gray-500">Anda belum menambahkan data kost.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-4 mt-auto">
        <div class="container mx-auto text-center">
            <p>&copy; <?php echo date("Y"); ?> Heaven Indekos. Semua Hak Dilindungi.</p>
        </div>
    </footer>
</body>
</html>
