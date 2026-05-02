<?php
require 'config.php';
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}

// ---------------------------------------------------------
// CUSTOMER LIFETIME EARNINGS LOGIC
// We find all distinct emails (customers) from both `bookings` and `registrations` tables.
// For each email, we fetch:
// 1. Total spent on bookings
// 2. Total spent on event registrations
// 3. Name, Phone (from bookings or user table)
// ---------------------------------------------------------

$customers = [];

// 1. GET BOOKING CUSTOMERS
$booking_query = "
    SELECT 
        customer_name as name, 
        email, 
        phone, 
        SUM(price) as total_booking_spent,
        COUNT(id) as booking_count,
        MAX(event_date) as last_booking_date
    FROM bookings
    WHERE status != 'Cancelled'
    GROUP BY email, customer_name, phone
";
$booking_result = $connection->query($booking_query);
if ($booking_result) {
    while($row = $booking_result->fetch_assoc()) {
        $email = strtolower(trim($row['email']));
        $customers[$email] = [
            'name' => $row['name'],
            'email' => $email,
            'phone' => $row['phone'],
            'total_spent' => (float)$row['total_booking_spent'],
            'total_events' => (int)$row['booking_count'],
            'last_active' => $row['last_booking_date'],
            'type' => 'Service Booking'
        ];
    }
}

// 2. GET EVENT REGISTRATION CUSTOMERS (Join with users and events table)
$reg_query = "
    SELECT 
        u.name,
        u.email,
        u.phone,
        SUM(e.price) as total_reg_spent,
        COUNT(r.id) as reg_count,
        MAX(r.registration_date) as last_reg_date
    FROM registrations r
    JOIN users u ON r.user_id = u.id
    JOIN events e ON r.event_id = e.id
    GROUP BY u.email, u.name, u.phone
";
$reg_result = $connection->query($reg_query);
if ($reg_result) {
    while($row = $reg_result->fetch_assoc()) {
        $email = strtolower(trim($row['email']));
        if (isset($customers[$email])) {
            // Merge data
            $customers[$email]['total_spent'] += (float)$row['total_reg_spent'];
            $customers[$email]['total_events'] += (int)$row['reg_count'];
            if (strtotime($row['last_reg_date']) > strtotime($customers[$email]['last_active'])) {
                $customers[$email]['last_active'] = $row['last_reg_date'];
            }
            $customers[$email]['type'] = 'Mixed (Bookings & Events)';
            // If name/phone were empty, fill them
            if (empty($customers[$email]['name'])) $customers[$email]['name'] = $row['name'];
            if (empty($customers[$email]['phone'])) $customers[$email]['phone'] = $row['phone'];
        } else {
            // New customer
            $customers[$email] = [
                'name' => $row['name'],
                'email' => $email,
                'phone' => $row['phone'] ?? 'N/A',
                'total_spent' => (float)$row['total_reg_spent'],
                'total_events' => (int)$row['reg_count'],
                'last_active' => $row['last_reg_date'],
                'type' => 'Event Registration'
            ];
        }
    }
}

// Ensure array is sorted by total spent (VIPs first)
usort($customers, function($a, $b) {
    return $b['total_spent'] <=> $a['total_spent'];
});

