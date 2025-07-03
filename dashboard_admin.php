<?php
// dashboard_admin.php (Gabungan Kelola Kost & Pengguna)

// Mulai session jika belum ada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Sertakan file koneksi database
include 'koneksi.php';

// Cek otorisasi, hanya admin yang boleh mengakses halaman ini
if (!isset($_SESSION['loggedin']) || $_SESSION['tipe_akun'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Ambil data pengguna dari session
$nama_pengguna = $_SESSION['full_name'] ?? 'Admin';
$tipe_akun = $_SESSION['tipe_akun'];

// --- Query untuk Statistik ---
$total_kost = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM kost"))['total'] ?? 0;
$kost_tersedia = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM kost WHERE status = 'publish'"))['total'] ?? 0;
$kost_terisi_draft = $total_kost - $kost_tersedia;
$total_pengguna = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pengguna"))['total'] ?? 0;
$total_pemilik = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pengguna WHERE tipe_akun = 'pemilik'"))['total'] ?? 0;
$total_penyewa = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pengguna WHERE tipe_akun = 'penyewa'"))['total'] ?? 0;
$pemesanan_menunggu = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pemesanan WHERE status = 'Menunggu Konfirmasi'"))['total'] ?? 0;


// --- Query untuk Tabel ---
// Query Daftar Kost
$query_daftar_kost = "SELECT k.*, p.nama_lengkap AS nama_pemilik FROM kost k LEFT JOIN pengguna p ON k.id_pemilik = p.id_pengguna ORDER BY k.id_kost DESC";
$daftar_kost_result = mysqli_query($koneksi, $query_daftar_kost);

// Query Daftar Pengguna
$query_daftar_pengguna = "SELECT * FROM pengguna ORDER BY id_pengguna ASC";
$daftar_pengguna_result = mysqli_query($koneksi, $query_daftar_pengguna);

// Query Daftar Pemesanan yang Menunggu Konfirmasi
$query_pemesanan = "SELECT p.*, u.nama_lengkap AS nama_penyewa FROM pemesanan p JOIN pengguna u ON p.id_penyewa = u.id_pengguna WHERE p.status = 'Menunggu Konfirmasi' ORDER BY p.id DESC";
$daftar_pemesanan_result = mysqli_query($koneksi, $query_pemesanan);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Heaven Indekos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style> body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; } </style>
</head>
<body class="flex flex-col min-h-screen">

    <header class="bg-white shadow-md py-4 sticky top-0 z-10">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Dashboard Admin</h1>
            <a href="logout.php" class="bg-red-500 text-white font-semibold px-5 py-2 rounded-lg hover:bg-red-600">Logout</a>
        </div>
    </header>

    <main class="flex-grow">
        <div class="container mx-auto px-4 py-8">
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white p-8 rounded-lg shadow-lg mb-8">
                <h2 class="text-3xl font-bold">Selamat Datang, <?php echo htmlspecialchars($nama_pengguna); ?>!</h2>
                <p class="mt-2 text-blue-100">Kelola semua data situs di satu tempat.</p>
            </div>

            <section class="mb-8">
                <h3 class="text-2xl font-bold text-gray-800 mb-4">Ringkasan Data Situs</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-4">
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 rounded-r-lg p-4 text-center"><h4 class="text-gray-500 text-sm">Pemesanan Baru</h4><p class="text-3xl font-bold text-yellow-800"><?= $pemesanan_menunggu ?></p></div>
                    <div class="bg-white rounded-lg shadow p-4 text-center"><h4 class="text-gray-500 text-sm">Total Kost</h4><p class="text-3xl font-bold text-gray-800"><?= $total_kost ?></p></div>
                    <div class="bg-white rounded-lg shadow p-4 text-center"><h4 class="text-gray-500 text-sm">Kost Tersedia</h4><p class="text-3xl font-bold text-green-600"><?= $kost_tersedia ?></p></div>
                    <div class="bg-white rounded-lg shadow p-4 text-center"><h4 class="text-gray-500 text-sm">Total Pengguna</h4><p class="text-3xl font-bold text-gray-800"><?= $total_pengguna ?></p></div>
                    <div class="bg-white rounded-lg shadow p-4 text-center"><h4 class="text-gray-500 text-sm">Pemilik</h4><p class="text-3xl font-bold text-blue-600"><?= $total_pemilik ?></p></div>
                    <div class="bg-white rounded-lg shadow p-4 text-center"><h4 class="text-gray-500 text-sm">Penyewa</h4><p class="text-3xl font-bold text-purple-600"><?= $total_penyewa ?></p></div>
                </div>
            </section>

            <?php if (isset($_SESSION['pesan_sukses'])): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert"><p><?= $_SESSION['pesan_sukses'] ?></p></div>
                <?php unset($_SESSION['pesan_sukses']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['pesan_error'])): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert"><p><?= $_SESSION['pesan_error'] ?></p></div>
                <?php unset($_SESSION['pesan_error']); ?>
            <?php endif; ?>

            <!-- Manajemen Pemesanan Section -->
            <section class="mb-12">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-2xl font-bold text-gray-800">Manajemen Pemesanan</h3>
                </div>
                <div class="bg-white rounded-lg shadow overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50"><tr class="text-gray-600"><th class="p-4">Nama Penyewa</th><th class="p-4">Nama Kost</th><th class="p-4">Tanggal Masuk</th><th class="p-4">Total Harga</th><th class="p-4">Aksi</th></tr></thead>
                        <tbody class="divide-y">
                            <?php if ($daftar_pemesanan_result && mysqli_num_rows($daftar_pemesanan_result) > 0): while ($pesanan = mysqli_fetch_assoc($daftar_pemesanan_result)): ?>
                                <tr>
                                    <td class="p-4 font-medium"><?= htmlspecialchars($pesanan['nama_penyewa']) ?></td>
                                    <td class="p-4"><?= htmlspecialchars($pesanan['nama_kost']) ?></td>
                                    <td class="p-4"><?= date('d M Y', strtotime($pesanan['tanggal_masuk'])) ?></td>
                                    <td class="p-4">Rp <?= isset($pesanan['total_harga']) ? number_format($pesanan['total_harga'], 0, ',', '.') : number_format($pesanan['harga'], 0, ',', '.') ?></td>
                                    <td class="p-4 flex space-x-2">
                                        <a href="proses_konfirmasi.php?id=<?= $pesanan['id'] ?>&aksi=konfirmasi" class="bg-green-500 text-white px-3 py-1 rounded-md text-xs hover:bg-green-600" onclick="return confirm('Konfirmasi pemesanan ini?')">Konfirmasi</a>
                                        <a href="proses_konfirmasi.php?id=<?= $pesanan['id'] ?>&aksi=tolak" class="bg-red-500 text-white px-3 py-1 rounded-md text-xs hover:bg-red-600" onclick="return confirm('Tolak pemesanan ini?')">Tolak</a>
                                    </td>
                                </tr>
                            <?php endwhile; else: ?>
                                <tr><td colspan="5" class="p-4 text-center text-gray-500">Tidak ada pemesanan yang menunggu konfirmasi.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Bagian Manajemen Kost & Pengguna -->
            <section class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-2xl font-bold text-gray-800">Manajemen Kost</h3>
                        <a href="tambah_kamar.php" class="bg-blue-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-blue-700 text-sm"><i class="fas fa-plus mr-2"></i>Tambah</a>
                    </div>
                    <div class="bg-white rounded-lg shadow overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50"><tr class="text-gray-600"><th class="p-4">Nama Kost</th><th class="p-4">Pemilik</th><th class="p-4">Status</th><th class="p-4">Aksi</th></tr></thead>
                            <tbody class="divide-y">
                                <?php if ($daftar_kost_result && mysqli_num_rows($daftar_kost_result) > 0): while ($kost = mysqli_fetch_assoc($daftar_kost_result)): ?>
                                    <tr>
                                        <td class="p-4 font-medium"><?= htmlspecialchars($kost['nama_kost']) ?></td>
                                        <td class="p-4"><?= htmlspecialchars($kost['nama_pemilik'] ?? 'N/A') ?></td>
                                        <td class="p-4"><span class="px-2 py-1 text-xs font-semibold rounded-full <?= ($kost['status'] === 'publish') ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>"><?= ucfirst($kost['status']) ?></span></td>
                                        <td class="p-4 flex space-x-3"><a href="edit_kamar.php?id=<?= $kost['id_kost'] ?>" class="text-yellow-600 hover:text-yellow-800" title="Edit"><i class="fas fa-edit"></i></a><a href="hapus_kamar.php?id=<?= $kost['id_kost'] ?>" class="text-red-600 hover:text-red-800" title="Hapus" onclick="return confirm('Yakin?')"><i class="fas fa-trash"></i></a></td>
                                    </tr>
                                <?php endwhile; else: ?>
                                    <tr><td colspan="4" class="p-4 text-center text-gray-500">Belum ada data kost.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-2xl font-bold text-gray-800">Manajemen Pengguna</h3>
                        <a href="tambah_pengguna.php" class="bg-blue-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-blue-700 text-sm"><i class="fas fa-plus mr-2"></i>Tambah</a>
                    </div>
                    <div class="bg-white rounded-lg shadow overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50"><tr class="text-gray-600"><th class="p-4">Nama Lengkap</th><th class="p-4">Tipe Akun</th><th class="p-4">Aksi</th></tr></thead>
                            <tbody class="divide-y">
                                <?php if ($daftar_pengguna_result && mysqli_num_rows($daftar_pengguna_result) > 0): while ($pengguna = mysqli_fetch_assoc($daftar_pengguna_result)): ?>
                                    <tr>
                                        <td class="p-4 font-medium"><?= htmlspecialchars($pengguna['nama_lengkap']) ?></td>
                                        <td class="p-4"><span class="px-2 py-1 text-xs font-semibold rounded-full <?php if ($pengguna['tipe_akun'] == 'admin') echo 'bg-red-100 text-red-800'; elseif ($pengguna['tipe_akun'] == 'pemilik') echo 'bg-blue-100 text-blue-800'; else echo 'bg-green-100 text-green-800'; ?>"><?= ucfirst(htmlspecialchars($pengguna['tipe_akun'])) ?></span></td>
                                        <td class="p-4 flex space-x-3"><a href="edit_pengguna.php?id=<?= $pengguna['id_pengguna'] ?>" class="text-yellow-600 hover:text-yellow-800" title="Edit"><i class="fas fa-edit"></i></a><?php if ($_SESSION['user_id'] != $pengguna['id_pengguna']): ?><a href="hapus_pengguna.php?id=<?= $pengguna['id_pengguna'] ?>" class="text-red-600 hover:text-red-800" title="Hapus" onclick="return confirm('Yakin?')"><i class="fas fa-trash"></i></a><?php endif; ?></td>
                                    </tr>
                                <?php endwhile; else: ?>
                                    <tr><td colspan="3" class="p-4 text-center text-gray-500">Belum ada data pengguna.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <footer class="bg-gray-800 text-white py-4 mt-auto">
        <div class="container mx-auto text-center"><p>&copy; <?php echo date("Y"); ?> Heaven Indekos. Semua Hak Dilindungi.</p></div>
    </footer>
</body>
</html>
