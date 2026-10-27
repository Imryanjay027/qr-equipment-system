<?php
$host = 'scvzklpphi95efixy0m7hpyg';
$db   = 'default';
$user = 'root'; 
$pass = 'qOAF8F2klYnvxBSlj15uEx88gJxMM95J6QC6TJ1FYRgX9cHvqgA3QlmOhnETItCV';

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Awtomatikong gagawin ang otp_table kapag wala pa ito
    $pdo->exec("CREATE TABLE IF NOT EXISTS otp_table (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        otp_code VARCHAR(10) NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

} catch (\PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}
?>
