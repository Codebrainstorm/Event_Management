<?php
require_once 'config.php';
$page_title = 'Login';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    $errors = [];

    if (empty($username)) {
        $errors[] = 'Designation is required.';
    }

    if (empty($password)) {
        $errors[] = 'Access code is required.';
    }

    if (empty($errors)) {
        // Check for admin login
        if ($username === 'admin' && $password === '1234') {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = 'admin';
            header('Location: admin-dashboard.php');
            exit();
        } 
        // Check for user login
        else {
            $username_escaped = mysqli_real_escape_string($connection, $username);
            $query = "SELECT id, name, email, password FROM users WHERE email = '$username_escaped' OR name = '$username_escaped' LIMIT 1";
            $result = mysqli_query($connection, $query);

            if (mysqli_num_rows($result) > 0) {
                $user = mysqli_fetch_assoc($result);
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    header('Location: dashboard.php');
                    exit();
                } else {
                    $error_message = 'Invalid designation or access code.';
                }
            } else {
                $error_message = 'Invalid designation or access code.';
            }
        }
    } else {
        $error_message = implode(' ', $errors);
    }
}

include 'header.php';
?>

<style>
    .login-container {
        min-height: calc(100vh - 70px);
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 40px 20px;
        position: relative;
    }

    .login-container::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: 
            radial-gradient(circle at center, rgba(0, 240, 255, 0.05) 0%, transparent 60%);
        pointer-events: none;
        z-index: 0;
    }

    .login-wrapper {
        background: rgba(8, 11, 26, 0.8);
        border: 1px solid rgba(0, 240, 255, 0.15);
        border-radius: 24px;
        box-shadow: 0 25px 50px rgba(0,0,0,0.8), inset 0 0 30px rgba(0,240,255,0.05);
        width: 100%;
        max-width: 450px;
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

    .login-wrapper::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, transparent, rgba(0,240,255,0.03));
        pointer-events: none;
        border-radius: 24px;
    }

    .login-header {
        text-align: center;
        margin-bottom: 40px;
        position: relative;
        z-index: 10;
    }

    .login-icon {
        font-size: 2.5rem;
        color: var(--primary, #00f0ff);
        margin-bottom: 15px;
        text-shadow: 0 0 20px rgba(0,240,255,0.4);
    }

    .login-header h1 {
        font-size: 2rem;
        color: white;
        margin-bottom: 10px;
        font-weight: 800;
        letter-spacing: 1px;
        text-shadow: 0 0 20px rgba(0, 240, 255, 0.3);
    }

    .login-header p {
        color: var(--text-muted, #94a3b8);
        font-size: 0.95rem;
        font-weight: 500;
        letter-spacing: 0.5px;
    }

    .form-group {
        margin-bottom: 25px;
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

    .remember-forgot {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        font-size: 0.9rem;
        position: relative;
        z-index: 10;
    }

    .remember-forgot a {
        color: var(--primary, #00f0ff);
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .remember-forgot a:hover {
        text-shadow: 0 0 10px rgba(0, 240, 255, 0.5);
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

    .login-btn {
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
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .login-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 0 25px rgba(0, 240, 255, 0.5);
    }

    .login-btn:active {
        transform: translateY(0);
    }

    .login-footer {
        text-align: center;
        margin-top: 30px;
        color: var(--text-muted, #94a3b8);
        font-size: 0.95rem;
        position: relative;
        z-index: 10;
    }

    .login-footer a {
        color: var(--primary, #00f0ff);
        text-decoration: none;
        font-weight: 700;
        transition: all 0.3s ease;
        margin-left: 5px;
    }

    .login-footer a:hover {
        text-shadow: 0 0 10px rgba(0, 240, 255, 0.5);
    }

    @media (max-width: 500px) {
        .login-wrapper { padding: 35px 25px; }
        .login-header h1 { font-size: 1.8rem; }
        .form-group { margin-bottom: 20px; }
    }
</style>

<div class="login-container">
    <div class="login-wrapper">
        <div class="login-header">
            <div class="login-icon"><i class="fas fa-fingerprint"></i></div>
            <h1>Establish Uplink</h1>
            <p>Authenticate to access the matrix</p>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <span><?php echo htmlspecialchars($error_message); ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Designation (Username or Email)</label>
                <input type="text" id="username" name="username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" placeholder="Enter entity designation" autocomplete="off">
            </div>

            <div class="form-group">
                <label for="password">Access Code (Password)</label>
                <input type="password" id="password" name="password" required placeholder="Enter sequence code">
            </div>

            <div class="remember-forgot">
                <label style="display: flex; align-items: center; gap: 8px; margin: 0; cursor: pointer; color: var(--text-muted, #94a3b8);">
                    <input type="checkbox" name="remember" style="width: 16px; height: 16px; cursor: pointer; accent-color: var(--primary, #00f0ff);">
                    <span>Maintain Node Connection</span>
                </label>
            </div>

            <button type="submit" class="login-btn"><i class="fas fa-sign-in-alt"></i> Execute Uplink</button>
        </form>

        <div class="login-footer">
            Unregistered entity? <a href="register.php">Initialize Base Node</a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
