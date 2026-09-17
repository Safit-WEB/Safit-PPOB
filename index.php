<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PPOB Digital</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header>
    <h1>⚡ PPOB Digital</h1>
    <nav>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Daftar</a>
        <?php endif; ?>
    </nav>
</header>

<main>
    <section class="hero">
        <h2>Isi Pulsa, Data, Token PLN, E-Wallet</h2>
        <p>Proses cepat 24 jam, otomatis, harga bersaing.</p>
        <a href="<?= isset($_SESSION['user_id']) ? 'dashboard.php' : 'register.php' ?>" class="btn-primary">
            <?= isset($_SESSION['user_id']) ? 'Mulai Transaksi' : 'Daftar Gratis' ?>
        </a>
    </section>

    <section class="fitur">
        <div>💳 QRIS, VA, GoPay</div>
        <div>⚡ Otomatis via Digiflazz</div>
        <div>🔒 Aman & Terpercaya</div>
    </section>
</main>

<footer>© <?= date('Y') ?> PPOB Digital</footer>
</body>
</html>
