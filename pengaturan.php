<?php
// pengaturan.php

// Mulai session jika belum ada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'koneksi.php';

// Cek apakah pengguna sudah login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

$id_pengguna = $_SESSION['user_id'];

// --- Logika untuk memproses form saat disubmit ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Logika untuk mengubah Tipe Akun
    if (isset($_POST['update_tipe_akun'])) {
        $tipe_akun_baru = $_POST['tipe_akun'] ?? '';
        if (in_array($tipe_akun_baru, ['pemilik', 'penyewa'])) {
            $query_update = "UPDATE pengguna SET tipe_akun = ? WHERE id_pengguna = ?";
            $stmt = mysqli_prepare($koneksi, $query_update);
            mysqli_stmt_bind_param($stmt, "si", $tipe_akun_baru, $id_pengguna);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['tipe_akun'] = $tipe_akun_baru;
                $_SESSION['pesan_sukses'] = "Tipe akun berhasil diperbarui! Silakan logout dan login kembali untuk melihat perubahan menu.";
            } else {
                $_SESSION['pesan_error'] = "Gagal memperbarui tipe akun.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $_SESSION['pesan_error'] = "Tipe akun tidak valid.";
        }
    }

    // Logika untuk mengubah Profil Pengguna
    if (isset($_POST['update_profil'])) {
        $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
        $username = mysqli_real_escape_string($koneksi, $_POST['nama_pengguna']);
        $email = mysqli_real_escape_string($koneksi, $_POST['email']);
        $nomor_telepon = mysqli_real_escape_string($koneksi, $_POST['nomor_telepon']);

        $query_update = "UPDATE pengguna SET nama_lengkap = ?, nama_pengguna = ?, email = ?, nomor_telepon = ? WHERE id_pengguna = ?";
        $stmt = mysqli_prepare($koneksi, $query_update);
        mysqli_stmt_bind_param($stmt, "ssssi", $nama_lengkap, $username, $email, $nomor_telepon, $id_pengguna);
        if (mysqli_stmt_execute($stmt)) {
            // Perbarui juga data di session
            $_SESSION['full_name'] = $nama_lengkap;
            $_SESSION['username'] = $username;
            $_SESSION['pesan_sukses'] = "Profil berhasil diperbarui.";
        } else {
            $_SESSION['pesan_error'] = "Gagal memperbarui profil.";
        }
        mysqli_stmt_close($stmt);
    }

    // Logika untuk mengubah Password
    if (isset($_POST['update_password'])) {
        $password_baru = $_POST['password_baru'];
        $konfirmasi_password = $_POST['konfirmasi_password'];

        if (!empty($password_baru)) {
            if ($password_baru === $konfirmasi_password) {
                $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);
                $query_update = "UPDATE pengguna SET kata_sandi = ? WHERE id_pengguna = ?";
                $stmt = mysqli_prepare($koneksi, $query_update);
                mysqli_stmt_bind_param($stmt, "si", $hashed_password, $id_pengguna);
                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['pesan_sukses'] = "Password berhasil diperbarui.";
                } else {
                    $_SESSION['pesan_error'] = "Gagal memperbarui password.";
                }
                mysqli_stmt_close($stmt);
            } else {
                $_SESSION['pesan_error'] = "Konfirmasi password tidak cocok.";
            }
        } else {
            $_SESSION['pesan_error'] = "Password baru tidak boleh kosong.";
        }
    }

    // Redirect kembali ke halaman pengaturan untuk menampilkan pesan
    header("Location: pengaturan.php");
    exit();
}

