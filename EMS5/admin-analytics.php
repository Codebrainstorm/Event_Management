<?php
require 'config.php';
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php'); exit;
}

// === BOOKINGS STATS ===
$total_bookings  = intval(($connection->query("SELECT COUNT(*) c FROM bookings")->fetch_assoc())['c'] ?? 0);
$pending  = intval(($connection->query("SELECT COUNT(*) c FROM bookings WHERE status='Pending'")->fetch_assoc())['c'] ?? 0);
$approved = intval(($connection->query("SELECT COUNT(*) c FROM bookings WHERE status='Approved'")->fetch_assoc())['c'] ?? 0);
$completed = intval(($connection->query("SELECT COUNT(*) c FROM bookings WHERE status='Completed'")->fetch_assoc())['c'] ?? 0);
$cancelled = intval(($connection->query("SELECT COUNT(*) c FROM bookings WHERE status='Cancelled'")->fetch_assoc())['c'] ?? 0);
$upcoming  = intval(($connection->query("SELECT COUNT(*) c FROM bookings WHERE event_date > NOW()")->fetch_assoc())['c'] ?? 0);
$total_guests = intval(($connection->query("SELECT SUM(guest_count) c FROM bookings")->fetch_assoc())['c'] ?? 0);

// === EARNINGS ===
$total_earned = floatval(($connection->query("SELECT SUM(amount_paid) c FROM bookings WHERE payment_status='Paid'")->fetch_assoc())['c'] ?? 0);
$premium_earned  = floatval(($connection->query("SELECT SUM(amount_paid) c FROM bookings WHERE payment_status='Paid' AND tier='Premium'")->fetch_assoc())['c'] ?? 0);
$ordinary_earned = floatval(($connection->query("SELECT SUM(amount_paid) c FROM bookings WHERE payment_status='Paid' AND tier='Ordinary'")->fetch_assoc())['c'] ?? 0);
$pending_revenue = floatval(($connection->query("SELECT SUM(price) c FROM bookings WHERE status IN('Pending','Approved') AND payment_status!='Paid'")->fetch_assoc())['c'] ?? 0);

