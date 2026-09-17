<?php
session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helper/Digiflazz.php';
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    die('Akses ditolak. Login sebagai admin dulu.');
}

$msg   = '';
$saldo = null;

if (isset($_POST['sync'])) {
    $res = Digiflazz::priceList();

    if (!empty($res['data']) && is_array($res['data'])) {
        $count = 0;
        $stmt = $pdo->prepare("
            INSERT INTO products (kode, nama, kategori, harga_modal, harga_jual, status)
            VALUES (?,?,?,?,?, 'aktif')
            ON DUPLICATE KEY UPDATE
                nama = VALUES(nama),
                harga_modal = VALUES(harga_modal),
                harga_jual = VALUES(harga_jual)
        ");

        foreach ($res['data'] as $p) {
            if (empty($p['buyer_sku_code'])) continue;
            if (($p['seller_product_status'] ?? false) !== true) continue;

            $modal = (int) $p['price'];
            $jual  = $modal + (int) round($modal * MARKUP_PERSEN / 100);

            $stmt->execute([
                $p['buyer_sku_code'],
                $p['product_name'] ?? '-',
                $p['category'] ?? 'Lainnya',
                $modal,
                $jual,
            ]);
            $count++;
        }
        $msg = "✅ Sinkron sukses: $count produk diproses.";
    } else {
        $msg = "❌ Gagal: " . ($res['data']['message'] ?? 'Tidak ada data dari Digiflazz');
    }
}

if (isset($_POST['cek_saldo'])) {
    $res   = Digiflazz::cekSaldo();
    $saldo = $res['data']['deposit'] ?? null;
}

$totalProduk = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalTrx    = $pdo->query("SELECT COUNT(*) FROM transactions")->fetchColumn();
$totalSukses = $pdo->query("SELECT COUNT(*) FROM transactions WHERE status = 'SUKSES'")->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Panel</title>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<header>
    <h1>🔧 Admin Panel</h1>
    <nav><a href="../dashboard.php">Ke Dashboard</a></nav>
</header>

<main>
    <?php if ($msg): ?><div class="card"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

    <section class="card">
        <h2>Statistik</h2>
        <p>Total produk: <b><?= $totalProduk ?></b></p>
        <p>Total transaksi: <b><?= $totalTrx ?></b></p>
        <p>Transaksi sukses: <b><?= $totalSukses ?></b></p>
        <?php if ($saldo !== null): ?>
            <p>💰 Saldo Digiflazz: <b>Rp<?= number_format((int) $saldo, 0, ',', '.') ?></b></p>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2>Aksi</h2>
        <form method="POST" style="margin-bottom:10px">
            <button name="sync" class="btn-primary">🔄 Sinkron Produk Digiflazz</button>
        </form>
        <form method="POST">
            <button name="cek_saldo" class="btn-primary">💵 Cek Saldo Digiflazz</button>
        </form>
    </section>
</main>
</body>
</html>
