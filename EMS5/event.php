<?php
require_once 'config.php';
$page_title = 'Event Details';

$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($event_id === 0) {
    header('Location: events.php');
    exit();
}

// Get event details
$event_query = "SELECT * FROM events WHERE id = $event_id AND active = 1 AND status = 'approved' LIMIT 1";
$event_result = mysqli_query($connection, $event_query);

if (mysqli_num_rows($event_result) === 0) {
    header('Location: events.php');
    exit();
}

$event = mysqli_fetch_assoc($event_result);
$isPremium = ($event['tier'] ?? 'Ordinary') === 'Premium';

// Category-based default images
function getDefaultImage($category) {
    $images = [
        'Conference' => 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=1200&h=400&fit=crop',
        'Wedding' => 'https://images.unsplash.com/photo-1519741497674-611481863552?w=1200&h=400&fit=crop',
        'Birthday' => 'https://images.unsplash.com/photo-1530103862676-de8c9debad1d?w=1200&h=400&fit=crop',
        'Seminar' => 'https://images.unsplash.com/photo-1475721027785-f74eccf877e2?w=1200&h=400&fit=crop',
        'Workshop' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=1200&h=400&fit=crop',
        'Meetup' => 'https://images.unsplash.com/photo-1528605248644-14dd04022da1?w=1200&h=400&fit=crop',
        'Concert' => 'https://images.unsplash.com/photo-1459749411175-04bf5292ceea?w=1200&h=400&fit=crop',
        'Sports' => 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?w=1200&h=400&fit=crop',
        'Other' => 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=1200&h=400&fit=crop'
    ];
    return isset($images[$category]) ? $images[$category] : 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=1200&h=400&fit=crop';
}

// Get event registrations count
$reg_query = "SELECT COUNT(*) as count FROM registrations WHERE event_id = $event_id";
$reg_result = mysqli_query($connection, $reg_query);
$reg_count = mysqli_fetch_assoc($reg_result);
$total_registrations = $reg_count['count'];

// Check if user is already registered
$is_registered = false;
if (isset($_SESSION['user_id'])) {
    $check_query = "SELECT id FROM registrations WHERE event_id = $event_id AND user_id = {$_SESSION['user_id']} LIMIT 1";
    $check_result = mysqli_query($connection, $check_query);
    $is_registered = mysqli_num_rows($check_result) > 0;
}

// Check if user is event creator
$is_creator = isset($_SESSION['user_id']) && $event['created_by'] == $_SESSION['user_id'];

include 'header.php';
?>

