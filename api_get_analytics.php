<?php
// api_get_analytics.php
session_start();
header('Content-Type: application/json');

// Require admin session
if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Database connection
$host = 'localhost';
$dbname = 'users';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $timeframe = $_GET['timeframe'] ?? 'day';
    
    $data = [];
    
    if ($timeframe === 'day') {
        // Get hourly data for today
        $sql = "SELECT 
                    HOUR(submitted_at) as hour,
                    COUNT(*) as count
                FROM requests
                WHERE DATE(submitted_at) = CURDATE()
                GROUP BY HOUR(submitted_at)
                ORDER BY hour";
        
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fill in all 24 hours
        for ($i = 0; $i < 24; $i++) {
            $found = false;
            foreach ($results as $row) {
                if ((int)$row['hour'] === $i) {
                    $data[] = [
                        'label' => sprintf('%02d:00', $i),
                        'value' => (int)$row['count']
                    ];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $data[] = ['label' => sprintf('%02d:00', $i), 'value' => 0];
            }
        }
        
    } elseif ($timeframe === 'week') {
        // Get daily data for last 7 days
        $sql = "SELECT 
                    DATE(submitted_at) as date,
                    DAYNAME(submitted_at) as day_name,
                    COUNT(*) as count
                FROM requests
                WHERE submitted_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY DATE(submitted_at)
                ORDER BY date";
        
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fill in all 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $dayName = date('D', strtotime("-$i days"));
            $found = false;
            
            foreach ($results as $row) {
                if ($row['date'] === $date) {
                    $data[] = [
                        'label' => $dayName,
                        'value' => (int)$row['count']
                    ];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $data[] = ['label' => $dayName, 'value' => 0];
            }
        }
        
    } elseif ($timeframe === 'month') {
        // Get daily data for last 30 days
        $sql = "SELECT 
                    DATE(submitted_at) as date,
                    COUNT(*) as count
                FROM requests
                WHERE submitted_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY DATE(submitted_at)
                ORDER BY date";
        
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fill in all 30 days
        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $label = date('M d', strtotime("-$i days"));
            $found = false;
            
            foreach ($results as $row) {
                if ($row['date'] === $date) {
                    $data[] = [
                        'label' => $label,
                        'value' => (int)$row['count']
                    ];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $data[] = ['label' => $label, 'value' => 0];
            }
        }
    }
    
    echo json_encode($data);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>