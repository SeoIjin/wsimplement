<?php
session_start();
require_once 'audit_trail_helper.php';

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
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

// Get notification ID from query parameter
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid notification ID']);
    exit();
}

// Get notification title before deleting
$stmt = $conn->prepare("SELECT title FROM notifications WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$notification = $result->fetch_assoc();
$stmt->close();

if (!$notification) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Notification not found']);
    exit();
}

$title = $notification['title'];

// Delete notification
$stmt = $conn->prepare("DELETE FROM notifications WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        // Log notification deletion
        $admin_id = $_SESSION['user_id'];
        $admin_email = $_SESSION['user_email'] ?? 'Unknown';
        logNotificationDelete($admin_id, $admin_email, $id, $title);
        
        echo json_encode([
            'success' => true,
            'message' => 'Notification deleted successfully'
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Notification not found'
        ]);
    }
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to delete notification: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>