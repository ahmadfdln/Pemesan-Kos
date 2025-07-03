<?php
// edit_kamar.php

include 'koneksi.php';
session_start();

// 1. Cek otorisasi, hanya admin atau pemilik yang boleh mengakses
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['tipe_akun'], ['pemilik', 'admin'])) {
    header("Location: login.php");
    exit();
}

$pesan_error = '';
$kost_data = null;
$id_kost = isset($_GET['id']) ? intval($_GET['id']) : 0;

// --- Logika untuk memproses form saat disubmit (UPDATE) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $id_kost_update = intval($_POST['id_kost']);
    $nama_kost = mysqli_real_escape_string($koneksi, $_POST['nama_kost']);
    $id_pemilik = intval($_POST['id_pemilik']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $harga = intval($_POST['harga']);
    $fasilitas = mysqli_real_escape_string($koneksi, $_POST['fasilitas']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    $foto_lama = mysqli_real_escape_string($koneksi, $_POST['foto_lama']);
    $nama_foto_baru = $foto_lama;

    // Logika upload foto baru jika ada
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $target_dir = "uploads/";
        $nama_foto_baru = time() . '_' . basename($_FILES["foto"]["name"]);
        $target_file = $target_dir . $nama_foto_baru;

        if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
            // Hapus foto lama jika upload foto baru berhasil
            if (!empty($foto_lama) && file_exists($target_dir . $foto_lama)) {
                unlink($target_dir . $foto_lama);
            }
        } else {
            $pesan_error = "Gagal mengupload foto baru.";
            $nama_foto_baru = $foto_lama; // Kembalikan ke foto lama jika gagal
        }
    }

    if (empty($pesan_error)) {
        // Query UPDATE
        $query_update = "UPDATE kost SET nama_kost=?, id_pemilik=?, alamat=?, harga=?, foto=?, fasilitas=?, status=? WHERE id_kost=?";
        $stmt = mysqli_prepare($koneksi, $query_update);
        mysqli_stmt_bind_param($stmt, "sisssssi", $nama_kost, $id_pemilik, $alamat, $harga, $nama_foto_baru, $fasilitas, $status, $id_kost_update);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['pesan_sukses'] = "Data kost berhasil diperbarui.";
            header("Location: dashboard_admin.php");
            exit();
        } else {
            $pesan_error = "Gagal memperbarui data: " . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    }
}


// --- Logika untuk menampilkan form (GET) ---
if ($id_kost > 0) {
    $query_get = "SELECT * FROM kost WHERE id_kost = ?";
    $stmt_get = mysqli_prepare($koneksi, $query_get);
    mysqli_stmt_bind_param($stmt_get, "i", $id_kost);
    mysqli_stmt_execute($stmt_get);
    $result_get = mysqli_stmt_get_result($stmt_get);
    $kost_data = mysqli_fetch_assoc($result_get);
    mysqli_stmt_close($stmt_get);

    if (!$kost_data) {
        $_SESSION['pesan_error'] = "Data kost tidak ditemukan.";
        header("Location: dashboard_admin.php");
        exit();
    }
} else {
    $_SESSION['pesan_error'] = "ID kost tidak valid.";
    header("Location: dashboard_admin.php");
    exit();
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
    <title>Edit Kost - Dashboard Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; } </style>
</head>
<body class="flex flex-col min-h-screen">

    <header class="bg-white shadow-md py-4">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Edit Kost</h1>
            <a href="dashboard_admin.php" class="text-gray-600 hover:text-blue-600 font-medium">Kembali ke Dasbor</a>
        </div>
    </header>

    <main class="flex-grow">
        <div class="container mx-auto px-4 py-8">
            <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-lg">
                <?php if (!empty($pesan_error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <p><?= htmlspecialchars($pesan_error) ?></p>
                    </div>
                <?php endif; ?>
                <form action="edit_kamar.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="id_kost" value="<?= $kost_data['id_kost'] ?>">
                    <input type="hidden" name="foto_lama" value="<?= $kost_data['foto'] ?>">

                    <div><label for="nama_kost" class="block text-sm font-medium text-gray-700">Nama Kost</label><input type="text" id="nama_kost" name="nama_kost" value="<?= htmlspecialchars($kost_data['nama_kost']) ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"></div>
                    <div><label for="id_pemilik" class="block text-sm font-medium text-gray-700">Pemilik Kost</label><select id="id_pemilik" name="id_pemilik" required class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm"><option value="">-- Pilih Pemilik --</option><?php while ($pemilik = mysqli_fetch_assoc($result_pemilik)): ?><option value="<?= $pemilik['id_pengguna'] ?>" <?= ($pemilik['id_pengguna'] == $kost_data['id_pemilik']) ? 'selected' : '' ?>><?= htmlspecialchars($pemilik['nama_lengkap']) ?></option><?php endwhile; ?></select></div>
                    <div><label for="alamat" class="block text-sm font-medium text-gray-700">Alamat Lengkap</label><textarea id="alamat" name="alamat" rows="3" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"><?= htmlspecialchars($kost_data['alamat']) ?></textarea></div>
                    <div><label for="harga" class="block text-sm font-medium text-gray-700">Harga per Bulan (Rp)</label><input type="number" id="harga" name="harga" value="<?= $kost_data['harga'] ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"></div>
                    <div><label for="fasilitas" class="block text-sm font-medium text-gray-700">Fasilitas</label><textarea id="fasilitas" name="fasilitas" rows="4" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" placeholder="Contoh: AC, WiFi, Kamar Mandi Dalam"><?= htmlspecialchars($kost_data['fasilitas']) ?></textarea><p class="text-xs text-gray-500 mt-1">Pisahkan setiap fasilitas dengan koma (,).</p></div>
                    <div><label for="foto" class="block text-sm font-medium text-gray-700">Ganti Foto Kost (Opsional)</label><?php if(!empty($kost_data['foto'])): ?><img src="uploads/<?= htmlspecialchars($kost_data['foto']) ?>" alt="Foto saat ini" class="w-32 h-32 object-cover rounded-md my-2"><?php endif; ?><input type="file" id="foto" name="foto" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"></div>
                    <div><label for="status" class="block text-sm font-medium text-gray-700">Status</label><select id="status" name="status" required class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm"><option value="publish" <?= ($kost_data['status'] == 'publish') ? 'selected' : '' ?>>Publish</option><option value="draft" <?= ($kost_data['status'] == 'draft') ? 'selected' : '' ?>>Draft</option></select></div>
                    
                    <div class="flex items-center justify-end space-x-4">
                        <a href="dashboard_admin.php" class="bg-gray-200 text-gray-800 font-semibold px-4 py-2 rounded-lg hover:bg-gray-300">Batal</a>
                        <button type="submit" class="bg-blue-600 text-white font-semibold px-6 py-2 rounded-lg hover:bg-blue-700">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <footer class="bg-gray-800 text-white py-4 mt-auto">
        <div class="container mx-auto text-center"><p>&copy; <?= date("Y"); ?> Heaven Indekos. Semua Hak Dilindungi.</p></div>
    </footer>
</body>
</html>
