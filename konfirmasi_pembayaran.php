<?php include '../koneksi.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Konfirmasi Pembayaran</title>
</head>
<body>
    <h2>Konfirmasi Pembayaran Penyewa</h2>

    <table border="1">
        <tr>
            <th>No</th>
            <th>Nama Kost</th>
            <th>Nama Penyewa</th>
            <th>Bukti Pembayaran</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>

        <?php
        $no = 1;
        $query = mysqli_query($koneksi, "SELECT * FROM pemesanan");
        while ($data = mysqli_fetch_array($query)) {
            echo "<tr>
                    <td>$no</td>
                    <td>{$data['nama_kost']}</td>
                    <td>{$data['id_penyewa']}</td>
                    <td><a href='../uploads/{$data['bukti_pembayaran']}' target='_blank'>Lihat</a></td>
                    <td>{$data['status']}</td>
                    <td>
                        <a href='konfirmasi_pembayaran.php?konfirmasi={$data['id']}'>Konfirmasi</a>
                    </td>
                  </tr>";
            $no++;
        }

        if (isset($_GET['konfirmasi'])) {
            $id = $_GET['konfirmasi'];
            mysqli_query($koneksi, "UPDATE pemesanan SET status='Terkonfirmasi' WHERE id='$id'");
            header("Location: konfirmasi_pembayaran.php");
        }
        ?>
    </table>
</body>
</html>
