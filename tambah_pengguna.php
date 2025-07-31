<?php
// tambah_pengguna.php

include 'koneksi.php';
session_start();

// 1. Cek otorisasi, hanya admin yang boleh mengakses
if (!isset($_SESSION['loggedin']) || $_SESSION['tipe_akun'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$pesan_error = '';

// --- Logika untuk memproses form saat disubmit ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $nama_pengguna = mysqli_real_escape_string($koneksi, $_POST['nama_pengguna']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = $_POST['password']; // Tidak di-escape karena akan di-hash
    $tipe_akun = mysqli_real_escape_string($koneksi, $_POST['tipe_akun']);
    $nomor_telepon = mysqli_real_escape_string($koneksi, $_POST['nomor_telepon']);

    // Validasi dasar
    if (empty($nama_lengkap) || empty($nama_pengguna) || empty($password) || empty($tipe_akun)) {
        $pesan_error = "Nama Lengkap, Username, Password, dan Tipe Akun wajib diisi.";
    } else {
        // Cek apakah username sudah ada
        $query_cek = "SELECT id_pengguna FROM pengguna WHERE nama_pengguna = ?";
        $stmt_cek = mysqli_prepare($koneksi, $query_cek);
        mysqli_stmt_bind_param($stmt_cek, "s", $nama_pengguna);
        mysqli_stmt_execute($stmt_cek);
        mysqli_stmt_store_result($stmt_cek);

        if (mysqli_stmt_num_rows($stmt_cek) > 0) {
            $pesan_error = "Username sudah digunakan. Silakan pilih username lain.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Query INSERT
            $query_insert = "INSERT INTO pengguna (nama_lengkap, nama_pengguna, email, kata_sandi, tipe_akun, nomor_telepon) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_insert = mysqli_prepare($koneksi, $query_insert);
            mysqli_stmt_bind_param($stmt_insert, "ssssss", $nama_lengkap, $nama_pengguna, $email, $hashed_password, $tipe_akun, $nomor_telepon);

            if (mysqli_stmt_execute($stmt_insert)) {
                $_SESSION['pesan_sukses'] = "Pengguna baru berhasil ditambahkan.";
                header("Location: dashboard_admin.php");
                exit();
            } else {
                $pesan_error = "Gagal menambahkan pengguna: " . mysqli_stmt_error($stmt_insert);
            }
            mysqli_stmt_close($stmt_insert);
        }
        mysqli_stmt_close($stmt_cek);
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pengguna - Dashboard Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; } </style>
</head>
<body class="flex flex-col min-h-screen">

    <header class="bg-white shadow-md py-4">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Tambah Pengguna Baru</h1>
            <a href="dashboard_admin.php" class="text-gray-600 hover:text-blue-600 font-medium">Kembali ke Dasbor</a>
        </div>
    </header>

    <main class="flex-grow">
        <div class="container mx-auto px-4 py-8">
            <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-lg">
                <?php if (!empty($pesan_error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <p><?= htmlspecialchars($pesan_error) ?></p>
                    </div>
                <?php endif; ?>
                <form action="tambah_pengguna.php" method="POST" class="space-y-6">
                    <div><label for="nama_lengkap" class="block text-sm font-medium text-gray-700">Nama Lengkap</label><input type="text" id="nama_lengkap" name="nama_lengkap" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"></div>
                    <div><label for="nama_pengguna" class="block text-sm font-medium text-gray-700">Username</label><input type="text" id="nama_pengguna" name="nama_pengguna" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"></div>
                    <div><label for="email" class="block text-sm font-medium text-gray-700">Email</label><input type="email" id="email" name="email" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"></div>
                    <div><label for="password" class="block text-sm font-medium text-gray-700">Password</label><input type="password" id="password" name="password" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"></div>
                    <div><label for="nomor_telepon" class="block text-sm font-medium text-gray-700">Nomor Telepon</label><input type="text" id="nomor_telepon" name="nomor_telepon" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"></div>
                    <div>
                        <label for="tipe_akun" class="block text-sm font-medium text-gray-700">Tipe Akun</label>
                        <select id="tipe_akun" name="tipe_akun" required class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm">
                            <option value="penyewa">Penyewa</option>
                            <option value="pemilik">Pemilik</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    
                    <div class="flex items-center justify-end space-x-4">
                        <a href="dashboard_admin.php" class="bg-gray-200 text-gray-800 font-semibold px-4 py-2 rounded-lg hover:bg-gray-300">Batal</a>
                        <button type="submit" class="bg-blue-600 text-white font-semibold px-6 py-2 rounded-lg hover:bg-blue-700">Tambah Pengguna</button>
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
