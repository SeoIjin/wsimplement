<?php
// api_get_audit_trail.php - Fetch audit trail logs
session_start();
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

// Require admin session
if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
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

// Get filter parameters
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

// Build query based on filter
$query = "SELECT 
    id,
    admin_id,
    admin_email,
    action_type,
    action_description,
    target_type,
    target_id,
    old_value,
    new_value,
    ip_address,
    user_agent,
    created_at
FROM audit_trail";

$where_clause = "";
$bind_types = "";
$bind_values = [];

if ($filter !== 'all') {
    // Check if filter contains multiple types (comma-separated)
    $filter_types = explode(',', $filter);
    
    if (count($filter_types) > 1) {
        // Multiple types - use IN clause
        $placeholders = implode(',', array_fill(0, count($filter_types), '?'));
        $where_clause = " WHERE action_type IN ($placeholders)";
        $bind_types = str_repeat('s', count($filter_types));
        $bind_values = $filter_types;
    } else {
        // Single type
        $where_clause = " WHERE action_type = ?";
        $bind_types = "s";
        $bind_values = [$filter];
    }
}

$query .= $where_clause . " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$bind_types .= "ii";
array_push($bind_values, $limit, $offset);

$stmt = $conn->prepare($query);

if ($bind_types) {
    $stmt->bind_param($bind_types, ...$bind_values);
}

$stmt->execute();
$result = $stmt->get_result();

$logs = [];
while ($row = $result->fetch_assoc()) {
    $logs[] = [
        'id' => (int)$row['id'],
        'adminId' => (int)$row['admin_id'],
        'adminEmail' => $row['admin_email'],
        'actionType' => $row['action_type'],
        'actionDescription' => $row['action_description'],
        'targetType' => $row['target_type'],
        'targetId' => $row['target_id'],
        'oldValue' => $row['old_value'],
        'newValue' => $row['new_value'],
        'ipAddress' => $row['ip_address'],
        'userAgent' => $row['user_agent'],
        'timestamp' => $row['created_at']
    ];
}

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM audit_trail" . $where_clause;
$count_stmt = $conn->prepare($count_query);

if ($bind_types && $filter !== 'all') {
    // Remove the last two 'ii' from bind_types for the count query
    $count_bind_types = substr($bind_types, 0, -2);
    // Remove limit and offset from bind_values
    $count_bind_values = array_slice($bind_values, 0, -2);
    
    if ($count_bind_types) {
        $count_stmt->bind_param($count_bind_types, ...$count_bind_values);
    }
}

$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total = $count_result->fetch_assoc()['total'];

$stmt->close();
$count_stmt->close();
$conn->close();

echo json_encode([
    'logs' => $logs,
    'total' => (int)$total,
    'limit' => $limit,
    'offset' => $offset
]);
?>