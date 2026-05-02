<?php
require 'config.php';
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php'); exit;
}

// Fetch all bookings AND events for calendar
$bookings_result = $connection->query("SELECT id, event_type, customer_name, email, phone, event_date, guest_count, special_requests, status, price, tier FROM bookings");
$bookings = $bookings_result ? $bookings_result->fetch_all(MYSQLI_ASSOC) : [];

$events_result = $connection->query("SELECT id, title, event_date, category, location, price, tier, status FROM events WHERE active=1 AND status='approved'");
$db_events = $events_result ? $events_result->fetch_all(MYSQLI_ASSOC) : [];

$calendar_events = [];

// Add bookings to calendar
foreach ($bookings as $b) {
    // Cyberpunk/neon inspired colors
    $colors = ['Pending' => '#f59e0b', 'Approved' => '#10b981', 'Completed' => '#00f0ff', 'Cancelled' => '#ef4444'];
    $textColors = ['Pending' => '#050812', 'Approved' => '#050812', 'Completed' => '#050812', 'Cancelled' => '#fff'];
    $bg   = $colors[$b['status']] ?? '#9b59b6';
    $text = $textColors[$b['status']] ?? '#fff';
    $calendar_events[] = [
        'id'              => 'b_' . $b['id'],
        'title'           => ($b['tier'] === 'Premium' ? '👑' : '🎟') . ' ' . $b['event_type'] . ' - ' . $b['customer_name'],
        'start'           => date('Y-m-d', strtotime($b['event_date'])),
        'backgroundColor' => $bg,
        'borderColor'     => $bg,
        'textColor'       => $text,
        'extendedProps'   => [
            'type'             => 'booking',
            'customer_name'    => $b['customer_name'],
            'email'            => $b['email'],
            'phone'            => $b['phone'],
            'event_type'       => $b['event_type'],
            'guest_count'      => $b['guest_count'],
            'special_requests' => $b['special_requests'],
            'status'           => $b['status'],
            'event_date'       => $b['event_date'],
            'price'            => number_format($b['price'] ?? 0, 2),
            'tier'             => $b['tier'] ?? 'Ordinary',
        ]
    ];
}

