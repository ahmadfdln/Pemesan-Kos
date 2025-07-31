<?php
// proses_tambah_kost.php (Diperbarui dengan multi-upload)
include 'session_handler.php';
include 'koneksi.php';

// Cek otorisasi admin
if (!isset($_SESSION['loggedin']) || ($_SESSION['tipe_akun'] ?? null) !== 'admin') {
    header("Location: login.php");
    exit();
}

// Pastikan request adalah POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Ambil data dari form
    $nama_kost = $_POST['nama_kost'] ?? '';
    $id_pemilik = $_POST['id_pemilik'] ?? 0;
    $alamat = $_POST['alamat'] ?? '';
    $harga = $_POST['harga'] ?? 0;
    $no_hp = $_POST['no_hp'] ?? '';
    $fasilitas = $_POST['fasilitas'] ?? '';
    $status = $_POST['status'] ?? 'draft';

    // 2. Validasi dasar
    if (empty($nama_kost) || empty($id_pemilik) || empty($alamat) || empty($harga)) {
        $_SESSION['flash_message'] = "Error: Nama Kost, Pemilik, Alamat, dan Harga wajib diisi.";
        header("Location: tambah_kost.php");
        exit();
    }

    // 3. Proses upload multi-gambar
    $uploaded_files = [];
    if (isset($_FILES['foto']['name']) && is_array($_FILES['foto']['name'])) {
        $target_dir = "uploads/"; // Pastikan folder ini ada dan writable
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $file_count = count($_FILES['foto']['name']);
        for ($i = 0; $i < $file_count; $i++) {
            // Hanya proses file jika tidak ada error dan ada nama file
            if ($_FILES['foto']['error'][$i] === UPLOAD_ERR_OK && !empty($_FILES['foto']['name'][$i])) {
                $nama_file_asli = basename($_FILES["foto"]["name"][$i]);
                $nama_file_unik = time() . '_' . uniqid() . '_' . $nama_file_asli;
                $target_file = $target_dir . $nama_file_unik;

                if (move_uploaded_file($_FILES["foto"]["tmp_name"][$i], $target_file)) {
                    $uploaded_files[] = $nama_file_unik;
                } else {
                    $_SESSION['flash_message'] = "Error: Gagal mengupload file: " . htmlspecialchars($nama_file_asli);
                    header("Location: tambah_kost.php");
                    exit();
                }
            }
        }
    }

    if (empty($uploaded_files)) {
        $_SESSION['flash_message'] = "Error: Anda wajib mengupload setidaknya satu foto.";
        header("Location: tambah_kost.php");
        exit();
    }
    
    // Gabungkan nama file menjadi satu string, dipisahkan koma
    $nama_foto_string = implode(',', $uploaded_files);

    // 4. Simpan ke database menggunakan prepared statement
    $query = "INSERT INTO kost (nama_kost, id_pemilik, alamat, harga, no_hp, fasilitas, foto, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($koneksi, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sissssss", $nama_kost, $id_pemilik, $alamat, $harga, $no_hp, $fasilitas, $nama_foto_string, $status);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['flash_message'] = "Data kost baru berhasil ditambahkan.";
            header("Location: dashboard_admin.php?page=kost");
            exit();
        } else {
            $_SESSION['flash_message'] = "Error: Gagal menyimpan data ke database. " . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['flash_message'] = "Error: Gagal mempersiapkan query. " . mysqli_error($koneksi);
    }
    
    // Jika gagal, redirect kembali ke form tambah
    header("Location: tambah_kost.php");
    exit();

} else {
    // Jika bukan POST, redirect
    header("Location: dashboard_admin.php?page=kost");
    exit();
}
?>
