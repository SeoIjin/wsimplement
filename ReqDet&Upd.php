<?php
session_start();
require_once 'audit_trail_helper.php';

// Database connection
$host = '127.0.0.1';
$dbname = 'users';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get admin info
$admin_id = $_SESSION['user_id'] ?? 0;
$admin_email = $_SESSION['user_email'] ?? 'Unknown';

// Get ticket ID from URL or use first available
$ticket_id = $_GET['ticket_id'] ?? null;

// Handle POST requests (updates/deletes)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'delete_request') {
            $stmt = $pdo->prepare("SELECT requesttype FROM requests WHERE ticket_id = ?");
            $stmt->execute([$_POST['ticket_id']]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $stmt = $pdo->prepare("DELETE FROM requests WHERE ticket_id = ?");
            $stmt->execute([$_POST['ticket_id']]);
            
            logRequestDelete($admin_id, $admin_email, $_POST['ticket_id'], $request['requesttype']);
            
            header("Location: admindashboard.php?msg=deleted");
            exit;
            
        } elseif ($_POST['action'] === 'add_update') {
            $stmt = $pdo->prepare("SELECT id, status, priority FROM requests WHERE ticket_id = ?");
            $stmt->execute([$_POST['ticket_id']]);
            $request_data = $stmt->fetch(PDO::FETCH_ASSOC);
            $request_id = $request_data['id'];
            $old_status = $request_data['status'];
            $old_priority = $request_data['priority'];
            
            $stmt = $pdo->prepare("UPDATE requests SET status = ?, priority = ? WHERE ticket_id = ?");
            $stmt->execute([$_POST['new_status'], $_POST['priority'], $_POST['ticket_id']]);
            
            $update_message = $_POST['update_message'] ?? 'Status updated by admin';
            $stmt_insert = $pdo->prepare("INSERT INTO request_updates (request_id, status, message, updated_by) VALUES (?, ?, ?, ?)");
            $stmt_insert->execute([$request_id, $_POST['new_status'], $update_message, 'Admin']);
            
            if ($old_status !== $_POST['new_status']) {
                logRequestUpdate($admin_id, $admin_email, $_POST['ticket_id'], $old_status, $_POST['new_status'], $update_message);
            }
            
            if ($old_priority !== $_POST['priority']) {
                logPriorityChange($admin_id, $admin_email, $_POST['ticket_id'], $old_priority, $_POST['priority']);
            }
            
            header("Location: admindashboard.php?msg=update_added");
            exit;
        }
    }
}