// Ambil data pengguna saat ini untuk ditampilkan di form
$query_get_user = "SELECT * FROM pengguna WHERE id_pengguna = ?";
$stmt_get = mysqli_prepare($koneksi, $query_get_user);
mysqli_stmt_bind_param($stmt_get, "i", $id_pengguna);
mysqli_stmt_execute($stmt_get);
$result_user = mysqli_stmt_get_result($stmt_get);
$user = mysqli_fetch_assoc($result_user);
mysqli_stmt_close($stmt_get);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Akun - Heaven Indekos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style> body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; } </style>
</head>
<body class="flex flex-col min-h-screen">

    <header class="bg-white shadow-md py-4">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Pengaturan Akun</h1>
            <a href="home_penyewa.php" class="text-gray-600 hover:text-blue-600 font-medium"><i class="fas fa-arrow-left mr-2"></i>Kembali ke Beranda</a>
        </div>
    </header>

    <main class="flex-grow">
        <div class="container mx-auto px-4 py-8">
            <div class="max-w-xl mx-auto space-y-8">
                
                <!-- Tampilkan pesan sukses/error jika ada -->
                <?php if (isset($_SESSION['pesan_sukses'])): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
                        <p><?= $_SESSION['pesan_sukses'] ?></p>
                    </div>
                    <?php unset($_SESSION['pesan_sukses']); ?>
                <?php endif; ?>
                <?php if (isset($_SESSION['pesan_error'])): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                        <p><?= $_SESSION['pesan_error'] ?></p>
                    </div>
                    <?php unset($_SESSION['pesan_error']); ?>
                <?php endif; ?>

                <!-- Form Profil Pengguna -->
                <div class="bg-white p-8 rounded-lg shadow-lg">
                    <h2 class="text-xl font-bold text-gray-800 mb-6">Profil Pengguna</h2>
                    <form action="pengaturan.php" method="POST" class="space-y-6">
                        <input type="hidden" name="update_profil" value="1">
                        <div><label for="nama_lengkap" class="block text-sm font-medium text-gray-700">Nama Lengkap</label><input type="text" id="nama_lengkap" name="nama_lengkap" value="<?= htmlspecialchars($user['nama_lengkap']) ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"></div>
                        <div><label for="nama_pengguna" class="block text-sm font-medium text-gray-700">Username</label><input type="text" id="nama_pengguna" name="nama_pengguna" value="<?= htmlspecialchars($user['nama_pengguna']) ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"></div>
                        <div><label for="email" class="block text-sm font-medium text-gray-700">Email</label><input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"></div>
                        <div><label for="nomor_telepon" class="block text-sm font-medium text-gray-700">Nomor Telepon</label><input type="text" id="nomor_telepon" name="nomor_telepon" value="<?= htmlspecialchars($user['nomor_telepon']) ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"></div>
                        <div class="flex items-center justify-end"><button type="submit" class="bg-blue-600 text-white font-semibold px-6 py-2 rounded-lg hover:bg-blue-700">Simpan Profil</button></div>
                    </form>
                </div>

                <!-- Form Ubah Password -->
                <div class="bg-white p-8 rounded-lg shadow-lg">
                    <h2 class="text-xl font-bold text-gray-800 mb-6">Ubah Password</h2>
                    <form action="pengaturan.php" method="POST" class="space-y-6">
                        <input type="hidden" name="update_password" value="1">
                        <div><label for="password_baru" class="block text-sm font-medium text-gray-700">Password Baru</label><input type="password" id="password_baru" name="password_baru" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"></div>
                        <div><label for="konfirmasi_password" class="block text-sm font-medium text-gray-700">Konfirmasi Password Baru</label><input type="password" id="konfirmasi_password" name="konfirmasi_password" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"></div>
                        <div class="flex items-center justify-end"><button type="submit" class="bg-blue-600 text-white font-semibold px-6 py-2 rounded-lg hover:bg-blue-700">Ubah Password</button></div>
                    </form>
                </div>

                <!-- Form Ubah Tipe Akun -->
                <div class="bg-white p-8 rounded-lg shadow-lg">
                    <h2 class="text-xl font-bold text-gray-800 mb-6">Ubah Tipe Akun</h2>
                    <form action="pengaturan.php" method="POST" class="space-y-6">
                        <input type="hidden" name="update_tipe_akun" value="1">
                        <div>
                            <label for="tipe_akun" class="block text-sm font-medium text-gray-700 mb-2">Ubah ke akun bisnis:</label>
                            <select id="tipe_akun" name="tipe_akun" class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="penyewa" <?= ($user['tipe_akun'] == 'penyewa') ? 'selected' : '' ?>>Penyewa</option>
                                <option value="pemilik" <?= ($user['tipe_akun'] == 'pemilik') ? 'selected' : '' ?>>Pemilik (Akun Bisnis)</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-2">Dengan menjadi pemilik, Anda dapat menambahkan dan mengelola data kost Anda sendiri.</p>
                        </div>
                        <div class="flex items-center justify-end"><button type="submit" class="bg-blue-600 text-white font-semibold px-6 py-2 rounded-lg hover:bg-blue-700">Simpan Tipe Akun</button></div>
                    </form>
                </div>

            </div>
        </div>
    </main>

    <footer class="bg-gray-800 text-white py-4 mt-auto">
        <div class="container mx-auto text-center"><p>&copy; <?= date("Y"); ?> Heaven Indekos. Semua Hak Dilindungi.</p></div>
    </footer>
</body>
</html>
