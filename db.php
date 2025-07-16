<?php
$host = "localhost";
$dbname = "dbbgsoperuclga";
$username = "uaozeqcbxyhyg";
$password = "f4kld3wzz1v3";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
