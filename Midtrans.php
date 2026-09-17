<?php
require_once __DIR__ . '/../config.php';

class MidtransHelper
{
    private static function snapUrl(): string
    {
        return MIDTRANS_MODE === 'production'
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    public static function createSnapToken(string $orderId, int $amount, array $customer): ?string
    {
        $payload = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => $amount,
            ],
            'customer_details' => [
                'first_name' => $customer['nama'],
                'email'      => $customer['email'],
                'phone'      => $customer['phone'] ?? '08123456789',
            ],
            'callbacks' => [
                'finish' => BASE_URL . '/dashboard.php',
            ],
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => self::snapUrl(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Basic ' . base64_encode(MIDTRANS_SERVER_KEY . ':'),
            ],
            CURLOPT_TIMEOUT        => 30,
        ]);

        $res  = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 201) {
            error_log("Midtrans Error [$code]: " . $res);
            return null;
        }

        $json = json_decode($res, true);
        return $json['token'] ?? null;
    }

    public static function verifySignature(string $orderId, string $statusCode, string $grossAmount): string
    {
        return hash('sha512', $orderId . $statusCode . $grossAmount . MIDTRANS_SERVER_KEY);
    }
}
