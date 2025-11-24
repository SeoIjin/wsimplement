<?php
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "users";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

// Fetch all notifications ordered by created date (newest first)
$sql = "SELECT id, type, title, date, description, created_at 
        FROM notifications 
        ORDER BY created_at DESC";

$result = $conn->query($sql);

$notifications = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'id' => (int)$row['id'],
            'type' => $row['type'],
            'title' => $row['title'],
            'date' => $row['date'],
            'description' => $row['description'],
            'created_at' => $row['created_at']
        ];
    }
}

$conn->close();

echo json_encode($notifications);
?>