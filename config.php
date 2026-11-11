<?php
// ---------------------------------------------------------------
// CakeCraft - shared configuration, security helpers, DB connection
// ---------------------------------------------------------------

// Never show raw PHP/MySQL errors to visitors; write them to the server log instead
// (XAMPP: xampp/apache/logs/error.log).
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Safer session cookies
$cc_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_set_cookie_params(array(
    'lifetime' => 0,
    'path' => '/',
    'secure' => $cc_https,
    'httponly' => true,
    'samesite' => 'Lax'
));
session_start();

// Log out anybody who has been idle for 30 minutes
if (isset($_SESSION['last_active']) && (time() - $_SESSION['last_active']) > 1800) 
    {
    session_unset();
    session_destroy();
    session_start();
    session_regenerate_id(true);
}
$_SESSION['last_active'] = time();

// Basic security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: same-origin");

// Database settings (can be overridden with environment variables when hosting online)
$host = getenv('DB_HOST') ? getenv('DB_HOST') : "localhost";
$db_user = getenv('DB_USER') ? getenv('DB_USER') : "root";
$db_pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : "";
$db_name = getenv('DB_NAME') ? getenv('DB_NAME') : "CakeCraft";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
set_exception_handler(function ($ex) 
    {
    error_log("CakeCraft error: " . $ex->getMessage());
    http_response_code(500);
    echo "Sorry, something went wrong. Please go back and try again.";
});

function tk($n)
    {
    $s = number_format((float) $n, 2, '.', '');
    if (substr($s, -3) === '.00') 
        {
        $s = substr($s, 0, -3);
    }
    return $s . " TK";
}

// Escape text before printing it into a page
function e($s)
    {
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ---------- CSRF protection ----------
function csrf_token()
    {
    if (empty($_SESSION['csrf'])) 
        {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}
function csrf_field()
    {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}
function csrf_check()
    {
    if (!isset($_POST['csrf_token']) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], (string) $_POST['csrf_token'])) 
        {
        http_response_code(403);
        die("Security check failed. Please go back, refresh the page and try again.");
    }
}

// ---------- Validation helpers ----------
// ID names (customers, riders, admin): 3-50 characters, letters/numbers/. _ - and at least one letter
function valid_idname($s)
    {
    return preg_match('/^(?=.*[A-Za-z])[A-Za-z0-9_.-]{3,50}$/', $s) === 1;
}
// Passwords: 8-72 characters (72 is the limit of the bcrypt hashing used by password_hash)
function valid_password($s)
    {
    return strlen($s) >= 8 && strlen($s) <= 72;
}

// ---------- Login attempt limiting (5 wrong tries per ID / 20 per IP in 15 minutes) ----------
define('DUMMY_HASH', '$2y$10$mCOqQ4y.D2o19SndlRopq.oI.8CX1zZjVuYAJW7eole3NILjY0QWC');
function client_ip()
    {
    return substr(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown', 0, 45);
}
function login_blocked($conn, $scope, $ident)
    {
    $ident = strtolower(substr($ident, 0, 100));
    $ip = client_ip();
    $q1 = $conn->prepare("SELECT COUNT(*) FROM login_attempts WHERE scope = ? AND ident = ? AND created_at > (NOW() - INTERVAL 15 MINUTE)");
    $q1->bind_param("ss", $scope, $ident);
    $q1->execute();
    $c1 = (int) $q1->get_result()->fetch_row()[0];
    $q1->close();
    $q2 = $conn->prepare("SELECT COUNT(*) FROM login_attempts WHERE scope = ? AND ip = ? AND created_at > (NOW() - INTERVAL 15 MINUTE)");
    $q2->bind_param("ss", $scope, $ip);
    $q2->execute();
    $c2 = (int) $q2->get_result()->fetch_row()[0];
    $q2->close();
    return ($c1 >= 5 || $c2 >= 20);
}
function login_fail($conn, $scope, $ident)
    {
    $ident = strtolower(substr($ident, 0, 100));
    $ip = client_ip();
    $ins = $conn->prepare("INSERT INTO login_attempts (scope, ident, ip) VALUES (?, ?, ?)");
    $ins->bind_param("sss", $scope, $ident, $ip);
    $ins->execute();
    $ins->close();
    $conn->query("DELETE FROM login_attempts WHERE created_at < (NOW() - INTERVAL 1 DAY)");
}
function login_clear($conn, $scope, $ident)
    {
    $ident = strtolower(substr($ident, 0, 100));
    $del = $conn->prepare("DELETE FROM login_attempts WHERE scope = ? AND ident = ?");
    $del->bind_param("ss", $scope, $ident);
    $del->execute();
    $del->close();
}

$conn = new mysqli($host, $db_user, $db_pass, $db_name);
$conn->set_charset("utf8mb4");
?>
