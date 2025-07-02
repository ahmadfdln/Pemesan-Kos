<?php include '../koneksi.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Invoice Pemesanan</title>
</head>
<body>
    <h2>Invoice Pemesanan Anda</h2>

    <?php
    $query = mysqli_query($koneksi, "SELECT * FROM pemesanan WHERE id_penyewa='1' ORDER BY id DESC LIMIT 1");
    $data = mysqli_fetch_array($query);
    ?>

    <p>Nama Kost: <strong><?= $data['nama_kost']; ?></strong></p>
    <p>Harga: <strong>Rp<?= number_format($data['harga']); ?></strong></p>
    <p>Tanggal Masuk: <?= $data['tanggal_masuk']; ?></p>
    <p>Tanggal Keluar: <?= $data['tanggal_keluar']; ?></p>
    <p>Status: <strong><?= $data['status']; ?></strong></p>

    <br>
    <a href="cetak_invoice.php?id=<?= $data['id']; ?>" target="_blank">Unduh Invoice</a>
</body>
</html>
