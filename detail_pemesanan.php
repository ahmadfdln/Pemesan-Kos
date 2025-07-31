<?php
// Memulai sesi jika belum ada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Memuat file koneksi dan autoloader vendor
include 'koneksi.php';
require_once 'vendor/autoload.php';

// Menggunakan namespace Dompdf
use Dompdf\Dompdf;
use Dompdf\Options;

// Cek apakah pengguna sudah login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

$id_pengguna = $_SESSION['user_id'];
$id_pemesanan = isset($_GET['id']) ? intval($_GET['id']) : 0;

// --- Logika untuk mengambil data detail pemesanan ---
$pemesanan = null;
if ($id_pemesanan > 0) {
    // Query untuk mengambil detail pemesanan, digabung dengan data kost dan pemilik kost
    $query = "
        SELECT 
            p.*, 
            k.alamat AS alamat_kost, 
            u.nama_lengkap AS nama_pemilik,
            penyewa.nama_lengkap AS nama_penyewa
        FROM 
            pemesanan p
        LEFT JOIN 
            kost k ON p.id_kost = k.id_kost
        LEFT JOIN 
            pengguna u ON k.id_pemilik = u.id_pengguna
        LEFT JOIN
            pengguna penyewa ON p.id_penyewa = penyewa.id_pengguna
        WHERE 
            p.id = ? AND p.id_penyewa = ?
    ";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "ii", $id_pemesanan, $id_pengguna);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $pemesanan = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

// Jika pemesanan tidak ditemukan atau bukan milik pengguna, redirect
if (!$pemesanan) {
    $_SESSION['pesan_error'] = "Detail pemesanan tidak ditemukan.";
    header("Location: riwayat_pemesanan.php");
    exit();
}


// --- Logika untuk Membuat dan Mengunduh PDF ---
if (isset($_GET['action']) && $_GET['action'] == 'download_pdf') {
    
    // Membersihkan semua output buffer yang ada untuk mencegah file PDF korup.
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // PERBAIKAN: Logika untuk memformat tanggal agar lebih aman
    // Ini akan mencegah tanggal "1970" jika data di database kosong atau salah format.
    $tanggal_pesan_formatted = 'Tidak tersedia';
    if (!empty($pemesanan['tanggal_pemesanan'])) {
        $timestamp = strtotime($pemesanan['tanggal_pemesanan']);
        // Cek apakah timestamp valid dan bukan tanggal default (epoch)
        if ($timestamp !== false && $timestamp > 0) {
            // Menampilkan tanggal dan waktu
            $tanggal_pesan_formatted = date('d F Y,', $timestamp);
        }
    }

    // Membuat konten HTML untuk PDF
    $html_content = '
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Invoice Pemesanan</title>
        <style>
            body { font-family: "Helvetica", sans-serif; font-size: 12px; color: #333; }
            .container { width: 100%; margin: 0 auto; padding: 20px; }
            .header { text-align: center; margin-bottom: 20px; border-bottom: 1px solid #ddd; padding-bottom: 10px;}
            .header h1 { margin: 0; font-size: 24px; color: #000; }
            .header p { margin: 5px 0; }
            .invoice-details { margin-bottom: 20px; }
            .invoice-details table { width: 100%; border-collapse: collapse; }
            .invoice-details th, .invoice-details td { padding: 8px; text-align: left; }
            .items-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            .items-table th, .items-table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
            .items-table th { background-color: #f2f2f2; }
            .total { text-align: right; margin-top: 20px; font-size: 16px; font-weight: bold; }
            .footer { text-align: center; margin-top: 40px; font-size: 10px; color: #777; border-top: 1px solid #ddd; padding-top: 10px;}
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>INVOICE PEMESANAN</h1>
                <p>Heaven Indekos</p>
            </div>
            <div class="invoice-details">
                <table>
                    <tr>
                        <td><strong>No. Pesanan:</strong> #' . htmlspecialchars($pemesanan['id']) . '</td>
                        <td><strong>Tanggal Pesan:</strong> ' . $tanggal_pesan_formatted . '</td>
                    </tr>
                    <tr>
                        <td><strong>Nama Penyewa:</strong> ' . htmlspecialchars($pemesanan['nama_penyewa']) . '</td>
                        <td><strong>Status:</strong> ' . htmlspecialchars($pemesanan['status']) . '</td>
                    </tr>
                </table>
            </div>

            <table class="items-table">
                <thead>
                    <tr>
                        <th>Deskripsi</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Nama Kost</td>
                        <td>' . htmlspecialchars($pemesanan['nama_kost']) . '</td>
                    </tr>
                    <tr>
                        <td>Alamat Kost</td>
                        <td>' . htmlspecialchars($pemesanan['alamat_kost'] ?? 'Alamat tidak tersedia') . '</td>
                    </tr>
                    <tr>
                        <td>Pemilik Kost</td>
                        <td>' . htmlspecialchars($pemesanan['nama_pemilik'] ?? 'Tidak diketahui') . '</td>
                    </tr>
                    <tr>
                        <td>Tanggal Masuk</td>
                        <td>' . date('d F Y', strtotime($pemesanan['tanggal_masuk'])) . '</td>
                    </tr>
                     <tr>
                        <td>Tanggal Keluar</td>
                        <td>' . date('d F Y', strtotime($pemesanan['tanggal_keluar'])) . '</td>
                    </tr>
                    <tr>
                        <td>Harga per Bulan</td>
                        <td>Rp ' . number_format($pemesanan['harga'], 0, ',', '.') . '</td>
                    </tr>
                </tbody>
            </table>

            <div class="total">
                Total Tagihan: Rp ' . number_format($pemesanan['total_harga'], 0, ',', '.') . '
            </div>

            <div class="footer">
                <p>Terima kasih telah melakukan pemesanan melalui Heaven Indekos.</p>
                <p>Ini adalah bukti pemesanan yang sah.</p>
            </div>
        </div>
    </body>
    </html>
    ';

    // Konfigurasi Dompdf
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'Helvetica');

    // Inisialisasi Dompdf
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html_content);

    // Atur ukuran kertas dan orientasi
    $dompdf->setPaper('A4', 'portrait');

    // Render HTML menjadi PDF
    $dompdf->render();

    // Hasilkan output PDF untuk diunduh oleh browser
    // Nama file: invoice-ID.pdf
    $dompdf->stream("invoice-" . $pemesanan['id'] . ".pdf", ["Attachment" => true]);
    
    // Hentikan eksekusi skrip setelah PDF diunduh
    exit();
}


// --- Logika untuk memproses upload bukti bayar ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_bukti'])) {
    $id_pemesanan_update = intval($_POST['id_pemesanan']);

    // Pastikan pemesanan ini milik pengguna yang login
    $cek_pemilik = mysqli_query($koneksi, "SELECT id FROM pemesanan WHERE id = $id_pemesanan_update AND id_penyewa = $id_pengguna");
    if (mysqli_num_rows($cek_pemilik) > 0) {
        if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] == 0) {
            $target_dir = "uploads/bukti/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            $nama_file = time() . '_' . basename($_FILES["bukti_pembayaran"]["name"]);
            $target_file = $target_dir . $nama_file;

            if (move_uploaded_file($_FILES["bukti_pembayaran"]["tmp_name"], $target_file)) {
                // Update nama file bukti pembayaran di database
                $query_update = "UPDATE pemesanan SET bukti_pembayaran = ? WHERE id = ?";
                $stmt = mysqli_prepare($koneksi, $query_update);
                mysqli_stmt_bind_param($stmt, "si", $nama_file, $id_pemesanan_update);
                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['pesan_sukses'] = "Bukti pembayaran berhasil diupload.";
                } else {
                    $_SESSION['pesan_error'] = "Gagal menyimpan data bukti pembayaran.";
                }
                mysqli_stmt_close($stmt);
            } else {
                $_SESSION['pesan_error'] = "Gagal mengupload file.";
            }
        } else {
            $_SESSION['pesan_error'] = "Tidak ada file yang dipilih atau terjadi error saat upload.";
        }
    } else {
        $_SESSION['pesan_error'] = "Anda tidak berhak mengubah pemesanan ini.";
    }
    header("Location: detail_pemesanan.php?id=" . $id_pemesanan_update);
    exit();
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pemesanan - Heaven Indekos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style> body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; } </style>
</head>
<body class="flex flex-col min-h-screen">

    <header class="bg-white shadow-md py-4">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Detail Pemesanan</h1>
            <a href="riwayat_pemesanan.php" class="text-gray-600 hover:text-blue-600 font-medium"><i class="fas fa-arrow-left mr-2"></i>Kembali ke Riwayat</a>
        </div>
    </header>

    <main class="flex-grow">
        <div class="container mx-auto px-4 py-8">
            <div class="max-w-3xl mx-auto">
                <!-- Tampilkan pesan sukses/error jika ada -->
                <?php if (isset($_SESSION['pesan_sukses'])): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert"><p><?= $_SESSION['pesan_sukses'] ?></p></div>
                    <?php unset($_SESSION['pesan_sukses']); ?>
                <?php endif; ?>
                <?php if (isset($_SESSION['pesan_error'])): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert"><p><?= $_SESSION['pesan_error'] ?></p></div>
                    <?php unset($_SESSION['pesan_error']); ?>
                <?php endif; ?>

                <div class="bg-white p-8 rounded-lg shadow-lg">
                    <div class="flex flex-wrap justify-between items-start mb-6 pb-6 border-b gap-4">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($pemesanan['nama_kost']) ?></h2>
                            <p class="text-gray-500"><?= htmlspecialchars($pemesanan['alamat_kost'] ?? 'Alamat tidak tersedia') ?></p>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="px-4 py-2 text-sm font-semibold rounded-full 
                                <?php 
                                    if ($pemesanan['status'] == 'Dikonfirmasi') echo 'bg-green-100 text-green-800';
                                    elseif ($pemesanan['status'] == 'Ditolak') echo 'bg-red-100 text-red-800';
                                    else echo 'bg-yellow-100 text-yellow-800';
                                ?>">
                                <?= htmlspecialchars($pemesanan['status']) ?>
                            </span>
                            <!-- Tombol Unduh PDF -->
                            <a href="detail_pemesanan.php?id=<?= $id_pemesanan ?>&action=download_pdf" class="bg-blue-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200 flex items-center">
                                <i class="fas fa-download mr-2"></i>Unduh PDF
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                        <div>
                            <h3 class="font-semibold text-gray-500 uppercase tracking-wider mb-2">Detail Sewa</h3>
                            <div class="space-y-2">
                                <p><strong class="font-medium text-gray-800">Tanggal Masuk:</strong> <?= date('d F Y', strtotime($pemesanan['tanggal_masuk'])) ?></p>
                                <p><strong class="font-medium text-gray-800">Tanggal Keluar:</strong> <?= date('d F Y', strtotime($pemesanan['tanggal_keluar'])) ?></p>
                                <p><strong class="font-medium text-gray-800">Pemilik Kost:</strong> <?= htmlspecialchars($pemesanan['nama_pemilik'] ?? 'Tidak diketahui') ?></p>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-500 uppercase tracking-wider mb-2">Detail Pembayaran</h3>
                            <div class="space-y-2">
                                <p><strong class="font-medium text-gray-800">Harga per Bulan:</strong> Rp <?= number_format($pemesanan['harga'], 0, ',', '.') ?></p>
                                <p><strong class="font-medium text-gray-800">Total Tagihan:</strong> <span class="text-blue-600 font-bold">Rp <?= number_format($pemesanan['total_harga'], 0, ',', '.') ?></span></p>
                                <p><strong class="font-medium text-gray-800">Metode Pembayaran:</strong> <?= htmlspecialchars($pemesanan['metode_pembayaran']) ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Bagian Upload Bukti Pembayaran -->
                    <div class="mt-8 border-t pt-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Bukti Pembayaran</h3>
                        <?php if (!empty($pemesanan['bukti_pembayaran'])): ?>
                            <div class="flex items-center space-x-4">
                                <img src="uploads/bukti/<?= htmlspecialchars($pemesanan['bukti_pembayaran']) ?>" alt="Bukti Pembayaran" class="w-40 h-40 object-cover rounded-md">
                                <a href="uploads/bukti/<?= htmlspecialchars($pemesanan['bukti_pembayaran']) ?>" target="_blank" class="text-blue-600 hover:underline">Lihat Gambar Penuh</a>
                            </div>
                        <?php else: ?>
                            <?php if ($pemesanan['status'] !== 'Ditolak'): ?>
                                <p class="text-gray-600 mb-4">Silakan upload bukti pembayaran Anda. Pastikan gambar jelas dan mudah dibaca.</p>
                                <form action="detail_pemesanan.php?id=<?= $id_pemesanan ?>" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="id_pemesanan" value="<?= $id_pemesanan ?>">
                                    <input type="hidden" name="upload_bukti" value="1">
                                    <div class="flex items-center space-x-4">
                                        <input type="file" name="bukti_pembayaran" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                        <button type="submit" class="bg-blue-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-blue-700 whitespace-nowrap">Upload Bukti</button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <p class="text-red-600">Pemesanan ini telah ditolak. Tidak perlu mengupload bukti pembayaran.</p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-gray-800 text-white py-4 mt-auto">
        <div class="container mx-auto text-center"><p>&copy; <?= date("Y"); ?> Heaven Indekos. Semua Hak Dilindungi.</p></div>
    </footer>
</body>
</html>
