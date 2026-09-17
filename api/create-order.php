<?php
session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helper/Midtrans.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'msg' => 'Silakan login dulu']);
    exit;
}

$kode   = trim($_POST['kode'] ?? '');
$tujuan = trim($_POST['tujuan'] ?? '');

if (!$kode || !$tujuan) {
    echo json_encode(['success' => false, 'msg' => 'Data tidak lengkap']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE kode = ? AND status = 'aktif'");
$stmt->execute([$kode]);
$produk = $stmt->fetch();

if (!$produk) {
    echo json_encode(['success' => false, 'msg' => 'Produk tidak ditemukan']);
    exit;
}

$refId = 'TRX' . date('YmdHis') . rand(100, 999);
$harga = (int) $produk['harga_jual'];

$pdo->prepare("INSERT INTO transactions (user_id, ref_id, produk_kode, tujuan, harga, status) VALUES (?,?,?,?,?, 'PENDING')")
    ->execute([$_SESSION['user_id'], $refId, $kode, $tujuan, $harga]);

$snapToken = MidtransHelper::createSnapToken($refId, $harga, [
    'nama'  => $_SESSION['nama'],
    'email' => $_SESSION['email'],
]);

if (!$snapToken) {
    echo json_encode(['success' => false, 'msg' => 'Gagal membuat pembayaran. Cek kredensial Midtrans.']);
    exit;
}

$pdo->prepare("UPDATE transactions SET snap_token = ? WHERE ref_id = ?")->execute([$snapToken, $refId]);

echo json_encode([
    'success'    => true,
    'snap_token' => $snapToken,
    'ref_id'     => $refId,
]);
