<?php
$host = '127.0.0.1';
$database = 'eventseat';
$username = 'root';
$password = '';

$pdo = new PDO(
    "mysql:host=$host;dbname=$database;charset=utf8mb4",
    $username,
    $password,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
     PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
     PDO::ATTR_EMULATE_PREPARES => false]
);
$pdo->exec("SET time_zone = '+08:00'");

