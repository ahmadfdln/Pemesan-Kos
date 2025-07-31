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
    $no_hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    $fasilitas = mysqli_real_escape_string($koneksi, $_POST['fasilitas']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    
    // Variabel untuk menampung nama file yang di-upload
    $nama_foto_string = '';

    // Validasi dasar
    if (empty($nama_kost) || empty($id_pemilik) || empty($alamat) || empty($harga) || empty($status)) {
        $pesan_error = "Kolom Nama Kost, Pemilik, Alamat, Harga, dan Status wajib diisi.";
    } else {
        // --- 1. LOGIKA UPLOAD MULTI-GAMBAR ---
        $uploaded_files = [];
        // Cek apakah ada file yang di-upload dan tidak ada error
        if (isset($_FILES['foto']['name']) && is_array($_FILES['foto']['name'])) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }

            $file_count = count($_FILES['foto']['name']);
            for ($i = 0; $i < $file_count; $i++) {
                // Hanya proses file jika tidak ada error upload
                if ($_FILES['foto']['error'][$i] === UPLOAD_ERR_OK) {
                    $nama_file_asli = basename($_FILES["foto"]["name"][$i]);
                    $nama_file_unik = time() . '_' . uniqid() . '_' . $nama_file_asli;
                    $target_file = $target_dir . $nama_file_unik;

                    if (move_uploaded_file($_FILES["foto"]["tmp_name"][$i], $target_file)) {
                        $uploaded_files[] = $nama_file_unik;
                    } else {
                        $pesan_error .= "Gagal mengupload file: " . htmlspecialchars($nama_file_asli) . ". ";
                    }
                }
            }
        }

        // --- 2. GABUNGKAN NAMA FILE MENJADI STRING ---
        if (!empty($uploaded_files)) {
            $nama_foto_string = implode(', ', $uploaded_files);
        }

        if (empty($pesan_error)) {
            // Query INSERT dengan nama file yang sudah digabung
            $query = "INSERT INTO kost (id_pemilik, nama_kost, alamat, harga, no_hp, foto, fasilitas, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($koneksi, $query);

            if ($stmt) {
                // Bind parameter dengan string nama file
                mysqli_stmt_bind_param($stmt, "ississss", $id_pemilik, $nama_kost, $alamat, $harga, $no_hp, $nama_foto_string, $fasilitas, $status);
                
                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['pesan_sukses'] = "Data kost berhasil ditambahkan!";
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
                    
                    <div><label for="nama_kost" class="block text-sm font-medium text-gray-700">Nama Kost</label><input type="text" id="nama_kost" name="nama_kost" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></div>
                    
                    <div><label for="id_pemilik" class="block text-sm font-medium text-gray-700">Pemilik Kost</label><select id="id_pemilik" name="id_pemilik" required class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"><option value="">-- Pilih Pemilik --</option><?php while ($pemilik = mysqli_fetch_assoc($result_pemilik)): ?><option value="<?= $pemilik['id_pengguna'] ?>"><?= htmlspecialchars($pemilik['nama_lengkap']) ?></option><?php endwhile; ?></select></div>
                    
                    <div><label for="alamat" class="block text-sm font-medium text-gray-700">Alamat Lengkap</label><textarea id="alamat" name="alamat" rows="3" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea></div>
                    
                    <div><label for="harga" class="block text-sm font-medium text-gray-700">Harga per Bulan (Rp)</label><input type="number" id="harga" name="harga" required placeholder="Contoh: 500000" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></div>

                    <div><label for="no_hp" class="block text-sm font-medium text-gray-700">Nomor HP Pemilik (WhatsApp)</label><input type="text" id="no_hp" name="no_hp" placeholder="Contoh: 081234567890" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></div>

                    <div><label for="fasilitas" class="block text-sm font-medium text-gray-700">Fasilitas</label><textarea id="fasilitas" name="fasilitas" rows="4" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Contoh: AC, WiFi, Kamar Mandi Dalam"></textarea><p class="text-xs text-gray-500 mt-1">Pisahkan setiap fasilitas dengan koma (,).</p></div>
                    
                    <!-- 3. UBAH INPUT FILE UNTUK MULTIPLE UPLOAD -->
                    <div>
                        <label for="foto" class="block text-sm font-medium text-gray-700">Foto Kost (Bisa pilih lebih dari satu)</label>
                        <input type="file" id="foto" name="foto[]" accept="image/*" multiple class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                    
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
