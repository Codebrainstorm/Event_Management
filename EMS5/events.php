<?php
require_once 'config.php';
$page_title = 'All Events';

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;
$search   = isset($_GET['search'])   ? trim($_GET['search'])   : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$tier     = isset($_GET['tier'])     ? trim($_GET['tier'])     : '';

$where = "WHERE active = 1 AND status = 'approved'";
if (!empty($search)) {
    $s = mysqli_real_escape_string($connection, $search);
    $where .= " AND (title LIKE '%$s%' OR description LIKE '%$s%')";
}
if (!empty($category)) {
    $c = mysqli_real_escape_string($connection, $category);
    $where .= " AND category = '$c'";
}
if (!empty($tier)) {
    $t = mysqli_real_escape_string($connection, $tier);
    $where .= " AND tier = '$t'";
}

$count_result = mysqli_query($connection, "SELECT COUNT(*) as total FROM events $where");
$total_events = mysqli_fetch_assoc($count_result)['total'];
$total_pages  = ceil($total_events / $per_page);

$events = mysqli_fetch_all(mysqli_query($connection, "SELECT id, title, description, event_date, location, category, image, price, tier FROM events $where ORDER BY event_date ASC LIMIT $offset, $per_page"), MYSQLI_ASSOC);
$categories = mysqli_fetch_all(mysqli_query($connection, "SELECT DISTINCT category FROM events WHERE active=1 AND status='approved' ORDER BY category"), MYSQLI_ASSOC);

function getDefaultImage($c) {
    $m = [
        'Conference'=>'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=700&h=480&fit=crop',
        'Wedding'=>'https://images.unsplash.com/photo-1519741497674-611481863552?w=700&h=480&fit=crop',
        'Birthday'=>'https://images.unsplash.com/photo-1530103862676-de8c9debad1d?w=700&h=480&fit=crop',
        'Seminar'=>'https://images.unsplash.com/photo-1475721027785-f74eccf877e2?w=700&h=480&fit=crop',
        'Workshop'=>'https://images.unsplash.com/photo-1552664730-d307ca884978?w=700&h=480&fit=crop',
        'Meetup'=>'https://images.unsplash.com/photo-1528605248644-14dd04022da1?w=700&h=480&fit=crop',
        'Concert'=>'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?w=700&h=480&fit=crop',
        'Sports'=>'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?w=700&h=480&fit=crop',
        'Other'=>'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=700&h=480&fit=crop'
    ];
    return $m[$c] ?? 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=700&h=480&fit=crop&q=80';
}

include 'header.php';
?>

<style>
/* CYBERPUNK/FUTURISTIC PAGE OVERRIDES */
body {
    background: #050812;
    color: #e2e8f0;
}

.page-hero {
    background: linear-gradient(135deg, rgba(5,8,18,0.95) 0%, rgba(13,20,50,0.9) 100%),
                url('https://images.unsplash.com/photo-1451187580459-43490279c0fa?q=80&w=1920&auto=format&fit=crop') center/cover;
    padding: 100px 24px 80px;
    text-align: center;
    color: white;
    border-bottom: 1px solid rgba(0,240,255,0.2);
    position: relative;
    overflow: hidden;
}

.page-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at center, rgba(0, 240, 255, 0.1) 0%, transparent 70%);
    pointer-events: none;
}

.page-hero::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0;
    width: 100%;
    height: 1px;
    background: linear-gradient(90deg, transparent, #00f0ff, transparent);
    box-shadow: 0 0 15px #00f0ff;
}

