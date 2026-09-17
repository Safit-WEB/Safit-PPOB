<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../config.php';

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

// Verifikasi signature dari Digiflazz
$header   = $_SERVER['HTTP_X_HUB_SIGNATURE'] ?? '';
$expected = 'sha1=' . hash_hmac('sha1', $raw, DIGI_API_KEY);

if (!hash_equals($expected, $header)) {
    http_response_code(403);
    exit('Invalid signature');
}

if (empty($data['data']['ref_id'])) {
    http_response_code(400);
    exit('Bad request');
}

$refId  = $data['data']['ref_id'];
$status = $data['data']['status'] ?? '';
$sn     = $data['data']['sn'] ?? '';

$newStatus = match ($status) {
    'Sukses'  => 'SUKSES',
    'Gagal'   => 'GAGAL',
    'Pending' => 'PENDING',
    default   => 'PENDING',
};

$pdo->prepare("UPDATE transactions SET status = ?, sn = ? WHERE ref_id = ?")
    ->execute([$newStatus, $sn, $refId]);

http_response_code(200);
echo 'OK';
