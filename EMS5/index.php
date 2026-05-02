<?php
require_once 'config.php';
$page_title = 'Home';
include 'header.php';

// Get featured events with tier info
$featured_query = "SELECT id, title, description, event_date, location, image, category, price, tier FROM events WHERE active = 1 AND status = 'approved' ORDER BY created_at DESC LIMIT 6";
$featured_result = mysqli_query($connection, $featured_query);
$featured_events = mysqli_fetch_all($featured_result, MYSQLI_ASSOC);

// Stats
$total_events = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as c FROM events WHERE active=1 AND status='approved'"))['c'] ?? 0;
$total_users = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as c FROM users"))['c'] ?? 0;
$total_bookings = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as c FROM bookings WHERE status IN ('Approved','Completed')"))['c'] ?? 0;

function getDefaultImage($category) {
    // High-quality, futuristic fallback images
    $images = [
        'Conference' => 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=700&h=480&fit=crop&q=80',
        'Wedding'    => 'https://images.unsplash.com/photo-1519741497674-611481863552?w=700&h=480&fit=crop&q=80',
        'Birthday'   => 'https://images.unsplash.com/photo-1530103862676-de8c9debad1d?w=700&h=480&fit=crop&q=80',
        'Seminar'    => 'https://images.unsplash.com/photo-1475721027785-f74eccf877e2?w=700&h=480&fit=crop&q=80',
        'Workshop'   => 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=700&h=480&fit=crop&q=80',
        'Meetup'     => 'https://images.unsplash.com/photo-1528605248644-14dd04022da1?w=700&h=480&fit=crop&q=80',
        'Concert'    => 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?w=700&h=480&fit=crop&q=80',
        'Sports'     => 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?w=700&h=480&fit=crop&q=80',
        'Anniversary'=> 'https://images.unsplash.com/photo-1464047736614-af63643285bf?w=700&h=480&fit=crop&q=80',
        'Other'      => 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=700&h=480&fit=crop&q=80',
    ];
    return $images[$category] ?? 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=700&h=480&fit=crop&q=80'; // Default futuristic server/cyber image
}
?>

<style>
/* ===== FUTURISTIC HERO STYLES ===== */
.hero {
    position: relative;
    background: url('https://images.unsplash.com/photo-1451187580459-43490279c0fa?q=80&w=1920&auto=format&fit=crop') center/cover no-repeat;
    color: white;
    padding: 140px 24px 100px;
    text-align: center;
    overflow: hidden;
    min-height: 80vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

/* Dark overlay with blue/purple tint */
.hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(5,8,18,0.92) 0%, rgba(13,20,50,0.85) 50%, rgba(30,12,48,0.92) 100%);
    z-index: 1;
}

/* Neon accents */
.hero::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 800px;
    height: 800px;
    background: radial-gradient(circle, rgba(0, 240, 255, 0.15) 0%, transparent 60%);
    z-index: 1;
    pointer-events: none;
    animation: neonPulse 6s infinite alternate ease-in-out;
}

@keyframes neonPulse {
    0% { transform: translate(-50%, -50%) scale(0.9); opacity: 0.8; }
    100% { transform: translate(-50%, -50%) scale(1.1); opacity: 1; }
}

.hero-content {
    position: relative;
    z-index: 2;
    max-width: 900px;
    margin: 0 auto;
}

.hero-tag {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(0, 240, 255, 0.1);
    border: 1px solid rgba(0, 240, 255, 0.4);
    color: #00f0ff;
    padding: 8px 24px;
    border-radius: 30px;
    font-size: 0.85rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    margin-bottom: 28px;
    box-shadow: 0 0 20px rgba(0, 240, 255, 0.2);
    backdrop-filter: blur(10px);
}

.hero h1 {
    font-size: clamp(3rem, 6vw, 5rem);
    font-weight: 900;
    line-height: 1.1;
    margin-bottom: 24px;
    letter-spacing: -1px;
    text-shadow: 0 10px 30px rgba(0,0,0,0.5);
}

