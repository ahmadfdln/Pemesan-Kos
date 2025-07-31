<?php
// tambah_kost.php (Diperbarui dengan multi-upload)
include 'session_handler.php';
include 'koneksi.php';

// Cek otorisasi, hanya admin yang boleh mengakses
if (!isset($_SESSION['loggedin']) || ($_SESSION['tipe_akun'] ?? null) !== 'admin') {
    header("Location: login.php");
    exit();
}

// Ambil daftar pengguna dengan tipe 'pemilik' untuk dropdown
$query_pemilik = "SELECT id_pengguna, nama_lengkap FROM pengguna WHERE tipe_akun = 'pemilik' ORDER BY nama_lengkap ASC";
$result_pemilik = mysqli_query($koneksi, $query_pemilik);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Kost Baru - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f9fafb; }
    </style>
</head>
<body class="flex flex-col min-h-screen">

    <header class="bg-white shadow-sm py-4">
        <div class="container mx-auto px-6 flex justify-between items-center">
            <h1 class="text-xl font-semibold text-gray-800">Tambah Data Kost Baru</h1>
            <a href="dashboard_admin.php?page=kost" class="text-indigo-600 hover:text-indigo-800 font-medium">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Manajemen Kost
            </a>
        </div>
    </header>

    <main class="flex-grow">
        <div class="container mx-auto px-6 py-8">
            <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md">
                <form action="proses_tambah_kost.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                    
                    <div>
                        <label for="nama_kost" class="block text-sm font-medium text-gray-700">Nama Kost</label>
                        <input type="text" name="nama_kost" id="nama_kost" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="id_pemilik" class="block text-sm font-medium text-gray-700">Pemilik Kost</label>
                        <select name="id_pemilik" id="id_pemilik" required class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Pilih Pemilik --</option>
                            <?php while($pemilik = mysqli_fetch_assoc($result_pemilik)): ?>
                                <option value="<?= $pemilik['id_pengguna'] ?>"><?= htmlspecialchars($pemilik['nama_lengkap']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div>
                        <label for="alamat" class="block text-sm font-medium text-gray-700">Alamat Lengkap</label>
                        <textarea name="alamat" id="alamat" rows="3" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="harga" class="block text-sm font-medium text-gray-700">Harga per Bulan (Rp)</label>
                            <input type="number" name="harga" id="harga" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Contoh: 500000">
                        </div>
                        <div>
                            <label for="no_hp" class="block text-sm font-medium text-gray-700">Nomor HP (WhatsApp)</label>
                            <input type="text" name="no_hp" id="no_hp" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Contoh: 08123456789">
                        </div>
                    </div>

                    <div>
                        <label for="fasilitas" class="block text-sm font-medium text-gray-700">Fasilitas</label>
                        <textarea name="fasilitas" id="fasilitas" rows="3" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Pisahkan dengan koma, contoh: AC, Wi-Fi, Kamar Mandi Dalam"></textarea>
                    </div>

                    <div>
                        <label for="foto" class="block text-sm font-medium text-gray-700">Foto Kost (Bisa pilih lebih dari satu)</label>
                        <input type="file" name="foto[]" id="foto" required multiple class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    </div>
                    
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" id="status" required class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="publish">Publish (Tampilkan)</option>
                            <option value="draft">Draft (Simpan sebagai draf)</option>
                        </select>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button type="submit" class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="fas fa-save mr-2"></i>Simpan Data Kost
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <footer class="bg-white py-4 mt-auto border-t">
        <div class="container mx-auto text-center text-sm text-gray-500"><p>&copy; <?= date("Y"); ?> Heaven Indekos. Admin Panel.</p></div>
    </footer>
</body>
</html>
