<?php
$host = 'localhost';
$db   = 'nativ_php_app_vf';
$user = 'root';
$pass = '';     // default for XAMPP MySQL, change if needed

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
?>