
CREATE DATABASE IF NOT EXISTS db_kos;
USE db_kos;

-- Tabel pengguna
CREATE TABLE pengguna (
    id_pengguna INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(225),
    nama_pengguna VARCHAR(30),
    kata_sandi VARCHAR(225),
    tipe_akun ENUM('penyewa','pemilik','admin') DEFAULT 'penyewa',
    nama_lengkap VARCHAR(225),
    alamat VARCHAR(225),
    nik VARCHAR(225),
    agama VARCHAR(225),
    jenis_kelamin VARCHAR(225),
    nomor_telepon VARCHAR(30),
    nomor_whatsapp VARCHAR(30),
    poto_profil VARCHAR(225),
    aktif TINYINT(1) DEFAULT 1,
    wajib_ganti_kata_sandi TINYINT(1) DEFAULT 0,
    status_akun VARCHAR(225),
    pesan_status VARCHAR(225),
    kode_reset VARCHAR(225),
    waktu_reset DATETIME,
    batas_waktu_reset DATETIME,
    kode_aktivitas VARCHAR(225),
    dibuat_pada DATETIME DEFAULT CURRENT_TIMESTAMP,
    diperbaharui_pada DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    dihapus_pada DATETIME DEFAULT NULL
);

-- Tabel kost
CREATE TABLE kost (
    id_kost INT AUTO_INCREMENT PRIMARY KEY,
    id_pemilik INT,
    nama_kost VARCHAR(100),
    alamat TEXT,
    harga INT,
    foto VARCHAR(225),
    status ENUM('draft', 'publish') DEFAULT 'draft',
    FOREIGN KEY (id_pemilik) REFERENCES pengguna(id_pengguna)
);

-- Tabel pemesanan
CREATE TABLE pemesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_penyewa INT,
    nama_kost VARCHAR(100),
    harga INT,
    tanggal_masuk DATE,
    tanggal_keluar DATE,
    status VARCHAR(100) DEFAULT 'Menunggu Konfirmasi',
    bukti_pembayaran VARCHAR(225),
    FOREIGN KEY (id_penyewa) REFERENCES pengguna(id_pengguna)
);

-- Tabel penilaian
CREATE TABLE penilaian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_penyewa INT,
    id_kost INT,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    komentar TEXT,
    tanggal DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_penyewa) REFERENCES pengguna(id_pengguna),
    FOREIGN KEY (id_kost) REFERENCES kost(id_kost)
);

-- Data dummy pengguna
INSERT INTO pengguna (email, nama_pengguna, kata_sandi, tipe_akun, nama_lengkap) VALUES
('admin@example.com', 'admin', 'admin123', 'admin', 'Admin Utama'),
('pemilik@example.com', 'pemilik1', 'pemilik123', 'pemilik', 'Budi Pemilik Kost'),
('penyewa@example.com', 'penyewa1', 'penyewa123', 'penyewa', 'Ani Penyewa');

-- Data dummy kost
INSERT INTO kost (id_pemilik, nama_kost, alamat, harga, foto, status) VALUES
(2, 'Kost Mawar', 'Jl. Melati No.1', 750000, 'mawar.jpg', 'publish'),
(2, 'Kost Anggrek', 'Jl. Kenanga No.2', 850000, 'anggrek.jpg', 'draft');

-- Data dummy pemesanan
INSERT INTO pemesanan (id_penyewa, nama_kost, harga, tanggal_masuk, tanggal_keluar, status, bukti_pembayaran) VALUES
(3, 'Kost Mawar', 750000, '2025-07-01', '2025-08-01', 'Menunggu Konfirmasi', 'bukti1.jpg');

-- Data dummy penilaian
INSERT INTO penilaian (id_penyewa, id_kost, rating, komentar) VALUES
(3, 1, 5, 'Kost nyaman dan bersih.');
