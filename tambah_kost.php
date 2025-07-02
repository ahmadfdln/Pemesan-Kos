<?php include '../koneksi.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Tambah Kost</title>
</head>
<body>
    <h2>Form Tambah Kost Baru</h2>

    <form action="" method="POST" enctype="multipart/form-data">
        <label>Nama Kost:</label><br>
        <input type="text" name="nama_kost" required><br><br>

        <label>Alamat Kost:</label><br>
        <textarea name="alamat" required></textarea><br><br>

        <label>Harga per Bulan:</label><br>
        <input type="number" name="harga" required><br><br>

        <label>Upload Foto Kost:</label><br>
        <input type="file" name="foto" required><br><br>

        <button type="submit" name="simpan">Simpan</button>
    </form>

    <?php
    if (isset($_POST['simpan'])) {
        $nama = $_POST['nama_kost'];
        $alamat = $_POST['alamat'];
        $harga = $_POST['harga'];

        $foto = $_FILES['foto']['name'];
        $tmp = $_FILES['foto']['tmp_name'];
        move_uploaded_file($tmp, "../uploads/$foto");

        $query = mysqli_query($koneksi, "INSERT INTO kost 
            (id_pemilik, nama_kost, alamat, harga, foto, status) 
            VALUES ('1', '$nama', '$alamat', '$harga', '$foto', 'draft')");

        if ($query) {
            echo "<p>Kost berhasil ditambahkan!</p>";
        } else {
            echo "<p>Gagal menambahkan kost.</p>";
        }
    }
    ?>
</body>
</html>
