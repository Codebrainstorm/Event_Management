<?php
require_once 'config.php';
$page_title = 'Register';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';

    $errors = [];

    if (empty($name) || strlen($name) < 2) {
        $errors[] = 'Designation must be at least 2 characters.';
    }

    if (empty($email)) {
        $errors[] = 'Comm link (Email) is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid comm link format.';
    }

    if (empty($phone) || strlen(preg_replace('/\D/', '', $phone)) < 10) {
        $errors[] = 'Valid standard device identifier is required.';
    }

    if (empty($password) || strlen($password) < 6) {
        $errors[] = 'Access sequence must be at least 6 characters.';
    }

    if ($password !== $confirm_password) {
        $errors[] = 'Access sequences do not match.';
    }

    if (empty($errors)) {
        $email = mysqli_real_escape_string($connection, $email);
        
        // Check if email already exists
        $check_query = "SELECT id FROM users WHERE email = '$email' LIMIT 1";
        $check_result = mysqli_query($connection, $check_query);

        if (mysqli_num_rows($check_result) > 0) {
            $error_message = 'Entity already registered. Execute uplink or use another identifier.';
        } else {
            $name = mysqli_real_escape_string($connection, $name);
            $phone = mysqli_real_escape_string($connection, $phone);
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            $insert_query = "INSERT INTO users (name, email, phone, password, created_at) VALUES ('$name', '$email', '$phone', '$hashed_password', NOW())";

            if (mysqli_query($connection, $insert_query)) {
                $success_message = 'Base Node successfully initialized. Standby for redirect...';
                echo '<meta http-equiv="refresh" content="2;url=login.php">';
            } else {
                $error_message = 'Initialization failed due to network lag. Retrying...';
            }
        }
    } else {
        $error_message = implode(' ', $errors);
    }
}

include 'header.php';
?>

