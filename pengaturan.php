<?php include '../koneksi.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Pengaturan Akun</title>
</head>
<body>
    <h2>Pengaturan Akun</h2>

    <form action="" method="POST">
        <label>Ubah ke akun bisnis:</label>
        <select name="tipe_akun">
            <option value="penyewa">Penyewa</option>
            <option value="pemilik">Pemilik (Akun Bisnis)</option>
        </select>
        <br><br>
        <button type="submit" name="ubah">Simpan Perubahan</button>
    </form>

    <?php
    if (isset($_POST['ubah'])) {
        $tipe = $_POST['tipe_akun'];
        $query = mysqli_query($koneksi, "UPDATE pengguna SET tipe_akun='$tipe' WHERE id_pengguna='1'");
        echo "<p>Akun berhasil diubah menjadi <strong>$tipe</strong>.</p>";
    }
    ?>
</body>
</html>
