<?php include '../koneksi.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Data Kost</title>
</head>
<body>
    <h2>Data Kost Pemilik</h2>

    <table border="1">
        <tr>
            <th>No</th>
            <th>Nama Kost</th>
            <th>Alamat</th>
            <th>Harga</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>

        <?php
        $no = 1;
        $query = mysqli_query($koneksi, "SELECT * FROM kost");
        while ($data = mysqli_fetch_array($query)) {
            echo "<tr>
                    <td>$no</td>
                    <td>{$data['nama_kost']}</td>
                    <td>{$data['alamat']}</td>
                    <td>Rp" . number_format($data['harga']) . "</td>
                    <td>{$data['status']}</td>
                    <td>
                        <a href='data_kost.php?publish={$data['id_kost']}'>Publish</a> | 
                        <a href='data_kost.php?draft={$data['id_kost']}'>Draft</a>
                    </td>
                  </tr>";
            $no++;
        }

        if (isset($_GET['publish'])) {
            $id = $_GET['publish'];
            mysqli_query($koneksi, "UPDATE kost SET status='publish' WHERE id_kost='$id'");
            header("Location: data_kost.php");
        }

        if (isset($_GET['draft'])) {
            $id = $_GET['draft'];
            mysqli_query($koneksi, "UPDATE kost SET status='draft' WHERE id_kost='$id'");
            header("Location: data_kost.php");
        }
        ?>
    </table>
</body>
</html>
