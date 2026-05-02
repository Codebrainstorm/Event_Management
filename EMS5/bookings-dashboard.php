<?php
// Redirect to admin dashboard
header('Location: admin-dashboard.php');
exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Admin Dashboard</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-content h1 {
            font-size: 28px;
        }
        
        .header-content p {
            font-size: 14px;
            opacity: 0.9;
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
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .dashboard-title {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }
        
        .bookings-table-wrapper {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table thead {
            background: #f8f9fa;
            border-bottom: 2px solid #ddd;
        }
        
        table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            white-space: nowrap;
        }
        
        table tbody tr {
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s;
        }
        
        table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        table td {
            padding: 15px;
            font-size: 14px;
        }
        
        .event-type-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
        }
        
        .event-type-wedding {
            background-color: #ffe4e6;
            color: #c2185b;
        }
        
        .event-type-birthday {
            background-color: #fff3e0;
            color: #e65100;
        }
        
        .delete-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .delete-btn:hover {
            background-color: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 3px 8px rgba(220, 53, 69, 0.3);
        }
        
        .delete-btn:active {
            transform: translateY(0);
        }
        
        .no-bookings {
            background: white;
            padding: 40px;
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            color: #666;
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
            }
            
            table,
            table thead,
            table tbody,
            table th,
            table td,
            table tr {
                display: block;
                width: 100%;
            }
            
            table thead {
                display: none;
            }
            
            table tbody tr {
                margin-bottom: 20px;
                border: 1px solid #ddd;
                border-radius: 5px;
                padding: 10px;
            }
            
            table td {
                padding: 10px;
                padding-left: 50%;
                text-align: right;
                position: relative;
            }
            
            table td:before {
                content: attr(data-label);
                position: absolute;
                left: 10px;
                font-weight: 600;
                color: #667eea;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div>
                <h1>Manage Bookings</h1>
                <p>Event Management System</p>
            </div>
            <div class="header-btns">
                <a href="admin-dashboard.php" class="btn-header">← Back to Dashboard</a>
                <a href="logout.php" class="btn-header">Logout</a>
            </div>
        </div>
    </header>
    
    <div class="container">
        <h2 class="dashboard-title">Event Service Bookings</h2>
        
        <div class="stats">
            <div class="stat-card">
                <h3>Total Bookings</h3>
                <div class="number"><?php echo count($bookings); ?></div>
            </div>
            <div class="stat-card">
                <h3>Wedding Bookings</h3>
                <div class="number"><?php echo count(array_filter($bookings, fn($b) => $b['event_type'] === 'Wedding')); ?></div>
            </div>
            <div class="stat-card">
                <h3>Birthday Bookings</h3>
                <div class="number"><?php echo count(array_filter($bookings, fn($b) => $b['event_type'] === 'Birthday')); ?></div>
            </div>
        </div>
        
        <?php if (count($bookings) > 0): ?>
            <div class="bookings-table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Event Type</th>
                            <th>Customer Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Event Date</th>
                            <th>Guest Count</th>
                            <th>Special Requests</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td data-label="ID"><?php echo htmlspecialchars($booking['id']); ?></td>
                                <td data-label="Event Type">
                                    <span class="event-type-badge event-type-<?php echo strtolower($booking['event_type']); ?>">
                                        <?php echo htmlspecialchars($booking['event_type']); ?>
                                    </span>
                                </td>
                                <td data-label="Customer Name"><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                                <td data-label="Email"><?php echo htmlspecialchars($booking['email']); ?></td>
                                <td data-label="Phone"><?php echo htmlspecialchars($booking['phone']); ?></td>
                                <td data-label="Event Date"><?php echo date('M d, Y', strtotime($booking['event_date'])); ?></td>
                                <td data-label="Guest Count"><?php echo htmlspecialchars($booking['guest_count']); ?></td>
                                <td data-label="Special Requests"><?php echo htmlspecialchars(substr($booking['special_requests'], 0, 50)) . (strlen($booking['special_requests']) > 50 ? '...' : ''); ?></td>
                                <td data-label="Action">
                                    <form method="POST" action="delete-booking.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this booking?');">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <button type="submit" class="delete-btn">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="no-bookings">
                <p>No bookings found. Event service bookings will appear here.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
