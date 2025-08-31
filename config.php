<?php
$host = 'localhost';
$db = 'saaz';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $koneksi = new PDO($dsn, $user, $pass, $options);
    $koneksi->exec("SET NAMES 'utf8'");
} catch (PDOException $e) {
    die('koneksi gagal: ' . $e->getMessage());
}

?>