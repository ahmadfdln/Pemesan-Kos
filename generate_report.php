<?php
// generate_report.php (Versi dengan Fitur Lengkap)
include 'session_handler.php';
include 'koneksi.php';

// Composer autoloader untuk Dompdf
require 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Cek otorisasi
if (!isset($_SESSION['loggedin']) || ($_SESSION['tipe_akun'] ?? null) !== 'admin') {
    die("Akses ditolak.");
}

// Ambil parameter dari URL
$report_type = $_GET['report'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$format = $_GET['format'] ?? 'pdf'; // default ke pdf

// Bangun klausa WHERE untuk tanggal
$date_where_clauses = [];
// Sesuaikan nama kolom tanggal untuk setiap tabel
$date_column_map = [
    'pemesanan' => 'tanggal_pemesanan',
    'pendapatan' => 'tanggal_pemesanan',
    'pengguna' => 'created_at', // Asumsi ada kolom created_at di tabel pengguna
    'kost' => 'created_at' // Asumsi ada kolom created_at di tabel kost
];
$date_column = $date_column_map[$report_type] ?? '';

if (!empty($start_date) && $date_column) {
    $date_where_clauses[] = "$date_column >= '" . mysqli_real_escape_string($koneksi, $start_date) . "'";
}
if (!empty($end_date) && $date_column) {
    $date_where_clauses[] = "$date_column <= '" . mysqli_real_escape_string($koneksi, $end_date) . " 23:59:59'";
}

// --- LOGIKA UNTUK CSV ---
if ($format === 'csv') {
    $filename = "Laporan_{$report_type}_" . date('Y-m-d') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // Tentukan header dan query berdasarkan tipe laporan
    $header = [];
    $query = "";
    $where_sql = "";

    switch ($report_type) {
        case 'pemesanan':
            $header = ['ID', 'Penyewa', 'Kost', 'Total Harga', 'Status', 'Tanggal'];
            $where_sql = count($date_where_clauses) > 0 ? "WHERE " . implode(' AND ', $date_where_clauses) : '';
            $query = "SELECT p.*, u.nama_lengkap AS nama_penyewa FROM pemesanan p JOIN pengguna u ON p.id_penyewa = u.id_pengguna $where_sql ORDER BY p.id DESC";
            break;
        case 'pendapatan':
            $header = ['ID Pesanan', 'Kost', 'Penyewa', 'Total Pendapatan', 'Tanggal Konfirmasi'];
            $date_where_clauses[] = "p.status = 'Dikonfirmasi'";
            $where_sql = "WHERE " . implode(' AND ', $date_where_clauses);
            $query = "SELECT p.*, u.nama_lengkap AS nama_penyewa FROM pemesanan p JOIN pengguna u ON p.id_penyewa = u.id_pengguna $where_sql ORDER BY p.id DESC";
            break;
        case 'kost':
             $header = ['ID Kost', 'Nama Kost', 'Nama Pemilik', 'Status'];
             $where_sql = count($date_where_clauses) > 0 ? "WHERE " . implode(' AND ', $date_where_clauses) : '';
             $query = "SELECT k.*, p.nama_lengkap AS nama_pemilik FROM kost k LEFT JOIN pengguna p ON k.id_pemilik = p.id_pengguna $where_sql ORDER BY k.id_kost DESC";
             break;
        case 'pengguna':
             $header = ['ID', 'Nama Lengkap', 'Tipe Akun'];
             $where_sql = count($date_where_clauses) > 0 ? "WHERE " . implode(' AND ', $date_where_clauses) : '';
             $query = "SELECT * FROM pengguna $where_sql ORDER BY id_pengguna ASC";
             break;
    }
    
    fputcsv($output, $header);
    $result = mysqli_query($koneksi, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            if ($report_type == 'pemesanan') {
                fputcsv($output, [$row['id'], $row['nama_penyewa'], $row['nama_kost'], $row['total_harga'], $row['status'], $row['tanggal_pemesanan']]);
            } elseif ($report_type == 'pendapatan') {
                fputcsv($output, [$row['id'], $row['nama_kost'], $row['nama_penyewa'], $row['total_harga'], $row['tanggal_pemesanan']]);
            } elseif ($report_type == 'kost') {
                fputcsv($output, [$row['id_kost'], $row['nama_kost'], $row['nama_pemilik'] ?? 'N/A', $row['status']]);
            } elseif ($report_type == 'pengguna') {
                fputcsv($output, [$row['id_pengguna'], $row['nama_lengkap'], $row['tipe_akun']]);
            }
        }
    }
    fclose($output);
    exit;
}


