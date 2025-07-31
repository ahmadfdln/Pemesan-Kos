<?php
/**
 * session_handler.php
 * * CATATAN PENTING TENTANG KEAMANAN:
 * Skrip ini dirancang untuk membuat sesi pengguna bertahan untuk waktu yang sangat lama (10 tahun).
 * PENDEKATAN INI MEMILIKI RISIKO KEAMANAN YANG SIGNIFIKAN.
 * Jika session ID pengguna dicuri (misalnya melalui serangan XSS atau Session Hijacking), 
 * penyerang bisa memiliki akses ke akun tersebut untuk waktu yang sangat lama.
 * * REKOMENDASI:
 * Untuk fungsionalitas "Ingat Saya" (Remember Me), sangat disarankan untuk menggunakan metode
 * berbasis token yang lebih aman, bukan hanya dengan memperpanjang masa aktif sesi.
 */

// Pastikan tidak ada output (seperti spasi atau HTML) yang dikirim sebelum baris ini.
// Cek jika sesi belum aktif untuk menghindari error.
if (session_status() === PHP_SESSION_NONE) {

    // --- PENGATURAN MASA AKTIF SESI & COOKIE ---
    // Atur masa aktif dalam detik. 10 tahun = 10 * 365 hari/tahun * 24 jam/hari * 60 menit/jam * 60 detik/menit
    $lifetime = 315360000; // 10 tahun dalam detik

    /**
     * Mengatur parameter cookie sesi.
     * Ini memberitahu browser untuk menyimpan cookie sesi selama $lifetime.
     * Menggunakan array lebih modern dan mudah dibaca.
     */
    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path' => '/',
        // 'domain' => '.domainanda.com', // Sesuaikan jika aplikasi Anda menggunakan subdomain
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', // WAJIB 'true' di production (jika sudah HTTPS)
        'httponly' => true, // WAJIB 'true' untuk mencegah akses cookie dari JavaScript (mitigasi XSS)
        'samesite' => 'Lax' // 'Lax' atau 'Strict' untuk melindungi dari serangan CSRF
    ]);

    /**
     * Mengatur masa hidup file sesi di server.
     * PERINGATAN: Fungsi ini_set() mungkin dinonaktifkan di beberapa lingkungan hosting.
     * Nilainya harus sama atau lebih besar dari $lifetime cookie.
     */
    ini_set('session.gc_maxlifetime', $lifetime);

    /**
     * Mengontrol kemungkinan garbage collector (GC) atau "pengumpul sampah" sesi berjalan.
     * * PERINGATAN SANGAT KERAS:
     * Mengatur 'session.gc_probability' ke 0 akan MENONAKTIFKAN mekanisme pembersihan sesi bawaan PHP.
     * Ini akan menyebabkan file-file sesi lama menumpuk di server Anda dan TIDAK AKAN PERNAH DIHAPUS,
     * yang dapat menghabiskan ruang disk dan menurunkan performa server seiring waktu.
     * Gunakan dengan risiko Anda sendiri dan pastikan ada mekanisme pembersihan manual jika diperlukan.
     */
    ini_set('session.gc_probability', 0); // Tidak direkomendasikan, tapi sesuai permintaan awal Anda.
    ini_set('session.gc_divisor', 100);   // Nilai standar untuk divisor.

    // Memulai sesi dengan konfigurasi yang sudah diatur di atas.
    session_start();

    /**
     * TIPS KEAMANAN PENTING:
     * Setelah pengguna berhasil login, selalu buat ulang session ID untuk mencegah serangan Session Fixation.
     * Tambahkan kode ini di file proses_login.php Anda setelah verifikasi password berhasil:
     * * if (password_verify($password, $hash_dari_db)) {
     * session_regenerate_id(true); // Baris ini wajib untuk keamanan!
     * $_SESSION['user_id'] = $id_user_dari_db;
     * // ... lanjutkan ke halaman dashboard
     * }
     */
}
?>
