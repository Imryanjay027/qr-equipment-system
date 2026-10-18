<?php
// db_connection.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$username = 'root';
$password = ''; // I-update kung may password ang live database mo
$database = 'qrcode';

try {
    $conn = new mysqli($host, $username, $password, $database);
} catch (mysqli_sql_exception $e) {
    // Fallback sakaling ang socket naman ang magka-problema sa cloud environment
    try {
        $conn = new mysqli('127.0.0.1', $username, $password, $database);
    } catch (mysqli_sql_exception $e2) {
        die("Database Connection Failed: " . $e2->getMessage());
    }
}
?>
