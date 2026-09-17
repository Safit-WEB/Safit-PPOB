<?php
// ====== DATABASE (sesuaikan dengan cPanel) ======
// cPanel otomatis menambahkan prefix username ke nama database
// Contoh: username cPanel = "john", maka nama DB = "john_ppob"
define('DB_HOST', 'localhost');
define('DB_NAME', 'username_ppob');        // ganti sesuai cPanel kamu
define('DB_USER', 'username_ppobuser');    // ganti
define('DB_PASS', 'password_db_kamu');     // ganti

// ====== MIDTRANS ======
// Ambil dari dashboard.midtrans.com → Settings → Access Keys
define('MIDTRANS_SERVER_KEY', 'SB-Mid-server-xxxxxxxxxxxx');
define('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-xxxxxxxxxxxx');
define('MIDTRANS_MODE', 'sandbox'); // 'sandbox' atau 'production'

// ====== DIGIFLAZZ ======
// Ambil dari member.digiflazz.com → Profil → API Key
define('DIGI_USERNAME', 'username_digi_kamu');
define('DIGI_API_KEY', 'xxxxxxxxxxxxxxxxxxxx');
define('DIGI_MODE', 'development'); // 'development' atau 'production'

// ====== APLIKASI ======
define('BASE_URL', 'https://domainkamu.com'); // tanpa trailing slash
define('MARKUP_PERSEN', 5); // markup 5% dari harga modal

// ====== ERROR LOGGING ======
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
