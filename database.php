<?php
// INFINEX Roadside Assistance
// Database connection
// File: config/database.php

$host = '127.0.0.1';
$port = 3306;
$dbname = 'infinex_db';
$username = 'root';
$password = ''; // Default XAMPP MySQL root password is usually empty

$charset = 'utf8mb4';

$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);

    // Compatibility if another INFINEX file uses $conn.
    $conn = $pdo;

} catch (PDOException $e) {
    // Local XAMPP development error.
    die(
        'Database connection failed. ' .
        'Please make sure XAMPP MySQL is running and that ' .
        'the database name, username, password, and port in ' .
        'config/database.php are correct.<br><br>' .
        'Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')
    );
}
?>
