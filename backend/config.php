<?php
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '1234sofia';
$db_name = 'astropic_db';

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

//check connection
if(!$conn){
    die('Connection failed: '.mysqli_connect_error());
}
$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!mysqli_query($conn, $sql_users)) {
    die("Error creating users table: " . mysqli_error($conn));
}
$sql_favorites = "CREATE TABLE IF NOT EXISTS favorites (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    title VARCHAR(255) NOT NULL,
    date DATE NOT NULL,
    explanation TEXT NOT NULL,
    url VARCHAR(255) NOT NULL,
    media_type VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if (!mysqli_query($conn, $sql_favorites)) {
    die("Error creating favorites table: " . mysqli_error($conn));
}
?>