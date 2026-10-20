
<?php
require_once "koneksi.php";

$id = (int)($_GET["id"] ?? 0);

$stmt = $pdo->prepare(
    "SELECT p.*, pl.nama, pl.no_hp, pl.alamat
     FROM pemesanan p
     JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan
     WHERE p.id_pemesanan = ?"
);
$stmt->execute([$id]);
$pesanan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pesanan) {
    exit("Pesanan tidak ditemukan.");
}

$stmt = $pdo->prepare(
    "SELECT d.*, m.nama_makanan
     FROM detail_pemesanan d
     JOIN menu m ON d.id_menu = m.id_menu
     WHERE d.id_pemesanan = ?"
);
$stmt->execute([$id]);
$details = $stmt->fetchAll(PDO::FETCH_ASSOC);

function rupiah($angka) {
    return "Rp " . number_format((float)$angka, 0, ",", ".");
}
function h($nilai) {
    return htmlspecialchars((string)$nilai, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Bukti Pesanan</title>
<style>
body{font-family:Arial;background:#fff7ed;margin:0;color:#333}
header{background:#e87524;color:white;text-align:center;padding:20px}
main{max-width:800px;margin:25px auto;padding:15px}
.card{background:white;padding:22px;border-radius:12px;box-shadow:0 3px 10px #ddd}
table{width:100%;border-collapse:collapse;margin-top:20px}
th,td{padding:12px;border-bottom:1px solid #ddd;text-align:left}
button,a{display:inline-block;background:#e87524;color:white;padding:11px 15px;border:0;border-radius:6px;text-decoration:none;cursor:pointer}
@media print{button,a{display:none}}
</style>
</head>
<body>
<header><h1>Pesanan Berhasil Disimpan</h1></header>
<main><section class="card">
<h2>Bukti Pesanan #<?= (int)$id ?></h2>
<p>Nama: <?= h($pesanan["nama"]) ?></p>
<p>Nomor HP: <?= h($pesanan["no_hp"]) ?></p>
<p>Alamat: <?= h($pesanan["alamat"]) ?></p>

<p>Tanggal: <?= htmlspecialchars($pesanan["tanggal_pesan"] ?? "-") ?></p>

<p>Metode pembayaran: 
    <?= htmlspecialchars($pesanan["metode_pembayaran"] ?? "Belum tersedia") ?>
</p>

<p>Status:
    <?= htmlspecialchars($pesanan["status"] ?? "-") ?>
</p>

<table>
<tr><th>Makanan</th><th>Jumlah</th><th>Harga</th><th>Subtotal</th></tr>
<?php foreach ($details as $d): ?>
<tr>
<td><?= h($d["nama_makanan"]) ?></td>
<td><?= (int)$d["jumlah"] ?></td>
<?php 
$harga = $item['harga'] ?? 0; 
echo "Rp " . number_format($harga, 0, ',', '.'); 
?>
<td><?= rupiah($d["subtotal"]) ?></td>
</tr>
<?php endforeach; ?>
</table>

<h2>Total: <?= rupiah($pesanan["total_harga"]) ?></h2>
<button onclick="window.print()">Cetak Bukti</button>
<a href="index.php">Kembali ke Menu</a>
</section></main>
</body>
</html>
