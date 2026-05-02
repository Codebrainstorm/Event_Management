<?php
require 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}

// Fetch all bookings
$query = "SELECT * FROM bookings ORDER BY event_date DESC";
$result = $connection->query($query);

if (!$result) {
    die('Query failed: ' . $connection->error);
}

$bookings = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings — EMS Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --dark-bg: #050812;
            --card-bg: rgba(8, 11, 26, 0.8);
            --neon-blue: #00f0ff;
            --neon-purple: #9b59b6;
            --neon-gold: #f59e0b;
            --neon-green: #10b981;
            --text-main: #e2e8f0;
            --text-muted: #94a3b8;
            --border-color: rgba(0, 240, 255, 0.15);
        }
        
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Outfit',sans-serif; background:var(--dark-bg); color:var(--text-main); min-height:100vh; position:relative; }
        
        body::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: 
                linear-gradient(90deg, rgba(5,8,18, 1) 0%, rgba(5,8,18, 0.8) 20%, rgba(5,8,18, 0.8) 80%, rgba(5,8,18, 1) 100%),
                linear-gradient(0deg, rgba(0, 240, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 240, 255, 0.03) 1px, transparent 1px);
            background-size: 100% 100%, 40px 40px, 40px 40px;
            pointer-events: none;
            z-index: -1;
            position: fixed;
        }

        .admin-header { background:rgba(5, 8, 18, 0.95); border-bottom: 1px solid var(--border-color); backdrop-filter:blur(10px); position: sticky; top: 0; z-index: 100; }
        .admin-nav { max-width:1400px; margin:0 auto; padding:0 24px; display:flex; justify-content:space-between; align-items:center; height:65px; }
        .admin-logo { font-size:1.3rem; font-weight:800; color:white; text-decoration:none; display: flex; align-items: center; gap: 10px; }
        .admin-logo span { background:linear-gradient(135deg,var(--neon-blue),var(--neon-purple)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
        .admin-nav-links { display:flex; gap:8px; }
        .admin-nav a { color:var(--text-muted); text-decoration:none; padding:8px 14px; border-radius:8px; font-size:0.875rem; font-weight:600; transition:all 0.3s; border: 1px solid transparent; }
        .admin-nav a:hover, .admin-nav a.active { color:var(--neon-blue); background:rgba(0,240,255,0.05); border-color: rgba(0,240,255,0.2); box-shadow: 0 0 10px rgba(0,240,255,0.1); }
        
        .container { max-width:1400px; margin:0 auto; padding:40px 24px; }
        .page-header { display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:30px; flex-wrap:wrap; gap:16px; }
        .page-title h1 { font-size:2rem; font-weight:800; color:white; text-shadow: 0 0 20px rgba(0, 240, 255, 0.3); margin-bottom: 5px; }
        .page-title p { color:var(--text-muted); font-size:1rem; }
        
        .btn-glass { padding:10px 20px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.2); color:white; border-radius:10px; font-weight:700; cursor:pointer; text-decoration:none; transition:all 0.3s; display:inline-flex; align-items:center; gap:8px; backdrop-filter: blur(4px); font-family:'Outfit',sans-serif; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px;}
        .btn-glass:hover { background:rgba(0,240,255,0.1); border-color:var(--neon-blue); color:var(--neon-blue); box-shadow: 0 0 15px rgba(0,240,255,0.2); }

        .btn-approve { background:rgba(16, 185, 129, 0.1); color:var(--neon-green); border-color:rgba(16, 185, 129, 0.3); }
        .btn-approve:hover { background:var(--neon-green); color:#050812; border-color:var(--neon-green); box-shadow: 0 0 15px rgba(16, 185, 129, 0.4); }
        
        .btn-complete { background:rgba(0, 240, 255, 0.1); color:var(--neon-blue); border-color:rgba(0, 240, 255, 0.3); }
        .btn-complete:hover { background:var(--neon-blue); color:#050812; border-color:var(--neon-blue); box-shadow: 0 0 15px rgba(0, 240, 255, 0.4); }
        
        .btn-cancel { background:rgba(239, 68, 68, 0.1); color:#ef4444; border-color:rgba(239, 68, 68, 0.3); }
        .btn-cancel:hover { background:#ef4444; color:#050812; border-color:#ef4444; box-shadow: 0 0 15px rgba(239, 68, 68, 0.4); }

        /* Stats */
        .stats-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:20px; margin-bottom:30px; }
        .stat-card { background:var(--card-bg); border-radius:16px; padding:24px; box-shadow:0 10px 30px rgba(0,0,0,0.5); border:1px solid var(--border-color); backdrop-filter: blur(12px); position: relative; overflow: hidden; }
        .stat-card::after { content: ''; position: absolute; inset: 0; background: linear-gradient(135deg, transparent, rgba(0,240,255,0.03)); pointer-events: none; }
        .stat-icon { font-size:2rem; margin-bottom:12px; color: var(--neon-blue); text-shadow: 0 0 15px rgba(0,240,255,0.4); }
        .stat-label { font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; font-weight:700; margin-bottom:4px; }
        .stat-value { font-size:2rem; font-weight:900; color:white; line-height: 1.2; }
        
        .stat-card.orange .stat-icon { color: var(--neon-gold); text-shadow: 0 0 15px rgba(245,158,11,0.4); }
        .stat-card.green .stat-icon { color: var(--neon-green); text-shadow: 0 0 15px rgba(16,185,129,0.4); }
        
        .events-wrapper { display:grid; gap:20px; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); }
        
        .event-card { background:var(--card-bg); border-radius:20px; box-shadow:0 15px 40px rgba(0,0,0,0.6); border:1px solid var(--border-color); overflow:hidden; transition:all 0.3s; backdrop-filter: blur(12px); display: flex; flex-direction: column; }
        .event-card:hover { transform: translateY(-5px); box-shadow: 0 20px 50px rgba(0,0,0,0.8), inset 0 0 20px rgba(0,240,255,0.05); border-color: var(--neon-blue); }
        
        .event-header { background:rgba(255,255,255,0.02); border-bottom:1px solid rgba(255,255,255,0.05); padding:20px 24px; display:flex; justify-content:space-between; align-items:flex-start; }
        .event-title { font-size:1.25rem; font-weight:800; color:white; margin-bottom:4px; display:flex; align-items:center; gap:10px; }
        .event-creator { font-size:0.85rem; color:var(--text-muted); }
        .event-creator strong { color: white; }
        
        /* Status Badges */
        .status-badge { display:inline-flex; align-items:center; gap:6px; padding:6px 14px; border-radius:20px; font-size:0.75rem; font-weight:800; text-transform:uppercase; letter-spacing:1px; border:1px solid transparent; }
        .status-pending { background:rgba(245,158,11,0.1); color:var(--neon-gold); border-color:rgba(245,158,11,0.3); box-shadow:inset 0 0 10px rgba(245,158,11,0.1); }
        .status-approved { background:rgba(16,185,129,0.1); color:var(--neon-green); border-color:rgba(16,185,129,0.3); box-shadow:inset 0 0 10px rgba(16,185,129,0.1); }
        .status-completed { background:rgba(0,240,255,0.1); color:var(--neon-blue); border-color:rgba(0,240,255,0.3); box-shadow:inset 0 0 10px rgba(0,240,255,0.1); }
        .status-cancelled { background:rgba(239,68,68,0.1); color:#ef4444; border-color:rgba(239,68,68,0.3); box-shadow:inset 0 0 10px rgba(239,68,68,0.1); }
        
        .event-body { padding:24px; flex-grow: 1; display: flex; flex-direction: column; }
        
        .event-info-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:20px; }
        .event-info-item { padding:14px; background:rgba(0,0,0,0.3); border-radius:12px; border-left:3px solid var(--neon-blue); }
        .event-info-label { font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; font-weight:700; margin-bottom:4px; letter-spacing: 0.5px; }
        .event-info-value { font-size:0.95rem; color:white; font-weight:600; }
        
        .event-description { background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.05); padding:16px; border-radius:12px; margin-bottom:24px; font-size:0.9rem; line-height:1.6; color:var(--text-muted); }
        .event-description strong { color:white; display:block; margin-bottom:6px; font-size:0.8rem; text-transform:uppercase; letter-spacing:1px; }
        
        .event-actions { display:flex; gap:10px; flex-wrap:wrap; margin-top: auto; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.05); }
        
        .no-events { text-align:center; padding:80px 20px; background:var(--card-bg); border-radius:20px; border:1px solid var(--border-color); }
        .no-events i { font-size:4rem; margin-bottom:20px; color:var(--neon-blue); opacity:0.5; filter: drop-shadow(0 0 20px rgba(0,240,255,0.3)); }
        .no-events p { color:var(--text-muted); font-size:1.1rem; }
        
        @media (max-width: 768px) {
            .events-wrapper { grid-template-columns: 1fr; }
            .event-info-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="admin-nav">
            <a href="admin-dashboard.php" class="admin-logo"><i class="fas fa-cube" style="color:#00f0ff;"></i> <span>NEXUS ADMIN</span></a>
            <div class="admin-nav-links">
                <a href="admin-dashboard.php" class="active" title="Bookings"><i class="fas fa-list"></i></a>
                <a href="admin-events.php" title="Events"><i class="fas fa-database"></i></a>
                <a href="admin-analytics.php" title="Analytics"><i class="fas fa-chart-line"></i></a>
                <a href="customers.php" title="Customers"><i class="fas fa-user-astronaut"></i></a>
                <a href="event-calendar.php" title="Matrix"><i class="fas fa-border-all"></i></a>
                <a href="logout.php" title="Disconnect"><i class="fas fa-power-off"></i></a>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="page-header">
            <div class="page-title">
                <h1>Bookings Management</h1>
                <p>Process, monitor, and finalize booking operations.</p>
            </div>
            <a href="admin-analytics.php" class="btn-glass"><i class="fas fa-chart-line"></i> View Analytics</a>
        </div>
        
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-ticket-alt"></i></div>
                <div class="stat-label">Total Bookings</div>
                <div class="stat-value"><?php echo count($bookings); ?></div>
            </div>
            <div class="stat-card orange">
                <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
                <div class="stat-label">Pending Approval</div>
                <div class="stat-value"><?php echo count(array_filter($bookings, fn($b) => $b['status'] === 'Pending')); ?></div>
            </div>
            <div class="stat-card green">
                <div class="stat-icon"><i class="fas fa-check-double"></i></div>
                <div class="stat-label">Approved</div>
                <div class="stat-value"><?php echo count(array_filter($bookings, fn($b) => $b['status'] === 'Approved')); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="color: #60a5fa;"><i class="fas fa-flag-checkered"></i></div>
                <div class="stat-label">Completed</div>
                <div class="stat-value"><?php echo count(array_filter($bookings, fn($b) => $b['status'] === 'Completed')); ?></div>
            </div>
        </div>
        
        <?php if (count($bookings) > 0): ?>
            <div class="events-wrapper">
                <?php foreach ($bookings as $booking): ?>
                    <div class="event-card">
                        <div class="event-header">
                            <div>
                                <div class="event-title"><i class="fas fa-cube" style="font-size:0.8rem;color:var(--neon-blue);"></i> <?php echo htmlspecialchars($booking['event_type']); ?></div>
                                <div class="event-creator">
                                    Entity: <strong><?php echo htmlspecialchars($booking['customer_name']); ?></strong>
                                </div>
                            </div>
                            <span class="status-badge status-<?php echo strtolower($booking['status']); ?>">
                                <?php 
                                    if(strtolower($booking['status']) == 'pending') echo '<i class="fas fa-hourglass-half"></i> ';
                                    if(strtolower($booking['status']) == 'approved') echo '<i class="fas fa-check-circle"></i> ';
                                    if(strtolower($booking['status']) == 'completed') echo '<i class="fas fa-flag-checkered"></i> ';
                                    if(strtolower($booking['status']) == 'cancelled') echo '<i class="fas fa-times-circle"></i> ';
                                    echo htmlspecialchars($booking['status']); 
                                ?>
                            </span>
                        </div>
                        
                        <div class="event-body">
                            <div class="event-info-grid">
                                <div class="event-info-item">
                                    <div class="event-info-label">Comm Link</div>
                                    <div class="event-info-value" style="font-size: 0.85rem; font-family: monospace;"><?php echo htmlspecialchars($booking['email']); ?></div>
                                </div>
                                <div class="event-info-item">
                                    <div class="event-info-label">Device Tracker</div>
                                    <div class="event-info-value" style="font-size: 0.85rem; font-family: monospace;"><?php echo htmlspecialchars($booking['phone']); ?></div>
                                </div>
                                <div class="event-info-item" style="border-left-color: var(--neon-purple);">
                                    <div class="event-info-label">Event Timestamp</div>
                                    <div class="event-info-value"><?php echo date('M d, Y', strtotime($booking['event_date'])); ?></div>
                                </div>
                                <div class="event-info-item" style="border-left-color: var(--neon-gold);">
                                    <div class="event-info-label">Sub-Entities</div>
                                    <div class="event-info-value"><?php echo htmlspecialchars($booking['guest_count']); ?> Guests</div>
                                </div>
                            </div>
                            
                            <?php if (!empty($booking['special_requests'])): ?>
                                <div class="event-description">
                                    <strong>Special Directives:</strong>
                                    <?php echo nl2br(htmlspecialchars($booking['special_requests'])); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="event-actions">
                                <?php if ($booking['status'] === 'Pending'): ?>
                                    <a href="update-status.php?id=<?php echo $booking['id']; ?>&status=Approved" class="btn-glass btn-approve" onclick="return confirm('Authorize this operational request?');"><i class="fas fa-check"></i> Authorize</a>
                                <?php endif; ?>
                                <?php if ($booking['status'] !== 'Completed' && $booking['status'] !== 'Cancelled'): ?>
                                    <a href="update-status.php?id=<?php echo $booking['id']; ?>&status=Completed" class="btn-glass btn-complete" onclick="return confirm('Mark operation completed?');"><i class="fas fa-flag-checkered"></i> Finalize</a>
                                <?php endif; ?>
                                <?php if ($booking['status'] !== 'Completed' && $booking['status'] !== 'Cancelled'): ?>
                                    <a href="update-status.php?id=<?php echo $booking['id']; ?>&status=Cancelled" class="btn-glass btn-cancel" onclick="return confirm('Terminate this request?');"><i class="fas fa-times"></i> Terminate</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-events">
                <i class="fas fa-radar"></i>
                <p>System radar is clear. No active bookings detected in the network.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>