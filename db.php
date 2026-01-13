<?php
session_start();
$servername = "localhost";
$username = "root"; // Thay bằng username MySQL của bạn
$password = ""; // Thay bằng password
$dbname = "log";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Use utf8mb4 for proper unicode support
if (!$conn->set_charset('utf8mb4')) {
    // fallback to utf8 if utf8mb4 isn't available
    $conn->set_charset('utf8');
}
function getCurrentUserId()
{
    return $_SESSION['user_id'] ?? null;
}

// Check for Remember Me cookie
function checkRememberMe()
{
    global $conn;
    if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
        $token = $_COOKIE['remember_token'];
        $stmt = $conn->prepare("SELECT id, username FROM users WHERE remember_token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows > 0) {
            $user = $res->fetch_assoc();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            return true;
        }
    }
    return isset($_SESSION['user_id']);
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']) || checkRememberMe();
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}
?>