// Add registered events to calendar
foreach ($db_events as $e) {
    $isPremium = $e['tier'] === 'Premium';
    $bg = $isPremium ? '#f59e0b' : '#9b59b6';
    $calendar_events[] = [
        'id'              => 'e_' . $e['id'],
        'title'           => ($isPremium ? '⭐' : '📌') . ' ' . $e['title'],
        'start'           => date('Y-m-d', strtotime($e['event_date'])),
        'backgroundColor' => $bg,
        'borderColor'     => $bg,
        'textColor'       => $isPremium ? '#050812' : '#fff',
        'extendedProps'   => [
            'type'       => 'event',
            'category'   => $e['category'],
            'location'   => $e['location'],
            'price'      => number_format($e['price'] ?? 0, 2),
            'tier'       => $e['tier'] ?? 'Ordinary',
            'status'     => $e['status'],
            'event_date' => $e['event_date'],
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Matrix Calendar — EMS Admin</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
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
        body { font-family: 'Outfit', sans-serif; background: var(--dark); color: var(--text); overflow-x: hidden; }
        
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

        /* Calendar wrapper */
        .calendar-header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 16px; }
        .calendar-header-bar h1 { font-size: 2rem; font-weight: 900; color: white; text-shadow: 0 0 15px rgba(0, 240, 255, 0.4); text-transform: uppercase; letter-spacing: 1px;}

        .btn-back {
            padding: 12px 24px; 
            background: rgba(255, 255, 255, 0.05); 
            color: #cbd5e1; 
            text-decoration:none; 
            border-radius: 12px; 
            font-weight: 800; 
            font-size: 0.95rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            backdrop-filter: blur(5px);
        }
        .btn-back:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border-color: rgba(255, 255, 255, 0.4);
            transform: translateY(-2px);
        }

        .calendar-wrapper { 
            background: var(--dark-2); 
            border-radius: 20px; 
            padding: 28px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.5), inset 0 0 20px rgba(0, 240, 255, 0.05); 
            border: 1px solid var(--border); 
            backdrop-filter: blur(12px);
        }

        /* FullCalendar overrides for Dark Nexus */
        .fc { font-family: 'Outfit', sans-serif; }
        .fc-toolbar-title { font-size: 1.4rem !important; font-weight: 900 !important; color: white !important; text-shadow: 0 0 15px rgba(0, 240, 255, 0.3); text-transform: uppercase; letter-spacing: 1px;}
        .fc-button-primary { 
            background: rgba(0, 240, 255, 0.1) !important; 
            color: var(--neon-blue) !important; 
            border: 1px solid var(--border) !important; 
            font-family: 'Outfit', sans-serif !important; 
            font-weight: 800 !important; 
            border-radius: 10px !important; 
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 0 10px rgba(0, 240, 255, 0.1);
            transition: all 0.3s ease !important;
        }
        .fc-button-primary:hover { 
            background: var(--neon-blue) !important; 
            color: var(--dark) !important; 
            box-shadow: 0 0 20px rgba(0, 240, 255, 0.5) !important;
        }
        .fc-button-primary:not(:disabled).fc-button-active { 
            background: var(--neon-blue) !important; 
            color: var(--dark) !important; 
            border-color: var(--neon-blue) !important;
            box-shadow: 0 0 20px rgba(0, 240, 255, 0.5) !important;
        }
        
        .fc-theme-standard td, .fc-theme-standard th, .fc-theme-standard .fc-scrollgrid {
            border-color: rgba(255, 255, 255, 0.05) !important;
        }
        
        .fc-col-header-cell { background: rgba(0, 0, 0, 0.5) !important; border-bottom: 1px solid var(--border) !important;}
        .fc-col-header-cell-cushion { color: var(--neon-blue) !important; font-weight: 800 !important; padding: 12px 4px !important; letter-spacing: 1.5px; text-transform: uppercase;}
        
        .fc-daygrid-day-number { color: #94a3b8 !important; font-weight: 700 !important; padding: 8px !important; }
        .fc-day-today { background: rgba(0, 240, 255, 0.05) !important; }
        
        .fc-event { 
            border-radius: 6px !important; 
            font-size: 0.8rem !important; 
            font-weight: 800 !important; 
            padding: 4px 8px !important; 
            cursor: pointer !important; 
            border: none !important;
            margin-bottom: 3px !important;
            transition: all 0.3s ease !important;
        }
        .fc-event:hover { transform: scale(1.02); box-shadow: 0 4px 15px rgba(0,0,0,0.5); z-index: 5 !important;}

        /* Legend */
        .legend { margin-top: 30px; padding-top: 25px; border-top: 1px solid rgba(255, 255, 255, 0.05); }
        .legend h3 { font-size: 1rem; font-weight: 900; color: var(--neon-blue); text-transform: uppercase; letter-spacing: 2px; margin-bottom: 20px; text-shadow: 0 0 10px rgba(0, 240, 255, 0.3);}
        .legend-grid { display: flex; flex-wrap: wrap; gap: 20px; }
        .legend-item { display: flex; align-items: center; gap: 10px; font-size: 0.85rem; color: #cbd5e1; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;}
        .legend-dot { width: 14px; height: 14px; border-radius: 50%; flex-shrink: 0; box-shadow: 0 0 15px currentColor;}

        /* Modal */
        .modal { display: none; position: fixed; z-index: 2000; inset: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(8px); animation: fadeIn 0.2s ease; }
        @keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
        
        .modal-content { 
            background: rgba(8, 11, 26, 0.95); 
            margin: 5% auto; 
            padding: 0; 
            border-radius: 20px; 
            box-shadow: 0 20px 60px rgba(0,0,0,0.8), inset 0 0 20px rgba(0, 240, 255, 0.05); 
            width: 90%; 
            max-width: 500px; 
            overflow: hidden; 
            animation: slideUp 0.3s ease; 
            border: 1px solid rgba(0,240,255,0.2);
        }
        
        @keyframes slideUp { from { transform: translateY(30px) scale(0.95); opacity:0; } to { transform: translateY(0) scale(1); opacity:1; } }
        
        .modal-header { background: rgba(0, 240, 255, 0.1); color: #00f0ff; padding: 22px 26px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(0,240,255,0.2); }
        .modal-header.premium { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-bottom: 1px solid rgba(245, 158, 11, 0.2); }
        
        .modal-header h2 { font-size: 1.2rem; font-weight: 900; letter-spacing: 1px; text-transform: uppercase; text-shadow: 0 0 10px currentColor;}
        .modal-close { background: none; border: none; color: inherit; font-size: 1.5rem; cursor: pointer; opacity: 0.7; transition: all 0.3s;}
        .modal-close:hover { opacity: 1; transform: scale(1.1);}
        
        .modal-body { padding: 30px 24px; display: flex; flex-direction: column; gap: 15px;}
        
        .detail-row { display: flex; align-items: flex-start; gap: 14px; padding: 14px; background: rgba(255,255,255,0.03); border-radius: 12px; border-left: 3px solid #00f0ff; }
        .premium .detail-row { border-left-color: #f59e0b; }
        
        .detail-icon { color: #00f0ff; width: 20px; flex-shrink: 0; margin-top: 2px; font-size: 1.1rem;}
        .premium .detail-icon { color: #f59e0b; }
        
        .detail-label { font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; font-weight: 800; letter-spacing: 1px; display: block; margin-bottom: 4px; }
        .detail-value { font-size: 0.95rem; color: #fff; font-weight: 600; }
        
        .modal-footer { padding: 20px 24px; border-top: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: flex-end; }
        
        .btn-close-modal { 
            padding: 12px 28px; 
            background: rgba(255,255,255,0.05); 
            color: #cbd5e1; 
            border: 1px solid rgba(255,255,255,0.2); 
            border-radius: 10px; 
            font-weight: 800; 
            cursor: pointer; 
            font-family: 'Outfit', sans-serif; 
            transition: all 0.3s; 
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
        .btn-close-modal:hover { background: rgba(255,255,255,0.15); color: white; border-color: rgba(255,255,255,0.4); }
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
                <a href="customers.php"><i class="fas fa-address-book"></i> Entity Records</a>
                <a href="event-calendar.php" class="active"><i class="fas fa-calendar-alt"></i> Temporal Matrix</a>
                <a href="logout.php"><i class="fas fa-power-off"></i> Terminate</a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="calendar-header-bar">
            <h1><i class="fas fa-calendar-alt" style="color:#00f0ff;"></i> Temporal Operations Matrix</h1>
            <div style="display:flex; gap:10px;">
                <a href="admin-dashboard.php" class="btn-back"><i class="fas fa-reply"></i> Return to Hub</a>
            </div>
        </div>

        <div class="calendar-wrapper">
            <div id="calendar"></div>
            <div class="legend">
                <h3>Temporal Node Legend</h3>
                <div class="legend-grid">
                    <div class="legend-item"><div class="legend-dot" style="background:#f59e0b; color:#f59e0b;"></div> Pending Protocol</div>
                    <div class="legend-item"><div class="legend-dot" style="background:#10b981; color:#10b981;"></div> Approved Access</div>
                    <div class="legend-item"><div class="legend-dot" style="background:#00f0ff; color:#00f0ff;"></div> Execution Complete</div>
                    <div class="legend-item"><div class="legend-dot" style="background:#ef4444; color:#ef4444;"></div> Signal Cancelled</div>
                    <div class="legend-item"><div class="legend-dot" style="background:#f59e0b; color:#f59e0b;"></div> ⭐ Premium Node</div>
                    <div class="legend-item"><div class="legend-dot" style="background:#9b59b6; color:#9b59b6;"></div> 📌 Standard Node</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Detail Modal -->
    <div id="eventModal" class="modal">
        <div class="modal-content">
            <div class="modal-header" id="modalHeader">
                <h2 id="modalTitle">Node Data</h2>
                <button class="modal-close" onclick="closeModal()">✕</button>
            </div>
            <div class="modal-body" id="modalBody"></div>
            <div class="modal-footer">
                <button class="btn-close-modal" onclick="closeModal()">Close Connection</button>
            </div>
        </div>
    </div>

    <script>
    const calendarData = <?php echo json_encode($calendar_events); ?>;

    document.addEventListener('DOMContentLoaded', function() {
        const el = document.getElementById('calendar');
        const calendar = new FullCalendar.Calendar(el, {
            initialView: 'dayGridMonth',
            initialDate: '<?php echo date('Y-m-d'); ?>',
            locale: 'en',
            firstDay: 1,
            headerToolbar: {
                left:   'prev,next today',
                center: 'title',
                right:  'dayGridMonth,timeGridWeek,listMonth'
            },
            events: calendarData,
            eventClick: function(info) { showDetail(info.event); },
            eventDidMount: function(info) {
                info.el.title = info.event.title;
            }
        });
        calendar.render();
    });

    function showDetail(event) {
        const p = event.extendedProps;
        const isPremium = p.tier === 'Premium';
        const header = document.getElementById('modalHeader');
        const body   = document.getElementById('modalBody');
        
        // Use a wrapper to easily target children for theme changes depending on premium state
        const modalContent = document.querySelector('.modal-content');
        
        if (isPremium) {
            header.className = 'modal-header premium';
            modalContent.classList.add('premium');
        } else {
            header.className = 'modal-header';
            modalContent.classList.remove('premium');
        }

        let html = '';
        if (p.type === 'booking') {
            const dateStr = new Date(p.event_date + 'T00:00').toLocaleDateString('en-US', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
            html += row('fas fa-tag',      'Protocol Subtype',       p.event_type);
            html += row('fas fa-user',     'Initiating Entity',      p.customer_name);
            html += row('fas fa-envelope', 'Direct Signal',          p.email);
            html += row('fas fa-phone',    'Comms Relay',            p.phone);
            html += row('fas fa-calendar', 'Execution Timestamp',    dateStr);
            html += row('fas fa-users',    'Entity Capacity',        p.guest_count + ' entries');
            html += row('fas fa-layer-group', 'Clearance Level',     (isPremium ? '👑 VIP Tier' : '🎟 Standard'));
            html += row('fas fa-dollar-sign', 'Credit Exchange',     '$' + p.price);
            if (p.special_requests) html += row('fas fa-comment', 'Custom Directives', p.special_requests);
            html += row('fas fa-circle',   'Network Status',         p.status);
        } else {
            const dateStr = new Date(p.event_date + 'T00:00').toLocaleDateString('en-US', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
            html += row('fas fa-heading',      'Node Designation',   event.title.replace(/^[⭐📌]\s/, ''));
            html += row('fas fa-network-wired','Architecture',       p.category);
            html += row('fas fa-map-marker-alt','Spatial Override',  p.location);
            html += row('fas fa-calendar',     'System Date',        dateStr);
            html += row('fas fa-layer-group',  'Access Tier',        (isPremium ? '👑 Premium' : '🎟 Ordinary'));
            html += row('fas fa-credit-card',  'Credit Cost',        p.price > 0 ? '$' + p.price : 'Free Broadcast');
        }

        document.getElementById('modalTitle').textContent = event.title.replace(/^[👑🎟⭐📌]\s/, '');
        body.innerHTML = html;
        document.getElementById('eventModal').style.display = 'block';
    }

    function row(icon, label, value) {
        return `<div class="detail-row"><i class="fas ${icon.replace('fas ','')} detail-icon"></i><div><span class="detail-label">${label}</span><span class="detail-value">${escape(value)}</span></div></div>`;
    }
    function escape(t) { if (!t) return '—'; return String(t).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
    function closeModal() { document.getElementById('eventModal').style.display = 'none'; }
    window.onclick = function(e) { if (e.target === document.getElementById('eventModal')) closeModal(); }
    </script>
</body>
</html>
