<?php
// Hubungkan ke database (sesuaikan nama file koneksi jika berbeda)
require_once 'koneksi.php'; 

// Ambil tanggal dari parameter URL (jika ada), atau gunakan default
$dari_tanggal = $_GET['dari_tanggal'] ?? date('YYYY-01-01');
$sampai_tanggal = $_GET['sampai_tanggal'] ?? date('YYYY-m-d');

// Header untuk mendownload file sebagai CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="rekap_pemesanan.csv"');

// Buka output stream
$output = fopen("php://output", "w");
fwrite($output, "\xEF\xBB\xBF"); // BOM agar Excel bisa membaca format UTF-8 dengan benar

// Buat baris judul kolom di CSV
fputcsv($output, [
    "ID Pesanan",
    "Tanggal",
    "Nama Pelanggan",
    "Total Harga",
    "Status"
]);

// Query database yang sudah disesuaikan dengan kolom yang benar
$stmt = $pdo->prepare(
    "SELECT p.id_pemesanan, 
            p.tanggal_pesan, 
            pl.nama AS nama_pelanggan, 
            p.total_harga, 
            p.status
     FROM pemesanan p
     JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan
     ORDER BY p.tanggal_pesan DESC"
);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Masukkan data dari database ke dalam file CSV
foreach ($data as $row) {
    fputcsv($output, [
        $row['id_pemesanan'],
        $row['tanggal_pesan'],
        $row['nama_pelanggan'],
        $row['total_harga'],
        $row['status']
    ]);
}

fclose($output);
exit;
?>