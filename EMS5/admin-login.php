<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin-dashboard.php');
    exit;
}

$error = '';
$username = 'admin';
$password = '1234';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input_username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $input_password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if ($input_username === $username && $input_password === $password) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: admin-dashboard.php');
        exit;
    } else {
        $error = 'Invalid designation or access code.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal — EMS Admin</title>
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
        body { 
            font-family:'Outfit',sans-serif; 
            background:var(--dark-bg); 
            color:var(--text-main); 
            min-height:100vh; 
            display: flex;
            justify-content: center;
            align-items: center;
            position:relative; 
        }
        
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
        }
        
        .login-container {
            background: var(--card-bg);
            padding: 40px 40px;
            border-radius: 24px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.8), inset 0 0 30px rgba(0,240,255,0.05);
            border: 1px solid var(--border-color);
            width: 100%;
            max-width: 420px;
            backdrop-filter: blur(12px);
            position: relative;
            overflow: hidden;
        }

        .login-container::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, transparent, rgba(0,240,255,0.03));
            pointer-events: none;
        }

        .login-icon {
            font-size: 3rem;
            color: var(--neon-blue);
            text-align: center;
            margin-bottom: 20px;
            text-shadow: 0 0 20px rgba(0,240,255,0.4);
        }
        
        .login-container h1 {
            text-align: center;
            color: white;
            margin-bottom: 5px;
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: 1px;
            text-shadow: 0 0 20px rgba(0, 240, 255, 0.3);
        }
        
        .login-container p {
            text-align: center;
            color: var(--text-muted);
            margin-bottom: 30px;
            font-size: 0.9rem;
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        
        .error {
            background: rgba(239, 68, 68, 0.1);
            color: #fca5a5;
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 24px;
            border: 1px solid rgba(239, 68, 68, 0.3);
            font-size: 0.9rem;
            text-align: center;
            box-shadow: 0 0 20px rgba(239, 68, 68, 0.1);
            backdrop-filter: blur(4px);
        }
        
        .form-group {
            margin-bottom: 24px;
            position: relative;
            z-index: 10;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px 16px;
            background: rgba(8, 11, 26, 0.6);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            font-size: 1rem;
            font-family: 'Outfit', sans-serif;
            color: white;
            transition: all 0.3s ease;
            backdrop-filter: blur(8px);
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--neon-blue);
            box-shadow: 0 0 20px rgba(0, 240, 255, 0.15);
            background: rgba(8, 11, 26, 0.9);
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--neon-blue), #0051ff);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
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
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 25px rgba(0, 240, 255, 0.5);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-icon"><i class="fas fa-cube"></i></div>
        <h1>NEXUS ADMIN</h1>
        <p>Authorize to Access Central Mainframe</p>
        
        <?php if (!empty($error)): ?>
            <div class="error"><i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Designation (Username)</label>
                <input type="text" id="username" name="username" required autofocus autocomplete="off">
            </div>
            
            <div class="form-group">
                <label for="password">Access Code (Password)</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn-login"><i class="fas fa-fingerprint"></i> Authenticate</button>
        </form>
    </div>
</body>
</html>