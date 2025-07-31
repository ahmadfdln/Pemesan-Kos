<?php
// edit_pengguna.php

include 'koneksi.php';
session_start();

// 1. Cek otorisasi, hanya admin yang boleh mengakses
if (!isset($_SESSION['loggedin']) || $_SESSION['tipe_akun'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$pesan_error = '';
$user_data = null;
$id_pengguna = isset($_GET['id']) ? intval($_GET['id']) : 0;

// --- Logika untuk memproses form saat disubmit (UPDATE) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $id_pengguna_update = intval($_POST['id_pengguna']);
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $nama_pengguna = mysqli_real_escape_string($koneksi, $_POST['nama_pengguna']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $nomor_telepon = mysqli_real_escape_string($koneksi, $_POST['nomor_telepon']);
    $tipe_akun = mysqli_real_escape_string($koneksi, $_POST['tipe_akun']);
    $password_baru = $_POST['password']; // Tidak di-escape dulu

    // Siapkan query update
    $query_update = "UPDATE pengguna SET nama_lengkap=?, nama_pengguna=?, email=?, nomor_telepon=?, tipe_akun=? ";
    $params = [$nama_lengkap, $nama_pengguna, $email, $nomor_telepon, $tipe_akun];
    $types = "sssss";

    // Jika password baru diisi, tambahkan ke query update
    if (!empty($password_baru)) {
        $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);
        $query_update .= ", kata_sandi=? ";
        $params[] = $hashed_password;
        $types .= "s";
    }

    $query_update .= "WHERE id_pengguna=?";
    $params[] = $id_pengguna_update;
    $types .= "i";

    $stmt = mysqli_prepare($koneksi, $query_update);
    mysqli_stmt_bind_param($stmt, $types, ...$params);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['pesan_sukses'] = "Data pengguna berhasil diperbarui.";
        header("Location: dashboard_admin.php");
        exit();
    } else {
        $pesan_error = "Gagal memperbarui data: " . mysqli_stmt_error($stmt);
    }
    mysqli_stmt_close($stmt);
}


// --- Logika untuk menampilkan form (GET) ---
if ($id_pengguna > 0) {
    $query_get = "SELECT * FROM pengguna WHERE id_pengguna = ?";
    $stmt_get = mysqli_prepare($koneksi, $query_get);
    mysqli_stmt_bind_param($stmt_get, "i", $id_pengguna);
    mysqli_stmt_execute($stmt_get);
    $result_get = mysqli_stmt_get_result($stmt_get);
    $user_data = mysqli_fetch_assoc($result_get);
    mysqli_stmt_close($stmt_get);

    if (!$user_data) {
        $_SESSION['pesan_error'] = "Data pengguna tidak ditemukan.";
        header("Location: dashboard_admin.php");
        exit();
    }
} else {
    $_SESSION['pesan_error'] = "ID pengguna tidak valid.";
    header("Location: dashboard_admin.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pengguna - Dashboard Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; } </style>
</head>
<body class="flex flex-col min-h-screen">

    <header class="bg-white shadow-md py-4">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Edit Pengguna</h1>
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
                <form action="edit_pengguna.php?id=<?= $id_pengguna ?>" method="POST" class="space-y-6">
                    <input type="hidden" name="id_pengguna" value="<?= $user_data['id_pengguna'] ?>">

                    <div><label for="nama_lengkap" class="block text-sm font-medium text-gray-700">Nama Lengkap</label><input type="text" id="nama_lengkap" name="nama_lengkap" value="<?= htmlspecialchars($user_data['nama_lengkap']) ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"></div>
                    <div><label for="nama_pengguna" class="block text-sm font-medium text-gray-700">Username</label><input type="text" id="nama_pengguna" name="nama_pengguna" value="<?= htmlspecialchars($user_data['nama_pengguna']) ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"></div>
                    <div><label for="email" class="block text-sm font-medium text-gray-700">Email</label><input type="email" id="email" name="email" value="<?= htmlspecialchars($user_data['email']) ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"></div>
                    <div><label for="nomor_telepon" class="block text-sm font-medium text-gray-700">Nomor Telepon</label><input type="text" id="nomor_telepon" name="nomor_telepon" value="<?= htmlspecialchars($user_data['nomor_telepon']) ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"></div>
                    
                    <div>
                        <label for="tipe_akun" class="block text-sm font-medium text-gray-700">Tipe Akun</label>
                        <select id="tipe_akun" name="tipe_akun" required class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm">
                            <option value="admin" <?= ($user_data['tipe_akun'] == 'admin') ? 'selected' : '' ?>>Admin</option>
                            <option value="pemilik" <?= ($user_data['tipe_akun'] == 'pemilik') ? 'selected' : '' ?>>Pemilik</option>
                            <option value="penyewa" <?= ($user_data['tipe_akun'] == 'penyewa') ? 'selected' : '' ?>>Penyewa</option>
                        </select>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password Baru (Opsional)</label>
                        <input type="password" id="password" name="password" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                        <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah password.</p>
                    </div>
                    
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