.page-hero h1 { 
    font-size: 3.5rem; 
    font-weight: 900; 
    margin-bottom: 16px;
    text-shadow: 0 10px 30px rgba(0,0,0,0.8);
    position: relative;
    z-index: 2;
    letter-spacing: -1px;
}
.page-hero h1 span { 
    background: linear-gradient(90deg, #00f0ff, #0051ff, #ff007f);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.page-hero p  { 
    color: #94a3b8; 
    font-size: 1.2rem; 
    max-width: 600px; 
    margin: 0 auto; 
    position: relative;
    z-index: 2;
    text-shadow: 0 2px 4px rgba(0,0,0,0.5);
}

.events-container { 
    padding: 60px 0 100px; 
    background: #050812; 
    position: relative; 
}
.events-container::before {
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

/* FILTER BAR (NEON/GLASS) */
.filter-bar {
    background: rgba(8, 11, 26, 0.8);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(0,240,255,0.2);
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 40px;
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    align-items: flex-end;
    box-shadow: 0 20px 40px rgba(0,0,0,0.5), inset 0 0 20px rgba(0,240,255,0.05);
    position: relative;
    z-index: 2;
}
.filter-group { display: flex; flex-direction: column; gap: 8px; flex: 1; min-width: 200px; }
.filter-group label {
    font-size: 0.85rem; font-weight: 800; color: #00f0ff;
    text-transform: uppercase; letter-spacing: 1.5px; 
}
.filter-group input, .filter-group select {
    padding: 14px 16px;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px;
    font-size: 1rem;
    font-family: 'Outfit', sans-serif;
    background: rgba(5,8,18,0.6);
    color: white;
    transition: all 0.3s ease;
    width: 100%;
}
.filter-group input::placeholder { color: #64748b; }
.filter-group input:focus, .filter-group select:focus {
    outline: none;
    border-color: #00f0ff;
    background: rgba(5,8,18,0.9);
    box-shadow: 0 0 15px rgba(0,240,255,0.2);
}
.filter-group select option { background: #080b1a; color: white; }

.filter-btn {
    padding: 14px 28px;
    background: linear-gradient(135deg, #00f0ff, #0051ff);
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 800;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
    align-self: flex-end;
    box-shadow: 0 0 20px rgba(0,240,255,0.4);
    text-transform: uppercase;
    letter-spacing: 1.5px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.filter-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 0 30px rgba(0,240,255,0.6);
}

.clear-btn {
    padding: 14px 20px;
    background: rgba(255,255,255,0.05);
    color: #cbd5e1;
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 12px;
    text-decoration: none;
    font-weight: 800;
    font-size: 0.95rem;
    align-self: flex-end;
    white-space: nowrap;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    text-transform: uppercase;
    letter-spacing: 1px;
}
.clear-btn:hover { background: rgba(255,255,255,0.15); color: white; border-color: rgba(255,255,255,0.4); transform: translateY(-2px); }

/* Tier tabs */
.tier-tabs { display: flex; gap: 16px; margin-bottom: 40px; flex-wrap: wrap; position: relative; z-index: 2;}
.tier-tab {
    padding: 12px 28px; border-radius: 30px; font-weight: 800; font-size: 0.9rem;
    text-decoration: none; transition: all 0.3s ease; border: 1px solid transparent;
    text-transform: uppercase; letter-spacing: 1.5px; backdrop-filter: blur(8px);
}
.tier-tab-all  { background: rgba(255,255,255,0.05); color: #e2e8f0; border-color: rgba(255,255,255,0.2); }
.tier-tab-all.active, .tier-tab-all:hover  { background: rgba(0,240,255,0.1); color: #00f0ff; border-color: rgba(0,240,255,0.4); box-shadow: 0 0 20px rgba(0,240,255,0.3); }

.tier-tab-premium { background: rgba(245,158,11,0.1); color: #fbbf24; border-color: rgba(245,158,11,0.3); }
.tier-tab-premium.active, .tier-tab-premium:hover { background: rgba(245,158,11,0.2); color: #f59e0b; border-color: #fbbf24; box-shadow: 0 0 20px rgba(245,158,11,0.4); }

.tier-tab-ordinary { background: rgba(255,255,255,0.05); color: #94a3b8; border-color: rgba(255,255,255,0.2); }
.tier-tab-ordinary.active, .tier-tab-ordinary:hover { background: rgba(255,255,255,0.15); color: white; border-color: rgba(255,255,255,0.4); box-shadow: 0 0 15px rgba(255,255,255,0.1); }

/* EVENTS GRID */
.events-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 30px; margin-bottom: 60px; position: relative; z-index: 2; }
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

.event-card:hover { transform: translateY(-10px) scale(1.02); box-shadow: 0 20px 40px rgba(0, 240, 255, 0.15); border-color: rgba(0, 240, 255, 0.3); }
.event-card.premium-card { border-color: rgba(245, 158, 11, 0.3); background: linear-gradient(180deg, rgba(20, 15, 5, 0.9) 0%, rgba(8, 11, 26, 0.9) 100%); }
.event-card.premium-card:hover { box-shadow: 0 20px 40px rgba(245, 158, 11, 0.2); border-color: rgba(245, 158, 11, 0.6); }

.event-card-image-wrap { position: relative; height: 220px; overflow: hidden; }
.event-card-image { width:100%; height:100%; object-fit: cover; transition: transform 0.6s ease; opacity: 0.85; }
.event-card:hover .event-card-image { transform: scale(1.1); opacity: 1; }
.event-card-image-wrap::after { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, transparent 0%, rgba(8, 11, 26, 1) 100%); }

.tier-badge { position: absolute; top: 16px; left: 16px; z-index: 2; display: inline-flex; align-items: center; gap: 5px; padding: 6px 14px; border-radius: 20px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; backdrop-filter: blur(8px); }
.tier-premium { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.5); box-shadow: 0 0 15px rgba(245, 158, 11, 0.3); }
.tier-ordinary { background: rgba(255, 255, 255, 0.1); color: #e2e8f0; border: 1px solid rgba(255, 255, 255, 0.2); }

.event-cat-label { position: absolute; top: 16px; right: 16px; z-index: 2; background: rgba(0,0,0,0.6); color: #00f0ff; border: 1px solid rgba(0, 240, 255, 0.3); padding: 4px 12px; border-radius: 12px; font-size: 0.75rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; backdrop-filter: blur(4px); }

.event-card-content { padding: 24px; flex: 1; display: flex; flex-direction: column; position: relative; z-index: 2; margin-top: -20px; }
.event-card-title { font-size: 1.35rem; font-weight: 900; margin-bottom: 12px; color: white; line-height: 1.3; letter-spacing: 0.5px;}
.event-meta-item { display: flex; align-items: center; gap: 10px; font-size: 0.95rem; color: #94a3b8; margin-bottom: 8px; }
.event-meta-item i { color: #00f0ff; width: 16px; }

.event-desc { color: #cbd5e1; font-size: 1rem; margin: 16px 0; line-height: 1.6; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }

.event-footer { display: flex; align-items: flex-end; justify-content: space-between; margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.08); }

.price-container { display: flex; flex-direction: column; }
.price-tag { font-size: 1.5rem; font-weight: 900; color: white; }
.price-free { color: #22c55e; font-size: 1.3rem; text-shadow: 0 0 10px rgba(34, 197, 94, 0.3); }
.premium-card .price-tag { color: #fbbf24; text-shadow: 0 0 10px rgba(245, 158, 11, 0.3); }
.premium-card .event-meta-item i { color: #fbbf24; }
.price-format { font-size: 0.75rem; color: #64748b; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; }

.view-btn {
    background: rgba(0, 240, 255, 0.1); color: #00f0ff; border: 1px solid rgba(0, 240, 255, 0.4);
    display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 800; font-size: 0.9rem; transition: all 0.3s ease; text-transform: uppercase; letter-spacing: 1.5px;
}
.view-btn:hover { background: #00f0ff; color: #050812; box-shadow: 0 0 20px rgba(0, 240, 255, 0.6); }
.premium-card .view-btn { background: rgba(245, 158, 11, 0.1); color: #fbbf24; border-color: rgba(245, 158, 11, 0.4); }
.premium-card .view-btn:hover { background: #fbbf24; color: #050812; box-shadow: 0 0 20px rgba(245, 158, 11, 0.6); }

/* NO EVENTS */
.no-events { background: rgba(8,11,26,0.6); border: 1px solid rgba(0,240,255,0.1); padding: 80px 20px; border-radius: 24px; text-align: center; box-shadow: inset 0 0 30px rgba(0,240,255,0.05); position: relative; z-index: 2;}
.no-events .icon { font-size: 4rem; margin-bottom: 20px; text-shadow: 0 0 30px rgba(0,240,255,0.6); animation: floatIcon 3s ease-in-out infinite;}
@keyframes floatIcon { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
.no-events h2 { color: white; margin-bottom: 12px; font-weight: 900; letter-spacing: 0.5px; font-size: 2rem;}
.no-events p { color: #94a3b8; font-size: 1.1rem; }
.no-events a { color: #00f0ff; font-weight: 800; text-decoration: none; border-bottom: 1px dashed rgba(0,240,255,0.4); transition: all 0.3s ease; }
.no-events a:hover { color: white; border-color: white; text-shadow: 0 0 10px rgba(255,255,255,0.5); }

/* PAGINATION */
.pagination { display: flex; justify-content: center; gap: 12px; flex-wrap: wrap; margin-top: 50px; position: relative; z-index: 2; }
.pagination a, .pagination span {
    padding: 12px 20px; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; text-decoration: none;
    color: #94a3b8; font-weight: 800; font-size: 0.95rem; transition: all 0.3s ease; background: rgba(8,11,26,0.6); backdrop-filter: blur(5px);
}
.pagination a:hover { background: rgba(0,240,255,0.1); color: #00f0ff; border-color: rgba(0,240,255,0.4); box-shadow: 0 0 20px rgba(0,240,255,0.3); transform: translateY(-2px); }
.pagination .active { background: #00f0ff; color: #050812; border-color: #00f0ff; box-shadow: 0 0 20px rgba(0,240,255,0.6); }

.results-info { color: #94a3b8; font-size: 0.95rem; margin-bottom: 24px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; position: relative; z-index: 2; }
.results-info strong { color: white; font-weight: 800; }

@media (max-width: 640px) {
    .filter-bar { flex-direction: column; }
    .events-grid { grid-template-columns: 1fr; }
    .page-hero h1 { font-size: 2.5rem; }
}
</style>

<!-- Page Hero -->
<div class="page-hero">
    <h1>Scan <span class="gradient-text">Matrix Instances</span></h1>
    <p>Locate upcoming experiences across the network. Filter by category, tier protocol, or query the database.</p>
</div>

<div class="events-container">
    <div class="container">
        <!-- Search & Filter Bar -->
        <div class="filter-bar">
            <form method="GET" action="events.php" style="display: contents;">
                <div class="filter-group">
                    <label><i class="fas fa-search"></i> Network Query</label>
                    <input type="text" name="search" placeholder="Enter parameters..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-tags"></i> Classification</label>
                    <select name="category">
                        <option value="">All Architectures</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['category']); ?>" <?php echo $category === $cat['category'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['category']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-layer-group"></i> Access Level</label>
                    <select name="tier">
                        <option value="">Global Array</option>
                        <option value="Premium"  <?php echo $tier === 'Premium'  ? 'selected' : ''; ?>>👑 Premium VIP</option>
                        <option value="Ordinary" <?php echo $tier === 'Ordinary' ? 'selected' : ''; ?>>🎟 Public Access</option>
                    </select>
                </div>
                <button type="submit" class="filter-btn"><i class="fas fa-filter"></i> Execute Filter</button>
                <a href="events.php" class="clear-btn">Reset</a>
            </form>
        </div>

        <!-- Tier Quick Tabs -->
        <div class="tier-tabs">
            <?php $baseUrl = 'events.php?' . ($search ? 'search='.urlencode($search).'&' : '') . ($category ? 'category='.urlencode($category).'&' : ''); ?>
            <a href="events.php<?php echo ($search || $category) ? '?' . ($search ? 'search='.urlencode($search) : '') . ($category && $search ? '&' : '') . ($category ? 'category='.urlencode($category) : '') : ''; ?>" class="tier-tab tier-tab-all <?php echo $tier === '' ? 'active' : ''; ?>">Global Array</a>
            <a href="<?php echo $baseUrl; ?>tier=Premium"  class="tier-tab tier-tab-premium  <?php echo $tier === 'Premium'  ? 'active' : ''; ?>">👑 Premium Clearance</a>
            <a href="<?php echo $baseUrl; ?>tier=Ordinary" class="tier-tab tier-tab-ordinary <?php echo $tier === 'Ordinary' ? 'active' : ''; ?>">🎟 Ordinary Clearance</a>
        </div>

        <!-- Results count -->
        <?php if ($total_events > 0): ?>
        <p class="results-info">Locating <strong><?php echo min($offset + $per_page, $total_events); ?></strong> of <strong><?php echo $total_events; ?></strong> active nodes<?php echo $tier ? ' · ' . $tier . ' level' : ''; ?><?php echo $category ? ' · ' . htmlspecialchars($category) : ''; ?></p>
        <?php endif; ?>

        <?php if (!empty($events)): ?>
            <div class="events-grid">
                <?php foreach ($events as $event):
                    $isPremium = ($event['tier'] ?? 'Ordinary') === 'Premium';
                    $img = !empty($event['image']) ? htmlspecialchars($event['image']) : getDefaultImage($event['category']);
                ?>
                    <div class="event-card <?php echo $isPremium ? 'premium-card' : ''; ?>">
                        <div class="event-card-image-wrap">
                            <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="event-card-image" loading="lazy">
                            <span class="tier-badge <?php echo $isPremium ? 'tier-premium' : 'tier-ordinary'; ?>">
                                <?php echo $isPremium ? '👑 Premium' : '🎟 Ordinary'; ?>
                            </span>
                            <span class="event-cat-label"><?php echo htmlspecialchars($event['category']); ?></span>
                        </div>
                        <div class="event-card-content">
                            <h3 class="event-card-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                            <div class="event-meta-item"><i class="fas fa-calendar-alt"></i><?php echo date('D, M d Y', strtotime($event['event_date'])); ?></div>
                            <div class="event-meta-item"><i class="fas fa-map-marker-alt"></i><?php echo htmlspecialchars($event['location']); ?></div>
                            <p class="event-desc"><?php echo htmlspecialchars($event['description']); ?></p>
                            
                            <div class="event-footer">
                                <div class="price-container">
                                    <div class="price-tag <?php echo $event['price'] <= 0 ? 'price-free' : ''; ?>">
                                        <?php if ($event['price'] > 0): ?>$<?php echo number_format($event['price'], 2); ?><?php else: ?>✅ Free Access<?php endif; ?>
                                    </div>
                                    <div class="price-format">Per Entity</div>
                                </div>
                                <a href="event.php?id=<?php echo $event['id']; ?>" class="view-btn">Connect <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php
                    $params = http_build_query(array_filter(['search' => $search, 'category' => $category, 'tier' => $tier]));
                    $sep = $params ? '&' : '?';
                    if ($page > 1): ?>
                        <a href="events.php?<?php echo $params; ?><?php echo $params ? '&' : ''; ?>page=1">« Base Node</a>
                        <a href="events.php?<?php echo $params; ?><?php echo $params ? '&' : ''; ?>page=<?php echo $page-1; ?>">‹ Preceding</a>
                    <?php endif; ?>
                    <?php for ($i = max(1,$page-2); $i <= min($total_pages,$page+2); $i++): ?>
                        <?php if ($i === $page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="events.php?<?php echo $params; ?><?php echo $params ? '&' : ''; ?>page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <?php if ($page < $total_pages): ?>
                        <a href="events.php?<?php echo $params; ?><?php echo $params ? '&' : ''; ?>page=<?php echo $page+1; ?>">Next Sequence ›</a>
                        <a href="events.php?<?php echo $params; ?><?php echo $params ? '&' : ''; ?>page=<?php echo $total_pages; ?>">Limit Node »</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="no-events">
                <div class="icon">🌌</div>
                <h2>Zero Nodes Detected</h2>
                <p>Try adjusting your query parameters or <a href="events.php">refresh the global array</a>.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
