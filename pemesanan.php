<?php include '../koneksi.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Pemesanan Kost</title>
</head>
<body>
    <h2>Form Pemesanan Kost</h2>

    <form action="" method="POST">
        <label>Nama Kost:</label><br>
        <input type="text" name="nama_kost"><br><br>

        <label>Harga:</label><br>
        <input type="number" name="harga"><br><br>

        <label>Tanggal Masuk:</label><br>
        <input type="date" name="tanggal_masuk"><br><br>

        <label>Tanggal Keluar:</label><br>
        <input type="date" name="tanggal_keluar"><br><br>

        <button type="submit" name="pesan">Pesan Sekarang</button>
    </form>

    <?php
    if (isset($_POST['pesan'])) {
        $kost = $_POST['nama_kost'];
        $harga = $_POST['harga'];
        $masuk = $_POST['tanggal_masuk'];
        $keluar = $_POST['tanggal_keluar'];
        $status = "Menunggu Konfirmasi";

        $insert = mysqli_query($koneksi, "INSERT INTO pemesanan (id_penyewa, nama_kost, harga, tanggal_masuk, tanggal_keluar, status) 
                                          VALUES ('1', '$kost', '$harga', '$masuk', '$keluar', '$status')");
        echo "<p>Pemesanan berhasil dikirim!</p>";
    }
    ?>
</body>
</html>
