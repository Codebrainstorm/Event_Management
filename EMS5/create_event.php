<?php
require_once 'config.php';
$page_title = 'Create Event';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $event_date = isset($_POST['event_date']) ? trim($_POST['event_date']) : '';
    $location = isset($_POST['location']) ? trim($_POST['location']) : '';
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $agenda = isset($_POST['agenda']) ? trim($_POST['agenda']) : '';
    $requirements = isset($_POST['requirements']) ? trim($_POST['requirements']) : '';
    $image = isset($_POST['image']) ? trim($_POST['image']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;

    $errors = [];

    if (empty($title) || strlen($title) < 3) {
        $errors[] = 'Event title must be at least 3 characters.';
    }

    if (empty($description) || strlen($description) < 10) {
        $errors[] = 'Event description must be at least 10 characters.';
    }

    if (empty($event_date)) {
        $errors[] = 'Event date is required.';
    } else {
        $event_timestamp = strtotime($event_date);
        $today_timestamp = strtotime(date('Y-m-d H:i'));
        if ($event_timestamp === false || $event_timestamp <= $today_timestamp) {
            $errors[] = 'Event date must be in the future.';
        }
    }

    if (empty($location) || strlen($location) < 3) {
        $errors[] = 'Location must be at least 3 characters.';
    }

    if (empty($category)) {
        $errors[] = 'Category is required.';
    }

    if (empty($errors)) {
        $title = mysqli_real_escape_string($connection, $title);
        $description = mysqli_real_escape_string($connection, $description);
        $location = mysqli_real_escape_string($connection, $location);
        $category = mysqli_real_escape_string($connection, $category);
        $agenda = mysqli_real_escape_string($connection, $agenda);
        $requirements = mysqli_real_escape_string($connection, $requirements);
        $image = mysqli_real_escape_string($connection, $image);
        $price = mysqli_real_escape_string($connection, $price);
        $user_id = $_SESSION['user_id'];

        $query = "INSERT INTO events (title, description, event_date, location, category, agenda, requirements, image, price, created_by, active, status, created_at) 
                  VALUES ('$title', '$description', '$event_date', '$location', '$category', '$agenda', '$requirements', '$image', '$price', $user_id, 1, 'pending', NOW())";

        if (mysqli_query($connection, $query)) {
            $success_message = 'Protocol initiated! Event creation pending admin validation. Redirecting to your dashboard...';
            echo '<meta http-equiv="refresh" content="2;url=dashboard.php">';
        } else {
            $error_message = 'Error compiling event protocol. Please try again later.';
        }
    } else {
        $error_message = implode(' ', $errors);
    }
}

include 'header.php';
?>

