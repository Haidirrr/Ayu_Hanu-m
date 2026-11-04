
<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "ayu hanu'm";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define("WHATSAPP_ADMIN", "6281234567890");
?>
