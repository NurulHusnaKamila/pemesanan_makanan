
<?php
require "koneksi.php";

$sql = "SELECT id_menu, nama_makanan, kategori,
               harga, stok, gambar
        FROM menu ORDER BY id_menu ASC";
$menus = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Menu Makanan";
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Menu Makanan</title>
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
    padding: 22px;
    text-align: center;
}
nav {
    background: white;
    padding: 15px;
    text-align: center;
}
nav a {
    margin: 10px;
    color: #c45412;
    text-decoration: none;
    font-weight: bold;
}
.container {
    max-width: 1100px;
    margin: 25px auto;
    padding: 15px;
}
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit,minmax(220px,1fr));
    gap: 20px;
}
.card {
    background: white;
    padding: 16px;
    border-radius: 12px;
    box-shadow: 0 3px 10px #ddd;
}
.card img {
    width: 100%;
    height: 160px;
    object-fit: cover;
    border-radius: 8px;
}
.harga {
    color: #e87524;
    font-size: 20px;
    font-weight: bold;
}
input, button {
    padding: 10px;
    margin-top: 8px;
}
button {
    background: #e87524;
    color: white;
    border: 0;
    border-radius: 6px;
    cursor: pointer;
}
</style>
</head>
<body>

<header>
    <h1>🍽️ Aplikasi Pemesanan Makanan</h1>
    <p>Pilih makanan favoritmu!</p>
</header>

<nav>
    <a href="index.php">Menu</a>
    <a href="keranjang.php">Keranjang</a>
    <a href="rekap.php">Rekap Penjualan</a>
</nav>

<div class="container">
    <h2>Daftar Menu Makanan</h2>

    <div class="grid">
    <?php foreach ($menus as $m): ?>
        <div class="card">
            <?php if (!empty($m["gambar"])): ?>
                <img
                    src="gambar/<?= htmlspecialchars(basename($m["gambar"])) ?>"
                    alt="<?= htmlspecialchars($m["nama_makanan"]) ?>"
                    onerror="this.style.display='none'">
            <?php endif; ?>

            <h3><?= htmlspecialchars($m["nama_makanan"]) ?></h3>

            <p>Kategori:
                <?= htmlspecialchars($m["kategori"]) ?>
            </p>

            <p>Stok: <?= (int)$m["stok"] ?></p>

            <p class="harga">
                Rp <?= number_format($m["harga"], 0, ",", ".") ?>
            </p>

            <?php if ((int)$m["stok"] > 0): ?>
            <form action="keranjang.php" method="post">
                <input type="hidden" name="id_menu"
                       value="<?= (int)$m["id_menu"] ?>">

                <label>Jumlah:</label>
                <input type="number" name="jumlah"
                       value="1" min="1"
                       max="<?= (int)$m["stok"] ?>" required>

                <button type="submit" name="tambah">
                    Tambah ke Keranjang
                </button>
            </form>
            <?php else: ?>
                <p>Stok habis.</p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    </div>
</div>

</body>
</html>
