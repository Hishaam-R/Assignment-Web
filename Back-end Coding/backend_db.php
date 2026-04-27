<?php
// backend_db.php - PDO connection used by all pages.

// Database connection settings
$host = 'localhost';
$db   = 'barbershop_db';
$user = 'root';
$pass = ''; // XAMPP default MySQL (MariaDB) configuration sets the root user with no password.
$charset = 'utf8mb4';

// Build DSN string for PDO
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// PDO configuration options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw errors as exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // fetch results as associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false, // use real prepared statements
];
try {
      // Create new PDO connection
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
      // Show error message if connection fails (avoid showing details in production)
     echo "DB Connection failed: " . $e->getMessage();
     exit;
}
session_start(); // start session for auth
?>