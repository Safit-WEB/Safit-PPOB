<?php
session_start();
require_once __DIR__ . '/db.php';

if (isset($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama  = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if (!$nama || !$email || !$pass) {
        $error = 'Semua field wajib diisi';
    } elseif (strlen($pass) < 6) {
        $error = 'Password minimal 6 karakter';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = 'Email sudah terdaftar';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO users (nama, email, password) VALUES (?,?,?)")
                ->execute([$nama, $email, $hash]);
            header('Location: login.php?registered=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Daftar</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="auth">
<form method="POST" class="card">
    <h2>Daftar Akun</h2>
    <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <input type="text" name="nama" placeholder="Nama lengkap" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password (min 6 karakter)" required>
    <button class="btn-primary">Daftar</button>
    <p style="margin-top:12px;font-size:14px">Sudah punya akun? <a href="login.php">Login</a></p>
</form>
</body>
</html>
