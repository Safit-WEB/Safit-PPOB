<?php
require_once __DIR__ . '/../config.php';

class Digiflazz
{
    private static function sign(string $refId = ''): string
    {
        return md5(DIGI_USERNAME . DIGI_API_KEY . $refId);
    }

    private static function post(string $endpoint, array $payload): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => 'https://api.digiflazz.com/v1/' . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 30,
        ]);
        $res  = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 200) {
            error_log("Digiflazz Error [$code]: " . $res);
            return ['data' => ['message' => 'Koneksi ke Digiflazz gagal']];
        }

        $json = json_decode($res, true);
        return is_array($json) ? $json : ['data' => ['message' => 'Response tidak valid']];
    }

    public static function cekSaldo(): array
    {
        return self::post('cek-saldo', [
            'cmd'      => 'deposit',
            'username' => DIGI_USERNAME,
            'sign'     => self::sign(),
        ]);
    }

    public static function priceList(): array
    {
        return self::post('price-list', [
            'cmd'      => 'prepaid',
            'username' => DIGI_USERNAME,
            'sign'     => self::sign(),
        ]);
    }

    public static function topup(string $sku, string $customerNo, string $refId): array
    {
        return self::post('transaction', [
            'username'       => DIGI_USERNAME,
            'buyer_sku_code' => $sku,
            'customer_no'    => $customerNo,
            'ref_id'         => $refId,
            'sign'           => self::sign($refId),
            'testing'        => DIGI_MODE === 'development',
        ]);
    }
}
