<?php
// tambah_kamar.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'koneksi.php';

// Cek otorisasi, hanya admin atau pemilik yang boleh mengakses
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['tipe_akun'], ['pemilik', 'admin'])) {
    header("Location: login.php");
    exit();
}

$pesan_error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $nama_kost = mysqli_real_escape_string($koneksi, $_POST['nama_kost']);
    $id_pemilik = (int)$_POST['id_pemilik'];
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $harga = (int)$_POST['harga'];
    $fasilitas = mysqli_real_escape_string($koneksi, $_POST['fasilitas']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    $nama_foto = '';

    if (empty($nama_kost) || empty($id_pemilik) || empty($alamat) || empty($harga) || empty($status)) {
        $pesan_error = "Semua kolom wajib diisi.";
    } else {
        // Logika upload foto
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            $nama_foto = time() . '_' . basename($_FILES["foto"]["name"]);
            $target_file = $target_dir . $nama_foto;

            if (!move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
                $pesan_error = "Maaf, terjadi kesalahan saat mengupload file gambar.";
                $nama_foto = '';
            }
        }

        if (empty($pesan_error)) {
            // Query INSERT dengan kolom fasilitas
            $query = "INSERT INTO kost (id_pemilik, nama_kost, alamat, harga, foto, fasilitas, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($koneksi, $query);

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ississs", $id_pemilik, $nama_kost, $alamat, $harga, $nama_foto, $fasilitas, $status);
                
                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['pesan_sukses'] = "Data kost berhasil ditambahkan!";
                    
                    // --- PERBAIKAN REDIRECT ---
                    // Arahkan kembali ke dasbor admin gabungan
                    header("Location: dashboard_admin.php"); 
                    exit();
                } else {
                    $pesan_error = "Gagal menyimpan data: " . mysqli_stmt_error($stmt);
                }
                mysqli_stmt_close($stmt);
            } else {
                $pesan_error = "Gagal menyiapkan statement: " . mysqli_error($koneksi);
            }
        }
    }
}

// Ambil daftar pemilik untuk dropdown
$query_pemilik = "SELECT id_pengguna, nama_lengkap FROM pengguna WHERE tipe_akun = 'pemilik' ORDER BY nama_lengkap ASC";
$result_pemilik = mysqli_query($koneksi, $query_pemilik);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kost Baru - Dashboard Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; } </style>
</head>
<body class="flex flex-col min-h-screen">
    <header class="bg-white shadow-md py-4">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Tambah Kost Baru</h1>
            <a href="dashboard_admin.php" class="text-gray-600 hover:text-blue-600 font-medium">Kembali ke Dasbor</a>
        </div>
    </header>

    <main class="flex-grow">
        <div class="container mx-auto px-4 py-8">
            <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-lg">
                <?php if (!empty($pesan_error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <strong class="font-bold">Error!</strong>
                        <span class="block sm:inline"><?= htmlspecialchars($pesan_error) ?></span>
                    </div>
                <?php endif; ?>
                <form action="tambah_kamar.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <!-- Formulir di sini (sama seperti sebelumnya) -->
                    <div><label for="nama_kost" class="block text-sm font-medium text-gray-700">Nama Kost</label><input type="text" id="nama_kost" name="nama_kost" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></div>
                    <div><label for="id_pemilik" class="block text-sm font-medium text-gray-700">Pemilik Kost</label><select id="id_pemilik" name="id_pemilik" required class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"><option value="">-- Pilih Pemilik --</option><?php while ($pemilik = mysqli_fetch_assoc($result_pemilik)): ?><option value="<?= $pemilik['id_pengguna'] ?>"><?= htmlspecialchars($pemilik['nama_lengkap']) ?></option><?php endwhile; ?></select></div>
                    <div><label for="alamat" class="block text-sm font-medium text-gray-700">Alamat Lengkap</label><textarea id="alamat" name="alamat" rows="3" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea></div>
                    <div><label for="harga" class="block text-sm font-medium text-gray-700">Harga per Bulan (Rp)</label><input type="number" id="harga" name="harga" required placeholder="Contoh: 500000" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></div>
                    <div><label for="fasilitas" class="block text-sm font-medium text-gray-700">Fasilitas</label><textarea id="fasilitas" name="fasilitas" rows="4" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Contoh: AC, WiFi, Kamar Mandi Dalam"></textarea><p class="text-xs text-gray-500 mt-1">Pisahkan setiap fasilitas dengan koma (,).</p></div>
                    <div><label for="foto" class="block text-sm font-medium text-gray-700">Foto Kost</label><input type="file" id="foto" name="foto" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"></div>
                    <div><label for="status" class="block text-sm font-medium text-gray-700">Status</label><select id="status" name="status" required class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"><option value="publish">Publish</option><option value="draft">Draft</option></select></div>
                    <div class="flex items-center justify-end space-x-4"><a href="dashboard_admin.php" class="bg-gray-200 text-gray-800 font-semibold px-4 py-2 rounded-lg hover:bg-gray-300">Batal</a><button type="submit" class="bg-blue-600 text-white font-semibold px-6 py-2 rounded-lg hover:bg-blue-700">Simpan Kost</button></div>
                </form>
            </div>
        </div>
    </main>

    <footer class="bg-gray-800 text-white py-4 mt-auto">
        <div class="container mx-auto text-center"><p>&copy; <?= date("Y"); ?> Heaven Indekos. Semua Hak Dilindungi.</p></div>
    </footer>
</body>
</html>
