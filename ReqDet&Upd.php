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
            // Get request type before deleting
            $stmt = $pdo->prepare("SELECT requesttype FROM requests WHERE ticket_id = ?");
            $stmt->execute([$_POST['ticket_id']]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $stmt = $pdo->prepare("DELETE FROM requests WHERE ticket_id = ?");
            $stmt->execute([$_POST['ticket_id']]);
            
            // Log deletion
            logRequestDelete($admin_id, $admin_email, $_POST['ticket_id'], $request['requesttype']);
            
            header("Location: admindashboard.php?msg=deleted");
            exit;
            
        } elseif ($_POST['action'] === 'add_update') {
            
            // Get request ID and old status
            $stmt = $pdo->prepare("SELECT id, status, priority FROM requests WHERE ticket_id = ?");
            $stmt->execute([$_POST['ticket_id']]);
            $request_data = $stmt->fetch(PDO::FETCH_ASSOC);
            $request_id = $request_data['id'];
            $old_status = $request_data['status'];
            $old_priority = $request_data['priority'];
            
            // Update both status and priority
            $stmt = $pdo->prepare("UPDATE requests SET status = ?, priority = ? WHERE ticket_id = ?");
            $stmt->execute([$_POST['new_status'], $_POST['priority'], $_POST['ticket_id']]);
            
            $update_message = $_POST['update_message'] ?? 'Status updated by admin';
            $stmt_insert = $pdo->prepare("INSERT INTO request_updates (request_id, status, message, updated_by) VALUES (?, ?, ?, ?)");
            $stmt_insert->execute([$request_id, $_POST['new_status'], $update_message, 'Admin']);
            
            // Log status change
            if ($old_status !== $_POST['new_status']) {
                logRequestUpdate($admin_id, $admin_email, $_POST['ticket_id'], $old_status, $_POST['new_status'], $update_message);
            }
            
            // Log priority change
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
    // Get the most recent request
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

// Status color mapping
$statusColors = [
    'PENDING' => '#ff6b4a',
    'UNDER REVIEW' => '#f39c12',
    'IN PROGRESS' => '#c29e64',
    'READY' => '#3498db',
    'COMPLETED' => '#1ea2a8'
];

// Format timestamp
function formatTimestamp($timestamp) {
    return date('M d, Y - g:i A', strtotime($timestamp));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Request Details & Updates - <?= htmlspecialchars($ticket_id) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }
    
    body {
      min-height: 100vh;
      background: linear-gradient(to bottom right, #d1fae5, #ecfdf5, #ccfbf1);
    }
    
    /* Header */
    header {
      background: white;
      border-bottom: 1px solid #e5e7eb;
      padding: 1rem 1.5rem;
    }
    
    .header-content {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }
    
    .header-logo {
      width: 32px;
      height: 32px;
      object-fit: contain;
    }
    
    .back-btn {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      color: #0d9488;
      text-decoration: none;
      font-weight: 500;
      transition: color 0.2s;
    }
    
    .back-btn:hover {
      color: #0f766e;
    }
    
    .back-icon {
      width: 16px;
      height: 16px;
    }
    
    .header-title {
      color: #0f766e;
      font-size: 1.125rem;
      font-weight: 600;
    }
    
    /* Main Container */
    main {
      max-width: 1400px;
      margin: 0 auto;
      padding: 1.5rem;
    }
    
    .card-container {
      background: white;
      border-radius: 0.5rem;
      border: 2px solid #5eead4;
      box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
      overflow: hidden;
    }
    
    /* Ticket Header */
    .ticket-header {
      background: #d1fae5;
      border-bottom: 2px solid #5eead4;
      padding: 1.5rem;
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
    }
    
    .ticket-info h2 {
      color: #0f766e;
      font-size: 1.25rem;
      font-weight: 600;
      margin-bottom: 0.25rem;
    }
    
    .ticket-info p {
      color: #0d9488;
      font-size: 0.875rem;
    }
    
    .status-badge {
      background: white;
      color: #0d9488;
      border: 2px solid #14b8a6;
      padding: 0.5rem 1rem;
      border-radius: 9999px;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      font-weight: 600;
      font-size: 0.875rem;
    }
    
    .check-icon {
      width: 16px;
      height: 16px;
    }
    
    /* Content Grid */
    .content-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1.5rem;
      padding: 1.5rem;
    }
    
    /* Section Styles */
    .section {
      border: 2px solid #5eead4;
      border-radius: 0.5rem;
      padding: 1rem;
    }
    
    .section-header {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 1rem;
    }
    
    .section-icon {
      width: 16px;
      height: 16px;
      color: #0d9488;
    }
    
    .section-title {
      color: #0f766e;
      font-size: 1rem;
      font-weight: 600;
    }
    
    .section-subtitle {
      color: #6b7280;
      font-size: 0.875rem;
      margin-bottom: 1rem;
    }
    
    /* Citizen Information */
    .info-item {
      display: flex;
      align-items: flex-start;
      gap: 0.5rem;
      margin-bottom: 0.75rem;
    }
    
    .info-icon {
      width: 16px;
      height: 16px;
      color: #0d9488;
      margin-top: 0.125rem;
      flex-shrink: 0;
    }
    
    .info-content {
      flex: 1;
    }
    
    .info-label {
      color: #6b7280;
      font-size: 0.75rem;
      margin-bottom: 0.125rem;
    }
    
    .info-value {
      color: #374151;
      font-size: 0.875rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .badge-inline {
      background: #ecfdf5;
      color: #0d9488;
      padding: 0.125rem 0.5rem;
      border-radius: 0.25rem;
      font-size: 0.75rem;
    }
    
    .verified-icon {
      width: 12px;
      height: 12px;
      color: #0d9488;
    }
    
    /* Description */
    .description-card {
      background: #ecfdf5;
      border: 1px solid #d1fae5;
      border-radius: 0.5rem;
      padding: 1rem;
    }
    
    .description-text {
      color: #374151;
      font-size: 0.875rem;
      line-height: 1.5;
      margin-bottom: 0.5rem;
    }
    
    .description-time {
      color: #9ca3af;
      font-size: 0.75rem;
      text-align: right;
    }
    
    /* Update History */
    .update-item {
      padding: 1rem;
      border-radius: 0.5rem;
      border-left: 4px solid;
      margin-bottom: 1rem;
    }
    
    .update-item.active {
      background: #fef3c7;
      border-left-color: #fbbf24;
    }
    
    .update-item.inactive {
      background: #f9fafb;
      border-left-color: #d1d5db;
    }
    
    .update-status {
      font-weight: 600;
      margin-bottom: 0.25rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .update-item.active .update-status {
      color: #92400e;
    }
    
    .update-item.inactive .update-status {
      color: #4b5563;
    }
    
    .update-time {
      color: #6b7280;
      font-size: 0.75rem;
      margin-bottom: 0.5rem;
    }
    
    .update-description {
      color: #4b5563;
      font-size: 0.875rem;
      margin-bottom: 0.25rem;
    }
    
    .update-by {
      color: #9ca3af;
      font-size: 0.75rem;
    }
    
    /* Request Type */
    .request-type-badge {
      background: #dcfce7;
      color: #166534;
      padding: 0.5rem 1rem;
      border-radius: 0.5rem;
      font-weight: 600;
      display: inline-block;
    }
    
    /* Priority Select */
    .priority-select {
      width: 100%;
      background: #fef3c7;
      border: 1px solid #fde68a;
      color: #92400e;
      padding: 0.5rem 0.75rem;
      border-radius: 0.5rem;
      font-size: 0.875rem;
      font-weight: 500;
      cursor: pointer;
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
      transition: opacity 0.2s;
    }
    
    .btn:hover {
      opacity: 0.9;
    }
    
    .btn-primary {
      background: #0d9488;
      color: white;
    }
    
    .btn-danger {
      background: #dc2626;
      color: white;
    }
    
    .button-group {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }
    
    /* Update Panel */
    .update-panel {
      margin-top: 1rem;
      background: #f0fdfa;
      border: 1px solid #ccfbf1;
      border-radius: 0.5rem;
      padding: 1rem;
      display: none;
    }
    
    .update-panel.show {
      display: block;
    }
    
    .form-group {
      margin-bottom: 1rem;
    }
    
    .form-label {
      display: block;
      color: #0f766e;
      font-weight: 600;
      font-size: 0.875rem;
      margin-bottom: 0.5rem;
    }
    
    .form-select,
    .form-textarea {
      width: 100%;
      padding: 0.5rem 0.75rem;
      border: 1px solid #d1fae5;
      border-radius: 0.5rem;
      font-size: 0.875rem;
      background: white;
    }
    
    .form-textarea {
      resize: vertical;
      min-height: 80px;
    }
    
    /* Responsive */
    @media (max-width: 1024px) {
      .content-grid {
        grid-template-columns: 1fr;
      }
    }
    
    /* SVG Icons */
    .icon-svg {
      display: inline-block;
      vertical-align: middle;
    }
  </style>
</head>
<body>
  <header>
    <div class="header-content">
      <a href="admindashboard.php" class="back-btn">
        <svg class="back-icon icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        <span>Back to Dashboard</span>
      </a>
      <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRTDCuh4kIpAtR-QmjA1kTjE_8-HSd8LSt3Gw&s" alt="seal" class="header-logo">
      <h1 class="header-title">Request Details & Updates</h1>
    </div>
  </header>

  <main>
    <div class="card-container">
      <!-- Ticket Header -->
      <div class="ticket-header">
        <div class="ticket-info">
          <h2>Request Details</h2>
          <p>Ticket ID: <?= htmlspecialchars($request['ticket_id']) ?></p>
        </div>
        <div class="status-badge">
          <?= htmlspecialchars($request['status']) ?>
          <svg class="check-icon icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
        </div>
      </div>

      <!-- Content Grid -->
      <div class="content-grid">
        <!-- Citizen Information -->
        <div>
          <div class="section" style="margin-bottom: 1.5rem;">
            <div class="section-header">
              <svg class="section-icon icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              <h3 class="section-title">Citizen Information</h3>
            </div>
            
            <div class="info-item">
              <svg class="info-icon icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              <div class="info-content">
                <div class="info-value">
                  <span><?= htmlspecialchars($request['fullname']) ?></span>
                  <span class="badge-inline">Resident</span>
                  <svg class="verified-icon icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                  </svg>
                </div>
              </div>
            </div>

            <div class="info-item">
              <svg class="info-icon icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
              </svg>
              <div class="info-content">
                <p class="info-label">Contact Number</p>
                <div class="info-value">
                  <span><?= htmlspecialchars($request['contact']) ?></span>
                  <svg class="verified-icon icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                  </svg>
                </div>
              </div>
            </div>

            <div class="info-item">
              <svg class="info-icon icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
              </svg>
              <div class="info-content">
                <p class="info-label">Email</p>
                <div class="info-value">
                  <span><?= htmlspecialchars($request['email'] ?? 'N/A') ?></span>
                  <svg class="verified-icon icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                  </svg>
                </div>
              </div>
            </div>

            <div class="info-item">
              <svg class="info-icon icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              <div class="info-content">
                <p class="info-label">Username</p>
                <p class="info-value"><?= htmlspecialchars($request['barangay'] ?? 'N/A') ?></p>
              </div>
            </div>

            <div class="info-item">
              <svg class="info-icon icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
              </svg>
              <div class="info-content">
                <p class="info-label">User ID</p>
                <p class="info-value"><?= htmlspecialchars($request['user_id']) ?></p>
              </div>
            </div>
          </div>

          <!-- Description -->
          <div class="section">
            <h3 class="section-title">Description</h3>
            <div class="description-card">
              <p class="description-text"><?= nl2br(htmlspecialchars($request['description'])) ?></p>
              <p class="description-time">Submitted <?= formatTimestamp($request['submitted_at']) ?></p>
            </div>
          </div>
        </div>

        <!-- Update History -->
        <div class="section">
          <h3 class="section-title">Update History</h3>
          <p class="section-subtitle">Track all status changes and updates</p>

          <div class="update-item active">
            <div class="update-status">
              <span>* <?= htmlspecialchars($request['status']) ?></span>
            </div>
            <p class="update-time"><?= formatTimestamp($request['submitted_at']) ?></p>
            <p class="update-description">Current status of the request.</p>
            <p class="update-by">Updated by: System</p>
          </div>

          <div class="update-item inactive">
            <div class="update-status">
              <span>* Submitted</span>
            </div>
            <p class="update-time"><?= formatTimestamp($request['submitted_at']) ?></p>
            <p class="update-description">Request has been received and will undergo review.</p>
            <p class="update-by">Updated by: System</p>
          </div>
        </div>

        <!-- Request Type & Actions -->
        <div class="section">
          <!-- Request Type -->
          <div style="margin-bottom: 1.5rem;">
            <div class="section-header">
              <div style="width: 16px; height: 16px; background: #0d9488; border-radius: 2px;"></div>
              <h3 class="section-title">Request Type</h3>
            </div>
            <p class="request-type-badge"><?= htmlspecialchars($request['requesttype']) ?></p>
          </div>

          <!-- Priority Level -->
          <div style="margin-bottom: 1.5rem;">
            <div class="section-header">
              <div style="width: 16px; height: 16px; background: #0d9488; border-radius: 2px;"></div>
              <h3 class="section-title">Priority Level</h3>
            </div>
            <!-- Changed: Removed onchange auto-submit, priority is now part of the update form -->
            <select id="prioritySelect" class="priority-select">
              <option value="LOW" <?= $request['priority'] === 'LOW' ? 'selected' : '' ?>>LOW</option>
              <option value="MEDIUM" <?= $request['priority'] === 'MEDIUM' ? 'selected' : '' ?>>MEDIUM</option>
              <option value="HIGH" <?= $request['priority'] === 'HIGH' ? 'selected' : '' ?>>HIGH</option>
            </select>
          </div>

          <!-- Status Management -->
          <div>
            <h3 class="section-title" style="margin-bottom: 0.75rem;">Status Management</h3>
            <div class="button-group">
              <button class="btn btn-primary" onclick="toggleUpdatePanel()">Update Request</button>
              
              <div id="updatePanel" class="update-panel">
                <form method="POST">
                  <input type="hidden" name="action" value="add_update">
                  <input type="hidden" name="ticket_id" value="<?= htmlspecialchars($ticket_id) ?>">
                  <input type="hidden" name="priority" id="hiddenPriority" value="<?= htmlspecialchars($request['priority']) ?>">
                  
                  <div class="form-group">
                    <label class="form-label" for="new_status">New Status</label>
                    <select id="new_status" name="new_status" class="form-select" required>
                      <option value="PENDING" <?= $request['status'] === 'PENDING' ? 'selected' : '' ?>>Pending</option>
                      <option value="IN PROGRESS" <?= $request['status'] === 'IN PROGRESS' ? 'selected' : '' ?>>In Progress</option>
                      <option value="READY" <?= $request['status'] === 'READY' ? 'selected' : '' ?>>Ready</option>
                      <option value="COMPLETED" <?= $request['status'] === 'COMPLETED' ? 'selected' : '' ?>>Completed</option>
                    </select>
                  </div>
                  
                  <div class="form-group">
                    <label class="form-label" for="update_message">Update Message</label>
                    <textarea id="update_message" name="update_message" class="form-textarea" placeholder="Describe this update..."></textarea>
                  </div>
                  
                  <button class="btn btn-primary" type="submit">Save Update</button>
                </form>
              </div>
              
              <form method="POST" onsubmit="return confirm('Are you sure you want to delete request <?= htmlspecialchars($ticket_id) ?>? This cannot be undone!');" style="margin: 0;">
                <input type="hidden" name="action" value="delete_request">
                <input type="hidden" name="ticket_id" value="<?= htmlspecialchars($request['ticket_id']) ?>">
                <button type="submit" class="btn btn-danger">Delete Request</button>
              </form>
            </div>
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
    
    // Sync priority select with hidden input before form submission
    document.getElementById('prioritySelect').addEventListener('change', function() {
      document.getElementById('hiddenPriority').value = this.value;
    });
  </script>
</body>
</html>