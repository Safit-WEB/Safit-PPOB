<?php
session_start();
require_once __DIR__ . '/db.php';

if (isset($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }

$error   = '';
$success = isset($_GET['registered']) ? 'Registrasi berhasil, silakan login.' : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama']    = $user['nama'];
        $_SESSION['email']   = $user['email'];
        $_SESSION['role']    = $user['role'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Email atau password salah';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="auth">
<form method="POST" class="card">
    <h2>Login</h2>
    <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button class="btn-primary">Masuk</button>
    <p style="margin-top:12px;font-size:14px">Belum punya akun? <a href="register.php">Daftar</a></p>
</form>
</body>
</html>
