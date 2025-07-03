<?php
include 'koneksi.php';
session_start();

// --- DEBUG: tampilkan session jika akses ditolak ---
if (!isset($_SESSION['loggedin']) || ($_SESSION['tipe_akun'] ?? '') !== 'admin') {
    echo '<div style="background-color:#fff3cd; border:1px solid #ffeeba; color:#856404; padding:10px; font-family:monospace; margin-bottom:20px">';
    echo '<strong>DEBUG SESSION</strong><br><pre>';
    print_r($_SESSION);
    echo '</pre></div>';

    echo '<div style="background-color:#f8d7da; border:1px solid #f5c6cb; color:#721c24; padding:15px; text-align:center; border-radius:5px">';
    echo '<strong>Akses Ditolak!</strong><br>Halaman ini hanya untuk Admin.';
    echo '<br><a href="logout.php" style="color:#007bff; text-decoration:underline;">Coba Login Ulang</a>';
    echo '</div>';
    exit;
}

// Ambil data pengguna
$query_pengguna = "SELECT * FROM pengguna ORDER BY id_pengguna ASC";
$result_pengguna = mysqli_query($koneksi, $query_pengguna);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Pengguna - Dashboard Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto mt-10 p-6">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-bold text-gray-800">Manajemen Pengguna</h2>
                <a href="dashboard_admin.php" class="text-gray-600 hover:text-blue-800 font-medium">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Dasbor
                </a>
            </div>

            <!-- Pesan sukses/error -->
            <?php if (isset($_SESSION['pesan_sukses'])): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4"><?= $_SESSION['pesan_sukses']; unset($_SESSION['pesan_sukses']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['pesan_error'])): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4"><?= $_SESSION['pesan_error']; unset($_SESSION['pesan_error']); ?></div>
            <?php endif; ?>

            <!-- Tabel pengguna -->
            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-200 text-sm text-left">
                    <thead class="bg-gray-100 text-gray-600 uppercase">
                        <tr>
                            <th class="px-4 py-3">Nama Lengkap</th>
                            <th class="px-4 py-3">Username</th>
                            <th class="px-4 py-3">Tipe Akun</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">No. Telepon</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white text-gray-700 divide-y divide-gray-200">
                        <?php if ($result_pengguna && mysqli_num_rows($result_pengguna) > 0): ?>
                            <?php while ($pengguna = mysqli_fetch_assoc($result_pengguna)): ?>
                                <tr>
                                    <td class="px-4 py-3 font-medium"><?= htmlspecialchars($pengguna['nama_lengkap']) ?></td>
                                    <td class="px-4 py-3"><?= htmlspecialchars($pengguna['nama_pengguna']) ?></td>
                                    <td class="px-4 py-3">
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full
                                            <?= $pengguna['tipe_akun'] === 'admin' ? 'bg-red-100 text-red-800' : ($pengguna['tipe_akun'] === 'pemilik' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') ?>">
                                            <?= ucfirst(htmlspecialchars($pengguna['tipe_akun'])) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3"><?= htmlspecialchars($pengguna['email']) ?></td>
                                    <td class="px-4 py-3"><?= htmlspecialchars($pengguna['nomor_telepon']) ?></td>
                                    <td class="px-4 py-3">
                                        <!-- Form ubah tipe akun -->
                                        <form action="ubah_tipe_akun.php" method="POST" class="flex items-center space-x-2 mb-2">
                                            <input type="hidden" name="id_pengguna" value="<?= $pengguna['id_pengguna'] ?>">
                                            <select name="tipe_akun_baru" class="text-sm border border-gray-300 rounded px-2 py-1">
                                                <option value="admin" <?= $pengguna['tipe_akun'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                                <option value="pemilik" <?= $pengguna['tipe_akun'] === 'pemilik' ? 'selected' : '' ?>>Pemilik</option>
                                                <option value="penghuni" <?= $pengguna['tipe_akun'] === 'penghuni' ? 'selected' : '' ?>>Penghuni</option>
                                            </select>
                                            <button type="submit" class="text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-2 py-1 rounded">Simpan</button>
                                        </form>

                                        <!-- Tombol edit dan hapus -->
                                        <div class="flex space-x-2">
                                            <a href="edit_pengguna.php?id=<?= $pengguna['id_pengguna'] ?>" class="text-yellow-600 hover:text-yellow-800" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($_SESSION['user_id'] != $pengguna['id_pengguna']): ?>
                                                <a href="hapus_pengguna.php?id=<?= $pengguna['id_pengguna'] ?>" class="text-red-600 hover:text-red-800" title="Hapus" onclick="return confirm('Yakin ingin menghapus pengguna ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-10 text-gray-500">Tidak ada data pengguna.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
