<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$produk = $pdo->query("SELECT * FROM products WHERE status = 'aktif' ORDER BY kategori, harga_jual")->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY id DESC LIMIT 15");
$stmt->execute([$_SESSION['user_id']]);
$riwayat = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard</title>
<link rel="stylesheet" href="assets/style.css">
<script type="text/javascript"
        src="https://app.<?= MIDTRANS_MODE === 'production' ? '' : 'sandbox.' ?>midtrans.com/snap/snap.js"
        data-client-key="<?= MIDTRANS_CLIENT_KEY ?>"></script>
</head>
<body>
<header>
    <h1>Halo, <?= htmlspecialchars($_SESSION['nama']) ?> 👋</h1>
    <nav>
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="admin/index.php">Admin</a>
        <?php endif; ?>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main>
    <section class="card">
        <h2>Beli Produk</h2>
        <?php if (!$produk): ?>
            <p style="color:#888;font-size:14px">Belum ada produk. Hubungi admin untuk sinkronisasi.</p>
        <?php else: ?>
        <form id="formBeli">
            <select name="kode" required>
                <option value="">-- Pilih Produk --</option>
                <?php foreach ($produk as $p): ?>
                    <option value="<?= htmlspecialchars($p['kode']) ?>">
                        [<?= htmlspecialchars($p['kategori']) ?>] <?= htmlspecialchars($p['nama']) ?>
                        — Rp<?= number_format($p['harga_jual'], 0, ',', '.') ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="tujuan" placeholder="Nomor tujuan / ID pelanggan" required>
            <button class="btn-primary" id="btnBeli">Bayar Sekarang</button>
        </form>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2>Riwayat Transaksi</h2>
        <?php if (!$riwayat): ?>
            <p style="color:#888;font-size:14px">Belum ada transaksi.</p>
        <?php else: ?>
            <table>
                <tr><th>Ref</th><th>Produk</th><th>Harga</th><th>Status</th></tr>
                <?php foreach ($riwayat as $r): ?>
                    <tr>
                        <td style="font-size:11px"><?= htmlspecialchars($r['ref_id']) ?></td>
                        <td><?= htmlspecialchars($r['produk_kode']) ?></td>
                        <td>Rp<?= number_format($r['harga'], 0, ',', '.') ?></td>
                        <td><span class="badge <?= strtolower($r['status']) ?>"><?= $r['status'] ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </section>
</main>

<script>
const form = document.getElementById('formBeli');
const btn  = document.getElementById('btnBeli');

if (form) form.onsubmit = async (e) => {
    e.preventDefault();
    btn.disabled = true;
    btn.textContent = 'Memproses...';

    try {
        const fd = new FormData(form);
        const res = await fetch('api/create-order.php', { method: 'POST', body: fd });
        const data = await res.json();

        if (!data.success) throw new Error(data.msg || 'Gagal membuat order');

        window.snap.pay(data.snap_token, {
            onSuccess: () => { alert('Pembayaran berhasil!'); location.reload(); },
            onPending: () => { alert('Menunggu pembayaran.'); location.reload(); },
            onError:   () => { alert('Pembayaran gagal.'); btn.disabled = false; btn.textContent = 'Bayar Sekarang'; },
            onClose:   () => { alert('Popup ditutup sebelum bayar.'); btn.disabled = false; btn.textContent = 'Bayar Sekarang'; }
        });
    } catch (err) {
        alert(err.message);
        btn.disabled = false;
        btn.textContent = 'Bayar Sekarang';
    }
};
</script>
</body>
</html>
