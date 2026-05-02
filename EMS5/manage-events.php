<?php
require 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Handle approve, reject, delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';
    $event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;
    
    if ($event_id > 0) {
        if ($action === 'approve') {
            $update_query = "UPDATE events SET status = 'approved' WHERE id = ?";
            $stmt = $connection->prepare($update_query);
            $stmt->bind_param('i', $event_id);
            $stmt->execute();
            $stmt->close();
        } elseif ($action === 'reject') {
            $update_query = "UPDATE events SET status = 'rejected' WHERE id = ?";
            $stmt = $connection->prepare($update_query);
            $stmt->bind_param('i', $event_id);
            $stmt->execute();
            $stmt->close();
        } elseif ($action === 'delete') {
            $delete_query = "DELETE FROM events WHERE id = ?";
            $stmt = $connection->prepare($delete_query);
            $stmt->bind_param('i', $event_id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Fetch all events with user information
$query = "SELECT e.*, u.name as creator_name, u.email as creator_email 
          FROM events e 
          JOIN users u ON e.created_by = u.id 
          ORDER BY e.created_at DESC";
$result = $connection->query($query);

if (!$result) {
    die('Query failed: ' . $connection->error);
}

$events = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - Admin Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            color: #333;
        }
        
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-content h1 {
            font-size: 28px;
        }
        
        .header-btns {
            display: flex;
            gap: 15px;
        }
        
        .btn-header {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid white;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-header:hover {
            background: white;
            color: #667eea;
        }
        
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .page-title {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .stat-card h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        
        .stat-card .number {
            font-size: 28px;
            font-weight: bold;
            color: #667eea;
        }
        
        .events-wrapper {
            display: grid;
            gap: 20px;
        }
        
        .event-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: all 0.3s;
        }
        
        .event-card:hover {
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }
        
        .event-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: start;
        }
        
        .event-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .event-creator {
            font-size: 13px;
            opacity: 0.9;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .event-body {
            padding: 20px;
        }
        
        .event-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .event-info-item {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .event-info-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .event-info-value {
            font-size: 14px;
            color: #333;
            font-weight: 500;
        }
        
        .event-description {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            line-height: 1.6;
            color: #555;
        }
        
        .event-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .event-actions form {
            display: inline;
        }
        
        .btn-action {
            padding: 10px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-approve {
            background: #28a745;
            color: white;
        }
        
        .btn-approve:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        
        .btn-reject {
            background: #ffc107;
            color: #333;
        }
        
        .btn-reject:hover {
            background: #e0a800;
            transform: translateY(-2px);
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        
        .btn-delete:hover {
            background: #c82333;
            transform: translateY(-2px);
        }
        
        .no-events {
            background: white;
            padding: 40px;
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            color: #666;
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .header-btns {
                flex-direction: column;
                width: 100%;
            }
            
            .btn-header {
                width: 100%;
                text-align: center;
            }
            
            .event-header {
                flex-direction: column;
                gap: 10px;
            }
            
            .event-actions {
                flex-direction: column;
            }
            
            .event-actions form,
            .btn-action {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <h1>Event Management</h1>
            <div class="header-btns">
                <a href="admin-dashboard.php" class="btn-header">← Back to Dashboard</a>
                <a href="logout.php" class="btn-header">Logout</a>
            </div>
        </div>
    </header>
    
    <div class="container">
        <h2 class="page-title">Manage User Events</h2>
        
        <div class="stats">
            <div class="stat-card">
                <h3>Total Events</h3>
                <div class="number"><?php echo count($events); ?></div>
            </div>
            <div class="stat-card">
                <h3>Pending Approval</h3>
                <div class="number"><?php echo count(array_filter($events, fn($e) => $e['status'] === 'pending')); ?></div>
            </div>
            <div class="stat-card">
                <h3>Approved</h3>
                <div class="number"><?php echo count(array_filter($events, fn($e) => $e['status'] === 'approved')); ?></div>
            </div>
            <div class="stat-card">
                <h3>Rejected</h3>
                <div class="number"><?php echo count(array_filter($events, fn($e) => $e['status'] === 'rejected')); ?></div>
            </div>
        </div>
        
        <?php if (count($events) > 0): ?>
            <div class="events-wrapper">
                <?php foreach ($events as $event): ?>
                    <div class="event-card">
                        <div class="event-header">
                            <div>
                                <div class="event-title"><?php echo htmlspecialchars($event['title']); ?></div>
                                <div class="event-creator">
                                    Posted by: <strong><?php echo htmlspecialchars($event['creator_name']); ?></strong> 
                                    (<?php echo htmlspecialchars($event['creator_email']); ?>)
                                </div>
                            </div>
                            <span class="status-badge status-<?php echo $event['status']; ?>">
                                <?php echo ucfirst($event['status']); ?>
                            </span>
                        </div>
                        
                        <div class="event-body">
                            <div class="event-info-grid">
                                <div class="event-info-item">
                                    <div class="event-info-label">📅 Date</div>
                                    <div class="event-info-value"><?php echo date('M d, Y H:i', strtotime($event['event_date'])); ?></div>
                                </div>
                                <div class="event-info-item">
                                    <div class="event-info-label">📍 Location</div>
                                    <div class="event-info-value"><?php echo htmlspecialchars($event['location']); ?></div>
                                </div>
                                <div class="event-info-item">
                                    <div class="event-info-label">🏷️ Category</div>
                                    <div class="event-info-value"><?php echo htmlspecialchars($event['category']); ?></div>
                                </div>
                                <div class="event-info-item">
                                    <div class="event-info-label">✅ Status</div>
                                    <div class="event-info-value"><?php echo $event['active'] ? 'Active' : 'Inactive'; ?></div>
                                </div>
                            </div>
                            
                            <div class="event-description">
                                <strong>Description:</strong><br>
                                <?php echo htmlspecialchars(substr($event['description'], 0, 200)); ?>
                                <?php echo strlen($event['description']) > 200 ? '...' : ''; ?>
                            </div>
                            
                            <?php if (!empty($event['requirements'])): ?>
                                <div class="event-description">
                                    <strong>Requirements:</strong><br>
                                    <?php echo htmlspecialchars(substr($event['requirements'], 0, 200)); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="event-actions">
                                <?php if ($event['status'] === 'pending'): ?>
                                    <form method="POST" action="">
                                        <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn-action btn-approve" onclick="return confirm('Approve this event?');">✓ Approve</button>
                                    </form>
                                    <form method="POST" action="">
                                        <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn-action btn-reject" onclick="return confirm('Reject this event?');">✗ Reject</button>
                                    </form>
                                <?php endif; ?>
                                <form method="POST" action="">
                                    <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn-action btn-delete" onclick="return confirm('Delete this event permanently?');">🗑 Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-events">
                <p>No events found. Events posted by users will appear here.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
