
<?php
require_once "koneksi.php";

$awal = $_GET["awal"] ?? date("Y-m-01");
$akhir = $_GET["akhir"] ?? date("Y-m-d");

// Ambil data rekap sesuai struktur database
$sql = "SELECT
            p.id_pemesanan,
            p.tanggal_pesan,
            p.total_harga,
            p.status,
            pl.nama,
            pl.no_hp
        FROM pemesanan p
        JOIN pelanggan pl
            ON p.id_pelanggan = pl.id_pelanggan
        WHERE p.tanggal_pesan BETWEEN ? AND ?
        ORDER BY p.tanggal_pesan DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$awal, $akhir]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
foreach ($data as $row) {
    $total += (float)$row["total_harga"];
}

function rupiah($angka) {
    return "Rp " . number_format((float)$angka, 0, ",", ".");
}

function h($teks) {
    return htmlspecialchars((string)$teks, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Rekap Pemesanan Makanan</title>

<style>
body {
    font-family: Arial, sans-serif;
    background: #fff7ed;
    margin: 0;
    color: #333;
}
header {
    background: #e87524;
    color: white;
    text-align: center;
    padding: 20px;
}
.container {
    max-width: 1100px;
    margin: 30px auto;
    padding: 20px;
}
.card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 3px 12px #ddd;
}
input, button, .btn {
    padding: 10px;
    margin: 5px 3px;
    border: 1px solid #ddd;
    border-radius: 6px;
}
button, .btn {
    background: #e87524;
    color: white;
    border: none;
    text-decoration: none;
    cursor: pointer;
}
.total {
    color: #e87524;
    font-size: 24px;
    font-weight: bold;
}
.table-wrap {
    overflow-x: auto;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
th, td {
    padding: 12px;
    border-bottom: 1px solid #ddd;
    text-align: left;
}
th {
    background: #ffead8;
}
</style>
</head>

<body>
<header>
    <h1>Rekap Pemesanan Makanan</h1>
</header>

<div class="container">
<div class="card">

<form method="get">
    <label>Dari tanggal:</label>
    <input type="date" name="awal"
           value="<?= h($awal) ?>" required>

    <label>Sampai tanggal:</label>
    <input type="date" name="akhir"
           value="<?= h($akhir) ?>" required>

    <button type="submit">Tampilkan</button>
</form>

<hr>

<p>Jumlah pesanan: <b><?= count($data) ?></b></p>

<p class="total">
    Total omzet: <?= rupiah($total) ?>
</p>

<a class="btn"
   href="rekap_csv.php?awal=<?= urlencode($awal) ?>&akhir=<?= urlencode($akhir) ?>">
   Download Rekap CSV
</a>

<button onclick="window.print()">Cetak Rekap</button>

<a class="btn" href="index.php">Kembali ke Menu</a>

<div class="table-wrap">
<table>
<thead>
<tr>
    <th>ID Pesanan</th>
    <th>Tanggal</th>
    <th>Nama Pelanggan</th>
    <th>No. HP</th>
    <th>Total</th>
    <th>Status</th>
</tr>
</thead>

<tbody>
<?php if (count($data) > 0): ?>
    <?php foreach ($data as $row): ?>
    <tr>
        <td>#<?= (int)$row["id_pemesanan"] ?></td>
        <td><?= h($row["tanggal_pesan"]) ?></td>
        <td><?= h($row["nama"]) ?></td>
        <td><?= h($row["no_hp"]) ?></td>
        <td><?= rupiah($row["total_harga"]) ?></td>
        <td><?= h($row["status"]) ?></td>
    </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="6">Belum ada pesanan pada tanggal tersebut.</td>
    </tr>
<?php endif; ?>
</tbody>
</table>
</div>

<p><small>
Catatan: total omzet adalah jumlah penjualan, bukan keuntungan bersih.
</small></p>

</div>
</div>
</body>
</html>
