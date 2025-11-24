<?php
session_start();
require_once 'audit_trail_helper.php';

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized: Please login first']);
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "users";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
    exit();
}

$type = isset($data['type']) ? $data['type'] : '';
$title = isset($data['title']) ? trim($data['title']) : '';
$date = isset($data['date']) ? trim($data['date']) : '';
$description = isset($data['description']) ? trim($data['description']) : '';

// Validate inputs
if (empty($type) || empty($title) || empty($date) || empty($description)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'All fields are required']);
    exit();
}

if (!in_array($type, ['NEWS', 'EVENT'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid notification type']);
    exit();
}

// Insert notification
$stmt = $conn->prepare("INSERT INTO notifications (type, title, date, description) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $type, $title, $date, $description);

if ($stmt->execute()) {
    $notification_id = $conn->insert_id;
    
    // Log notification addition
    $admin_id = $_SESSION['user_id'];
    $admin_email = $_SESSION['user_email'] ?? 'Unknown';
    logNotificationAdd($admin_id, $admin_email, $type, $title);
    
    echo json_encode([
        'success' => true,
        'id' => $notification_id,
        'message' => 'Notification added successfully'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to add notification: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>