
<?php
require_once "koneksi.php";

if (empty($_SESSION["keranjang"])) {
    header("Location: keranjang.php");
    exit;
}

$error = "";
$items = [];
$total = 0;

// Ambil menu dalam keranjang
foreach ($_SESSION["keranjang"] as $id => $jumlah) {
    $stmt = $pdo->prepare(
        "SELECT id_menu, nama_makanan, harga, stok
         FROM menu WHERE id_menu = ?"
    );
    $stmt->execute([(int)$id]);
    $menu = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($menu && (int)$menu["stok"] > 0) {
        $jumlah = min((int)$jumlah, (int)$menu["stok"]);
        $menu["jumlah"] = $jumlah;
        $menu["subtotal"] = $jumlah * (float)$menu["harga"];
        $items[] = $menu;
        $total += $menu["subtotal"];
    }
}

if (!$items) {
    exit("Keranjang kosong atau stok makanan habis.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama = trim($_POST["nama"] ?? "");
    $no_hp = trim($_POST["no_hp"] ?? "");
    $alamat = trim($_POST["alamat"] ?? "");
    $metode = $_POST["metode"] ?? "";
    $bayar = (float)($_POST["bayar"] ?? 0);

    if ($nama === "" || $no_hp === "" || $alamat === "") {
        $error = "Semua data pelanggan wajib diisi.";
    } elseif (!in_array($metode, ["Tunai", "Transfer Bank", "QRIS"], true)) {
        $error = "Metode pembayaran tidak valid.";
    } elseif ($metode === "Tunai" && $bayar < $total) {
        $error = "Uang pembayaran kurang.";
    } else {
        try {
            $pdo->beginTransaction();

            // Simpan pelanggan menggunakan kolom nama
            $stmt = $pdo->prepare(
                "INSERT INTO pelanggan (nama, no_hp, alamat)
                 VALUES (?, ?, ?)"
            );
            $stmt->execute([$nama, $no_hp, $alamat]);
            $id_pelanggan = $pdo->lastInsertId();

            // Simpan pesanan
            // Sesuaikan nama kolom jika tabel pemesanan berbeda.
            $status = ($metode === "Tunai")
                ? "Dibayar"
                : "Menunggu konfirmasi";

            
$status = ($metode === "Tunai")
    ? "Lunas"
    : "Menunggu";

$stmt = $pdo->prepare(
    "INSERT INTO pemesanan
    (id_pelanggan, tanggal_pesan, total_harga, status)
    VALUES (?, CURDATE(), ?, ?)"
);

$stmt->execute([
    $id_pelanggan,
    $total,
    $status
]);

$id_pesanan = $pdo->lastInsertId();

            // Simpan detail dan kurangi stok
           
$detail = $pdo->prepare(
    "INSERT INTO detail_pemesanan
    (id_pemesanan, id_menu, jumlah, subtotal)
    VALUES (?, ?, ?, ?)"
);

$stok = $pdo->prepare(
    "UPDATE menu SET stok = stok - ?
     WHERE id_menu = ? AND stok >= ?"
);

foreach ($items as $item) {
    $jumlah = (int)$item["jumlah"];
    $id_menu = (int)$item["id_menu"];
    $subtotal = (int)$item["harga"] * $jumlah;

    $stok->execute([$jumlah, $id_menu, $jumlah]);

    if ($stok->rowCount() !== 1) {
        throw new Exception(
            "Stok makanan tidak mencukupi."
        );
    }

    $detail->execute([
        $id_pesanan,
        $id_menu,
        $jumlah,
        $subtotal
    ]);
}

            
            

            $pdo->commit();
            $_SESSION["keranjang"] = [];

            header("Location: sukses.php?id=" . $id_pesanan);
            exit;

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = "Pesanan gagal disimpan: " . $e->getMessage();
        }
    }
}

function rupiah($angka) {
    return "Rp " . number_format((float)$angka, 0, ",", ".");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Checkout Makanan</title>
<style>
body{font-family:Arial;background:#fff7ed;margin:0;color:#333}
header{background:#e87524;color:white;text-align:center;padding:20px}
main{max-width:800px;margin:25px auto;padding:15px}
.card{background:white;padding:22px;margin-bottom:20px;border-radius:12px;box-shadow:0 3px 10px #ddd}
label{display:block;margin:15px 0;font-weight:bold}
input,textarea,select{width:100%;padding:11px;margin-top:7px;box-sizing:border-box;border:1px solid #ccc;border-radius:6px}
button{background:#e87524;color:white;padding:12px 18px;border:0;border-radius:6px;cursor:pointer}
.total{color:#e87524;font-size:24px;font-weight:bold}
.error{background:#ffe0e0;color:#a00;padding:12px;border-radius:6px;overflow-wrap:anywhere}
</style>
</head>
<body>
<header><h1>Checkout & Pembayaran</h1></header>
<main>

<?php if ($error): ?>
<p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<section class="card">
<h2>Ringkasan Pesanan</h2>
<?php foreach ($items as $item): ?>
<p>
<?= htmlspecialchars($item["nama_makanan"]) ?>
× <?= $item["jumlah"] ?> =
<?= rupiah($item["subtotal"]) ?>
</p>
<?php endforeach; ?>
<hr>
<p class="total">Total: <?= rupiah($total) ?></p>
</section>

<section class="card">
<h2>Data Pelanggan</h2>
<form method="post">
<label>Nama pelanggan
<input name="nama" required value="<?= htmlspecialchars($_POST["nama"] ?? "") ?>">
</label>

<label>Nomor HP
<input name="no_hp" required value="<?= htmlspecialchars($_POST["no_hp"] ?? "") ?>">
</label>

<label>Alamat
<textarea name="alamat" rows="3" required><?= htmlspecialchars($_POST["alamat"] ?? "") ?></textarea>
</label>

<label>Metode pembayaran
<select name="metode" id="metode" onchange="ubahMetode()">
<option value="Tunai">Tunai</option>
<option value="Transfer Bank">Transfer Bank</option>
<option value="QRIS">QRIS</option>
</select>
</label>

<div id="tunai">
<label>Uang dibayarkan
<input type="number" name="bayar" id="bayar"
min="<?= (float)$total ?>" value="<?= (float)$total ?>">
</label>
<p id="kembalian"></p>
</div>

<p>Transfer Bank dan QRIS dicatat sebagai menunggu konfirmasi manual.</p>
<button type="submit">Konfirmasi Pesanan</button>
<a href="keranjang.php">Kembali ke keranjang</a>
</form>
</section>
</main>

<script>
const total = <?= json_encode((float)$total) ?>;

function ubahMetode() {
    const tunai = document.getElementById("metode").value === "Tunai";
    document.getElementById("tunai").style.display = tunai ? "block" : "none";
    document.getElementById("bayar").required = tunai;
    hitungKembalian();
}

function hitungKembalian() {
    const bayar = Number(document.getElementById("bayar").value);
    document.getElementById("kembalian").textContent =
        bayar >= total
        ? "Kembalian: Rp " + (bayar-total).toLocaleString("id-ID")
        : "Uang pembayaran kurang.";
}

document.getElementById("bayar").addEventListener("input", hitungKembalian);
ubahMetode();
</script>
</body>
</html>