<style>
    .register-container {
        min-height: calc(100vh - 70px);
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 40px 20px;
        position: relative;
    }

    .register-container::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: 
            radial-gradient(circle at center, rgba(0, 240, 255, 0.05) 0%, transparent 60%);
        pointer-events: none;
        z-index: 0;
    }

    .register-wrapper {
        background: rgba(8, 11, 26, 0.8);
        border: 1px solid rgba(0, 240, 255, 0.15);
        border-radius: 24px;
        box-shadow: 0 25px 50px rgba(0,0,0,0.8), inset 0 0 30px rgba(0,240,255,0.05);
        width: 100%;
        max-width: 500px;
        padding: 50px;
        animation: slideUp 0.5s ease-out;
        position: relative;
        z-index: 10;
        backdrop-filter: blur(12px);
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .register-wrapper::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, transparent, rgba(0,240,255,0.03));
        pointer-events: none;
        border-radius: 24px;
    }

    .register-header {
        text-align: center;
        margin-bottom: 40px;
        position: relative;
        z-index: 10;
    }

    .register-icon {
        font-size: 2.5rem;
        color: var(--primary, #00f0ff);
        margin-bottom: 15px;
        text-shadow: 0 0 20px rgba(0,240,255,0.4);
    }

    .register-header h1 {
        font-size: 2rem;
        color: white;
        margin-bottom: 10px;
        font-weight: 800;
        letter-spacing: 1px;
        text-shadow: 0 0 20px rgba(0, 240, 255, 0.3);
    }

    .register-header p {
        color: var(--text-muted, #94a3b8);
        font-size: 0.95rem;
        font-weight: 500;
        letter-spacing: 0.5px;
    }

    .form-group {
        margin-bottom: 20px;
        position: relative;
        z-index: 10;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--text-muted, #94a3b8);
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .form-group input {
        width: 100%;
        padding: 14px 16px;
        background: rgba(8, 11, 26, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        font-size: 1rem;
        font-family: 'Outfit', sans-serif;
        color: white;
        transition: all 0.3s ease;
        backdrop-filter: blur(8px);
    }

    .form-group input::placeholder {
        color: rgba(255,255,255,0.2);
    }

    .form-group input:focus {
        outline: none;
        border-color: var(--primary, #00f0ff);
        background: rgba(8, 11, 26, 0.9);
        box-shadow: 0 0 20px rgba(0, 240, 255, 0.15);
    }

    .alert {
        padding: 15px 20px;
        border-radius: 12px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 500;
        animation: slideDown 0.4s ease-out;
        font-size: 0.9rem;
        backdrop-filter: blur(4px);
        position: relative;
        z-index: 10;
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .alert-error {
        background: rgba(239, 68, 68, 0.1);
        color: #fca5a5;
        border: 1px solid rgba(239, 68, 68, 0.3);
        box-shadow: 0 0 20px rgba(239, 68, 68, 0.1);
    }

    .alert-success {
        background: rgba(34, 197, 94, 0.1);
        color: #86efac;
        border: 1px solid rgba(34, 197, 94, 0.3);
        box-shadow: 0 0 20px rgba(34, 197, 94, 0.1);
    }

    .register-btn {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, var(--primary, #00f0ff), #0051ff);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 1rem;
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 1px;
        box-shadow: 0 0 15px rgba(0, 240, 255, 0.3);
        position: relative;
        z-index: 10;
        margin-top: 25px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .register-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 0 25px rgba(0, 240, 255, 0.5);
    }

    .register-footer {
        text-align: center;
        margin-top: 30px;
        color: var(--text-muted, #94a3b8);
        font-size: 0.95rem;
        position: relative;
        z-index: 10;
    }

    .register-footer a {
        color: var(--primary, #00f0ff);
        text-decoration: none;
        font-weight: 700;
        transition: all 0.3s ease;
        margin-left: 5px;
    }

    .register-footer a:hover {
        text-shadow: 0 0 10px rgba(0, 240, 255, 0.5);
    }

    @media (max-width: 500px) {
        .register-wrapper { padding: 35px 25px; }
        .register-header h1 { font-size: 1.8rem; }
    }
</style>

<div class="register-container">
    <div class="register-wrapper">
        <div class="register-header">
            <div class="register-icon"><i class="fas fa-satellite"></i></div>
            <h1>Initialize Node</h1>
            <p>Establish your entity profile to access events</p>
        </div>

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

        <form method="POST" action="register.php">
            <div class="form-group">
                <label for="name">Entity Designation (Full Name)</label>
                <input type="text" id="name" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" placeholder="Enter designation name" autocomplete="off">
            </div>

            <div class="form-group">
                <label for="email">Comm Link (Email)</label>
                <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" placeholder="Enter frequency address" autocomplete="off">
            </div>

            <div class="form-group">
                <label for="phone">Device Identifier (Phone)</label>
                <input type="tel" id="phone" name="phone" required value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" placeholder="Enter cellular ID" autocomplete="off">
            </div>

            <div class="form-group">
                <label for="password">Access Sequence (Password)</label>
                <input type="password" id="password" name="password" required placeholder="Minimum 6 characters">
            </div>

            <div class="form-group">
                <label for="confirm_password">Verify Sequence</label>
                <input type="password" id="confirm_password" name="confirm_password" required placeholder="Re-enter sequence">
            </div>

            <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 20px; cursor: pointer; color: var(--text-muted, #94a3b8); position: relative; z-index: 10;">
                <input type="checkbox" name="terms" required style="width: 16px; height: 16px; cursor: pointer; accent-color: var(--primary, #00f0ff);">
                <span style="font-size: 0.85rem;">I acknowledge Nexus Network Protocols</span>
            </label>

            <button type="submit" class="register-btn"><i class="fas fa-plus-circle"></i> Create Instance</button>
        </form>

        <div class="register-footer">
            Profile already exists? <a href="login.php">Execute Uplink</a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