// Earnings by event type
$earnings_by_type_result = $connection->query("SELECT event_type, SUM(amount_paid) total, COUNT(*) cnt, tier FROM bookings WHERE payment_status='Paid' GROUP BY event_type, tier ORDER BY total DESC");
$earnings_by_type = $earnings_by_type_result ? $earnings_by_type_result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Network Analytics — EMS Admin</title>
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
        .page-title h1 { font-size:2rem; font-weight:800; color:white; text-shadow: 0 0 20px rgba(0, 240, 255, 0.3); margin-bottom: 5px; display: flex; align-items: center; gap: 15px; }
        .page-title p { color:var(--text-muted); font-size:1rem; }
        
        .btn-glass { padding:10px 20px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.2); color:white; border-radius:10px; font-weight:700; cursor:pointer; text-decoration:none; transition:all 0.3s; display:inline-flex; align-items:center; gap:8px; backdrop-filter: blur(4px); font-family:'Outfit',sans-serif; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px;}
        .btn-glass:hover { background:rgba(0,240,255,0.1); border-color:var(--neon-blue); color:var(--neon-blue); box-shadow: 0 0 15px rgba(0,240,255,0.2); }

        /* STAT CARDS */
        .stats-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:20px; margin-bottom:32px; }
        .stat-card { background:var(--card-bg); border-radius:16px; padding:24px; box-shadow:0 10px 30px rgba(0,0,0,0.5); border:1px solid var(--border-color); position:relative; overflow:hidden; transition:all 0.2s; backdrop-filter: blur(12px); }
        .stat-card:hover { transform:translateY(-3px); box-shadow:0 15px 40px rgba(0,0,0,0.7), inset 0 0 20px rgba(0,240,255,0.1); border-color: var(--neon-blue); }
        .stat-card::before { content:''; position:absolute; top:0; left:0; right:0; height:4px; }
        .stat-card.purple::before { background:linear-gradient(90deg,var(--neon-purple),#d946ef); }
        .stat-card.orange::before { background:linear-gradient(90deg,var(--neon-gold),#fbbf24); }
        .stat-card.green::before  { background:linear-gradient(90deg,var(--neon-green),#34d399); }
        .stat-card.blue::before   { background:linear-gradient(90deg,var(--neon-blue),#3b82f6); }
        .stat-card.red::before    { background:linear-gradient(90deg,#ef4444,#f87171); }
        
        .stat-icon { font-size:2.2rem; margin-bottom:15px; color: var(--neon-blue); text-shadow: 0 0 15px rgba(0,240,255,0.4); }
        .stat-card.purple .stat-icon { color: var(--neon-purple); text-shadow: 0 0 15px rgba(155,89,182,0.4); }
        .stat-card.orange .stat-icon { color: var(--neon-gold); text-shadow: 0 0 15px rgba(245,158,11,0.4); }
        .stat-card.green .stat-icon { color: var(--neon-green); text-shadow: 0 0 15px rgba(16,185,129,0.4); }
        
        .stat-label { font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; font-weight:700; margin-bottom:6px; }
        .stat-value { font-size:2.5rem; font-weight:900; color:white; line-height: 1.2; text-shadow: 0 0 10px rgba(255,255,255,0.1); }
        .stat-sub { font-size:0.75rem; color:var(--text-muted); margin-top:8px; font-weight: 500; font-family: monospace; }
        
        /* SECTION */
        .section { background:var(--card-bg); border-radius:20px; padding:28px; box-shadow:0 15px 40px rgba(0,0,0,0.5); border:1px solid var(--border-color); margin-bottom:24px; backdrop-filter: blur(12px); overflow: hidden; position: relative; }
        .section-title { font-size:1.3rem; font-weight:800; color:white; margin-bottom:25px; display:flex; align-items:center; gap:12px; text-transform: uppercase; letter-spacing: 1px; }
        .section-title i { color:var(--neon-blue); text-shadow: 0 0 15px rgba(0,240,255,0.4); }
        .section::after { content: ''; position: absolute; inset: 0; background: linear-gradient(135deg, transparent, rgba(0,240,255,0.02)); pointer-events: none; }
        
        /* Earnings table */
        .earn-table { width:100%; border-collapse:collapse; }
        .earn-table th { background:rgba(255,255,255,0.03); color:var(--text-muted); font-size:0.8rem; text-transform:uppercase; letter-spacing:1px; padding:18px 20px; font-weight:800; border-bottom:1px solid rgba(255,255,255,0.1); white-space:nowrap; text-align: left;}
        .earn-table td { padding:16px 20px; border-bottom:1px solid rgba(255,255,255,0.05); font-size:0.95rem; vertical-align:middle; transition: background 0.2s; color: white; }
        .earn-table tr:hover td { background:rgba(0,240,255,0.03); }
        .earn-table tr:last-child td { border-bottom:none; }
        
        .tier-pill { display:inline-flex; align-items:center; gap:6px; padding:4px 12px; border-radius:20px; font-size:0.75rem; font-weight:800; text-transform: uppercase; border: 1px solid transparent; }
        .tier-premium { background:rgba(245,158,11,0.15); color:var(--neon-gold); border-color: rgba(245,158,11,0.3); box-shadow: inset 0 0 10px rgba(245,158,11,0.1); }
        .tier-ordinary { background:rgba(255,255,255,0.05); color:var(--text-main); border-color: rgba(255,255,255,0.1); }
        .amount { font-weight:800; color:var(--neon-green); font-family: monospace; font-size: 1.1rem; text-shadow: 0 0 10px rgba(16,185,129,0.3); }

        /* Summary rows */
        .summary-row { display:flex; justify-content:space-between; align-items:center; padding:20px 0; border-bottom:1px solid rgba(255,255,255,0.05); }
        .summary-row:last-child { border-bottom:none; padding-bottom: 0; }
        .summary-label { font-size:1rem; color:var(--text-main); font-weight:600; }
        .summary-value { font-size:1.5rem; font-weight:900; color:var(--neon-blue); text-shadow: 0 0 15px rgba(0,240,255,0.3); }
        
        .progress-bar { height:8px; background:rgba(255,255,255,0.05); border-radius:10px; overflow:hidden; margin-top:10px; flex:1; max-width:250px; border: 1px solid rgba(255,255,255,0.1); }
        .progress-fill { height:100%; border-radius:10px; background:linear-gradient(90deg,var(--neon-blue),#0051ff); transition:width 0.8s ease; box-shadow: 0 0 10px rgba(0,240,255,0.5); }
        .progress-fill.gold { background:linear-gradient(90deg,var(--neon-gold),#d97706); box-shadow: 0 0 10px rgba(245,158,11,0.5); }
        .progress-fill.red { background:linear-gradient(90deg,#ef4444,#991b1b); box-shadow: 0 0 10px rgba(239,68,68,0.5); }

        /* Earnings split */
        .earn-split { display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-top:20px; }
        .earn-split-card { padding:24px; border-radius:16px; border:1px solid; background: rgba(0,0,0,0.3); position: relative; overflow: hidden; }
        .earn-split-card.premium { border-color:rgba(245,158,11,0.3); box-shadow: inset 0 0 20px rgba(245,158,11,0.05); }
        .earn-split-card.ordinary { border-color:rgba(255,255,255,0.1); box-shadow: inset 0 0 20px rgba(255,255,255,0.02); }
        .earn-split-card .label { font-size:0.85rem; text-transform:uppercase; letter-spacing:1px; font-weight:800; margin-bottom:12px; display: flex; align-items: center; gap: 8px; }
        .earn-split-card.premium .label { color:var(--neon-gold); }
        .earn-split-card.ordinary .label { color:white; }
        .earn-split-card .value { font-size:2.5rem; font-weight:900; }
        .earn-split-card.premium .value { color:var(--neon-gold); text-shadow: 0 0 20px rgba(245,158,11,0.4); }
        .earn-split-card.ordinary .value { color:white; }
        
        .table-wrap { overflow-x: auto; }

        @media(max-width:768px){ .stats-grid{grid-template-columns:1fr 1fr;} .earn-split{grid-template-columns:1fr;} .earn-table{font-size:0.8rem;} }
        @media(max-width:480px){ .stats-grid{grid-template-columns:1fr;} }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="admin-nav">
            <a href="admin-dashboard.php" class="admin-logo"><i class="fas fa-cube" style="color:#00f0ff;"></i> <span>NEXUS ADMIN</span></a>
            <div class="admin-nav-links">
                <a href="admin-dashboard.php" title="Bookings"><i class="fas fa-list"></i></a>
                <a href="admin-events.php" title="Events"><i class="fas fa-database"></i></a>
                <a href="admin-analytics.php" class="active" title="Analytics"><i class="fas fa-chart-line"></i></a>
                <a href="customers.php" title="Customers"><i class="fas fa-user-astronaut"></i></a>
                <a href="event-calendar.php" title="Matrix"><i class="fas fa-border-all"></i></a>
                <a href="logout.php" title="Disconnect"><i class="fas fa-power-off"></i></a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="page-header">
            <div class="page-title">
                <h1><i class="fas fa-chart-network" style="color:var(--neon-blue);"></i> Network Analytics</h1>
                <p>Complete financial overview and structural performance metrics.</p>
            </div>
            <a href="customers.php" class="btn-glass"><i class="fas fa-user-astronaut"></i> View Entities</a>
        </div>

        <!-- SUMMARY STATS ROW -->
        <div class="stats-grid">
            <div class="stat-card purple">
                <div class="stat-icon"><i class="fas fa-cubes"></i></div>
                <div class="stat-label">Total Engagements</div>
                <div class="stat-value"><?php echo $total_bookings; ?></div>
                <div class="stat-sub">ALL-TIME NETWORK REQUESTS</div>
            </div>
            <div class="stat-card green">
                <div class="stat-icon"><i class="fas fa-wallet"></i></div>
                <div class="stat-label">Total Accrued</div>
                <div class="stat-value">$<?php echo number_format($total_earned, 0); ?></div>
                <div class="stat-sub">VERIFIED PAID TRANSACTIONS</div>
            </div>
            <div class="stat-card orange">
                <div class="stat-icon"><i class="fas fa-crown"></i></div>
                <div class="stat-label">Premium Yield</div>
                <div class="stat-value">$<?php echo number_format($premium_earned, 0); ?></div>
                <div class="stat-sub"><?php echo $total_earned > 0 ? round($premium_earned/$total_earned*100) : 0; ?>% OF TOTAL REVENUE</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-ticket-alt" style="color:#e2e8f0;"></i></div>
                <div class="stat-label">Standard Yield</div>
                <div class="stat-value">$<?php echo number_format($ordinary_earned, 0); ?></div>
                <div class="stat-sub"><?php echo $total_earned > 0 ? round($ordinary_earned/$total_earned*100) : 0; ?>% OF TOTAL REVENUE</div>
            </div>
            <div class="stat-card blue">
                <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
                <div class="stat-label">Pending Yield</div>
                <div class="stat-value">$<?php echo number_format($pending_revenue, 0); ?></div>
                <div class="stat-sub">AWAITING FINALIZATION</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users" style="color:#60a5fa;"></i></div>
                <div class="stat-label">Sub-Entities</div>
                <div class="stat-value"><?php echo number_format($total_guests); ?></div>
                <div class="stat-sub">ACROSS VERIFIED NODES</div>
            </div>
            <div class="stat-card green">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-label">Completed</div>
                <div class="stat-value"><?php echo $completed; ?></div>
                <div class="stat-sub"><?php echo $total_bookings > 0 ? round($completed/$total_bookings*100) : 0; ?>% SUCCESS RATE</div>
            </div>
            <div class="stat-card blue">
                <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
                <div class="stat-label">Upcoming Nodes</div>
                <div class="stat-value"><?php echo $upcoming; ?></div>
                <div class="stat-sub">SCHEDULED IN CALENDAR</div>
            </div>
        </div>

        <!-- EARNINGS SPLIT -->
        <div class="section">
            <div class="section-title"><i class="fas fa-chart-pie"></i> Financial Distribution</div>
            <div class="earn-split">
                <div class="earn-split-card premium">
                    <div class="label"><i class="fas fa-crown"></i> Premium Tier Volume</div>
                    <div class="value">$<?php echo number_format($premium_earned, 2); ?></div>
                    <div style="font-size:0.8rem; color:white; margin-top:8px; font-weight: 500; font-family: monospace;">[ <?php echo $total_earned > 0 ? round($premium_earned/$total_earned*100) : 0; ?>% DEPENDENCY ]</div>
                    <div class="progress-bar" style="max-width:100%; margin-top:15px; background:rgba(245,158,11,0.1); border-color: rgba(245,158,11,0.2);"><div class="progress-fill gold" style="width:<?php echo $total_earned > 0 ? round($premium_earned/$total_earned*100) : 0; ?>%"></div></div>
                </div>
                <div class="earn-split-card ordinary">
                    <div class="label"><i class="fas fa-ticket-alt"></i> Standard Tier Volume</div>
                    <div class="value">$<?php echo number_format($ordinary_earned, 2); ?></div>
                    <div style="font-size:0.8rem; color:var(--text-muted); margin-top:8px; font-weight: 500; font-family: monospace;">[ <?php echo $total_earned > 0 ? round($ordinary_earned/$total_earned*100) : 0; ?>% DEPENDENCY ]</div>
                    <div class="progress-bar" style="max-width:100%; margin-top:15px;"><div class="progress-fill" style="width:<?php echo $total_earned > 0 ? round($ordinary_earned/$total_earned*100) : 0; ?>%"></div></div>
                </div>
            </div>
        </div>

        <!-- EARNINGS BY EVENT TYPE -->
        <div class="section">
            <div class="section-title"><i class="fas fa-table"></i> Yield by Matrix Category</div>
            <?php if (!empty($earnings_by_type)): ?>
            <div class="table-wrap">
                <table class="earn-table">
                    <thead>
                        <tr>
                            <th>Classification</th>
                            <th>Tier Designation</th>
                            <th>Requests</th>
                            <th>Extracted Value</th>
                            <th>Distribution Share</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($earnings_by_type as $row):
                            $share = $total_earned > 0 ? round($row['total']/$total_earned*100) : 0;
                        ?>
                        <tr>
                            <td style="font-weight:700; color: white;"><i class="fas fa-cube" style="color:var(--neon-blue); font-size: 0.8rem; margin-right: 5px;"></i> <?php echo htmlspecialchars($row['event_type']); ?></td>
                            <td><span class="tier-pill <?php echo $row['tier'] === 'Premium' ? 'tier-premium' : 'tier-ordinary'; ?>"><?php echo $row['tier'] === 'Premium' ? '<i class="fas fa-crown"></i>' : '<i class="fas fa-ticket-alt"></i>'; ?> <?php echo $row['tier']; ?></span></td>
                            <td style="font-family: monospace; font-size: 1.1rem;"><?php echo $row['cnt']; ?></td>
                            <td class="amount">$<?php echo number_format($row['total'], 2); ?></td>
                            <td>
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div class="progress-bar" style="max-width:120px; margin-top: 0;"><div class="progress-fill <?php echo $row['tier']==='Premium' ? 'gold' : ''; ?>" style="width:<?php echo $share; ?>%"></div></div>
                                    <span style="font-size:0.85rem; color:var(--text-muted); font-family: monospace; font-weight: 700;"><?php echo $share; ?>%</span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                <i class="fas fa-database" style="font-size: 3rem; margin-bottom: 15px; color: var(--border-color);"></i>
                <p style="font-size: 1.1rem;">Insufficient data in ledger. Process engagements to populate yield data.</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- SUMMARY STATS -->
        <div class="section">
            <div class="section-title"><i class="fas fa-tachometer-alt"></i> System Metrics</div>
            <div class="summary-row">
                <span class="summary-label">Operation Success Rate</span>
                <div style="display:flex; align-items:center; gap:20px; flex:1; justify-content:flex-end;">
                    <div class="progress-bar"><div class="progress-fill" style="width:<?php echo $total_bookings > 0 ? round($completed/$total_bookings*100) : 0; ?>%"></div></div>
                    <span class="summary-value"><?php echo $total_bookings > 0 ? round($completed/$total_bookings*100) : 0; ?>%</span>
                </div>
            </div>
            <div class="summary-row">
                <span class="summary-label">Authorization Rate</span>
                <div style="display:flex; align-items:center; gap:20px; flex:1; justify-content:flex-end;">
                    <div class="progress-bar"><div class="progress-fill" style="width:<?php echo $total_bookings > 0 ? round($approved/$total_bookings*100) : 0; ?>%"></div></div>
                    <span class="summary-value" style="color: white;"><?php echo $total_bookings > 0 ? round($approved/$total_bookings*100) : 0; ?>%</span>
                </div>
            </div>
            <div class="summary-row">
                <span class="summary-label">Termination Rate</span>
                <div style="display:flex; align-items:center; gap:20px; flex:1; justify-content:flex-end;">
                    <div class="progress-bar" style="background: rgba(239,68,68,0.1); border-color: rgba(239,68,68,0.2);"><div class="progress-fill red" style="width:<?php echo $total_bookings > 0 ? round($cancelled/$total_bookings*100) : 0; ?>%"></div></div>
                    <span class="summary-value" style="color:#ef4444;text-shadow: 0 0 15px rgba(239, 68, 68, 0.4);"><?php echo $total_bookings > 0 ? round($cancelled/$total_bookings*100) : 0; ?>%</span>
                </div>
            </div>
            <div class="summary-row">
                <span class="summary-label">Mean Sub-Entities per Operation</span>
                <span class="summary-value" style="color: white; font-family: monospace; font-size: 1.8rem;"><?php echo $total_bookings > 0 ? round($total_guests/$total_bookings) : 0; ?> <span style="font-size:0.8rem; color:var(--text-muted); font-family: 'Outfit';">UNITS</span></span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Mean Yield per Operation</span>
                <span class="summary-value" style="color: var(--neon-green); text-shadow: 0 0 15px rgba(16,185,129,0.3); font-family: monospace; font-size: 1.8rem;">$<?php echo $total_bookings > 0 ? number_format($total_earned/$total_bookings, 2) : '0.00'; ?></span>
            </div>
        </div>
    </div>
</body>
</html>
