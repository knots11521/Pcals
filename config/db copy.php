<?php
// Database configuration — env-driven (no hardcoded credentials).
// Uses a tiny native .env reader so no Composer/external dependency is required.

if (!function_exists('env')) {
    function env(string $key, $default = null) {
        static $vars = null;
        if ($vars === null) {
            $vars = [];
            $envFile = __DIR__ . '/../.env';
            if (is_file($envFile)) {
                $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '' || $line[0] === '#') {
                        continue;
                    }
                    $pos = strpos($line, '=');
                    if ($pos === false) {
                        continue;
                    }
                    $k = trim(substr($line, 0, $pos));
                    $v = trim(substr($line, $pos + 1));
                    $vars[$k] = $v;
                }
            }
        }
        return array_key_exists($key, $vars) ? $vars[$key] : $default;
    }
}

$host = env('DB_HOST', 'localhost');
$db   = env('DB_NAME', 'ca_loan_system');
$user = env('DB_USER', 'root');
$pass = env('DB_PASS', '');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection Failed: " . $e->getMessage());
}
?>
