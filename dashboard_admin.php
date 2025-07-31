<?php
// dashboard_admin.php (Versi Final dengan Semua Fitur)
include 'session_handler.php';
include 'koneksi.php';

// Cek otorisasi
if (!isset($_SESSION['loggedin']) || ($_SESSION['tipe_akun'] ?? null) !== 'admin') {
    header("Location: login.php");
    exit();
}

$nama_pengguna = $_SESSION['full_name'] ?? 'Admin';
$current_page = $_GET['page'] ?? 'dashboard';

// --- LOGIKA UNTUK SETIAP HALAMAN ---

// Ambil pesan flash jika ada, lalu hapus
$flash_message = $_SESSION['flash_message'] ?? null;
if ($flash_message) {
    unset($_SESSION['flash_message']);
}


// Variabel umum untuk pencarian dan filter
$search = $_GET['search'] ?? '';
$where_clauses = [];
if (!empty($search)) {
    // Disesuaikan per halaman di bawah
}

// Variabel umum untuk pagination
$limit = 10; // Jumlah item per halaman
$p = $_GET['p'] ?? 1; // Halaman saat ini
$offset = ($p - 1) * $limit;

// --- Halaman Dashboard ---
if ($current_page == 'dashboard') {
    $total_kost = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM kost"))['total'] ?? 0;
    $kost_tersedia = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM kost WHERE status = 'publish'"))['total'] ?? 0;
    $total_pengguna = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pengguna"))['total'] ?? 0;
    $pemesanan_menunggu = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pemesanan WHERE status = 'Menunggu Konfirmasi'"))['total'] ?? 0;
    
    $query_recent_pemesanan = "SELECT p.nama_kost, p.status, u.nama_lengkap AS nama_penyewa FROM pemesanan p JOIN pengguna u ON p.id_penyewa = u.id_pengguna ORDER BY p.id DESC LIMIT 5";
    $recent_pemesanan_result = mysqli_query($koneksi, $query_recent_pemesanan);

    // Data untuk Grafik Pemesanan 6 Bulan Terakhir
    $chart_data = [];
    for ($i = 5; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        $month_name = date('M Y', strtotime("-$i months"));
        $query_chart = "SELECT COUNT(*) AS total FROM pemesanan WHERE status = 'Dikonfirmasi' AND DATE_FORMAT(tanggal_pemesanan, '%Y-%m') = '$month'";
        $result_chart = mysqli_query($koneksi, $query_chart);
        $data = mysqli_fetch_assoc($result_chart);
        $chart_data['labels'][] = $month_name;
        $chart_data['values'][] = $data['total'] ?? 0;
    }
    $chart_data_json = json_encode($chart_data);
}

// --- Halaman Manajemen Pemesanan ---
if ($current_page == 'pemesanan') {
    $filter_status = $_GET['status'] ?? '';
    $where_clauses = [];
    if (!empty($search)) {
        $where_clauses[] = "(u.nama_lengkap LIKE '%$search%' OR p.nama_kost LIKE '%$search%')";
    }
    if (!empty($filter_status)) {
        $where_clauses[] = "p.status = '" . mysqli_real_escape_string($koneksi, $filter_status) . "'";
    }
    $where_sql = count($where_clauses) > 0 ? "WHERE " . implode(' AND ', $where_clauses) : '';

    $query_pemesanan = "SELECT p.*, u.nama_lengkap AS nama_penyewa FROM pemesanan p JOIN pengguna u ON p.id_penyewa = u.id_pengguna $where_sql ORDER BY p.id DESC LIMIT $limit OFFSET $offset";
    $daftar_pemesanan_result = mysqli_query($koneksi, $query_pemesanan);
    
    $total_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pemesanan p JOIN pengguna u ON p.id_penyewa = u.id_pengguna $where_sql");
    $total_records = mysqli_fetch_assoc($total_query)['total'];
    $total_pages = ceil($total_records / $limit);
}

