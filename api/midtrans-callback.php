<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helper/Midtrans.php';
require_once __DIR__ . '/../helper/Digiflazz.php';

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data) || empty($data['order_id'])) {
    http_response_code(400);
    exit('Bad request');
}

$orderId     = $data['order_id'];
$statusCode  = $data['status_code'];
$grossAmount = $data['gross_amount'];
$signature   = $data['signature_key'] ?? '';

// Verifikasi signature
$expected = MidtransHelper::verifySignature($orderId, $statusCode, $grossAmount);
if (!hash_equals($expected, $signature)) {
    http_response_code(403);
    exit('Invalid signature');
}

$trxStatus   = $data['transaction_status'];
$fraudStatus = $data['fraud_status'] ?? 'accept';

$stmt = $pdo->prepare("SELECT * FROM transactions WHERE ref_id = ?");
$stmt->execute([$orderId]);
$trx = $stmt->fetch();

if (!$trx) {
    http_response_code(404);
    exit('Transaction not found');
}

// Idempotent: kalau sudah diproses, skip
if (in_array($trx['status'], ['SUKSES', 'GAGAL', 'REFUND'], true)) {
    http_response_code(200);
    exit('Already processed');
}

$isPaid = ($statusCode === '200')
    && ($fraudStatus === 'accept')
    && in_array($trxStatus, ['settlement', 'capture'], true);

if ($isPaid && $trx['status'] === 'PENDING') {
    $pdo->prepare("UPDATE transactions SET status = 'PAID' WHERE ref_id = ?")->execute([$orderId]);

    $digi       = Digiflazz::topup($trx['produk_kode'], $trx['tujuan'], $orderId);
    $digiStatus = $digi['data']['status'] ?? '';
    $sn         = $digi['data']['sn'] ?? '';

    if ($digiStatus === 'Sukses') {
        $pdo->prepare("UPDATE transactions SET status = 'SUKSES', sn = ? WHERE ref_id = ?")
            ->execute([$sn, $orderId]);
    } elseif ($digiStatus === 'Pending') {
        // Biarkan PENDING, nanti digiflazz-callback yang update
    } else {
        $pdo->prepare("UPDATE transactions SET status = 'GAGAL' WHERE ref_id = ?")->execute([$orderId]);
    }
} elseif (in_array($trxStatus, ['deny', 'cancel', 'expire', 'failure'], true)) {
    $pdo->prepare("UPDATE transactions SET status = 'GAGAL' WHERE ref_id = ?")->execute([$orderId]);
}

http_response_code(200);
echo 'OK';