.hero h1 .gradient-text {
    background: linear-gradient(90deg, #00f0ff, #0051ff, #ff007f);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    display: inline-block;
}

.hero p {
    font-size: 1.2rem;
    color: #94a3b8;
    margin-bottom: 40px;
    line-height: 1.8;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
    font-weight: 400;
    text-shadow: 0 2px 4px rgba(0,0,0,0.5);
}

.hero-buttons {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 70px;
}

/* Futuristic Buttons */
.btn-neon {
    background: linear-gradient(135deg, #00f0ff, #0051ff);
    color: white;
    padding: 16px 36px;
    border-radius: 12px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    text-decoration: none;
    border: none;
    box-shadow: 0 0 20px rgba(0, 240, 255, 0.4);
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.btn-neon:hover {
    transform: translateY(-3px);
    box-shadow: 0 0 30px rgba(0, 240, 255, 0.6);
}

.btn-glass {
    background: rgba(255, 255, 255, 0.05);
    color: white;
    padding: 16px 36px;
    border-radius: 12px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    text-decoration: none;
    border: 1px solid rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.btn-glass:hover {
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.4);
    transform: translateY(-3px);
    box-shadow: 0 0 20px rgba(255, 255, 255, 0.1);
}

/* Stats */
.hero-stats {
    display: flex;
    justify-content: center;
    gap: 40px;
    flex-wrap: wrap;
    position: relative;
    z-index: 2;
    background: rgba(8, 11, 26, 0.8);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(0, 240, 255, 0.2);
    border-radius: 20px;
    padding: 30px 50px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5), inset 0 0 20px rgba(0, 240, 255, 0.05);
}

.hero-stat {
    text-align: center;
}

.hero-stat .number {
    font-size: 2.5rem;
    font-weight: 900;
    background: linear-gradient(135deg, #00f0ff, #fff);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 5px;
    text-shadow: 0 0 20px rgba(0, 240, 255, 0.3);
}

.hero-stat .label {
    font-size: 0.85rem;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    font-weight: 700;
}

/* ===== FUTURISTIC SECTIONS ===== */
.cyber-section {
    background: #050812;
    padding: 100px 0;
    position: relative;
}

.cyber-section::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: 
        linear-gradient(90deg, rgba(5,8,18, 1) 0%, rgba(5,8,18, 0.8) 20%, rgba(5,8,18, 0.8) 80%, rgba(5,8,18, 1) 100%),
        linear-gradient(0deg, rgba(0, 240, 255, 0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0, 240, 255, 0.03) 1px, transparent 1px);
    background-size: 100% 100%, 40px 40px, 40px 40px;
    pointer-events: none;
}

.section-title {
    color: white;
    font-size: 3rem;
    font-weight: 900;
    text-align: center;
    margin-bottom: 12px;
    position: relative;
    z-index: 2;
    letter-spacing: -0.5px;
}

.section-title span {
    color: #00f0ff;
    text-shadow: 0 0 20px rgba(0,240,255,0.4);
}

.section-subtitle {
    color: #94a3b8;
    text-align: center;
    font-size: 1.15rem;
    margin-bottom: 60px;
    position: relative;
    z-index: 2;
}

/* ===== GLASSMORPHISM EVENT CARDS ===== */
.events-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 30px;
    position: relative;
    z-index: 2;
}

.event-card {
    background: rgba(8, 11, 26, 0.8);
    border: 1px solid rgba(0, 240, 255, 0.1);
    border-radius: 20px;
    overflow: hidden;
    backdrop-filter: blur(10px);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    display: flex;
    flex-direction: column;
}

.event-card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 20px 40px rgba(0, 240, 255, 0.15);
    border-color: rgba(0, 240, 255, 0.3);
}

.event-card.premium-card {
    border-color: rgba(245, 158, 11, 0.3);
    background: linear-gradient(180deg, rgba(20, 15, 5, 0.9) 0%, rgba(8, 11, 26, 0.9) 100%);
}

.event-card.premium-card:hover {
    box-shadow: 0 20px 40px rgba(245, 158, 11, 0.2);
    border-color: rgba(245, 158, 11, 0.6);
}

.event-card-image-wrap {
    position: relative;
    height: 220px;
    overflow: hidden;
}

.event-card-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
    opacity: 0.85;
}

.event-card:hover .event-card-image {
    transform: scale(1.1);
    opacity: 1;
}

/* Image Overlay Gradient */
.event-card-image-wrap::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, transparent 0%, rgba(8, 11, 26, 1) 100%);
}

.tier-badge {
    position: absolute;
    top: 16px; left: 16px;
    z-index: 2;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    backdrop-filter: blur(8px);
}

.tier-premium {
    background: rgba(245, 158, 11, 0.2);
    color: #fbbf24;
    border: 1px solid rgba(245, 158, 11, 0.5);
    box-shadow: 0 0 15px rgba(245, 158, 11, 0.3);
}

.tier-ordinary {
    background: rgba(255, 255, 255, 0.1);
    color: #e2e8f0;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.event-cat-badge {
    position: absolute;
    top: 16px; right: 16px;
    z-index: 2;
    background: rgba(0,0,0,0.6);
    color: #00f0ff;
    border: 1px solid rgba(0, 240, 255, 0.3);
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 1px;
    backdrop-filter: blur(4px);
    text-transform: uppercase;
}

