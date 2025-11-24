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

// Fetch all users (excluding admins for security)
$sql = "SELECT 
    id,
    CONCAT(first_name, ' ', IFNULL(CONCAT(middle_name, ' '), ''), last_name) AS name,
    first_name,
    middle_name,
    last_name,
    email,
    barangay,
    id_type,
    resident_type,
    file_path,
    created_at,
    usertype
FROM account 
WHERE usertype != 'admin' OR usertype = ''
ORDER BY created_at DESC";

$result = $conn->query($sql);

$users = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'firstName' => $row['first_name'],
            'middleName' => $row['middle_name'],
            'lastName' => $row['last_name'],
            'email' => $row['email'],
            'barangay' => $row['barangay'],
            'idType' => $row['id_type'],
            'isResident' => strtolower($row['resident_type']) === 'resident',
            'residentType' => $row['resident_type'],
            'filePath' => $row['file_path'],
            'joinedDate' => $row['created_at'],
            'userType' => $row['usertype']
        ];
    }
}

$conn->close();

echo json_encode($users);
?>