<style>
    body {
        background: #050812;
        color: #e2e8f0;
    }

    .event-details-container {
        min-height: calc(100vh - 200px);
        padding: 60px 20px 100px;
        position: relative;
    }
    
    .event-details-container::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: 
            linear-gradient(90deg, rgba(5,8,18, 1) 0%, rgba(5,8,18, 0.8) 20%, rgba(5,8,18, 0.8) 80%, rgba(5,8,18, 1) 100%),
            linear-gradient(0deg, rgba(0, 240, 255, 0.03) 1px, transparent 1px),
            linear-gradient(90deg, rgba(0, 240, 255, 0.03) 1px, transparent 1px);
        background-size: 100% 100%, 40px 40px, 40px 40px;
        pointer-events: none;
        z-index: 0;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        position: relative;
        z-index: 2;
    }

    .event-hero {
        background: rgba(8, 11, 26, 0.8);
        border: 1px solid rgba(0, 240, 255, 0.2);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6), inset 0 0 20px rgba(0, 240, 255, 0.05);
        margin-bottom: 50px;
        backdrop-filter: blur(12px);
    }
    
    .event-hero.premium-view {
        border-color: rgba(245, 158, 11, 0.4);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6), inset 0 0 30px rgba(245, 158, 11, 0.1);
        background: linear-gradient(180deg, rgba(20, 15, 5, 0.9) 0%, rgba(8, 11, 26, 0.9) 100%);
    }

    .event-hero-image-wrap {
        position: relative;
        width: 100%;
        height: 400px;
        overflow: hidden;
    }

    .event-hero-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.85;
    }
    
    .event-hero-image-wrap::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, transparent 0%, rgba(8, 11, 26, 1) 100%);
    }

    .event-hero-content {
        padding: 40px;
        position: relative;
        margin-top: -60px;
        z-index: 2;
    }

    .event-badges {
        display: flex;
        gap: 12px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    .event-category-badge {
        display: inline-flex;
        background: rgba(0, 240, 255, 0.1);
        color: #00f0ff;
        padding: 8px 18px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 800;
        border: 1px solid rgba(0, 240, 255, 0.3);
        text-transform: uppercase;
        letter-spacing: 1px;
        backdrop-filter: blur(4px);
    }
    
    .tier-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 18px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        backdrop-filter: blur(4px);
    }

    .tier-premium {
        background: rgba(245, 158, 11, 0.15);
        color: #fbbf24;
        border: 1px solid rgba(245, 158, 11, 0.4);
        box-shadow: 0 0 15px rgba(245, 158, 11, 0.2);
    }

    .tier-ordinary {
        background: rgba(255, 255, 255, 0.05);
        color: #e2e8f0;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .event-hero-content h1 {
        font-size: 3rem;
        color: white;
        margin-bottom: 20px;
        font-weight: 900;
        letter-spacing: -0.5px;
        line-height: 1.2;
    }
    
    .premium-view .event-hero-content h1 {
        text-shadow: 0 0 30px rgba(245, 158, 11, 0.3);
    }

    .event-meta {
        display: flex;
        gap: 30px;
        flex-wrap: wrap;
        margin-bottom: 30px;
        padding-bottom: 30px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    .event-meta-item {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 1.05rem;
    }

    .event-meta-item i {
        color: #00f0ff;
        font-size: 1.2rem;
    }
    
    .premium-view .event-meta-item i {
        color: #fbbf24;
    }

    .event-meta-value {
        color: #e2e8f0;
        font-weight: 600;
    }
    
    .price-display {
        display: flex;
        align-items: flex-end;
        gap: 8px;
    }
    
    .price-value {
        font-size: 1.8rem;
        font-weight: 900;
        color: white;
        line-height: 1;
    }
    
    .price-value.free {
        color: #22c55e;
        text-shadow: 0 0 15px rgba(34, 197, 94, 0.4);
    }
    
    .premium-view .price-value:not(.free) {
        color: #fbbf24;
        text-shadow: 0 0 15px rgba(245, 158, 11, 0.4);
    }
    
    .price-format {
        font-size: 0.8rem;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
        margin-bottom: 3px;
    }

    .event-description-short {
        color: #cbd5e1;
        font-size: 1.15rem;
        line-height: 1.7;
        margin-bottom: 30px;
        max-width: 800px;
    }

    .event-actions {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 16px 32px;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 800;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        font-size: 1rem;
        text-transform: uppercase;
        letter-spacing: 1.5px;
    }

    .btn-neon {
        background: linear-gradient(135deg, #00f0ff, #0051ff);
        color: white;
        box-shadow: 0 0 20px rgba(0, 240, 255, 0.4);
    }

    .btn-neon:hover {
        transform: translateY(-3px);
        box-shadow: 0 0 30px rgba(0, 240, 255, 0.6);
    }
    
    .btn-neon-premium {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: #050812;
        box-shadow: 0 0 20px rgba(245, 158, 11, 0.4);
    }
    
    .btn-neon-premium:hover {
        transform: translateY(-3px);
        box-shadow: 0 0 30px rgba(245, 158, 11, 0.6);
    }

    .btn-glass {
        background: rgba(255, 255, 255, 0.05);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
    }

    .btn-glass:hover {
        background: rgba(255, 255, 255, 0.15);
        border-color: rgba(255, 255, 255, 0.4);
        transform: translateY(-3px);
    }

    .btn-danger {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.4);
    }

    .btn-danger:hover {
        background: #ef4444;
        color: white;
        box-shadow: 0 0 20px rgba(239, 68, 68, 0.4);
        transform: translateY(-3px);
    }

    .btn:disabled, .btn.disabled-btn {
        background: rgba(255,255,255,0.05);
        color: #64748b;
        border: 1px solid rgba(255,255,255,0.1);
        cursor: not-allowed;
        box-shadow: none;
        transform: none;
    }

    .event-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 40px;
    }

    .event-details-card, .event-sidebar-card {
        background: rgba(8, 11, 26, 0.8);
        padding: 40px;
        border-radius: 24px;
        border: 1px solid rgba(0, 240, 255, 0.15);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(12px);
    }
    
    .premium-view .event-details-card, .premium-view .event-sidebar-card {
        border-color: rgba(245, 158, 11, 0.2);
    }

    .event-sidebar {
        display: flex;
        flex-direction: column;
        gap: 30px;
    }

    .event-info-title {
        font-size: 1.4rem;
        font-weight: 800;
        margin-bottom: 24px;
        color: white;
        display: flex;
        align-items: center;
        gap: 12px;
        padding-bottom: 15px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    
    .event-info-title i {
        color: #00f0ff;
    }
    .premium-view .event-info-title i {
        color: #fbbf24;
    }

    .detail-section {
        margin-bottom: 40px;
    }
    
    .detail-section:last-child {
        margin-bottom: 0;
    }

    .detail-section h2 {
        font-size: 1.6rem;
        color: white;
        margin-bottom: 20px;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .detail-section h2 i {
        color: #00f0ff;
    }
    .premium-view .detail-section h2 i {
        color: #fbbf24;
    }

    .detail-section p {
        color: #94a3b8;
        line-height: 1.8;
        font-size: 1.05rem;
    }

    .info-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .info-list li {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    
    .info-list li:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }
    
    .info-icon {
        background: rgba(0, 240, 255, 0.1);
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #00f0ff;
        font-size: 1.2rem;
        border: 1px solid rgba(0, 240, 255, 0.2);
        flex-shrink: 0;
    }
    
    .premium-view .info-icon {
        background: rgba(245, 158, 11, 0.1);
        color: #fbbf24;
        border-color: rgba(245, 158, 11, 0.2);
    }
    
    .info-content .info-label {
        font-size: 0.85rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
        margin-bottom: 4px;
        display: block;
    }
    
    .info-content .info-value {
        color: #e2e8f0;
        font-weight: 600;
        font-size: 1.05rem;
    }

    .registration-stats-container {
        text-align: center;
        padding: 20px 0;
    }
    
    .registration-stats {
        font-size: 3rem;
        font-weight: 900;
        background: linear-gradient(135deg, #00f0ff, #fff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 5px;
    }
    
    .premium-view .registration-stats {
        background: linear-gradient(135deg, #fbbf24, #fff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    .registration-label {
        color: #94a3b8;
        font-size: 1rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
    }

    .alert {
        padding: 24px;
        border-radius: 16px;
        margin-top: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        font-weight: 600;
        background: rgba(15, 23, 42, 0.8);
        border: 1px solid rgba(0, 240, 255, 0.3);
        box-shadow: inset 0 0 20px rgba(0, 240, 255, 0.05);
    }
    
    .alert i {
        font-size: 1.5rem;
        color: #00f0ff;
    }
    
    .alert-content {
        color: #e2e8f0;
        line-height: 1.5;
    }

    @media (max-width: 968px) {
        .event-grid { grid-template-columns: 1fr; }
        .event-hero-content h1 { font-size: 2.2rem; }
    }

    @media (max-width: 600px) {
        .event-hero-image-wrap { height: 250px; }
        .event-hero-content { padding: 25px; margin-top: -40px; }
        .event-hero-content h1 { font-size: 1.8rem; }
        .event-details-card, .event-sidebar-card { padding: 25px; }
        .event-meta { flex-direction: column; gap: 15px; }
        .event-actions { flex-direction: column; }
        .btn { width: 100%; }
        .info-list li { flex-direction: column; gap: 10px; }
    }
</style>

<div class="event-details-container">
    <div class="container">
        <div class="event-hero <?php echo $isPremium ? 'premium-view' : ''; ?>">
            <div class="event-hero-image-wrap">
                <img src="<?php echo !empty($event['image']) ? htmlspecialchars($event['image']) : getDefaultImage($event['category']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="event-hero-image">
            </div>
            <div class="event-hero-content">
                <div class="event-badges">
                    <span class="event-category-badge"><?php echo htmlspecialchars($event['category']); ?></span>
                    <span class="tier-badge <?php echo $isPremium ? 'tier-premium' : 'tier-ordinary'; ?>">
                        <?php echo $isPremium ? '👑 Premium Protocol' : '🎟 Standard Authorization'; ?>
                    </span>
                </div>
                
                <h1><?php echo htmlspecialchars($event['title']); ?></h1>
                
                <div class="event-meta">
                    <div class="event-meta-item" title="Event Date">
                        <i class="fas fa-calendar-alt"></i>
                        <span class="event-meta-value"><?php echo date('F d, Y', strtotime($event['event_date'])); ?></span>
                    </div>
                    <div class="event-meta-item" title="Event Time">
                        <i class="fas fa-clock"></i>
                        <span class="event-meta-value"><?php echo date('g:i A', strtotime($event['event_date'])); ?></span>
                    </div>
                    <div class="event-meta-item" title="Location">
                        <i class="fas fa-map-marker-alt"></i>
                        <span class="event-meta-value"><?php echo htmlspecialchars($event['location']); ?></span>
                    </div>
                    <div class="event-meta-item" title="Pricing">
                        <div class="price-display">
                            <span class="price-value <?php echo $event['price'] <= 0 ? 'free' : ''; ?>">
                                <?php echo $event['price'] > 0 ? '$' . number_format($event['price'], 2) : 'FREE'; ?>
                            </span>
                            <span class="price-format">Per Entity</span>
                        </div>
                    </div>
                </div>

                <div class="event-actions">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($is_creator): ?>
                            <a href="edit_event.php?id=<?php echo $event['id']; ?>" class="btn btn-glass"><i class="fas fa-edit"></i> Modify Protocol</a>
                            <a href="delete_event.php?id=<?php echo $event['id']; ?>" class="btn btn-danger" onclick="return confirm('WARNING: Are you sure you want to terminate this instance?')"><i class="fas fa-trash-alt"></i> Terminate</a>
                        <?php elseif (!$is_registered): ?>
                            <a href="register_for_event.php?id=<?php echo $event['id']; ?>" class="btn <?php echo $isPremium ? 'btn-neon-premium' : 'btn-neon'; ?>"><i class="fas fa-id-card"></i> Initialize Registration</a>
                        <?php else: ?>
                            <button class="btn disabled-btn" disabled><i class="fas fa-check-circle"></i> Connection Established</button>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-glass"><i class="fas fa-sign-in-alt"></i> Authenticate to Connect</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="event-grid">
            <div class="event-details-card <?php echo $isPremium ? 'premium-view' : ''; ?>">
                <div class="detail-section">
                    <h2><i class="fas fa-align-left"></i> Mission Briefing</h2>
                    <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                </div>

                <div class="detail-section">
                    <h2><i class="fas fa-map-marked-alt"></i> Coordinate Data</h2>
                    <p><strong><?php echo htmlspecialchars($event['location']); ?></strong></p>
                    <p style="color: #64748b; margin-top: 10px; font-size: 0.95rem;">Encrypted geographical coordinates and secure pathways will be transmitted to validated entities post-registration.</p>
                </div>

                <?php if (!empty($event['agenda'])): ?>
                    <div class="detail-section">
                        <h2><i class="fas fa-stream"></i> Sequence Timeline</h2>
                        <p><?php echo nl2br(htmlspecialchars($event['agenda'])); ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($event['requirements'])): ?>
                    <div class="detail-section">
                        <h2><i class="fas fa-shield-alt"></i> Prerequisites</h2>
                        <p><?php echo nl2br(htmlspecialchars($event['requirements'])); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="event-sidebar">
                <div class="event-sidebar-card <?php echo $isPremium ? 'premium-view' : ''; ?>">
                    <div class="event-info-title"><i class="fas fa-microchip"></i> Node Specifications</div>
                    <ul class="info-list">
                        <li>
                            <div class="info-icon"><i class="fas fa-calendar-day"></i></div>
                            <div class="info-content">
                                <span class="info-label">Synchronization Date</span>
                                <span class="info-value"><?php echo date('F d, Y', strtotime($event['event_date'])); ?></span>
                            </div>
                        </li>
                        <li>
                            <div class="info-icon"><i class="fas fa-hourglass-start"></i></div>
                            <div class="info-content">
                                <span class="info-label">Initialization Time</span>
                                <span class="info-value"><?php echo date('g:i A', strtotime($event['event_date'])); ?></span>
                            </div>
                        </li>
                        <li>
                            <div class="info-icon"><i class="fas fa-globe"></i></div>
                            <div class="info-content">
                                <span class="info-label">Sector</span>
                                <span class="info-value"><?php echo htmlspecialchars($event['location']); ?></span>
                            </div>
                        </li>
                        <li>
                            <div class="info-icon"><i class="fas fa-project-diagram"></i></div>
                            <div class="info-content">
                                <span class="info-label">Architecture</span>
                                <span class="info-value"><?php echo htmlspecialchars($event['category']); ?></span>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="event-sidebar-card <?php echo $isPremium ? 'premium-view' : ''; ?>">
                    <div class="event-info-title"><i class="fas fa-users"></i> Network Traffic</div>
                    <div class="registration-stats-container">
                        <div class="registration-stats"><?php echo $total_registrations; ?></div>
                        <div class="registration-label">Connected Entities</div>
                    </div>
                </div>

                <?php if (isset($_SESSION['user_id']) && !$is_registered && !$is_creator): ?>
                    <div class="alert">
                        <i class="fas fa-satellite-dish"></i>
                        <div class="alert-content">
                            Signal acquired. Initialize registration sequence to secure your connection to this node before capacity limits are reached.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
