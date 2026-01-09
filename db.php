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
if (! $conn->set_charset('utf8mb4')) {
    // fallback to utf8 if utf8mb4 isn't available
    $conn->set_charset('utf8');
}
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}
?>