// Fetch request details
if ($ticket_id) {
    $stmt = $pdo->prepare("SELECT r.*, a.email, a.barangay FROM requests r 
                           LEFT JOIN account a ON r.user_id = a.id 
                           WHERE r.ticket_id = ?");
    $stmt->execute([$ticket_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->query("SELECT r.*, a.email, a.barangay FROM requests r 
                         LEFT JOIN account a ON r.user_id = a.id 
                         ORDER BY r.submitted_at DESC LIMIT 1");
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($request) {
        $ticket_id = $request['ticket_id'];
    }
}

if (!$request) {
    die("No request found");
}

// Fetch updates
$stmt = $pdo->prepare("SELECT * FROM request_updates WHERE request_id = ? ORDER BY created_at DESC");
$stmt->execute([$request['id']]);
$updates = $stmt->fetchAll(PDO::FETCH_ASSOC);

function formatTimestamp($timestamp) {
    return date('M d, Y - g:i A', strtotime($timestamp));
}

function getStatusColor($status) {
    $colors = [
        'PENDING' => '#f59e0b',
        'UNDER REVIEW' => '#f59e0b',
        'IN PROGRESS' => '#ff6b4a',
        'READY' => '#3b82f6',
        'COMPLETED' => '#16a34a'
    ];
    return $colors[strtoupper($status)] ?? '#6b7280';
}

function getPriorityColor($priority) {
    $colors = [
        'LOW' => '#16a34a',
        'MEDIUM' => '#f59e0b',
        'HIGH' => '#ef4444'
    ];
    return $colors[strtoupper($priority)] ?? '#6b7280';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Request Details - <?= htmlspecialchars($ticket_id) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }
    
    body {
      min-height: 100vh;
      background: #DAF1DE;
      color: #2c3e50;
    }
    
    /* Header */
    header {
      background: white;
      border-bottom: 1px solid #e5e7eb;
      padding: 1rem 1.5rem;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .header-content {
      max-width: 1400px;
      margin: 0 auto;
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }
    
    .header-logo {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      object-fit: cover;
    }
    
    .back-btn {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      color: #16a34a;
      text-decoration: none;
      font-weight: 500;
      padding: 0.5rem 1rem;
      border-radius: 0.5rem;
      transition: background 0.2s;
    }
    
    .back-btn:hover {
      background: #f0fdf4;
    }
    
    .header-title {
      color: #14532d;
      font-size: 1.125rem;
      font-weight: 600;
    }
    
    /* Main Container */
    main {
      max-width: 1400px;
      margin: 0 auto;
      padding: 1.5rem;
    }
    
    .page-header {
      background: white;
      border-radius: 0.75rem;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .page-title h2 {
      color: #14532d;
      font-size: 1.5rem;
      font-weight: 600;
      margin-bottom: 0.25rem;
    }
    
    .page-title p {
      color: #6b7280;
      font-size: 0.875rem;
    }
    
    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem 1rem;
      border-radius: 9999px;
      font-weight: 600;
      font-size: 0.875rem;
      border: 2px solid;
    }
    
    /* Content Grid */
    .content-grid {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 1.5rem;
    }
    
    .section {
      background: white;
      border-radius: 0.75rem;
      padding: 1.5rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .section-header {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 1rem;
      padding-bottom: 0.75rem;
      border-bottom: 2px solid #f3f4f6;
    }
    
    .section-icon {
      color: #16a34a;
      font-size: 1.125rem;
    }
    
    .section-title {
      color: #14532d;
      font-size: 1rem;
      font-weight: 600;
    }
    
    /* Info Items */
    .info-item {
      margin-bottom: 0.75rem;
      padding-bottom: 0.75rem;
      border-bottom: 1px solid #f3f4f6;
    }
    
    .info-item:last-child {
      border-bottom: none;
      margin-bottom: 0;
      padding-bottom: 0;
    }
    
    .info-label {
      color: #6b7280;
      font-size: 0.75rem;
      margin-bottom: 0.25rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      font-weight: 500;
    }
    
    .info-value {
      color: #2c3e50;
      font-size: 0.875rem;
      font-weight: 500;
    }
    
    .badge-inline {
      display: inline-block;
      background: #d1fae5;
      color: #166534;
      padding: 0.125rem 0.5rem;
      border-radius: 0.25rem;
      font-size: 0.75rem;
      font-weight: 600;
      margin-left: 0.5rem;
    }
    
    /* Description */
    .description-card {
      background: #f0fdf4;
      border: 1px solid #d1fae5;
      border-radius: 0.5rem;
      padding: 1rem;
      margin-top: 0.5rem;
    }
    
    .description-text {
      color: #166534;
      font-size: 0.875rem;
      line-height: 1.6;
      margin-bottom: 0.5rem;
    }
    
    .description-time {
      color: #6b7280;
      font-size: 0.75rem;
      text-align: right;
    }
    
    /* Update History */
    .update-item {
      padding: 1rem;
      border-radius: 0.5rem;
      border-left: 4px solid;
      margin-bottom: 1rem;
      background: #f9fafb;
    }
    
    .update-status {
      font-weight: 600;
      margin-bottom: 0.25rem;
      font-size: 0.875rem;
    }
    
    .update-time {
      color: #6b7280;
      font-size: 0.75rem;
      margin-bottom: 0.5rem;
    }
    
    .update-message {
      color: #4b5563;
      font-size: 0.875rem;
      line-height: 1.5;
      margin-bottom: 0.25rem;
    }
    
    .update-by {
      color: #16a34a;
      font-size: 0.75rem;
      font-weight: 500;
    }
    
    /* Request Type Badge */
    .type-badge {
      background: #d1fae5;
      color: #166534;
      padding: 0.5rem 1rem;
      border-radius: 0.5rem;
      font-weight: 600;
      display: inline-block;
      font-size: 0.875rem;
    }
    
    /* Forms */
    .form-group {
      margin-bottom: 1rem;
    }
    
    .form-label {
      display: block;
      color: #14532d;
      font-weight: 600;
      font-size: 0.875rem;
      margin-bottom: 0.5rem;
    }
    
    .form-select,
    .form-textarea {
      width: 100%;
      padding: 0.625rem 0.75rem;
      border: 1px solid #e5e7eb;
      border-radius: 0.5rem;
      font-size: 0.875rem;
      background: white;
      font-family: 'Poppins', sans-serif;
      transition: border-color 0.2s;
    }
    
    .form-select:focus,
    .form-textarea:focus {
      outline: none;
      border-color: #16a34a;
      box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
    }
    
    .form-textarea {
      resize: vertical;
      min-height: 80px;
    }
    
    /* Buttons */
    .btn {
      width: 100%;
      padding: 0.625rem 1rem;
      border-radius: 0.5rem;
      border: none;
      font-weight: 600;
      font-size: 0.875rem;
      cursor: pointer;
      transition: all 0.2s;
      font-family: 'Poppins', sans-serif;
    }
    
    .btn-primary {
      background: #16a34a;
      color: white;
    }
    
    .btn-primary:hover {
      background: #15803d;
    }
    
    .btn-danger {
      background: #ef4444;
      color: white;
    }
    
    .btn-danger:hover {
      background: #dc2626;
    }
    
    .button-group {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
      margin-top: 1rem;
    }
    
    /* Update Panel */
    .update-panel {
      margin-top: 1rem;
      background: #f0fdf4;
      border: 1px solid #d1fae5;
      border-radius: 0.5rem;
      padding: 1rem;
      display: none;
    }
    
    .update-panel.show {
      display: block;
    }
    
    /* Responsive */
    @media (max-width: 1024px) {
      .content-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <header>
    <div class="header-content">
      <a href="admindashboard.php" class="back-btn">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Dashboard</span>
      </a>
      <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRTDCuh4kIpAtR-QmjA1kTjE_8-HSd8LSt3Gw&s" alt="seal" class="header-logo">
      <h1 class="header-title">Request Details & Updates</h1>
    </div>
  </header>

  <main>
    <!-- Page Header -->
    <div class="page-header">
      <div class="page-title">
        <h2>Request Details</h2>
        <p>Ticket ID: <?= htmlspecialchars($request['ticket_id']) ?></p>
      </div>
      <div 
        class="status-badge" 
        style="background: <?= getStatusColor($request['status']) ?>20; 
               color: <?= getStatusColor($request['status']) ?>; 
               border-color: <?= getStatusColor($request['status']) ?>"
      >
        <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
        <?= htmlspecialchars($request['status']) ?>
      </div>
    </div>

    <!-- Content Grid -->
    <div class="content-grid">
      <!-- Citizen Information -->
      <div class="section">
        <div class="section-header">
          <i class="fas fa-user section-icon"></i>
          <h3 class="section-title">Citizen Information</h3>
        </div>
        
        <div class="info-item">
          <div class="info-label">Full Name</div>
          <div class="info-value">
            <?= htmlspecialchars($request['fullname']) ?>
            <span class="badge-inline">Resident</span>
          </div>
        </div>

        <div class="info-item">
          <div class="info-label">Contact Number</div>
          <div class="info-value"><?= htmlspecialchars($request['contact']) ?></div>
        </div>

        <div class="info-item">
          <div class="info-label">Email Address</div>
          <div class="info-value"><?= htmlspecialchars($request['email'] ?? 'N/A') ?></div>
        </div>

        <div class="info-item">
          <div class="info-label">Barangay</div>
          <div class="info-value"><?= htmlspecialchars($request['barangay'] ?? 'N/A') ?></div>
        </div>

        <div class="info-item">
          <div class="info-label">User ID</div>
          <div class="info-value"><?= htmlspecialchars($request['user_id']) ?></div>
        </div>

        <div style="margin-top: 1rem;">
          <div class="section-header">
            <i class="fas fa-file-text section-icon"></i>
            <h3 class="section-title">Description</h3>
          </div>
          <div class="description-card">
            <p class="description-text"><?= nl2br(htmlspecialchars($request['description'])) ?></p>
            <p class="description-time">Submitted: <?= formatTimestamp($request['submitted_at']) ?></p>
          </div>
        </div>
      </div>

      <!-- Update History -->
      <div class="section">
        <div class="section-header">
          <i class="fas fa-history section-icon"></i>
          <h3 class="section-title">Update History</h3>
        </div>

        <?php if (!empty($updates)): ?>
          <?php foreach ($updates as $update): ?>
            <div 
              class="update-item" 
              style="border-color: <?= getStatusColor($update['status']) ?>"
            >
              <div class="update-status" style="color: <?= getStatusColor($update['status']) ?>">
                <?= htmlspecialchars($update['status']) ?>
              </div>
              <p class="update-time"><?= formatTimestamp($update['created_at']) ?></p>
              <p class="update-message"><?= htmlspecialchars($update['message']) ?></p>
              <p class="update-by">Updated by: <?= htmlspecialchars($update['updated_by']) ?></p>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div style="text-align: center; padding: 2rem; color: #6b7280;">
            <i class="fas fa-inbox" style="font-size: 2rem; opacity: 0.3; margin-bottom: 0.5rem;"></i>
            <p>No updates yet</p>
          </div>
        <?php endif; ?>
      </div>

      <!-- Request Management -->
      <div class="section">
        <!-- Request Type -->
        <div style="margin-bottom: 1.5rem;">
          <div class="section-header">
            <i class="fas fa-file-alt section-icon"></i>
            <h3 class="section-title">Request Type</h3>
          </div>
          <span class="type-badge"><?= htmlspecialchars($request['requesttype']) ?></span>
        </div>

        <!-- Priority Level -->
        <div style="margin-bottom: 1.5rem;">
          <div class="section-header">
            <i class="fas fa-exclamation-triangle section-icon"></i>
            <h3 class="section-title">Priority Level</h3>
          </div>
          <select id="prioritySelect" class="form-select">
            <option value="LOW" <?= $request['priority'] === 'LOW' ? 'selected' : '' ?>>Low Priority</option>
            <option value="MEDIUM" <?= $request['priority'] === 'MEDIUM' ? 'selected' : '' ?>>Medium Priority</option>
            <option value="HIGH" <?= $request['priority'] === 'HIGH' ? 'selected' : '' ?>>High Priority</option>
          </select>
        </div>

        <!-- Status Management -->
        <div>
          <div class="section-header">
            <i class="fas fa-tasks section-icon"></i>
            <h3 class="section-title">Status Management</h3>
          </div>
          
          <div class="button-group">
            <button class="btn btn-primary" onclick="toggleUpdatePanel()">
              <i class="fas fa-edit"></i> Update Request
            </button>
            
            <div id="updatePanel" class="update-panel">
              <form method="POST">
                <input type="hidden" name="action" value="add_update">
                <input type="hidden" name="ticket_id" value="<?= htmlspecialchars($ticket_id) ?>">
                <input type="hidden" name="priority" id="hiddenPriority" value="<?= htmlspecialchars($request['priority']) ?>">
                
                <div class="form-group">
                  <label class="form-label" for="new_status">New Status</label>
                  <select id="new_status" name="new_status" class="form-select" required>
                    <option value="PENDING">Pending</option>
                    <option value="IN PROGRESS">In Progress</option>
                    <option value="READY">Ready</option>
                    <option value="COMPLETED">Completed</option>
                  </select>
                </div>
                
                <div class="form-group">
                  <label class="form-label" for="update_message">Update Message</label>
                  <textarea id="update_message" name="update_message" class="form-textarea" placeholder="Describe this update..."></textarea>
                </div>
                
                <button class="btn btn-primary" type="submit">
                  <i class="fas fa-save"></i> Save Update
                </button>
              </form>
            </div>
            
            <form method="POST" onsubmit="return confirm('Are you sure you want to delete request <?= htmlspecialchars($ticket_id) ?>? This cannot be undone!');" style="margin: 0;">
              <input type="hidden" name="action" value="delete_request">
              <input type="hidden" name="ticket_id" value="<?= htmlspecialchars($request['ticket_id']) ?>">
              <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash"></i> Delete Request
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script>
    function toggleUpdatePanel() {
      const panel = document.getElementById('updatePanel');
      panel.classList.toggle('show');
    }
    
    document.getElementById('prioritySelect').addEventListener('change', function() {
      document.getElementById('hiddenPriority').value = this.value;
    });
  </script>
</body>
</html>