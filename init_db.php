<?php
require_once 'db_connection.php'; // Siguraduhing konektado sa live database mo

try {
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(50) DEFAULT 'custodian',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "<h2>Tagumpay! Nalikha na ang 'users' table sa database.</h2>";
} catch (PDOException $e) {
    echo "Error sa paggawa ng table: " . $e->getMessage();
}
?>
