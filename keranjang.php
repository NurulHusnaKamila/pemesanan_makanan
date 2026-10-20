
<?php
require "koneksi.php";

if (!isset($_SESSION["keranjang"])) {
    $_SESSION["keranjang"] = [];
}

if (isset($_POST["tambah"])) {
    $id = (int)($_POST["id_menu"] ?? 0);
    $jumlah = max(1, (int)($_POST["jumlah"] ?? 1));

    $stmt = $pdo->prepare(
        "SELECT stok FROM menu WHERE id_menu = ?"
    );
    $stmt->execute([$id]);
    $menu = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($menu && $menu["stok"] > 0) {
        $lama = (int)($_SESSION["keranjang"][$id] ?? 0);
        $_SESSION["keranjang"][$id] =
            min($lama + $jumlah, (int)$menu["stok"]);
    }

    header("Location: keranjang.php");
    exit;
}

if (isset($_GET["hapus"])) {
    unset($_SESSION["keranjang"][(int)$_GET["hapus"]]);
    header("Location: keranjang.php");
    exit;
}

if (isset($_POST["update"])) {
    foreach ($_POST["jumlah"] ?? [] as $id => $jumlah) {
        $id = (int)$id;
        $jumlah = (int)$jumlah;

        if ($jumlah <= 0) {
            unset($_SESSION["keranjang"][$id]);
            continue;
        }

        $stmt = $pdo->prepare(
            "SELECT stok FROM menu WHERE id_menu = ?"
        );
        $stmt->execute([$id]);
        $menu = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($menu) {
            $_SESSION["keranjang"][$id] =
                min($jumlah, (int)$menu["stok"]);
        }
    }

    header("Location: keranjang.php");
    exit;
}

$items = [];
$total = 0;

foreach ($_SESSION["keranjang"] as $id => $jumlah) {
    $stmt = $pdo->prepare(
        "SELECT * FROM menu WHERE id_menu = ?"
    );
    $stmt->execute([(int)$id]);
    $menu = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($menu) {
        $menu["jumlah"] = (int)$jumlah;
        $menu["subtotal"] = $menu["harga"] * $jumlah;
        $total += $menu["subtotal"];
        $items[] = $menu;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Keranjang</title>
<style>
body{font-family:Arial;background:#fff7ed;margin:0;color:#333}
header{background:#e87524;color:white;padding:20px;text-align:center}
main{max-width:900px;margin:25px auto;padding:15px}
table{width:100%;border-collapse:collapse;background:white}
th,td{padding:12px;border-bottom:1px solid #ddd;text-align:left}
th{background:#ffe3cc}
input,button{padding:9px}
button,.btn{background:#e87524;color:white;padding:10px;border:0;border-radius:5px;text-decoration:none;cursor:pointer}
a{color:#c45412}
</style>
</head>
<body>
<header><h1>Keranjang Pesanan</h1></header>
<main>
<p><a href="index.php">← Kembali ke menu</a></p>

<?php if (!$items): ?>
    <p>Keranjang masih kosong.</p>
<?php else: ?>
<form method="post">
<table>
<tr>
    <th>Nama Makanan</th>
    <th>Harga</th>
    <th>Jumlah</th>
    <th>Subtotal</th>
    <th>Aksi</th>
</tr>
<?php foreach ($items as $m): ?>
<tr>
    <td><?= htmlspecialchars($m["nama_makanan"]) ?></td>
    <td>Rp <?= number_format($m["harga"],0,",",".") ?></td>
    <td>
        <input type="number"
               name="jumlah[<?= (int)$m["id_menu"] ?>]"
               value="<?= $m["jumlah"] ?>"
               min="0" max="<?= (int)$m["stok"] ?>">
    </td>
    <td>Rp <?= number_format($m["subtotal"],0,",",".") ?></td>
    <td>
        <a href="keranjang.php?hapus=<?= (int)$m["id_menu"] ?>">
            Hapus
        </a>
    </td>
</tr>
<?php endforeach; ?>
</table>

<p><button type="submit" name="update">Perbarui Jumlah</button></p>
</form>

<h2>Total: Rp <?= number_format($total,0,",",".") ?></h2>
<a class="btn" href="checkout.php">Lanjut Pembayaran</a>
<?php endif; ?>

</main>
</body>
</html>
