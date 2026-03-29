<?php
// Shared bootstrap: secure session, env, helpers, and DB connection
if (session_status() !== PHP_SESSION_ACTIVE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => $isHttps,
        'cookie_samesite' => 'Strict'
    ]);
}

$rootPath = dirname(__DIR__);
$autoload = $rootPath . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

if (class_exists('Dotenv\\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable($rootPath);
    $dotenv->safeLoad();
}

function app_env(string $key, $default = null) {
    return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
}

function ensure_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_input(): void
{
    $token = ensure_csrf_token();
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">';
}

function require_post_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    $posted = $_POST['csrf_token'] ?? '';
    if (!$sessionToken || !$posted || !hash_equals($sessionToken, $posted)) {
        http_response_code(400);
        exit('Invalid request token. Please refresh and try again.');
    }
}

function login_rate_limited(string $key, int $maxAttempts = 5, int $windowSeconds = 600): bool
{
    $now = time();
    $attempts = $_SESSION['rate_limits'][$key] ?? [];
    $attempts = array_filter($attempts, static fn($ts) => ($now - $ts) < $windowSeconds);
    $_SESSION['rate_limits'][$key] = $attempts;
    return count($attempts) >= $maxAttempts;
}

function record_login_attempt(string $key): void
{
    $_SESSION['rate_limits'][$key][] = time();
}

function clear_login_attempts(string $key): void
{
    unset($_SESSION['rate_limits'][$key]);
}

function purifier_instance()
{
    static $purifier = null;
    if ($purifier === null && class_exists('HTMLPurifier') && class_exists('HTMLPurifier_Config')) {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', sys_get_temp_dir());
        $purifier = new HTMLPurifier($config);
    }
    return $purifier;
}

function purify_html(string $value): string
{
    $purifier = purifier_instance();
    if ($purifier) {
        return $purifier->purify($value);
    }
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

ensure_csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post_csrf();
}

$host = app_env('DB_HOST', 'localhost');
$user = app_env('DB_USER', 'root');
$pass = app_env('DB_PASS', '');
$db   = app_env('DB_NAME', 'aqpg_db');

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_errno) {
    error_log('MySQL connection failed: ' . $conn->connect_error);
    die('Failed to connect to database.');
}

$conn->set_charset('utf8mb4');
