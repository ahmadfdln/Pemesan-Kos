<?php
include 'koneksi.php';
session_start();

// Pastikan pengguna sudah login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit();
}

$id_penyewa = $_SESSION['username']; // Sesuaikan jika kamu pakai id_user
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Pemesanan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
    <div class="max-w-6xl mx-auto mt-10 p-6 bg-white rounded shadow">
        <h2 class="text-2xl font-bold mb-6 text-center text-blue-700">Riwayat Pemesanan Anda</h2>

        <table class="min-w-full border border-gray-300 text-sm text-left">
            <thead class="bg-blue-600 text-white">
                <tr>
                    <th class="px-4 py-2">No</th>
                    <th class="px-4 py-2">Nama Kost</th>
                    <th class="px-4 py-2">Tanggal Masuk</th>
                    <th class="px-4 py-2">Tanggal Keluar</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Metode Pembayaran</th>
                    <th class="px-4 py-2">Bukti Pembayaran</th>
                </tr>
            </thead>
            <tbody class="bg-white text-gray-700">
                <?php
                $no = 1;
                $query = mysqli_query($koneksi, "SELECT * FROM pemesanan WHERE id_penyewa='$id_penyewa'");
                while ($data = mysqli_fetch_array($query)) {
                    echo "<tr class='border-t border-gray-200'>
                            <td class='px-4 py-2'>$no</td>
                            <td class='px-4 py-2'>{$data['nama_kost']}</td>
                            <td class='px-4 py-2'>{$data['tanggal_masuk']}</td>
                            <td class='px-4 py-2'>{$data['tanggal_keluar']}</td>
                            <td class='px-4 py-2'>{$data['status']}</td>
                            <td class='px-4 py-2'>{$data['metode_pembayaran']}</td>
                            <td class='px-4 py-2'>";
                    if (!empty($data['bukti_pembayaran'])) {
                        echo "<a href='../uploads/{$data['bukti_pembayaran']}' target='_blank' class='text-blue-600 hover:underline'>Lihat Bukti</a>";
                    } else {
                        echo "<span class='text-red-500 italic'>Belum diupload</span>";
                    }
                    echo "</td></tr>";
                    $no++;
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
