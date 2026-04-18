<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('SITE_NAME', 'CleanBook Pro');
define('SITE_URL', 'http://localhost/CB/');
define('SITE_EMAIL', 'support@cleanbook.com');
define('SITE_PHONE', '+254-712-345-678');

define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'] . '/CB/');
define('CONFIG_PATH', ROOT_PATH . 'config/');
define('INCLUDES_PATH', ROOT_PATH . 'includes/');
define('ASSETS_PATH', ROOT_PATH . 'assets/');

date_default_timezone_set('Africa/Nairobi');

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once CONFIG_PATH . 'database.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getUserType() {
    return $_SESSION['user_type'] ?? null;
}

function getUserName() {
    return $_SESSION['user_name'] ?? null;
}

function redirect($url) {
    header("Location: " . SITE_URL . $url);
    exit();
}

function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function formatMoney($amount) {
    return 'KES ' . number_format($amount, 2);
}

function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}
?>