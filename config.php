<?php
// config.php
$host = "127.0.0.1:3306";
$dbname = "divisional_office";
$username = "root"; // Change as per your setup
$password = ""; // Change as per your setup

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>