<style>
    /* CYBERPUNK/FUTURISTIC PAGE OVERRIDES */
    body {
        background: #050812;
        color: #e2e8f0;
    }

    .create-event-container {
        min-height: calc(100vh - 200px);
        padding: 60px 20px 100px;
        position: relative;
        background: #050812;
    }

    .create-event-container::before {
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
        max-width: 800px;
        margin: 0 auto;
        position: relative;
        z-index: 2;
    }

    .create-event-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .create-event-header h1 {
        font-size: 3rem;
        color: white;
        margin-bottom: 12px;
        font-weight: 900;
        letter-spacing: -0.5px;
        text-shadow: 0 0 20px rgba(0, 240, 255, 0.3);
    }

    .create-event-header p {
        color: #94a3b8;
        font-size: 1.1rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .form-wrapper {
        background: rgba(8, 11, 26, 0.8);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(0, 240, 255, 0.2);
        border-radius: 20px;
        padding: 50px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5), inset 0 0 20px rgba(0, 240, 255, 0.05);
        animation: slideUp 0.5s ease-out;
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-group-double {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
    }

    .form-group label {
        display: block;
        margin-bottom: 10px;
        font-weight: 700;
        color: #00f0ff;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 14px 16px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        font-size: 1rem;
        font-family: 'Outfit', sans-serif;
        background: rgba(5, 8, 18, 0.6);
        color: white;
        transition: all 0.3s ease;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #00f0ff;
        background: rgba(5, 8, 18, 0.9);
        box-shadow: 0 0 15px rgba(0, 240, 255, 0.2);
    }

    .form-group input::placeholder,
    .form-group textarea::placeholder {
        color: #64748b;
    }

    .form-group select option { background: #080b1a; color: white; }

    .form-group textarea {
        resize: vertical;
        min-height: 120px;
    }

    .alert {
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 600;
        animation: slideDown 0.4s ease-out;
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .alert-error {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.3);
        box-shadow: inset 0 0 20px rgba(239, 68, 68, 0.05);
    }

    .alert-success {
        background: rgba(34, 197, 94, 0.1);
        color: #22c55e;
        border: 1px solid rgba(34, 197, 94, 0.3);
        box-shadow: inset 0 0 20px rgba(34, 197, 94, 0.05);
    }

    .submit-btn {
        width: 100%;
        padding: 16px;
        background: linear-gradient(135deg, #00f0ff, #0051ff);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 1.1rem;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 0 20px rgba(0, 240, 255, 0.4);
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-top: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .submit-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 0 30px rgba(0, 240, 255, 0.6);
    }

    @media (max-width: 768px) {
        .form-wrapper { padding: 30px 20px; }
        .form-group-double { grid-template-columns: 1fr; gap: 20px; }
        .create-event-header h1 { font-size: 2.2rem; }
    }
</style>

<div class="create-event-container">
    <div class="container">
        <div class="create-event-header">
            <h1><i class="fas fa-satellite-dish" style="color: #00f0ff; margin-right: 15px;"></i>Initialize Node</h1>
            <p>Deploy a new instance into the global network</p>
        </div>

        <div class="form-wrapper">
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span><?php echo htmlspecialchars($error_message); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo htmlspecialchars($success_message); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="create_event.php">
                <div class="form-group">
                    <label for="title"><i class="fas fa-terminal"></i> Instance Designation *</label>
                    <input type="text" id="title" name="title" required value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" placeholder="Enter node designation...">
                </div>

                <div class="form-group">
                    <label for="description"><i class="fas fa-file-code"></i> Mission Parameters *</label>
                    <textarea id="description" name="description" required placeholder="Detail the event objectives and parameters..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                </div>

                <div class="form-group-double">
                    <div class="form-group">
                        <label for="event_date"><i class="fas fa-clock"></i> Execution Timestamp *</label>
                        <input type="datetime-local" id="event_date" name="event_date" required value="<?php echo isset($_POST['event_date']) ? htmlspecialchars($_POST['event_date']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="category"><i class="fas fa-network-wired"></i> Architecture Class *</label>
                        <select id="category" name="category" required>
                            <option value="">Select Architecture</option>
                            <option value="Conference" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Conference') ? 'selected' : ''; ?>>Conference Protocol</option>
                            <option value="Wedding" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Wedding') ? 'selected' : ''; ?>>Wedding Union</option>
                            <option value="Birthday" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Birthday') ? 'selected' : ''; ?>>Birthday Commemoration</option>
                            <option value="Seminar" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Seminar') ? 'selected' : ''; ?>>Seminar Exchange</option>
                            <option value="Workshop" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Workshop') ? 'selected' : ''; ?>>Workshop Node</option>
                            <option value="Meetup" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Meetup') ? 'selected' : ''; ?>>Meetup Point</option>
                            <option value="Concert" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Concert') ? 'selected' : ''; ?>>Concert Frequency</option>
                            <option value="Sports" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Sports') ? 'selected' : ''; ?>>Sports Arena</option>
                            <option value="Other" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Other') ? 'selected' : ''; ?>>Entity Unknown (Other)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group-double">
                    <div class="form-group">
                        <label for="location"><i class="fas fa-map-marker-alt"></i> Spatial Coordinates *</label>
                        <input type="text" id="location" name="location" required value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>" placeholder="Enter sector or physical address...">
                    </div>

                    <div class="form-group">
                        <label for="price"><i class="fas fa-credit-card"></i> Access Credit ($)</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : '0'; ?>" placeholder="0.00 for bypass">
                    </div>
                </div>

                <div class="form-group">
                    <label for="agenda"><i class="fas fa-stream"></i> Sequenced Timeline</label>
                    <textarea id="agenda" name="agenda" placeholder="Detail the event sequence schedule..."><?php echo isset($_POST['agenda']) ? htmlspecialchars($_POST['agenda']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="requirements"><i class="fas fa-shield-alt"></i> Access Prerequisites</label>
                    <textarea id="requirements" name="requirements" placeholder="Hardware/software prerequisites for entities..."><?php echo isset($_POST['requirements']) ? htmlspecialchars($_POST['requirements']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="image"><i class="fas fa-image"></i> Visual Asset URL</label>
                    <input type="url" id="image" name="image" value="<?php echo isset($_POST['image']) ? htmlspecialchars($_POST['image']) : ''; ?>" placeholder="https://secure-node.com/asset.jpg">
                </div>

                <button type="submit" class="submit-btn"><i class="fas fa-upload"></i> Compile and Deploy Event</button>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