// Calculate Totals
$total_customers = count($customers);
$total_revenue = 0;
$top_spenders = 0;
foreach($customers as $c) {
    $total_revenue += $c['total_spent'];
    if ($c['total_spent'] >= 500) { // Threshold for VIP
        $top_spenders++;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Data Matrix — EMS Admin</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root { 
            --primary: #00f0ff; 
            --dark: #050812; 
            --dark-2: rgba(8, 11, 26, 0.8);
            --text: #e2e8f0; 
            --muted: #94a3b8; 
            --border: rgba(0, 240, 255, 0.2); 
            --radius: 16px; 
            --neon-blue: #00f0ff;
            --neon-purple: #9b59b6;
            --neon-gold: #f59e0b;
        }

        * { margin:0; padding:0; box-sizing:border-box; }
        
        body { 
            font-family: 'Outfit', sans-serif; 
            background: var(--dark); 
            color: var(--text); 
            overflow-x: hidden; 
        }

        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: 
                linear-gradient(90deg, rgba(5,8,18, 1) 0%, rgba(5,8,18, 0.8) 20%, rgba(5,8,18, 0.8) 80%, rgba(5,8,18, 1) 100%),
                linear-gradient(0deg, rgba(0, 240, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 240, 255, 0.03) 1px, transparent 1px);
            background-size: 100% 100%, 40px 40px, 40px 40px;
            pointer-events: none;
            z-index: 0;
        }

        /* Admin header */
        .admin-header { background: rgba(5, 8, 18, 0.95); backdrop-filter: blur(10px); color: white; padding: 0; border-bottom: 2px solid rgba(0,240,255,0.2); position: relative; z-index: 10; }
        .admin-nav { max-width: 1400px; margin: 0 auto; padding: 0 24px; display: flex; justify-content: space-between; align-items: center; height: 75px; }
        .admin-logo { font-size: 1.5rem; font-weight: 900; color: white; text-decoration: none; display: flex; align-items: center; gap: 10px; letter-spacing: 1px; }
        .admin-logo span { background: linear-gradient(90deg, #00f0ff, #0051ff, #ff007f); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        
        .admin-nav-links { display: flex; gap: 8px; }
        .admin-nav a { color: #94a3b8; text-decoration: none; padding: 10px 18px; border-radius: 12px; font-size: 0.95rem; font-weight: 700; transition: all 0.3s; text-transform: uppercase; letter-spacing: 1px; }
        .admin-nav a:hover { color: #00f0ff; background: rgba(0,240,255,0.1); box-shadow: 0 0 15px rgba(0,240,255,0.2); }
        .admin-nav a.active { color: #050812; background: #00f0ff; box-shadow: 0 0 20px rgba(0,240,255,0.6); }

        .container { max-width: 1400px; margin: 0 auto; padding: 40px 24px; position: relative; z-index: 2; }

        .dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 20px; }
        .dashboard-title { font-size: 2rem; font-weight: 900; color: white; text-shadow: 0 0 15px rgba(0, 240, 255, 0.4); text-transform: uppercase; letter-spacing: 1px; display: flex; align-items: center; gap: 12px;}
        
        .btn-action {
            padding: 12px 24px; 
            background: linear-gradient(135deg, #00f0ff, #0051ff);
            color: white; 
            text-decoration:none; 
            border-radius: 12px; 
            font-weight: 800; 
            font-size: 0.95rem;
            border: none;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            box-shadow: 0 0 15px rgba(0, 240, 255, 0.4);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 25px rgba(0, 240, 255, 0.6);
        }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; margin-bottom: 40px; }
        .stat-card { 
            background: var(--dark-2); 
            padding: 30px; 
            border-radius: 20px; 
            border: 1px solid var(--border); 
            box-shadow: 0 20px 40px rgba(0,0,0,0.5), inset 0 0 20px rgba(0, 240, 255, 0.05);
            display: flex; align-items: center; gap: 20px; 
            backdrop-filter: blur(12px);
            transition: all 0.3s ease;
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,0.6), inset 0 0 20px rgba(0, 240, 255, 0.1); border-color: rgba(0,240,255,0.4); }
        .stat-icon { width: 60px; height: 60px; border-radius: 16px; background: rgba(0, 240, 255, 0.1); display: flex; align-items: center; justify-content: center; font-size: 1.8rem; color: #00f0ff; border: 1px solid rgba(0, 240, 255, 0.2); }
        
        .stat-card:nth-child(2) .stat-icon { background: rgba(155, 89, 182, 0.1); color: #9b59b6; border-color: rgba(155, 89, 182, 0.2); }
        .stat-card:nth-child(3) .stat-icon { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-color: rgba(245, 158, 11, 0.2); }
        
        .stat-info h3 { font-size: 0.85rem; color: #94a3b8; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 8px; }
        .stat-info .value { font-size: 2rem; font-weight: 900; color: white; letter-spacing: -1px; }

        .table-wrapper { 
            background: var(--dark-2); 
            backdrop-filter: blur(12px); 
            border-radius: 20px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.5), inset 0 0 20px rgba(0, 240, 255, 0.05); 
            overflow-x: auto; 
            border: 1px solid var(--border); 
        }
        
        table { width: 100%; border-collapse: collapse; }
        table thead { background: rgba(0, 0, 0, 0.5); border-bottom: 1px solid var(--border); }
        table th { padding: 20px 24px; text-align: left; font-weight: 800; color: #00f0ff; text-transform: uppercase; letter-spacing: 1.5px; font-size: 0.85rem; white-space: nowrap; }
        table tbody tr { border-bottom: 1px solid rgba(255, 255, 255, 0.05); transition: background-color 0.3s; }
        table tbody tr:hover { background-color: rgba(0, 240, 255, 0.05); }
        table td { padding: 20px 24px; font-size: 0.95rem; color: #e2e8f0; font-weight: 500;}
        
        .vip-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; background: rgba(245, 158, 11, 0.15); color: #f59e0b; border-radius: 20px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; border: 1px solid rgba(245, 158, 11, 0.3); }
        .tag-badge { display: inline-flex; align-items: center; padding: 6px 12px; background: rgba(155, 89, 182, 0.1); color: #9b59b6; border-radius: 8px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; border: 1px solid rgba(155, 89, 182, 0.3); }
        .tag-badge.mixed { background: rgba(0, 240, 255, 0.1); color: #00f0ff; border-color: rgba(0, 240, 255, 0.3); }
        .tag-badge.booking { background: rgba(16, 185, 129, 0.1); color: #10b981; border-color: rgba(16, 185, 129, 0.3); }
        
        .money { font-weight: 800; color: #10b981; }
        .money.high { color: #f59e0b; text-shadow: 0 0 10px rgba(245, 158, 11, 0.3); }

        .customer-avatar { width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(135deg, #00f0ff, #0051ff); color: white; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.2rem; margin-right: 15px; box-shadow: 0 0 15px rgba(0, 240, 255, 0.4);}
        .customer-name-cell { display: flex; align-items: center; }
        .customer-details { display: flex; flex-direction: column; }
        .customer-email { font-size: 0.8rem; color: #94a3b8; margin-top: 4px; }
        
        .empty-state { padding: 60px 20px; text-align: center; color: #94a3b8; }
        .empty-state i { font-size: 3rem; color: rgba(0,240,255,0.3); margin-bottom: 20px; }
        .empty-state h3 { font-size: 1.5rem; color: white; margin-bottom: 10px; font-weight: 800; }

        @media(max-width: 1024px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media(max-width: 768px) {
            .stats-grid { grid-template-columns: 1fr; }
            .dashboard-header { flex-direction: column; align-items: flex-start; }
            .admin-nav { flex-wrap: wrap; height: auto; padding: 15px 24px; gap: 15px; }
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="admin-nav">
            <a href="admin-dashboard.php" class="admin-logo">
                <i class="fas fa-layer-group" style="color:#00f0ff; margin-right: 5px; text-shadow: 0 0 10px currentColor;"></i> 
                <span>NEXUS ADMIN</span>
            </a>
            <div class="admin-nav-links">
                <a href="admin-dashboard.php"><i class="fas fa-satellite-dish"></i> Matrix Hub</a>
                <a href="admin-events.php"><i class="fas fa-project-diagram"></i> Network Nodes</a>
                <a href="admin-analytics.php"><i class="fas fa-chart-pie"></i> Data Telemetry</a>
                <a href="customers.php" class="active"><i class="fas fa-address-book"></i> Entity Records</a>
                <a href="event-calendar.php"><i class="fas fa-calendar-alt"></i> Temporal Matrix</a>
                <a href="logout.php"><i class="fas fa-power-off"></i> Terminate</a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="dashboard-header">
            <h1 class="dashboard-title"><i class="fas fa-users-cog"></i> Global Entity Array</h1>
            <a href="javascript:window.print()" class="btn-action"><i class="fas fa-print"></i> Export Array</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <h3>Identified Entities</h3>
                    <div class="value"><?php echo number_format($total_customers); ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-crown"></i></div>
                <div class="stat-info">
                    <h3>VIP / Platinum Access</h3>
                    <div class="value"><?php echo number_format($top_spenders); ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-coins"></i></div>
                <div class="stat-info">
                    <h3>Total Network Revenue</h3>
                    <div class="value">$<?php echo number_format($total_revenue, 2); ?></div>
                </div>
            </div>
        </div>

        <div class="table-wrapper">
            <?php if ($total_customers > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Entity Designation</th>
                        <th>Comm Frequency</th>
                        <th>Interactions</th>
                        <th>Category Match</th>
                        <th>Last Synchronization</th>
                        <th>Total Value (LTV)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($customers as $c): 
                        $is_vip = $c['total_spent'] >= 500;
                        $money_class = $is_vip ? 'money high' : 'money';
                        $initial = !empty($c['name']) ? strtoupper(substr($c['name'], 0, 1)) : '?';
                        
                        $tag_class = 'tag-badge';
                        if ($c['type'] == 'Mixed (Bookings & Events)') {
                            $tag_class .= ' mixed';
                            $c_type = 'Hybrid';
                        } elseif ($c['type'] == 'Service Booking') {
                            $tag_class .= ' booking';
                            $c_type = 'Booking';
                        } else {
                            $c_type = 'Event Reg';
                        }
                    ?>
                    <tr>
                        <td>
                            <div class="customer-name-cell">
                                <div class="customer-avatar"><?php echo $initial; ?></div>
                                <div class="customer-details">
                                    <strong>
                                        <?php echo htmlspecialchars($c['name'] ?: 'Unknown Entity'); ?>
                                        <?php if($is_vip): ?> <span class="vip-badge" style="margin-left: 10px;"><i class="fas fa-star"></i> VIP</span> <?php endif; ?>
                                    </strong>
                                    <span class="customer-email"><?php echo htmlspecialchars($c['email']); ?></span>
                                </div>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($c['phone']); ?></td>
                        <td><strong><?php echo $c['total_events']; ?></strong> nodes</td>
                        <td><span class="<?php echo $tag_class; ?>"><?php echo $c_type; ?></span></td>
                        <td><?php echo $c['last_active'] ? date('M d, Y', strtotime($c['last_active'])) : 'Unknown'; ?></td>
                        <td class="<?php echo $money_class; ?>">
                            $<?php echo number_format($c['total_spent'], 2); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-user-astronaut"></i>
                <h3>Array is Empty</h3>
                <p>No entity records extracted from the matrix.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
