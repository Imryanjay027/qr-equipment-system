<?php
// Kunin ang mga configuration mula sa Environment Variables ng Coolify
$host     = getenv('DB_HOST') ?: 'mariadb-database-wfbyfhr4404k2y9u2spiv101';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: ''; // Kung may password ka sa Coolify, ilagay rito o hayaang basahin ng getenv
$dbname   = getenv('DB_NAME') ?: 'qrcode';
$port     = getenv('DB_PORT') ?: '3306';

// Piliin kung PDO o MySQLi ang gusto mong gamitin:

// --- OPTION A: Gamit ang PDO (Recommended) ---
try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $conn = new PDO($dsn, $username, $password);
    
    // Set PDO error mode to exception para madaling ma-debug kung may mali
    $conn->setAttribute(PDO.ATTR_ERRMODE, PDO.ERRMODE_EXCEPTION);
    
    // Pwede mong tanggalin o i-comment out itong echo kapag gumagana na para hindi makaistorbo sa UI
    // echo "Connected successfully using PDO!";

} catch (PDOException $e) {
    // Kapag nag-fail, ipapakita ang eksaktong error message
    die("Connection failed: " . $e->getMessage());
}


/*
// --- OPTION B: Gamit ang MySQLi (I-uncomment ito kung MySQLi ang gamit ng buong sistema mo) ---
$conn = new mysqli($host, $username, $password, $dbname, $port);

// Suriin ang koneksyon
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// echo "Connected successfully using MySQLi!";
*/
?>