// --- LOGIKA UNTUK PDF (DOMPDF) ---
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: "Helvetica", sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 20px; }
        .header p { margin: 5px 0; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .total-row td { font-weight: bold; background-color: #f2f2f2; }
    </style>
</head>
<body>';

$report_title = '';
$table_html = '';
$total_pendapatan = 0;

$where_sql = count($date_where_clauses) > 0 ? "WHERE " . implode(' AND ', $date_where_clauses) : '';

switch ($report_type) {
    case 'pemesanan':
        $report_title = 'Laporan Pemesanan';
        $query = "SELECT p.*, u.nama_lengkap AS nama_penyewa FROM pemesanan p JOIN pengguna u ON p.id_penyewa = u.id_pengguna $where_sql ORDER BY p.id DESC";
        $result = mysqli_query($koneksi, $query);
        
        $table_html .= '<thead><tr><th>ID</th><th>Penyewa</th><th>Kost</th><th class="text-right">Total</th><th class="text-center">Status</th><th>Tanggal</th></tr></thead><tbody>';
        while ($row = mysqli_fetch_assoc($result)) {
            $table_html .= '<tr><td>'.$row['id'].'</td><td>'.htmlspecialchars($row['nama_penyewa']).'</td><td>'.htmlspecialchars($row['nama_kost']).'</td><td class="text-right">Rp '.number_format($row['total_harga'] ?? $row['harga']).'</td><td class="text-center">'.$row['status'].'</td><td>'.$row['tanggal_pemesanan'].'</td></tr>';
        }
        $table_html .= '</tbody>';
        break;

    case 'pendapatan':
        $report_title = 'Laporan Pendapatan';
        $pendapatan_clauses = $date_where_clauses;
        $pendapatan_clauses[] = "p.status = 'Dikonfirmasi'";
        $where_sql = "WHERE " . implode(' AND ', $pendapatan_clauses);
        $query = "SELECT p.*, u.nama_lengkap AS nama_penyewa FROM pemesanan p JOIN pengguna u ON p.id_penyewa = u.id_pengguna $where_sql ORDER BY p.id DESC";
        $result = mysqli_query($koneksi, $query);

        $table_html .= '<thead><tr><th>ID Pesanan</th><th>Kost</th><th>Penyewa</th><th>Tanggal</th><th class="text-right">Pendapatan</th></tr></thead><tbody>';
        while ($row = mysqli_fetch_assoc($result)) {
            $table_html .= '<tr><td>'.$row['id'].'</td><td>'.htmlspecialchars($row['nama_kost']).'</td><td>'.htmlspecialchars($row['nama_penyewa']).'</td><td>'.$row['tanggal_pemesanan'].'</td><td class="text-right">Rp '.number_format($row['total_harga'] ?? $row['harga']).'</td></tr>';
            $total_pendapatan += ($row['total_harga'] ?? $row['harga']);
        }
        $table_html .= '<tr class="total-row"><td colspan="4" class="text-right">Total Pendapatan</td><td class="text-right">Rp '.number_format($total_pendapatan).'</td></tr>';
        $table_html .= '</tbody>';
        break;
    
    case 'kost':
        $report_title = 'Laporan Data Kost';
        $query = "SELECT k.*, p.nama_lengkap AS nama_pemilik FROM kost k LEFT JOIN pengguna p ON k.id_pemilik = p.id_pengguna $where_sql ORDER BY k.id_kost DESC";
        $result = mysqli_query($koneksi, $query);
        $table_html .= '<thead><tr><th class="text-center">ID</th><th>Nama Kost</th><th>Pemilik</th><th class="text-center">Status</th></tr></thead><tbody>';
        while ($row = mysqli_fetch_assoc($result)) {
            $table_html .= '<tr><td class="text-center">'.$row['id_kost'].'</td><td>'.htmlspecialchars($row['nama_kost']).'</td><td>'.htmlspecialchars($row['nama_pemilik'] ?? 'N/A').'</td><td class="text-center">'.ucfirst($row['status']).'</td></tr>';
        }
        $table_html .= '</tbody>';
        break;

    case 'pengguna':
        $report_title = 'Laporan Data Pengguna';
        $query = "SELECT * FROM pengguna $where_sql ORDER BY id_pengguna ASC";
        $result = mysqli_query($koneksi, $query);
        $table_html .= '<thead><tr><th class="text-center">ID</th><th>Nama Lengkap</th><th class="text-center">Tipe Akun</th></tr></thead><tbody>';
        while ($row = mysqli_fetch_assoc($result)) {
            $table_html .= '<tr><td class="text-center">'.$row['id_pengguna'].'</td><td>'.htmlspecialchars($row['nama_lengkap']).'</td><td class="text-center">'.ucfirst($row['tipe_akun']).'</td></tr>';
        }
        $table_html .= '</tbody>';
        break;
}

$date_info = (!empty($start_date) && !empty($end_date)) ? "Periode: $start_date s/d $end_date" : "Semua Data";

$html .= '
    <div class="header">
        <h1>' . $report_title . '</h1>
        <p>Heaven Indekos</p>
        <p>' . $date_info . '</p>
    </div>
    <table>' . $table_html . '</table>
</body></html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$file_name = "Laporan_{$report_type}_" . date('Y-m-d') . ".pdf";
$dompdf->stream($file_name, ["Attachment" => 1]); // 1 untuk unduh, 0 untuk pratinjau

exit;
?>
