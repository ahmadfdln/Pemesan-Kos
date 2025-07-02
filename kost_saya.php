<?php include '../koneksi.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Kost Saya</title>
</head>
<body>
    <h2>Daftar Kost yang Anda Miliki</h2>
    <a href="tambah_kost.php">➕ Tambah Kost Baru</a><br><br>

    <table border="1">
        <tr>
            <th>No</th>
            <th>Nama Kost</th>
            <th>Alamat</th>
            <th>Status</th>
        </tr>

        <?php
        $no = 1;
        $query = mysqli_query($koneksi, "SELECT * FROM kost WHERE id_pemilik='1'");
        while ($data = mysqli_fetch_array($query)) {
            echo "<tr>
                    <td>$no</td>
                    <td>{$data['nama_kost']}</td>
                    <td>{$data['alamat']}</td>
                    <td>{$data['status']}</td>
                  </tr>";
            $no++;
        }
        ?>
    </table>
</body>
</html>
