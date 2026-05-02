<?php
require_once 'config.php';
$page_title = 'Dashboard';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user info
$user_query = "SELECT * FROM users WHERE id = $user_id LIMIT 1";
$user_result = mysqli_query($connection, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Get user's created events
$events_query = "SELECT id, title, event_date, location, category, image FROM events WHERE created_by = $user_id ORDER BY created_at DESC LIMIT 10";
$events_result = mysqli_query($connection, $events_query);
$user_events = mysqli_fetch_all($events_result, MYSQLI_ASSOC);

// Get user's registrations
$registrations_query = "SELECT e.id, e.title, e.event_date, e.location, e.category, e.image FROM registrations r 
                       JOIN events e ON r.event_id = e.id 
                       WHERE r.user_id = $user_id 
                       ORDER BY e.event_date ASC LIMIT 10";
$registrations_result = mysqli_query($connection, $registrations_query);
$registered_events = mysqli_fetch_all($registrations_result, MYSQLI_ASSOC);

// Get stats
$total_created = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as count FROM events WHERE created_by = $user_id"))['count'];
$total_registered = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as count FROM registrations WHERE user_id = $user_id"))['count'];

include 'header.php';
?>

<style>
    .dashboard-container {
        min-height: calc(100vh - 70px);
        padding: 40px 20px;
        position: relative;
    }

    .container {
        max-width: 1400px;
        margin: 0 auto;
        position: relative;
        z-index: 10;
    }

    .dashboard-header {
        background: rgba(8, 11, 26, 0.8);
        border: 1px solid rgba(0, 240, 255, 0.15);
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 15px 40px rgba(0,0,0,0.6);
        margin-bottom: 40px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
        backdrop-filter: blur(12px);
        position: relative;
        overflow: hidden;
    }

    .dashboard-header::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, transparent, rgba(0,240,255,0.03));
        pointer-events: none;
    }

    .dashboard-user-info {
        display: flex;
        align-items: center;
        gap: 20px;
        position: relative;
        z-index: 2;
    }

    .user-avatar {
        width: 80px;
        height: 80px;
        border-radius: 20px;
        background: rgba(0,240,255,0.1);
        border: 1px solid rgba(0,240,255,0.3);
        color: var(--primary, #00f0ff);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        font-weight: 800;
        box-shadow: inset 0 0 20px rgba(0,240,255,0.2);
    }

    .user-details h1 {
        font-size: 1.8rem;
        color: white;
        margin-bottom: 5px;
        font-weight: 800;
        text-shadow: 0 0 20px rgba(0, 240, 255, 0.3);
    }
    
    .user-details span.badge {
        font-size: 0.7rem;
        vertical-align: middle;
        margin-left: 10px;
        background: rgba(0,240,255,0.1);
        color: #00f0ff;
        padding: 4px 10px;
        border-radius: 20px;
        border: 1px solid rgba(0,240,255,0.3);
    }

    .user-details p {
        color: var(--text-muted, #94a3b8);
        font-size: 0.95rem;
        font-family: monospace;
    }

    .dashboard-actions {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        position: relative;
        z-index: 2;
    }

    .btn-neon {
        background: linear-gradient(135deg, var(--primary, #00f0ff), #0051ff);
        color: white;
        padding: 12px 24px;
        border-radius: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        text-decoration: none;
        border: none;
        box-shadow: 0 0 15px rgba(0, 240, 255, 0.3);
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .btn-neon:hover {
        transform: translateY(-2px);
        box-shadow: 0 0 25px rgba(0, 240, 255, 0.5);
    }

    .btn-glass {
        background: rgba(255, 255, 255, 0.05);
        color: white;
        padding: 12px 24px;
        border-radius: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        text-decoration: none;
        border: 1px solid rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(4px);
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .btn-glass:hover {
        background: rgba(255, 255, 255, 0.15);
        border-color: rgba(255, 255, 255, 0.4);
        transform: translateY(-2px);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }

    .stat-card {
        background: rgba(8, 11, 26, 0.8);
        padding: 30px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        text-align: center;
        border: 1px solid rgba(255,255,255,0.05);
        backdrop-filter: blur(12px);
        transition: transform 0.3s ease, border-color 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        border-color: rgba(0,240,255,0.3);
    }
    
    .stat-card::after {
        content: '';
        position: absolute;
        top: 0; left: 0; width: 100%; height: 4px;
        background: linear-gradient(90deg, #00f0ff, #0051ff);
    }
    
    .stat-card:nth-child(2)::after {
        background: linear-gradient(90deg, #9b59b6, #d946ef);
    }

    .stat-number {
        font-size: 3rem;
        font-weight: 900;
        color: white;
        margin-bottom: 5px;
        text-shadow: 0 0 20px rgba(0, 240, 255, 0.4);
        line-height: 1;
    }

    .stat-label {
        color: var(--text-muted, #94a3b8);
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-top: 10px;
    }

    .section {
        margin-bottom: 50px;
    }

    .section-title {
        font-size: 1.5rem;
        color: white;
        margin-bottom: 25px;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .section-title i {
        color: var(--primary, #00f0ff);
        text-shadow: 0 0 15px rgba(0,240,255,0.3);
    }

    .events-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 25px;
    }

    .event-card {
        background: rgba(8, 11, 26, 0.8);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 15px 40px rgba(0,0,0,0.6);
        transition: all 0.4s ease;
        border: 1px solid rgba(0, 240, 255, 0.1);
        backdrop-filter: blur(12px);
        display: flex;
        flex-direction: column;
    }

    .event-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 50px rgba(0,0,0,0.8), inset 0 0 20px rgba(0,240,255,0.05);
        border-color: rgba(0, 240, 255, 0.3);
    }

    .event-card-image-wrap {
        position: relative;
        height: 180px;
        overflow: hidden;
    }

    .event-card-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.8;
        transition: transform 0.5s ease;
    }
    
    .event-card:hover .event-card-image {
        transform: scale(1.1);
        opacity: 1;
    }

    .event-card-image-wrap::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, transparent 0%, rgba(8, 11, 26, 1) 100%);
    }

    .event-card-content {
        padding: 24px;
        display: flex;
        flex-direction: column;
        flex: 1;
        position: relative;
        z-index: 2;
        margin-top: -20px;
    }

    .event-card-title {
        font-size: 1.25rem;
        font-weight: 800;
        margin-bottom: 12px;
        color: white;
        letter-spacing: 0.5px;
    }

    .event-card-meta {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 20px;
    }

    .event-card-meta-item {
        font-size: 0.9rem;
        color: var(--text-muted, #94a3b8);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .event-card-meta-item i {
        color: var(--primary, #00f0ff);
        width: 16px;
    }

    .event-card-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: auto;
        padding-top: 15px;
        border-top: 1px solid rgba(255,255,255,0.05);
    }

    .event-card-link {
        flex: 1;
        background: rgba(0, 240, 255, 0.1);
        color: var(--primary, #00f0ff);
        padding: 10px;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 700;
        text-align: center;
        font-size: 0.85rem;
        transition: all 0.3s ease;
        border: 1px solid rgba(0, 240, 255, 0.3);
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .event-card-link:hover {
        background: var(--primary, #00f0ff);
        color: #050812;
        box-shadow: 0 0 15px rgba(0, 240, 255, 0.5);
    }

    .event-card-delete {
        flex: 1;
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        padding: 10px;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 700;
        text-align: center;
        font-size: 0.85rem;
        transition: all 0.3s ease;
        border: 1px solid rgba(239, 68, 68, 0.3);
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .event-card-delete:hover {
        background: #ef4444;
        color: #050812;
        box-shadow: 0 0 15px rgba(239, 68, 68, 0.5);
    }

    .empty-message {
        background: rgba(8, 11, 26, 0.5);
        padding: 60px 40px;
        border-radius: 20px;
        text-align: center;
        border: 1px dashed rgba(255,255,255,0.2);
    }
    
    .empty-icon {
        font-size: 3.5rem;
        margin-bottom: 20px;
        color: var(--text-muted, #94a3b8);
        opacity: 0.5;
    }

    .empty-message p {
        font-size: 1.1rem;
        margin-bottom: 25px;
        color: white;
    }

    @media (max-width: 968px) {
        .dashboard-header { flex-direction: column; text-align: center; }
        .dashboard-user-info { flex-direction: column; }
        .dashboard-actions { justify-content: center; }
        .events-grid { grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); }
    }

    @media (max-width: 600px) {
        .dashboard-header { padding: 30px 20px; }
        .user-avatar { width: 60px; height: 60px; font-size: 1.8rem; }
        .user-details h1 { font-size: 1.5rem; }
        .events-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="dashboard-container">
    <div class="container">
        <div class="dashboard-header">
            <div class="dashboard-user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($user['name'], 0, 1)); ?></div>
                <div class="user-details">
                    <h1>Terminal Access: <?php echo htmlspecialchars($user['name']); ?> <span class="badge">VERIFIED</span></h1>
                    <p>COM LINK ID: <?php echo htmlspecialchars($user['email']); ?></p>
                </div>
            </div>
            <div class="dashboard-actions">
                <a href="create_event.php" class="btn-neon"><i class="fas fa-satellite-dish"></i> Deploy Instance</a>
                <a href="events.php" class="btn-glass"><i class="fas fa-radar"></i> Scan Network</a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo str_pad($total_created, 2, '0', STR_PAD_LEFT); ?></div>
                <div class="stat-label"><i class="fas fa-cubes"></i> Instances Deployed</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo str_pad($total_registered, 2, '0', STR_PAD_LEFT); ?></div>
                <div class="stat-label"><i class="fas fa-link"></i> Network Integrations</div>
            </div>
        </div>

        <div class="section">
            <h2 class="section-title"><i class="fas fa-server"></i> Origin Instances</h2>
            <?php if (!empty($user_events)): ?>
                <div class="events-grid">
                    <?php foreach ($user_events as $event): ?>
                        <div class="event-card">
                            <div class="event-card-image-wrap">
                                <img src="<?php echo !empty($event['image']) ? htmlspecialchars($event['image']) : 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=500&h=300&fit=crop'; ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="event-card-image">
                            </div>
                            <div class="event-card-content">
                                <h3 class="event-card-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                                <div class="event-card-meta">
                                    <div class="event-card-meta-item"><i class="fas fa-clock"></i> <?php echo date('M d, Y', strtotime($event['event_date'])); ?></div>
                                    <div class="event-card-meta-item"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></div>
                                    <div class="event-card-meta-item"><i class="fas fa-folder"></i> <?php echo htmlspecialchars($event['category']); ?></div>
                                </div>
                                <div class="event-card-actions">
                                    <a href="event.php?id=<?php echo $event['id']; ?>" class="event-card-link"><i class="fas fa-eye"></i> Access</a>
                                    <a href="delete_event.php?id=<?php echo $event['id']; ?>" class="event-card-delete" onclick="return confirm('Eradicate instance permanently?')"><i class="fas fa-trash"></i> Drop</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-message">
                    <div class="empty-icon"><i class="fas fa-box-open"></i></div>
                    <p>No origin instances detected in your quadrant.</p>
                    <a href="create_event.php" class="btn-neon"><i class="fas fa-plus"></i> Initialize First Run</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="section">
            <h2 class="section-title"><i class="fas fa-plug"></i> Active Integrations</h2>
            <?php if (!empty($registered_events)): ?>
                <div class="events-grid">
                    <?php foreach ($registered_events as $event): ?>
                        <div class="event-card">
                            <div class="event-card-image-wrap">
                                <img src="<?php echo !empty($event['image']) ? htmlspecialchars($event['image']) : 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=500&h=300&fit=crop'; ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="event-card-image">
                            </div>
                            <div class="event-card-content">
                                <h3 class="event-card-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                                <div class="event-card-meta">
                                    <div class="event-card-meta-item"><i class="fas fa-clock"></i> <?php echo date('M d, Y', strtotime($event['event_date'])); ?></div>
                                    <div class="event-card-meta-item"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></div>
                                    <div class="event-card-meta-item"><i class="fas fa-folder"></i> <?php echo htmlspecialchars($event['category']); ?></div>
                                </div>
                                <div class="event-card-actions">
                                    <a href="event.php?id=<?php echo $event['id']; ?>" class="event-card-link"><i class="fas fa-eye"></i> Establish Feed</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-message">
                    <div class="empty-icon"><i class="fas fa-unlink"></i></div>
                    <p>No active integrations found in matrix.</p>
                    <a href="events.php" class="btn-neon"><i class="fas fa-search"></i> Seek Nodes</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