// --- Halaman Manajemen Kost ---
if ($current_page == 'kost') {
    $where_clauses = [];
    if (!empty($search)) {
        $where_clauses[] = "(k.nama_kost LIKE '%$search%' OR p.nama_lengkap LIKE '%$search%')";
    }
    $where_sql = count($where_clauses) > 0 ? "WHERE " . implode(' AND ', $where_clauses) : '';

    $query_daftar_kost = "SELECT k.*, p.nama_lengkap AS nama_pemilik FROM kost k LEFT JOIN pengguna p ON k.id_pemilik = p.id_pengguna $where_sql ORDER BY k.id_kost DESC LIMIT $limit OFFSET $offset";
    $daftar_kost_result = mysqli_query($koneksi, $query_daftar_kost);

    $total_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM kost k LEFT JOIN pengguna p ON k.id_pemilik = p.id_pengguna $where_sql");
    $total_records = mysqli_fetch_assoc($total_query)['total'];
    $total_pages = ceil($total_records / $limit);
}

// --- Halaman Manajemen Pengguna ---
if ($current_page == 'pengguna') {
    $filter_tipe = $_GET['tipe_akun'] ?? '';
    $where_clauses = [];
    if (!empty($search)) {
        $where_clauses[] = "nama_lengkap LIKE '%$search%'";
    }
    if (!empty($filter_tipe)) {
        $where_clauses[] = "tipe_akun = '" . mysqli_real_escape_string($koneksi, $filter_tipe) . "'";
    }
    $where_sql = count($where_clauses) > 0 ? "WHERE " . implode(' AND ', $where_clauses) : '';

    $query_daftar_pengguna = "SELECT * FROM pengguna $where_sql ORDER BY id_pengguna ASC LIMIT $limit OFFSET $offset";
    $daftar_pengguna_result = mysqli_query($koneksi, $query_daftar_pengguna);

    $total_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pengguna $where_sql");
    $total_records = mysqli_fetch_assoc($total_query)['total'];
    $total_pages = ceil($total_records / $limit);
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Heaven Indekos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-link.active { background-color: #4f46e5; color: white; }
        .pagination-link { display: inline-block; padding: 8px 12px; margin: 0 2px; border: 1px solid #ddd; color: #4f46e5; border-radius: 4px; }
        .pagination-link:hover { background-color: #eef2ff; }
        .pagination-link.active { background-color: #4f46e5; color: white; border-color: #4f46e5; }
        .modal-overlay { transition: opacity 0.3s ease; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside id="sidebar" class="w-64 bg-gray-800 text-white flex flex-col fixed inset-y-0 left-0 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out z-30">
            <div class="p-6 text-center border-b border-gray-700">
                <h2 class="text-2xl font-bold">Admin Panel</h2>
            </div>
            <nav class="flex-grow p-4 space-y-2">
                <a href="dashboard_admin.php?page=dashboard" class="sidebar-link flex items-center px-4 py-2.5 rounded-lg <?= $current_page == 'dashboard' ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt w-6"></i><span class="ml-3">Dashboard</span>
                </a>
                <a href="dashboard_admin.php?page=pemesanan" class="sidebar-link flex items-center px-4 py-2.5 rounded-lg <?= $current_page == 'pemesanan' ? 'active' : '' ?>">
                    <i class="fas fa-receipt w-6"></i><span class="ml-3">Pemesanan</span>
                </a>
                <a href="dashboard_admin.php?page=kost" class="sidebar-link flex items-center px-4 py-2.5 rounded-lg <?= $current_page == 'kost' ? 'active' : '' ?>">
                    <i class="fas fa-building w-6"></i><span class="ml-3">Manajemen Kost</span>
                </a>
                <a href="dashboard_admin.php?page=pengguna" class="sidebar-link flex items-center px-4 py-2.5 rounded-lg <?= $current_page == 'pengguna' ? 'active' : '' ?>">
                    <i class="fas fa-users w-6"></i><span class="ml-3">Manajemen Pengguna</span>
                </a>
                <a href="dashboard_admin.php?page=laporan" class="sidebar-link flex items-center px-4 py-2.5 rounded-lg <?= $current_page == 'laporan' ? 'active' : '' ?>">
                    <i class="fas fa-file-alt w-6"></i><span class="ml-3">Laporan</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden md:ml-64 transition-all duration-300 ease-in-out">
            <header class="bg-white shadow-sm p-4 flex items-center justify-between">
                <button id="sidebar-toggle" class="md:hidden text-gray-600 focus:outline-none"><i class="fas fa-bars text-xl"></i></button>
                <h1 class="text-xl font-semibold text-gray-700">Selamat Datang, <?= htmlspecialchars($nama_pengguna) ?>!</h1>
                <a href="logout.php" class="text-sm text-gray-600 hover:text-indigo-600"><i class="fas fa-sign-out-alt mr-1"></i>Logout</a>
            </header>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                
                <!-- Notifikasi Flash Message -->
                <?php if ($flash_message): ?>
                <div id="flash-message" class="mb-4 p-4 rounded-md bg-green-100 text-green-800 border border-green-200">
                    <?= htmlspecialchars($flash_message) ?>
                </div>
                <?php endif; ?>

                <?php if ($current_page == 'dashboard'): ?>
                    <!-- KONTEN DASHBOARD -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="bg-yellow-100 p-6 rounded-lg shadow"><h3 class="text-yellow-800 font-bold">Pemesanan Baru</h3><p class="text-4xl font-extrabold text-yellow-900 mt-2"><?= $pemesanan_menunggu ?></p></div>
                        <div class="bg-blue-100 p-6 rounded-lg shadow"><h3 class="text-blue-800 font-bold">Total Kost</h3><p class="text-4xl font-extrabold text-blue-900 mt-2"><?= $total_kost ?></p></div>
                        <div class="bg-green-100 p-6 rounded-lg shadow"><h3 class="text-green-800 font-bold">Kost Tersedia</h3><p class="text-4xl font-extrabold text-green-900 mt-2"><?= $kost_tersedia ?></p></div>
                        <div class="bg-purple-100 p-6 rounded-lg shadow"><h3 class="text-purple-800 font-bold">Total Pengguna</h3><p class="text-4xl font-extrabold text-purple-900 mt-2"><?= $total_pengguna ?></p></div>
                    </div>
                    <div class="mt-8 grid grid-cols-1 lg:grid-cols-5 gap-6">
                        <div class="lg:col-span-3 bg-white p-6 rounded-lg shadow-md">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Pemesanan Dikonfirmasi (6 Bulan Terakhir)</h3>
                            <canvas id="pemesananChart"></canvas>
                        </div>
                        <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Aktivitas Terbaru</h3>
                            <div class="space-y-4">
                                <?php if ($recent_pemesanan_result && mysqli_num_rows($recent_pemesanan_result) > 0): ?>
                                    <?php while ($item = mysqli_fetch_assoc($recent_pemesanan_result)): ?>
                                        <div class="flex items-center space-x-4"><div class="p-3 rounded-full <?php if ($item['status'] == 'Dikonfirmasi') echo 'bg-green-100 text-green-600'; elseif ($item['status'] == 'Ditolak') echo 'bg-red-100 text-red-600'; else echo 'bg-yellow-100 text-yellow-600'; ?>"><i class="fas fa-receipt"></i></div><div><p class="font-semibold text-gray-800"><?= htmlspecialchars($item['nama_penyewa']) ?></p><p class="text-sm text-gray-500">Memesan <span class="font-medium text-gray-700"><?= htmlspecialchars($item['nama_kost']) ?></span></p></div></div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="text-center text-gray-500">Belum ada aktivitas.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                <?php elseif ($current_page == 'pemesanan'): ?>
                    <!-- KONTEN MANAJEMEN PEMESANAN -->
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h2 class="text-2xl font-bold text-gray-800 mb-4">Manajemen Pemesanan</h2>
                        <form method="GET" class="mb-4 flex flex-wrap items-center gap-4">
                            <input type="hidden" name="page" value="pemesanan">
                            <input type="text" name="search" placeholder="Cari penyewa atau kost..." value="<?= htmlspecialchars($search) ?>" class="px-4 py-2 border rounded-lg w-full md:w-1/3">
                            <select name="status" class="px-4 py-2 border rounded-lg">
                                <option value="">Semua Status</option>
                                <option value="Menunggu Konfirmasi" <?= $filter_status == 'Menunggu Konfirmasi' ? 'selected' : '' ?>>Menunggu Konfirmasi</option>
                                <option value="Dikonfirmasi" <?= $filter_status == 'Dikonfirmasi' ? 'selected' : '' ?>>Dikonfirmasi</option>
                                <option value="Ditolak" <?= $filter_status == 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
                            </select>
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Filter</button>
                        </form>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-gray-50"><tr><th class="p-4">Penyewa</th><th class="p-4">Kost</th><th class="p-4">Total</th><th class="p-4">Status</th><th class="p-4">Aksi</th></tr></thead>
                                <tbody class="divide-y">
                                    <?php if ($daftar_pemesanan_result && mysqli_num_rows($daftar_pemesanan_result) > 0): while ($pesanan = mysqli_fetch_assoc($daftar_pemesanan_result)): ?>
                                        <tr>
                                            <td class="p-4 font-medium"><?= htmlspecialchars($pesanan['nama_penyewa']) ?></td>
                                            <td class="p-4"><?= htmlspecialchars($pesanan['nama_kost']) ?></td>
                                            <td class="p-4">Rp <?= number_format($pesanan['total_harga'] ?? $pesanan['harga'], 0, ',', '.') ?></td>
                                            <td class="p-4"><span class="px-2 py-1 text-xs font-semibold rounded-full <?= ($pesanan['status'] === 'Dikonfirmasi') ? 'bg-green-100 text-green-800' : (($pesanan['status'] === 'Ditolak') ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') ?>"><?= htmlspecialchars($pesanan['status']) ?></span></td>
                                            <td class="p-4 flex items-center space-x-2">
                                                <a href="detail_pemesanan_admin.php?id=<?= $pesanan['id'] ?>" class="text-blue-600 hover:text-blue-800" title="Detail"><i class="fas fa-eye"></i></a>
                                                <?php if ($pesanan['status'] === 'Menunggu Konfirmasi'): ?>
                                                    <button onclick="showConfirmationModal('konfirmasi', <?= $pesanan['id'] ?>)" class="text-green-600 hover:text-green-800" title="Konfirmasi Pesanan"><i class="fas fa-check-circle"></i></button>
                                                    <button onclick="showConfirmationModal('batalkan', <?= $pesanan['id'] ?>)" class="text-red-600 hover:text-red-800" title="Batalkan Pesanan"><i class="fas fa-times-circle"></i></button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; else: ?>
                                        <tr><td colspan="5" class="p-4 text-center text-gray-500">Data tidak ditemukan.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?page=pemesanan&p=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($filter_status) ?>" class="pagination-link <?= $p == $i ? 'active' : '' ?>"><?= $i ?></a>
                            <?php endfor; ?>
                        </div>
                    </div>

                <?php elseif ($current_page == 'kost'): ?>
                    <!-- KONTEN MANAJEMEN KOST -->
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-2xl font-bold text-gray-800">Manajemen Kost</h2>
                            <a href="tambah_kost.php" class="bg-indigo-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-indigo-700 transition duration-200 flex items-center">
                                <i class="fas fa-plus mr-2"></i>Tambah Kost Baru
                            </a>
                        </div>
                        <form method="GET" class="mb-4 flex flex-wrap items-center gap-4">
                            <input type="hidden" name="page" value="kost">
                            <input type="text" name="search" placeholder="Cari nama kost atau pemilik..." value="<?= htmlspecialchars($search) ?>" class="px-4 py-2 border rounded-lg w-full md:w-1/3">
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Cari</button>
                        </form>
                        <div class="overflow-x-auto">
                           <table class="w-full text-left text-sm">
                                <thead class="bg-gray-50"><tr><th class="p-4">Nama Kost</th><th class="p-4">Pemilik</th><th class="p-4">Status</th><th class="p-4">Aksi</th></tr></thead>
                                <tbody class="divide-y">
                                    <?php if ($daftar_kost_result && mysqli_num_rows($daftar_kost_result) > 0): while ($kost = mysqli_fetch_assoc($daftar_kost_result)): ?>
                                        <tr>
                                            <td class="p-4 font-medium"><?= htmlspecialchars($kost['nama_kost']) ?></td>
                                            <td class="p-4"><?= htmlspecialchars($kost['nama_pemilik'] ?? 'N/A') ?></td>
                                            <td class="p-4"><span class="px-2 py-1 text-xs font-semibold rounded-full <?= ($kost['status'] === 'publish') ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>"><?= ucfirst($kost['status']) ?></span></td>
                                            <td class="p-4 flex space-x-3">
                                                <a href="edit_kamar.php?id=<?= $kost['id_kost'] ?>" class="text-yellow-600 hover:text-yellow-800" title="Edit"><i class="fas fa-edit"></i></a>
                                                <button onclick="showConfirmationModal('hapus_kost', <?= $kost['id_kost'] ?>, '<?= htmlspecialchars(addslashes($kost['nama_kost'])) ?>')" class="text-red-600 hover:text-red-800" title="Hapus"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    <?php endwhile; else: ?>
                                        <tr><td colspan="4" class="p-4 text-center text-gray-500">Data tidak ditemukan.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?page=kost&p=<?= $i ?>&search=<?= urlencode($search) ?>" class="pagination-link <?= $p == $i ? 'active' : '' ?>"><?= $i ?></a>
                            <?php endfor; ?>
                        </div>
                    </div>
                
                <?php elseif ($current_page == 'pengguna'): ?>
                    <!-- KONTEN MANAJEMEN PENGGUNA -->
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h2 class="text-2xl font-bold text-gray-800 mb-4">Manajemen Pengguna</h2>
                        <form method="GET" class="mb-4 flex flex-wrap items-center gap-4">
                            <input type="hidden" name="page" value="pengguna">
                            <input type="text" name="search" placeholder="Cari nama pengguna..." value="<?= htmlspecialchars($search) ?>" class="px-4 py-2 border rounded-lg w-full md:w-1/3">
                            <select name="tipe_akun" class="px-4 py-2 border rounded-lg">
                                <option value="">Semua Tipe</option>
                                <option value="admin" <?= ($filter_tipe ?? '') == 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="pemilik" <?= ($filter_tipe ?? '') == 'pemilik' ? 'selected' : '' ?>>Pemilik</option>
                                <option value="penyewa" <?= ($filter_tipe ?? '') == 'penyewa' ? 'selected' : '' ?>>Penyewa</option>
                            </select>
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Filter</button>
                        </form>
                        <div class="overflow-x-auto">
                           <table class="w-full text-left text-sm">
                                <thead class="bg-gray-50"><tr><th class="p-4">Nama Lengkap</th><th class="p-4">Tipe Akun</th><th class="p-4">Aksi</th></tr></thead>
                                <tbody class="divide-y">
                                    <?php if ($daftar_pengguna_result && mysqli_num_rows($daftar_pengguna_result) > 0): while ($pengguna = mysqli_fetch_assoc($daftar_pengguna_result)): ?>
                                        <tr>
                                            <td class="p-4 font-medium"><?= htmlspecialchars($pengguna['nama_lengkap']) ?></td>
                                            <td class="p-4"><span class="px-2 py-1 text-xs font-semibold rounded-full <?php if ($pengguna['tipe_akun'] == 'admin') echo 'bg-red-100 text-red-800'; elseif ($pengguna['tipe_akun'] == 'pemilik') echo 'bg-blue-100 text-blue-800'; else echo 'bg-green-100 text-green-800'; ?>"><?= ucfirst(htmlspecialchars($pengguna['tipe_akun'])) ?></span></td>
                                            <td class="p-4 flex space-x-3">
                                                <a href="edit_pengguna.php?id=<?= $pengguna['id_pengguna'] ?>" class="text-yellow-600 hover:text-yellow-800" title="Edit"><i class="fas fa-edit"></i></a>
                                                <?php if (($_SESSION['user_id'] != $pengguna['id_pengguna']) && $pengguna['tipe_akun'] !== 'admin'): ?>
                                                    <button onclick="showConfirmationModal('hapus_pengguna', <?= $pengguna['id_pengguna'] ?>, '<?= htmlspecialchars(addslashes($pengguna['nama_lengkap'])) ?>')" class="text-red-600 hover:text-red-800" title="Hapus"><i class="fas fa-trash"></i></button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; else: ?>
                                        <tr><td colspan="3" class="p-4 text-center text-gray-500">Data tidak ditemukan.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?page=pengguna&p=<?= $i ?>&search=<?= urlencode($search) ?>&tipe_akun=<?= urlencode($filter_tipe ?? '') ?>" class="pagination-link <?= $p == $i ? 'active' : '' ?>"><?= $i ?></a>
                            <?php endfor; ?>
                        </div>
                    </div>

                <?php elseif ($current_page == 'laporan'): ?>
                    <!-- KONTEN LAPORAN -->
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">Pusat Laporan</h2>
                        <form action="generate_report.php" method="GET" target="_blank" class="border rounded-lg p-6 grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                            <div>
                                <label for="report_type" class="block text-sm font-medium text-gray-700">Jenis Laporan</label>
                                <select name="report" id="report_type" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="pemesanan">Laporan Pemesanan</option>
                                    <option value="pendapatan">Laporan Pendapatan</option>
                                    <option value="kost">Laporan Data Kost</option>
                                    <option value="pengguna">Laporan Pengguna</option>
                                </select>
                            </div>
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                                <input type="date" name="start_date" id="start_date" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700">Tanggal Akhir</label>
                                <input type="date" name="end_date" id="end_date" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div class="md:col-span-3 flex justify-end gap-3">
                                <button type="submit" name="format" value="pdf" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700"><i class="fas fa-file-pdf mr-2"></i>Unduh PDF</button>
                                <button type="submit" name="format" value="csv" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700"><i class="fas fa-file-excel mr-2"></i>Unduh Excel (CSV)</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Modal Konfirmasi -->
    <div id="confirmationModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 modal-overlay opacity-0 pointer-events-none">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
            <h3 id="modalTitle" class="text-xl font-bold text-gray-800 mb-4">Konfirmasi Tindakan</h3>
            <p id="modalMessage" class="text-gray-600 mb-6">Apakah Anda yakin ingin melanjutkan tindakan ini?</p>
            <div class="flex justify-end space-x-4">
                <button onclick="hideConfirmationModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">Batal</button>
                <a id="modalConfirmButton" href="#" class="px-4 py-2 text-white rounded-lg">Ya, Lanjutkan</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sidebarToggle = document.getElementById('sidebar-toggle');
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', () => {
                    document.getElementById('sidebar').classList.toggle('-translate-x-full');
                });
            }

            // Chart.js
            const ctx = document.getElementById('pemesananChart');
            if (ctx) {
                const chartData = JSON.parse('<?= $chart_data_json ?? 'null' ?>');
                if (chartData) {
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: chartData.labels,
                            datasets: [{
                                label: 'Jumlah Pemesanan',
                                data: chartData.values,
                                backgroundColor: 'rgba(79, 70, 229, 0.8)',
                                borderColor: 'rgba(79, 70, 229, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            scales: { y: { beginAtZero: true } },
                            responsive: true,
                            plugins: { legend: { display: false } }
                        }
                    });
                }
            }

            // Hilangkan flash message setelah beberapa detik
            const flashMessage = document.getElementById('flash-message');
            if (flashMessage) {
                setTimeout(() => {
                    flashMessage.style.transition = 'opacity 0.5s ease';
                    flashMessage.style.opacity = '0';
                    setTimeout(() => flashMessage.remove(), 500);
                }, 5000); // 5 detik
            }
        });

        // Script untuk Modal
        const modal = document.getElementById('confirmationModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalMessage = document.getElementById('modalMessage');
        const modalConfirmButton = document.getElementById('modalConfirmButton');

        function showConfirmationModal(action, id, name = '') {
            let title, message, buttonClass, url;

            if (action === 'konfirmasi') {
                title = 'Konfirmasi Pesanan';
                message = `Apakah Anda yakin ingin mengonfirmasi pesanan #${id}? Tindakan ini tidak dapat dibatalkan.`;
                buttonClass = 'bg-green-600 hover:bg-green-700';
                url = `proses_pemesanan.php?aksi=konfirmasi&id=${id}`;
            } else if (action === 'batalkan') {
                title = 'Batalkan Pesanan';
                message = `Apakah Anda yakin ingin membatalkan (menolak) pesanan #${id}? Tindakan ini tidak dapat dibatalkan.`;
                buttonClass = 'bg-red-600 hover:bg-red-700';
                url = `proses_pemesanan.php?aksi=batalkan&id=${id}`;
            } 
            else if (action === 'hapus_pengguna') {
                title = 'Hapus Pengguna';
                message = `Apakah Anda yakin ingin menghapus pengguna "${name}"? Semua data pemesanan terkait akan dihapus. Tindakan ini tidak dapat dibatalkan.`;
                buttonClass = 'bg-red-600 hover:bg-red-700';
                url = `hapus_pengguna.php?id=${id}`;
            }
            else if (action === 'hapus_kost') {
                title = 'Hapus Data Kost';
                message = `Apakah Anda yakin ingin menghapus kost "${name}"? Semua data pemesanan terkait kost ini juga akan dihapus secara permanen.`;
                buttonClass = 'bg-red-600 hover:bg-red-700';
                url = `hapus_kost.php?id=${id}`;
            }

            modalTitle.textContent = title;
            modalMessage.textContent = message;
            modalConfirmButton.href = url;
            
            modalConfirmButton.className = 'px-4 py-2 text-white rounded-lg ' + buttonClass;
            modal.classList.remove('opacity-0', 'pointer-events-none');
        }

        function hideConfirmationModal() {
            modal.classList.add('opacity-0', 'pointer-events-none');
        }
    </script>
</body>
</html>