.event-card-content {
    padding: 24px;
    flex: 1;
    display: flex;
    flex-direction: column;
    position: relative;
    z-index: 2;
    margin-top: -20px;
}

.event-card-title {
    font-size: 1.35rem;
    font-weight: 900;
    margin-bottom: 12px;
    color: white;
    line-height: 1.3;
    letter-spacing: 0.5px;
}

.event-meta {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 20px;
}

.event-meta-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.95rem;
    color: #94a3b8;
}

.event-meta-item i {
    color: #00f0ff;
    width: 16px;
}

.event-price {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    margin-top: auto;
    padding-top: 20px;
    border-top: 1px solid rgba(255,255,255,0.08);
}

.price-container {
    display: flex;
    flex-direction: column;
}

.price-tag {
    font-size: 1.5rem;
    font-weight: 900;
    color: white;
}

.price-format {
    font-size: 0.75rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 700;
}

.price-tag.free { color: #22c55e;text-shadow: 0 0 10px rgba(34, 197, 94, 0.3); }
.event-card.premium-card .price-tag { color: #fbbf24; text-shadow: 0 0 10px rgba(245, 158, 11, 0.3); }
.event-card.premium-card .event-meta-item i { color: #fbbf24; }

.event-card-link {
    background: rgba(0, 240, 255, 0.1);
    color: #00f0ff;
    border: 1px solid rgba(0, 240, 255, 0.4);
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 800;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    transition: all 0.3s ease;
}

.event-card-link:hover {
    background: #00f0ff;
    color: #050812;
    box-shadow: 0 0 20px rgba(0, 240, 255, 0.6);
}

.event-card.premium-card .event-card-link {
    background: rgba(245, 158, 11, 0.1);
    color: #fbbf24;
    border-color: rgba(245, 158, 11, 0.4);
}

.event-card.premium-card .event-card-link:hover {
    background: #fbbf24;
    color: #050812;
    box-shadow: 0 0 20px rgba(245, 158, 11, 0.6);
}

/* ===== FEATURES ===== */
.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
    position: relative;
    z-index: 2;
}

.feature-card {
    background: rgba(8, 11, 26, 0.8);
    padding: 40px 30px;
    border-radius: 20px;
    border: 1px solid rgba(255,255,255,0.05);
    text-align: center;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}

.feature-card:hover {
    border-color: rgba(0, 240, 255, 0.3);
    transform: translateY(-5px);
    background: rgba(15, 23, 42, 0.9);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.5), inset 0 0 20px rgba(0, 240, 255, 0.05);
}

.feature-icon {
    width: 70px;
    height: 70px;
    border-radius: 20px;
    background: rgba(0, 240, 255, 0.1);
    border: 1px solid rgba(0, 240, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: #00f0ff;
    margin: 0 auto 24px;
    box-shadow: inset 0 0 20px rgba(0, 240, 255, 0.1);
}

.feature-card h3 {
    color: white;
    font-size: 1.3rem;
    font-weight: 900;
    margin-bottom: 12px;
    letter-spacing: 0.5px;
}

.feature-card p {
    color: #94a3b8;
    font-size: 1rem;
    line-height: 1.6;
}

/* ===== CTA ===== */
.cta-section {
    background: linear-gradient(135deg, #050812 0%, #0c1126 50%, #050812 100%);
    padding: 100px 24px;
    text-align: center;
    position: relative;
    border-top: 1px solid rgba(0, 240, 255, 0.2);
}

.cta-section::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at center, rgba(0, 240, 255, 0.1) 0%, transparent 60%);
}

.cta-section h2 { color: white; font-size: 3rem; font-weight: 900; margin-bottom: 20px; position: relative; z-index: 1; letter-spacing: -1px;}
.cta-section p { color: #94a3b8; font-size: 1.2rem; margin-bottom: 40px; position: relative; z-index: 1; max-width: 600px; margin-left: auto; margin-right: auto;}
</style>

<!-- HERO -->
<div class="hero">
    <div class="hero-content">
        <div class="hero-tag"><i class="fas fa-bolt"></i> Next-Gen Event Matrix</div>
        <h1>Deploy Unforgettable<br><span class="gradient-text">Experiences</span></h1>
        <p>Architect, manage, and scale your events within the Nexus framework. Initialize exclusive Premium instances or establish secure Ordinary nodes seamlessly.</p>
        <div class="hero-buttons">
            <a href="events.php" class="btn-glass"><i class="fas fa-search"></i> Access Database</a>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="register.php" class="btn-neon"><i class="fas fa-rocket"></i> Initialize Uplink</a>
            <?php else: ?>
                <a href="create_event.php" class="btn-neon"><i class="fas fa-code-branch"></i> Deploy Event</a>
            <?php endif; ?>
        </div>
        
        <div class="hero-stats">
            <div class="hero-stat">
                <div class="number"><?php echo number_format($total_events); ?></div>
                <div class="label">Active Nodes</div>
            </div>
            <div class="hero-stat">
                <div class="number"><?php echo number_format($total_users); ?></div>
                <div class="label">Entities</div>
            </div>
            <div class="hero-stat">
                <div class="number"><?php echo number_format($total_bookings); ?></div>
                <div class="label">Transactions</div>
            </div>
        </div>
    </div>
</div>

<!-- FEATURED EVENTS -->
<section class="cyber-section">
    <div class="container">
        <h2 class="section-title">Trending <span>Instances</span></h2>
        <p class="section-subtitle">Discover the most highly-accessed events currently active in the matrix.</p>

        <div class="events-grid">
            <?php if (!empty($featured_events)): ?>
                <?php foreach ($featured_events as $event):
                    $isPremium = ($event['tier'] ?? 'Ordinary') === 'Premium';
                    $imgSrc = !empty($event['image']) ? htmlspecialchars($event['image']) : getDefaultImage($event['category']);
                ?>
                    <div class="event-card <?php echo $isPremium ? 'premium-card' : ''; ?>">
                        <div class="event-card-image-wrap">
                            <img src="<?php echo $imgSrc; ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="event-card-image" loading="lazy">
                            <span class="tier-badge <?php echo $isPremium ? 'tier-premium' : 'tier-ordinary'; ?>">
                                <?php echo $isPremium ? '👑 Premium' : '🎟 Ordinary'; ?>
                            </span>
                            <span class="event-cat-badge"><?php echo htmlspecialchars($event['category']); ?></span>
                        </div>
                        <div class="event-card-content">
                            <h3 class="event-card-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                            <div class="event-meta">
                                <div class="event-meta-item"><i class="fas fa-calendar-alt"></i><?php echo date('D, M d, Y', strtotime($event['event_date'])); ?></div>
                                <div class="event-meta-item"><i class="fas fa-map-marker-alt"></i><?php echo htmlspecialchars($event['location']); ?></div>
                            </div>
                            <div class="event-price">
                                <div class="price-container">
                                    <div class="price-tag <?php echo $event['price'] <= 0 ? 'free' : ''; ?>">
                                        <?php if ($event['price'] > 0): ?>
                                            $<?php echo number_format($event['price'], 2); ?>
                                        <?php else: ?>
                                            ✅ Free Access
                                        <?php endif; ?>
                                    </div>
                                    <div class="price-format">Per Entity</div>
                                </div>
                                <a href="event.php?id=<?php echo $event['id']; ?>" class="event-card-link">
                                    Connect <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 60px; background: rgba(8,11,26,0.5); border-radius: 20px; border: 1px solid rgba(0,240,255,0.1); box-shadow: inset 0 0 20px rgba(0,240,255,0.05);">
                    <div style="font-size: 3rem; margin-bottom: 16px;">🌌</div>
                    <h3 style="margin-bottom: 8px; color: white;">No Signal Detected</h3>
                    <p style="color: #94a3b8;">Initialize the matrix by deploying a new event instance.</p>
                </div>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin-top: 60px; position:relative; z-index:2;">
            <a href="events.php" class="btn-neon"><i class="fas fa-th-large"></i> Scan All Instances</a>
        </div>
    </div>
</section>

<!-- FEATURES -->
<section class="cyber-section" style="background: #03050a; padding-top: 60px; border-top: 1px solid rgba(255,255,255,0.05);">
    <div class="container">
        <h2 class="section-title">Nexus <span>Modules</span></h2>
        <p class="section-subtitle">Advanced computational systems for unparalleled event orchestration.</p>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-crown"></i></div>
                <h3>Tier Protocols</h3>
                <p>Deploy Premium instances for VIP access or initialize Ordinary environments for public network nodes.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-calendar-check"></i></div>
                <h3>Chrono Matrix</h3>
                <p>Visualize all spatial-temporal events on a unified HUD. Track timelines and synchronization flawlessly.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-database"></i></div>
                <h3>Data Streams</h3>
                <p>Monitor real-time capital flow with distinct analytical streams for Premium and Ordinary transactional ledgers.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <h2>Initiate Next Sequence</h2>
    <p>Upload your consciousness to the network and elevate your event management capabilities today.</p>
    <?php if (!isset($_SESSION['user_id'])): ?>
        <a href="register.php" class="btn-neon"><i class="fas fa-user-plus"></i> Establish Connection</a>
    <?php else: ?>
        <a href="create_event.php" class="btn-neon"><i class="fas fa-plus-circle"></i> Deploy Event</a>
    <?php endif; ?>
</section>

<?php include 'footer.php'; ?>

