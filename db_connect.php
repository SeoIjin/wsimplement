<?php 

$servername = "localhost";
$username = "root";  
$password = "";      
$dbname = "users";

try {
    // Create PDO instance
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
} catch (PDOException $e) {
    // Log error and show user-friendly message
    error_log("Database Connection Error: " . $e->getMessage());
    
    // In production, don't expose database errors to users
    if (php_sapi_name() === 'cli') {
        die("Database connection failed: " . $e->getMessage() . "\n");
    } else {
        http_response_code(500);
        die(json_encode(['error' => 'Database connection failed. Please contact administrator.']));
    }
}

?>