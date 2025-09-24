<?php
// db.php
// Database connection using PDO for security and ease.
 
$host = 'localhost'; // Change if your host is different
$dbname = 'dbodkfxtsde4vs';
$username = 'um4u5gpwc3dwc';
$password = 'neqhgxo10ioe';
 
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
