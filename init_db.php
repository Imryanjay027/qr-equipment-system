<?php
require_once 'db_connection.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(50) DEFAULT 'borrower',
        education_level VARCHAR(50) DEFAULT 'College',
        course_year_section VARCHAR(255) NULL,
        profile_picture LONGTEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    $pdo->exec($sql);
    echo "<h3>Tagumpay! Nalikha na ang 'users' table sa live database.</h3>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
