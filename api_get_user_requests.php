<?php
// api_get_user_requests.php - Fetch user's requests with updates and notifications
session_start();
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
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
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all requests for the logged-in user
$stmt = $conn->prepare("
    SELECT 
        r.id,
        r.ticket_id,
        r.requesttype as type,
        r.status,
        r.priority,
        r.submitted_at,
        r.updated_at
    FROM requests r
    WHERE r.user_id = ?
    ORDER BY r.updated_at DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$requests = [];
while ($row = $result->fetch_assoc()) {
    $request_id = $row['id'];
    
    // Get all updates for this request
    $updates_stmt = $conn->prepare("
        SELECT 
            id,
            status,
            message,
            updated_by,
            created_at
        FROM request_updates
        WHERE request_id = ?
        ORDER BY created_at DESC
    ");
    
    $updates_stmt->bind_param("i", $request_id);
    $updates_stmt->execute();
    $updates_result = $updates_stmt->get_result();
    
    $updates = [];
    while ($update = $updates_result->fetch_assoc()) {
        $updates[] = [
            'id' => (int)$update['id'],
            'status' => $update['status'],
            'message' => $update['message'],
            'updated_by' => $update['updated_by'],
            'created_at' => $update['created_at']
        ];
    }
    $updates_stmt->close();
    
    $requests[] = [
        'id' => (int)$row['id'],
        'ticket_id' => $row['ticket_id'],
        'type' => $row['type'],
        'status' => $row['status'],
        'priority' => $row['priority'],
        'submitted_at' => $row['submitted_at'],
        'updated_at' => $row['updated_at'],
        'updates' => $updates
    ];
}

$stmt->close();

// Get urgent notifications from admin (REQUEST_UPDATE type from audit_trail)
$notifications_stmt = $conn->prepare("
    SELECT 
        at.id,
        at.action_description as title,
        at.action_description as description,
        at.target_id as ticket_id,
        at.created_at as date,
        at.action_type as type
    FROM audit_trail at
    WHERE at.action_type IN ('STATUS_CHANGE', 'PRIORITY_CHANGE', 'REQUEST_UPDATE')
    AND at.target_id IN (
        SELECT ticket_id FROM requests WHERE user_id = ?
    )
    AND at.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ORDER BY at.created_at DESC
    LIMIT 10
");

$notifications_stmt->bind_param("i", $user_id);
$notifications_stmt->execute();
$notifications_result = $notifications_stmt->get_result();

$notifications = [];
while ($notif = $notifications_result->fetch_assoc()) {
    $notifications[] = [
        'id' => (int)$notif['id'],
        'type' => $notif['type'],
        'title' => $notif['title'],
        'description' => $notif['description'],
        'ticketId' => $notif['ticket_id'],
        'date' => date('M j, Y g:i A', strtotime($notif['date']))
    ];
}

$notifications_stmt->close();
$conn->close();

// Return response
echo json_encode([
    'requests' => $requests,
    'notifications' => $notifications
]);
?>