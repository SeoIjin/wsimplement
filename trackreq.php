<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: sign-in.php");
    exit();
}
// Handle logout from header
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['logout'])) {
    session_destroy();
    header("Location: sign-in.php");
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
$search_result = null;
$search_error = "";
// Handle search
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ticket_id']) && !isset($_POST['logout'])) {
    $ticket_id = trim($_POST['ticket_id']);
    if (!empty($ticket_id)) {
        $stmt = $conn->prepare("SELECT id, ticket_id, requesttype, status, submitted_at FROM requests WHERE ticket_id = ? AND user_id = ?");
        $stmt->bind_param("si", $ticket_id, $user_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $ticket_id, $requesttype, $status, $submitted_at);
            $stmt->fetch();
            $search_result = [
                'id' => $id,
                'ticket_id' => $ticket_id, 
                'requesttype' => $requesttype, 
                'status' => $status, 
                'submitted_at' => $submitted_at
            ];
        } else {
            $search_error = "No request found for that ticket ID.";
        }
        $stmt->close();
    } else {
        $search_error = "Please enter a ticket ID.";
    }
}
// Fetch recent requests for the user
$recent_requests = [];
$stmt = $conn->prepare("SELECT id, ticket_id, requesttype, status, submitted_at FROM requests WHERE user_id = ? ORDER BY submitted_at DESC LIMIT 20");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $recent_requests[] = $row;
}
$stmt->close();
$conn->close();
// Helper function for status color
function statusColor($status) {
    switch (strtolower($status)) {
        case 'ready': return '#064b38';
        case 'under review': return '#f39c12';
        case 'completed': return '#1ea2a8';
        case 'in progress': return '#ff6b4a';
        default: return '#6b6f72';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Track Request – eBCsH</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
  <style>
    * { 
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }
    body {
      background: #DAF1DE;
      color: #223;
      min-height: 100vh;
    }
    
    /* Header */
    header {
      background: #fff;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 2rem;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .header-left {
      display: flex;
      align-items: center;
      gap: 12px;
      cursor: pointer;
      text-decoration: none;
      color: inherit;
      transition: opacity 0.3s;
    }
    .header-left:hover {
      opacity: 0.8;
    }
    .header-left img {
      height: 50px;
      width: 50px;
      border-radius: 50%;
    }
    .header-title {
      text-align: left;
    }
    .header-title > div:first-child {
      font-size: 16px;
      font-weight: 500;
    }
    .header-title > div:last-child {
      font-size: 14px;
      opacity: 0.7;
    }
    .logout-btn {
      background: #FD7E7E;
      color: #fff;
      border: none;
      padding: 10px 20px;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.3s;
    }
    .logout-btn:hover {
      background: #fc6565;
    }
    .logout-btn i {
      margin-right: 6px;
    }
    
    /* Main Container */
    .main-container {
      max-width: 960px;
      margin: 1.75rem auto;
      background: #fff;
      border-radius: 0.75rem;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      overflow: hidden;
    }
    
    /* Track Section */
    .track-section {
      padding: 1.5rem;
    }
    .track-box {
      background: linear-gradient(180deg, #fafafa, #f6f8f7);
      border-radius: 0.5rem;
      padding: 1.25rem;
    }
    .track-box h2 {
      margin: 0 0 0.5rem 0;
      font-size: 1.125rem;
      font-weight: 600;
      color: #064b38;
    }
    .label {
      color: #6b6f72;
      font-size: 0.875rem;
      margin-bottom: 0.5rem;
    }
    .search-form {
      display: flex;
      gap: 0.625rem;
      margin-top: 0.625rem;
    }
    .search-form input[type=text] {
      flex: 1;
      padding: 0.625rem 0.75rem;
      border-radius: 0.5rem;
      border: 1px solid #e1e6e4;
      background: #fff;
      font-size: 0.875rem;
      font-family: 'Poppins', sans-serif;
    }
    .search-form input[type=text]:focus {
      outline: none;
      border-color: #064b38;
    }
    .btn-track {
      background: #064b38;
      color: #fff;
      border: none;
      padding: 0.625rem 0.875rem;
      border-radius: 0.5rem;
      cursor: pointer;
      font-weight: 600;
      font-size: 0.875rem;
      white-space: nowrap;
      transition: opacity 0.2s;
    }
    .btn-track:hover {
      opacity: 0.9;
    }
    
    /* Search Results */
    .search-result {
      margin-top: 0.875rem;
      padding: 0.75rem;
      border-radius: 0.5rem;
      background: #f0f8f0;
      border: 1px solid #d0e0d0;
      line-height: 1.6;
      cursor: pointer;
      transition: all 0.3s;
    }
    .search-result:hover {
      background: #e8f4e8;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .search-result strong {
      color: #064b38;
    }
    .view-details {
      font-size: 0.75rem;
      color: #064b38;
      margin-top: 0.5rem;
      display: flex;
      align-items: center;
      gap: 0.25rem;
    }
    .search-error {
      margin-top: 0.875rem;
      padding: 0.75rem;
      border-radius: 0.5rem;
      background: #ffeaea;
      border: 1px solid #ffdddd;
      color: #d9534f;
    }
    .info-text {
      margin-top: 0.875rem;
      display: flex;
      align-items: center;
      gap: 0.625rem;
    }
    .info-text .label {
      margin: 0;
    }
    
    /* Recent Requests Section */
    .recent-section {
      padding: 1.5rem;
      border-top: 1px solid #eef3ef;
    }
    .recent-header {
      margin-bottom: 0.5rem;
    }
    .recent-title {
      font-weight: 700;
      font-size: 1rem;
      margin-bottom: 0.5rem;
    }
    .filter-tabs {
      display: flex;
      gap: 0.5rem;
      flex-wrap: wrap;
    }
    .tab {
      background: #f2f6f4;
      padding: 0.375rem 0.625rem;
      border-radius: 20px;
      font-weight: 600;
      color: #064b38;
      cursor: pointer;
      border: none;
      font-size: 0.875rem;
      transition: all 0.2s;
      text-transform: capitalize;
      font-family: 'Poppins', sans-serif;
    }
    .tab.active {
      background: #064b38;
      color: #fff;
    }
    .tab:hover {
      opacity: 0.8;
    }
    
    /* Request Cards */
    .cards-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 1rem;
      margin-top: 0.5rem;
    }
    .request-card {
      background: linear-gradient(180deg, #fff, #fbfffd);
      border-radius: 0.5rem;
      padding: 0.875rem;
      border: 1px solid #eef4f1;
      cursor: pointer;
      transition: all 0.3s;
    }
    .request-card:hover {
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      border-color: #064b38;
    }
    .card-header {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 0.5rem;
    }
    .status-dot {
      width: 0.625rem;
      height: 0.625rem;
      border-radius: 50%;
      flex-shrink: 0;
    }
    .ticket-id {
      font-weight: 700;
      flex: 1;
      font-size: 0.875rem;
    }
    .status-text {
      font-size: 0.875rem;
      font-weight: 600;
    }
    .card-type {
      font-weight: 600;
      margin-top: 0.375rem;
      font-size: 0.875rem;
    }
    .card-meta {
      font-size: 0.875rem;
      color: #6b6f72;
      margin-top: 0.375rem;
    }
    .card-view-hint {
      font-size: 0.75rem;
      color: #064b38;
      margin-top: 0.5rem;
      display: flex;
      align-items: center;
      gap: 0.25rem;
    }
    .no-requests {
      grid-column: 1 / -1;
      text-align: center;
      padding: 2rem 1rem;
      color: #666;
    }
    
    /* Footer */
    footer {
      background: white;
      border-top: 1px solid #dcfce7;
      margin-top: 3rem;
    }
    .footer-container {
      max-width: 1000px;
      margin: 0 auto;
      padding: 2rem 1.5rem;
    }
    .footer-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 2rem;
      margin-bottom: 1.5rem;
    }
    .footer-section {
      text-align: center;
    }
    .footer-section h3 {
      font-size: 1.125rem;
      color: #14532d;
      font-weight: 600;
      margin-bottom: 1rem;
    }
    .footer-content {
      display: inline-block;
      text-align: left;
    }
    .footer-item {
      margin-bottom: 0.75rem;
      font-size: 0.9375rem;
    }
    .footer-label {
      color: #15803d;
      font-weight: 500;
      margin-bottom: 0.25rem;
    }
    .footer-value {
      color: #166534;
      font-size: 0.875rem;
    }
    .footer-hospital {
      margin-bottom: 0.75rem;
    }
    .footer-hospital-name {
      color: #15803d;
      font-weight: 500;
    }
    .footer-hospital-phone {
      color: #166534;
      font-size: 0.875rem;
    }
    .footer-copyright {
      border-top: 1px solid #dcfce7;
      padding-top: 1.5rem;
      text-align: center;
      color: #15803d;
      font-size: 0.9375rem;
    }
    .footer-copyright p {
      margin-bottom: 0.5rem;
    }
    
    @media(max-width: 768px) {
      header {
        padding: 1rem;
      }
      .cards-grid {
        grid-template-columns: 1fr;
      }
      .footer-grid {
        grid-template-columns: 1fr;
      }
      .search-form {
        flex-direction: column;
      }
      .btn-track {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <header>
    <a href="homepage.php" class="header-left">
      <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRTDCuh4kIpAtR-QmjA1kTjE_8-HSd8LSt3Gw&s" alt="Logo">
      <div class="header-title">
        <div>Barangay 170</div>
        <div>Community Portal</div>
      </div>
    </a>
    <form method="POST" style="display:inline; margin: 0;">
      <button type="submit" name="logout" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i>Logout
      </button>
    </form>
  </header>
  
  <div class="main-container">
    <!-- Track Section -->
    <div class="track-section">
      <div class="track-box">
        <h2>Track New Request</h2>
        <div class="label">Ticket ID</div>
        
        <form method="POST" class="search-form">
          <input 
            type="text" 
            name="ticket_id" 
            placeholder="Enter your ticket ID (e.g., BHR-2024-001234)" 
            required 
          />
          <button type="submit" class="btn-track">Track Request</button>
        </form>
        
        <?php if ($search_result): ?>
          <div 
            class="search-result" 
            onclick="window.location.href='requestdetails.php?id=<?php echo $search_result['id']; ?>'"
          >
            <div><strong>Ticket ID:</strong> <?php echo htmlspecialchars($search_result['ticket_id']); ?></div>
            <div><strong>Type:</strong> <?php echo htmlspecialchars($search_result['requesttype']); ?></div>
            <div><strong>Status:</strong> <?php echo htmlspecialchars($search_result['status']); ?></div>
            <div><strong>Submitted:</strong> <?php echo date('M j, Y', strtotime($search_result['submitted_at'])); ?></div>
            <div class="view-details">
              <i class="fas fa-eye"></i>
              <span>Click to view full details</span>
            </div>
          </div>
        <?php elseif ($search_error): ?>
          <div class="search-error"><?php echo htmlspecialchars($search_error); ?></div>
        <?php endif; ?>
        
        <div class="info-text">
          <div class="label">Enter a ticket ID and click "Track Request" to find its status.</div>
        </div>
      </div>
    </div>
    
    <!-- Recent Requests Section -->
    <div class="recent-section">
      <div class="recent-header">
        <div class="recent-title">My Recent Requests</div>
        <div class="filter-tabs">
          <button class="tab active" data-filter="all">all</button>
          <button class="tab" data-filter="pending">pending</button>
          <button class="tab" data-filter="ongoing">in progress</button>
          <button class="tab" data-filter="submitted">ready</button>
          <button class="tab" data-filter="completed">completed</button>
        </div>
      </div>
      
      <div class="cards-grid" id="cardsContainer">
        <?php if (empty($recent_requests)): ?>
          <div class="no-requests">No requests found</div>
        <?php else: ?>
          <?php foreach ($recent_requests as $request): ?>
            <div 
              class="request-card" 
              data-status="<?php echo strtolower($request['status']); ?>"
              onclick="window.location.href='requestdetails.php?id=<?php echo $request['id']; ?>'"
            >
              <div class="card-header">
                <span 
                  class="status-dot" 
                  style="background:<?php echo statusColor($request['status']); ?>"
                ></span>
                <div class="ticket-id"><?php echo htmlspecialchars($request['ticket_id']); ?></div>
                <div 
                  class="status-text" 
                  style="color:<?php echo statusColor($request['status']); ?>"
                >
                  <?php echo htmlspecialchars($request['status']); ?>
                </div>
              </div>
              <div class="card-type"><?php echo htmlspecialchars($request['requesttype']); ?></div>
              <div class="card-meta">
                Submitted: <?php echo date('M j, Y', strtotime($request['submitted_at'])); ?>
              </div>
              <div class="card-view-hint">
                <i class="fas fa-eye"></i>
                <span>Click to view details</span>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
  
  <!-- Footer -->
  <footer>
    <div class="footer-container">
      <div class="footer-grid">
        <!-- Barangay Health Office -->
        <div class="footer-section">
          <h3>🏢 Barangay Health Office</h3>
          <div class="footer-content">
            <div class="footer-item">
              <div class="footer-label">📍 Address</div>
              <div class="footer-value">Deparo, Caloocan City, Metro Manila</div>
            </div>
            <div class="footer-item">
              <div class="footer-label">📞 Hotline</div>
              <div class="footer-value">(02) 8123-4567</div>
            </div>
            <div class="footer-item">
              <div class="footer-label">📧 Email</div>
              <div class="footer-value">K1contrerascris@gmail.com</div>
            </div>
            <div class="footer-item">
              <div class="footer-label">🕐 Office Hours</div>
              <div class="footer-value">Mon-Fri, 8:00 AM - 5:00 PM</div>
            </div>
          </div>
        </div>

        <!-- Emergency Hotlines -->
        <div class="footer-section">
          <h3>📞 Emergency Hotlines</h3>
          <div class="footer-content">
            <div class="footer-item">
              <span class="footer-label" style="min-width: 80px; display: inline-block;">Police</span>
              <span class="footer-value">(02) 8426-4663</span>
            </div>
            <div class="footer-item">
              <span class="footer-label" style="min-width: 80px; display: inline-block;">BFP</span>
              <span class="footer-value">(02) 8245 0849</span>
            </div>
          </div>
        </div>

        <!-- Hospitals Near Barangay -->
        <div class="footer-section">
          <h3>🏥 Hospitals Near Barangay</h3>
          <div class="footer-content">
            <div class="footer-hospital">
              <div class="footer-hospital-name">Camarin Doctors Hospital</div>
              <div class="footer-hospital-phone">(02) 2-7004-2881</div>
            </div>
            <div class="footer-hospital">
              <div class="footer-hospital-name">Caloocan City North Medical</div>
              <div class="footer-hospital-phone">(02) 8288 7077</div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Copyright -->
      <div class="footer-copyright">
        <p>© 2025 Barangay 170, Deparo, Caloocan. All rights reserved.</p>
        <p>Barangay Citizen Document Request System (BCDRS)</p>
      </div>
    </div>
  </footer>
  
  <script>
    // Filter functionality
document.querySelectorAll('.tab').forEach(tab => {
  tab.addEventListener('click', () => {
    // Update active state
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    
    const filter = tab.dataset.filter;
    const cards = document.querySelectorAll('.request-card');
    const noRequestsMsg = document.querySelector('.no-requests');
    let visibleCount = 0;
    
    cards.forEach(card => {
      const status = card.dataset.status.toLowerCase().trim();
      let show = false;
      
      if (filter === 'all') {
        show = true;
      } else if (filter === 'pending') {
        // For "pending" tab - show both PENDING and UNDER REVIEW statuses
        show = status === 'pending' || status === 'under review';
      } else if (filter === 'ongoing') {
        // For "in progress" tab
        show = status === 'in progress';
      } else if (filter === 'submitted') {
        // For "ready" tab
        show = status === 'ready';
      } else if (filter === 'completed') {
        show = status === 'completed';
      }
      
      card.style.display = show ? 'block' : 'none';
      if (show) visibleCount++;
    });
    
    // Show/hide "no requests" message
    if (noRequestsMsg) {
      noRequestsMsg.style.display = visibleCount === 0 ? 'block' : 'none';
    }
  });
});
  </script>
</body>
</html>