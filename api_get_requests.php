<?php
// api_get_requests.php
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate');

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'users';

$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Fetch requests with user information from account table
$sql = "
    SELECT 
        r.id,
        r.ticket_id,
        CONCAT(
            a.first_name, 
            ' ', 
            IFNULL(CONCAT(a.middle_name, ' '), ''), 
            a.last_name
        ) AS name,
        r.requesttype AS type,
        IFNULL(NULLIF(r.priority, ''), 'MEDIUM') AS priority,
        r.status,
        DATE_FORMAT(r.submitted_at, '%b %d, %Y %h:%i %p') AS submitted,
        r.submitted_at AS raw_date
    FROM requests AS r
    LEFT JOIN account AS a ON r.user_id = a.id
    ORDER BY r.submitted_at DESC
";

$result = $conn->query($sql);

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Query failed: ' . $conn->error]);
    exit;
}

$requests = [];
while ($row = $result->fetch_assoc()) {
    // Normalize status for frontend filters
    $status = strtoupper(trim($row['status']));
    
    // Ensure priority is uppercase
    $row['priority'] = strtoupper($row['priority']);
    
    // Map status to display format
    switch ($status) {
        case 'PENDING':
            $row['status'] = 'Pending';
            break;
        case 'IN PROGRESS':
            $row['status'] = 'In Progress';
            break;
        case 'READY':
            $row['status'] = 'Ready';
            break;
        case 'COMPLETED':
            $row['status'] = 'Completed';
            break;
        default:
            $row['status'] = ucwords(strtolower($status));
            break;
    }

    // Remove raw_date from response
    unset($row['raw_date']);
    
    $requests[] = $row;
}

echo json_encode($requests);
$conn->close();
?>