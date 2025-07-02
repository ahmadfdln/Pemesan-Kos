<?php include '../koneksi.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Data Akun Admin</title>
</head>
<body>
    <h2>Daftar Akun Admin</h2>

    <form method="POST">
        <input type="text" name="nama_admin" placeholder="Nama Admin" required>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Kata Sandi" required>
        <button type="submit" name="tambah">Tambah Admin</button>
    </form>

    <hr>

    <table border="1">
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Username</th>
            <th>Aksi</th>
        </tr>

        <?php
        $no = 1;
        $data = mysqli_query($koneksi, "SELECT * FROM pengguna WHERE tipe_akun='admin'");
        while ($d = mysqli_fetch_array($data)) {
            echo "<tr>
                    <td>$no</td>
                    <td>{$d['nama_lengkap']}</td>
                    <td>{$d['nama_pengguna']}</td>
                    <td><a href='data_admin.php?hapus={$d['id_pengguna']}'>Hapus</a></td>
                  </tr>";
            $no++;
        }

        if (isset($_POST['tambah'])) {
            $nama = $_POST['nama_admin'];
            $username = $_POST['username'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

            mysqli_query($koneksi, "INSERT INTO pengguna (nama_lengkap, nama_pengguna, kata_sandi, tipe_akun) 
                                    VALUES ('$nama', '$username', '$password', 'admin')");
            header("Location: data_admin.php");
        }

        if (isset($_GET['hapus'])) {
            $id = $_GET['hapus'];
            mysqli_query($koneksi, "DELETE FROM pengguna WHERE id_pengguna='$id'");
            header("Location: data_admin.php");
        }
        ?>
    </table>
</body>
</html>
