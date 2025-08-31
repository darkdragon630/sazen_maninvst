<?php
$host = 'db.fr-pari1.bengt.wasmernet.com';
$db = 'dbNANgVXJBJEnGrupi8tAkXpz';
$user = '4569b4f873728000025f9d30c379';
$pass = '068b4569-b4f8-763b-8000-2f1c3f10046c';
$port = '10272';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;port=$port;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $koneksi = new PDO($dsn, $user, $pass, $port, $options);
    $koneksi->exec("SET NAMES 'utf8'");
} catch (PDOException $e) {
    die('koneksi gagal: ' . $e->getMessage());
}

?>