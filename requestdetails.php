<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: sign-in.php");
    exit();
}

// Check if request ID is provided
if (!isset($_GET['id'])) {
    header("Location: trackreq.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "users";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$request_id = $_GET['id'];

// Check if user is admin
$stmt = $conn->prepare("SELECT usertype FROM account WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$is_admin = ($user_data['usertype'] === 'admin');
$stmt->close();

// Handle status update
$update_success = false;
$update_error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $update_message = trim($_POST['message']);
    $updated_by = $is_admin ? 'Admin' : 'System';
    
    if (!empty($update_message)) {
        // Update request status
        $stmt = $conn->prepare("UPDATE requests SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $new_status, $request_id);
        
        if ($stmt->execute()) {
            // Insert update record
            $stmt2 = $conn->prepare("INSERT INTO request_updates (request_id, status, message, updated_by) VALUES (?, ?, ?, ?)");
            $stmt2->bind_param("isss", $request_id, $new_status, $update_message, $updated_by);
            $stmt2->execute();
            $stmt2->close();
            
            $update_success = true;
        } else {
            $update_error = "Failed to update request.";
        }
        $stmt->close();
    }
}

// Handle mark as done
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mark_done'])) {
    $done_status = 'COMPLETED';
    $done_message = 'Request has been completed and is ready for pickup/delivery.';
    $updated_by = 'Admin';
    
    $stmt = $conn->prepare("UPDATE requests SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("si", $done_status, $request_id);
    
    if ($stmt->execute()) {
        $stmt2 = $conn->prepare("INSERT INTO request_updates (request_id, status, message, updated_by) VALUES (?, ?, ?, ?)");
        $stmt2->bind_param("isss", $request_id, $done_status, $done_message, $updated_by);
        $stmt2->execute();
        $stmt2->close();
        
        if ($is_admin) {
            header("Location: admin-dashboard.php");
            exit();
        }
    }
    $stmt->close();
}

// Fetch request details with user information
$query = "SELECT r.*, 
          a.email, 
          CONCAT(a.first_name, ' ', a.last_name) as fullname,
          a.barangay
          FROM requests r
          LEFT JOIN account a ON r.user_id = a.id
          WHERE r.id = ?";

if (!$is_admin) {
    $query .= " AND r.user_id = ?";
}

$stmt = $conn->prepare($query);
if (!$is_admin) {
    $stmt->bind_param("ii", $request_id, $user_id);
} else {
    $stmt->bind_param("i", $request_id);
}

$stmt->execute();
$result = $stmt->get_result();
$request = $result->fetch_assoc();

if (!$request) {
    header("Location: trackreq.php");
    exit();
}

$stmt->close();

// Fetch request updates
$updates = [];
$stmt = $conn->prepare("SELECT * FROM request_updates WHERE request_id = ? ORDER BY created_at ASC");
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $updates[] = $row;
}
$stmt->close();

$conn->close();

// Helper function for status badge styling
function getStatusBadgeStyle($status) {
    $styles = [
        'PENDING' => ['bg' => '#e0f2fe', 'text' => '#0369a1', 'border' => '#7dd3fc'],
        'UNDER REVIEW' => ['bg' => '#fef3c7', 'text' => '#d97706', 'border' => '#fbbf24'],
        'IN PROGRESS' => ['bg' => '#ffedd5', 'text' => '#ea580c', 'border' => '#fb923c'],
        'READY' => ['bg' => '#e0e7ff', 'text' => '#4f46e5', 'border' => '#a5b4fc'],
        'COMPLETED' => ['bg' => '#d1fae5', 'text' => '#059669', 'border' => '#6ee7b7']
    ];
    return $styles[strtoupper($status)] ?? ['bg' => '#f3f4f6', 'text' => '#4b5563', 'border' => '#d1d5db'];
}

// Helper function for priority background color
function getPriorityBgColor($priority) {
    $colors = [
        'Low' => '#fef9c3',
        'Medium' => '#fed7aa',
        'High' => '#fecaca'
    ];
    return $colors[$priority] ?? '#f3f4f6';
}

$statusStyle = getStatusBadgeStyle($request['status']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Details - <?php echo htmlspecialchars($request['ticket_id']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            background: #C8E6C9;
        }

        /* Header */
        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: white;
            padding: 1rem 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .back-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f3f4f6;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s;
        }

        .back-btn:hover {
            background: #e5e7eb;
        }

        .header-left img {
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            transition: opacity 0.3s;
        }

        .header-left img:hover {
            opacity: 0.8;
        }

        .header-title h1 {
            font-size: 1.25rem;
            color: #2c5f2d;
            margin-bottom: 0.25rem;
        }

        .header-title p {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .header-actions {
            display: flex;
            gap: 0.75rem;
        }

        .btn-done {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            background: #059669;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
        }

        .btn-done:hover {
            background: #047857;
        }

        /* Main Content */
        main {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Top Header Card */
        .top-header {
            background: #A5D6A7;
            border-radius: 1rem 1rem 0 0;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: start;
        }

        .top-header h2 {
            font-size: 1.5rem;
            color: #1b5e20;
            margin-bottom: 0.25rem;
        }

        .top-header p {
            color: #2e7d32;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border: 2px solid;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        /* Content Grid */
        .content-wrapper {
            background: white;
            border-radius: 0 0 1rem 1rem;
            border: 2px solid #A5D6A7;
            border-top: none;
            padding: 1.5rem;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1.5rem;
        }

        /* Card Styles */
        .info-card {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 1.25rem;
        }

        .info-card h3 {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #2e7d32;
            margin-bottom: 1rem;
            font-size: 1rem;
        }

        .info-item {
            margin-bottom: 0.75rem;
        }

        .info-label {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .info-value {
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .verified-badge {
            color: #059669;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        /* Description Card */
        .description-box {
            background: #d1fae5;
            border: 1px solid #a7f3d0;
            border-radius: 0.5rem;
            padding: 1rem;
        }

        .description-box p {
            color: #065f46;
            font-size: 0.875rem;
            white-space: pre-wrap;
            line-height: 1.6;
        }

        .description-meta {
            font-size: 0.75rem;
            color: #059669;
            margin-top: 0.75rem;
            text-align: right;
        }

        /* Update History */
        .update-history {
            max-height: 600px;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        .update-item {
            border-left: 4px solid;
            padding-left: 1rem;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }

        .update-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-top: 0.375rem;
            flex-shrink: 0;
        }

        .update-content {
            flex: 1;
        }

        .update-status {
            text-transform: uppercase;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .update-time {
            font-size: 0.75rem;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }

        .update-message {
            font-size: 0.875rem;
            color: #374151;
            line-height: 1.5;
        }

        .update-by {
            font-size: 0.75rem;
            color: #059669;
            margin-top: 0.25rem;
        }

        /* Request Type Badge */
        .type-badge {
            background: #dcfce7;
            color: #166534;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            display: inline-block;
            font-weight: 500;
        }

        /* Priority Box */
        .priority-box {
            width: 100%;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            border: 2px solid #e5e7eb;
            font-weight: 500;
        }

        /* Update Form */
        .update-form {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .update-form select,
        .update-form textarea {
            width: 100%;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            border: 2px solid #e5e7eb;
            outline: none;
            font-family: 'Poppins', sans-serif;
        }

        .update-form select:focus,
        .update-form textarea:focus {
            border-color: #228650;
        }

        .update-form textarea {
            min-height: 80px;
            resize: vertical;
        }

        .btn-send {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            background: #059669;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
            align-self: flex-end;
        }

        .btn-send:hover:not(:disabled) {
            background: #047857;
        }

        .btn-send:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Success Message */
        .success-message {
            background: #d1fae5;
            color: #065f46;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid #059669;
        }

        .error-message {
            background: #fee2e2;
            color: #991b1b;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid #dc2626;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }

            header {
                flex-direction: column;
                gap: 1rem;
                padding: 1rem;
            }

            main {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-left">
            <button 
                class="back-btn" 
                onclick="window.location.href='<?php echo $is_admin ? 'admin-dashboard.php' : 'trackreq.php'; ?>'"
                title="Go back"
            >
                <i class="fas fa-arrow-left" style="color: #374151;"></i>
            </button>
            <img 
                src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRTDCuh4kIpAtR-QmjA1kTjE_8-HSd8LSt3Gw&s" 
                alt="Logo"
                onclick="window.location.href='homepage.php'"
            />
            <div class="header-title">
                <h1>Request Details</h1>
                <p>Ticket ID: <?php echo htmlspecialchars($request['ticket_id']); ?></p>
            </div>
        </div>
        
        <?php if ($is_admin && $request['status'] !== 'COMPLETED'): ?>
        <div class="header-actions">
            <form method="POST" style="display: inline;">
                <button type="submit" name="mark_done" class="btn-done">
                    <i class="fas fa-check-double"></i>
                    Mark as Done
                </button>
            </form>
        </div>
        <?php endif; ?>
    </header>

    <!-- Main Content -->
    <main>
        <?php if ($update_success): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> Request updated successfully!
            </div>
        <?php endif; ?>

        <?php if ($update_error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($update_error); ?>
            </div>
        <?php endif; ?>

        <!-- Top Header Card -->
        <div class="top-header">
            <div>
                <h2>Request Details</h2>
                <p>Ticket ID: <?php echo htmlspecialchars($request['ticket_id']); ?></p>
            </div>
            <div 
                class="status-badge"
                style="background-color: <?php echo $statusStyle['bg']; ?>; 
                       color: <?php echo $statusStyle['text']; ?>; 
                       border-color: <?php echo $statusStyle['border']; ?>"
            >
                <span><?php echo htmlspecialchars($request['status']); ?></span>
                <i class="fas fa-check-circle"></i>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-wrapper">
            <div class="content-grid">
                <!-- Left Column -->
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <!-- Citizen Information -->
                    <div class="info-card">
                        <h3>
                            <i class="fas fa-user"></i>
                            Citizen Information
                        </h3>
                        
                        <div class="info-item">
                            <p class="info-label">Name</p>
                            <p class="info-value">
                                <?php echo htmlspecialchars($request['fullname']); ?>
                                <span class="verified-badge">
                                    Barangay Resident <i class="fas fa-check-circle"></i>
                                </span>
                            </p>
                        </div>

                        <div class="info-item">
                            <p class="info-label">
                                <i class="fas fa-phone"></i> Contact Number
                            </p>
                            <p class="info-value">
                                <?php echo htmlspecialchars($request['contact']); ?>
                                <span class="verified-badge">
                                    Verified <i class="fas fa-check-circle"></i>
                                </span>
                            </p>
                        </div>

                        <div class="info-item">
                            <p class="info-label">
                                <i class="fas fa-envelope"></i> Email
                            </p>
                            <p class="info-value">
                                <?php echo htmlspecialchars($request['email']); ?>
                                <span class="verified-badge">
                                    Verified <i class="fas fa-check-circle"></i>
                                </span>
                            </p>
                        </div>

                        <div class="info-item">
                            <p class="info-label">
                                <i class="fas fa-home"></i> Barangay
                            </p>
                            <p class="info-value"><?php echo htmlspecialchars($request['barangay']); ?></p>
                        </div>

                        <div class="info-item">
                            <p class="info-label">
                                <i class="fas fa-id-card"></i> User ID
                            </p>
                            <p class="info-value"><?php echo htmlspecialchars($request['user_id']); ?></p>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="info-card">
                        <h3>
                            <i class="fas fa-file-text"></i>
                            Description
                        </h3>
                        <div class="description-box">
                            <p><?php echo nl2br(htmlspecialchars($request['description'])); ?></p>
                            <p class="description-meta">
                                Submitted: <?php echo date('M j, Y, g:i A', strtotime($request['submitted_at'])); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Middle Column - Update History -->
                <div class="info-card">
                    <h3>Update History</h3>
                    <p style="font-size: 0.75rem; color: #6b7280; margin-bottom: 1rem;">
                        Track all status changes and updates
                    </p>
                    
                    <div class="update-history">
                        <?php 
                        $reversed_updates = array_reverse($updates);
                        foreach ($reversed_updates as $update): 
                            $updateStyle = getStatusBadgeStyle($update['status']);
                        ?>
                            <div 
                                class="update-item"
                                style="border-color: <?php echo $updateStyle['border']; ?>"
                            >
                                <div style="display: flex; gap: 0.5rem;">
                                    <div 
                                        class="update-dot"
                                        style="background-color: <?php echo $updateStyle['text']; ?>"
                                    ></div>
                                    <div class="update-content">
                                        <h4 
                                            class="update-status"
                                            style="color: <?php echo $updateStyle['text']; ?>"
                                        >
                                            <?php echo htmlspecialchars($update['status']); ?>
                                        </h4>
                                        <p class="update-time">
                                            <?php echo date('M j, Y, g:i A', strtotime($update['created_at'])); ?>
                                        </p>
                                        <p class="update-message">
                                            <?php echo nl2br(htmlspecialchars($update['message'])); ?>
                                        </p>
                                        <p class="update-by">
                                            Updated by: <?php echo htmlspecialchars($update['updated_by']); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php if (empty($updates)): ?>
                            <p style="text-align: center; color: #6b7280; padding: 2rem;">
                                No updates yet
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right Column -->
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <!-- Request Type -->
                    <div class="info-card">
                        <h3>
                            <i class="fas fa-file-alt"></i>
                            Request Type
                        </h3>
                        <div class="type-badge">
                            <?php echo htmlspecialchars($request['requesttype']); ?>
                        </div>
                    </div>

                    <!-- Priority Level -->
                    <?php if (!empty($request['priority'])): ?>
                    <div class="info-card">
                        <h3>
                            <i class="fas fa-exclamation-triangle"></i>
                            Priority Level
                        </h3>
                        <div 
                            class="priority-box"
                            style="background-color: <?php echo getPriorityBgColor($request['priority']); ?>"
                        >
                            <?php echo htmlspecialchars($request['priority']); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Update Status (Admin Only) -->
                    <?php if ($is_admin): ?>
                    <div class="info-card">
                        <h3>Update Status</h3>
                        <form method="POST" class="update-form" id="updateForm">
                            <select name="status" id="statusSelect" required>
                                <option value="PENDING" <?php echo $request['status'] === 'PENDING' ? 'selected' : ''; ?>>Pending</option>
                                <option value="UNDER REVIEW" <?php echo $request['status'] === 'UNDER REVIEW' ? 'selected' : ''; ?>>Under Review</option>
                                <option value="IN PROGRESS" <?php echo $request['status'] === 'IN PROGRESS' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="READY" <?php echo $request['status'] === 'READY' ? 'selected' : ''; ?>>Ready</option>
                                <option value="COMPLETED" <?php echo $request['status'] === 'COMPLETED' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                            <textarea 
                                name="message" 
                                id="messageInput"
                                placeholder="Enter update message"
                                required
                            ></textarea>
                            <button type="submit" name="update_status" class="btn-send" id="sendBtn" disabled>
                                <i class="fas fa-paper-plane"></i>
                                Send Update
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Enable/disable send button based on message input
        const messageInput = document.getElementById('messageInput');
        const sendBtn = document.getElementById('sendBtn');
        
        if (messageInput && sendBtn) {
            messageInput.addEventListener('input', function() {
                sendBtn.disabled = !this.value.trim();
            });
        }

        // Auto-scroll to top on page load
        window.scrollTo(0, 0);
    </script>
</body>
</html>