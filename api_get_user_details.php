<?php
session_start();
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

// Get user ID from query parameter
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($user_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid user ID']);
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

// Fetch user details
$stmt = $conn->prepare("
    SELECT 
        id,
        first_name,
        middle_name,
        last_name,
        email,
        barangay,
        id_type,
        resident_type,
        file_path,
        created_at,
        updated_at,
        usertype
    FROM account 
    WHERE id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
    $stmt->close();
    $conn->close();
    exit();
}

$user = $result->fetch_assoc();

// Get user's request statistics
$stats_stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_requests,
        SUM(CASE WHEN status = 'COMPLETED' THEN 1 ELSE 0 END) as completed_requests,
        SUM(CASE WHEN status IN ('PENDING', 'UNDER REVIEW') THEN 1 ELSE 0 END) as pending_requests,
        SUM(CASE WHEN status = 'IN PROGRESS' THEN 1 ELSE 0 END) as in_progress_requests
    FROM requests 
    WHERE user_id = ?
");

$stats_stmt->bind_param("i", $user_id);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result();
$stats = $stats_result->fetch_assoc();

// Get user's recent requests
$requests_stmt = $conn->prepare("
    SELECT 
        ticket_id,
        requesttype,
        status,
        submitted_at
    FROM requests 
    WHERE user_id = ?
    ORDER BY submitted_at DESC
    LIMIT 5
");

$requests_stmt->bind_param("i", $user_id);
$requests_stmt->execute();
$requests_result = $requests_stmt->get_result();

$recent_requests = [];
while ($req = $requests_result->fetch_assoc()) {
    $recent_requests[] = [
        'ticketId' => $req['ticket_id'],
        'type' => $req['requesttype'],
        'status' => $req['status'],
        'date' => $req['submitted_at']
    ];
}

// Prepare response
$response = [
    'id' => (int)$user['id'],
    'firstName' => $user['first_name'],
    'middleName' => $user['middle_name'],
    'lastName' => $user['last_name'],
    'fullName' => trim($user['first_name'] . ' ' . ($user['middle_name'] ? $user['middle_name'] . ' ' : '') . $user['last_name']),
    'email' => $user['email'],
    'barangay' => $user['barangay'],
    'idType' => $user['id_type'],
    'residentType' => $user['resident_type'],
    'isResident' => strtolower($user['resident_type']) === 'resident',
    'filePath' => $user['file_path'],
    'joinedDate' => $user['created_at'],
    'updatedDate' => $user['updated_at'],
    'userType' => $user['usertype'],
    'stats' => [
        'totalRequests' => (int)$stats['total_requests'],
        'completedRequests' => (int)$stats['completed_requests'],
        'pendingRequests' => (int)$stats['pending_requests'],
        'inProgressRequests' => (int)$stats['in_progress_requests']
    ],
    'recentRequests' => $recent_requests
];

$stmt->close();
$stats_stmt->close();
$requests_stmt->close();
$conn->close();

echo json_encode($response);
?>