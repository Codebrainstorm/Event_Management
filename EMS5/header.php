<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' | ' : ''; ?><?php echo APP_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary: #00f0ff;
            --primary-dark: #00b3cc;
            --secondary: #ff007f;
            --accent: #f59e0b;
            --premium: #fbbf24;
            --premium-dark: #d97706;
            --dark: #050812;
            --dark-2: #080b1a;
            --dark-3: #0f172a;
            --light: #f8fafc;
            --text: #e2e8f0;
            --text-muted: #94a3b8;
            --border: rgba(255,255,255,0.1);
            --success: #22c55e;
            --warning: #f59e0b;
            --danger: #ef4444;
            --radius: 12px;
            --radius-lg: 20px;
            --shadow: 0 10px 30px rgba(0,0,0,0.5);
            --shadow-lg: 0 20px 40px rgba(0,240,255,0.15);
            --gradient: linear-gradient(135deg, #00f0ff 0%, #0051ff 100%);
            --gradient-gold: linear-gradient(135deg, #f59e0b 0%, #fbbf24 50%, #fcd34d 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--dark);
            color: var(--text);
            line-height: 1.6;
        }

        /* ===== HEADER ===== */
        header {
            background: rgba(8, 11, 26, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 1px 0 rgba(255,255,255,0.05), 0 4px 30px rgba(0, 240, 255, 0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(0, 240, 255, 0.1);
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
            max-width: 1400px;
            margin: 0 auto;
            height: 70px;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 900;
            background: linear-gradient(90deg, #00f0ff, #fff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            letter-spacing: 1px;
            text-shadow: 0 0 20px rgba(0, 240, 255, 0.3);
        }

        .logo-icon {
            width: 38px;
            height: 38px;
            background: rgba(0, 240, 255, 0.1);
            border: 1px solid rgba(0, 240, 255, 0.3);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: #00f0ff;
            flex-shrink: 0;
            box-shadow: inset 0 0 10px rgba(0, 240, 255, 0.2);
            -webkit-text-fill-color: #00f0ff;
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 0.5rem;
        }

        nav a {
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            padding: 8px 16px;
            border-radius: 8px;
            position: relative;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        nav a:hover {
            color: var(--primary);
            background: rgba(0, 240, 255, 0.05);
            text-shadow: 0 0 10px rgba(0, 240, 255, 0.5);
        }

        nav a.active {
            color: var(--primary);
            background: rgba(0, 240, 255, 0.1);
            border: 1px solid rgba(0, 240, 255, 0.2);
            font-weight: 600;
            box-shadow: 0 0 15px rgba(0, 240, 255, 0.1);
        }

        .nav-buttons {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        /* ===== BUTTONS ===== */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            letter-spacing: 1px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .btn-primary {
            background: var(--gradient);
            color: white;
            box-shadow: 0 0 20px rgba(0, 240, 255, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 30px rgba(0, 240, 255, 0.6);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.4);
            transform: translateY(-2px);
        }

        .btn-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #fca5a5;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .btn-danger:hover {
            background: var(--danger);
            color: white;
            box-shadow: 0 0 20px rgba(239, 68, 68, 0.4);
        }

        .btn-gold {
            background: rgba(245, 158, 11, 0.1);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.4);
            box-shadow: 0 0 20px rgba(245, 158, 11, 0.2);
        }

        .btn-gold:hover {
            background: var(--gradient-gold);
            color: #080b1a;
            transform: translateY(-2px);
            box-shadow: 0 0 30px rgba(245, 158, 11, 0.5);
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 0.8rem;
        }

        .btn-lg {
            padding: 14px 32px;
            font-size: 1rem;
            border-radius: 12px;
        }

        /* ===== USER AVATAR ===== */
        .user-menu {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255,255,255,0.03);
            padding: 4px 12px 4px 4px;
            border-radius: 30px;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--gradient);
            color: #080b1a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.9rem;
            flex-shrink: 0;
            box-shadow: 0 0 10px rgba(0, 240, 255, 0.5);
        }

        .user-name {
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--text);
            letter-spacing: 0.5px;
        }

        /* ===== MOBILE MENU ===== */
        .mobile-menu-toggle {
            display: none;
            background: rgba(0, 240, 255, 0.1);
            border: 1px solid rgba(0, 240, 255, 0.3);
            border-radius: 8px;
            padding: 8px 12px;
            cursor: pointer;
            color: #00f0ff;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }

        .mobile-menu-toggle:hover {
            background: rgba(0, 240, 255, 0.2);
            box-shadow: 0 0 15px rgba(0, 240, 255, 0.3);
        }

        /* ===== ALERT STYLES ===== */
        .alert {
            padding: 16px 24px;
            border-radius: var(--radius);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            font-size: 0.95rem;
            backdrop-filter: blur(10px);
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

        .alert-info {
            background: rgba(0, 240, 255, 0.1);
            color: #93c5fd;
            border: 1px solid rgba(0, 240, 255, 0.3);
            box-shadow: 0 0 20px rgba(0, 240, 255, 0.1);
        }

        /* ===== BADGE STYLES ===== */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            backdrop-filter: blur(4px);
        }

        .badge-premium {
            background: rgba(245, 158, 11, 0.2);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.5);
            box-shadow: 0 0 10px rgba(245, 158, 11, 0.2);
        }

        .badge-ordinary {
            background: rgba(255, 255, 255, 0.1);
            color: #e2e8f0;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .badge-pending { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
        .badge-approved { background: rgba(34, 197, 94, 0.15); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.3); }
        .badge-completed { background: rgba(0, 240, 255, 0.15); color: #60a5fa; border: 1px solid rgba(0, 240, 255, 0.3); }
        .badge-cancelled { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }

        /* ===== STATUS BADGE ===== */
        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            backdrop-filter: blur(4px);
        }
        .status-pending { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
        .status-approved { background: rgba(34, 197, 94, 0.15); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.3); }
        .status-completed { background: rgba(0, 240, 255, 0.15); color: #60a5fa; border: 1px solid rgba(0, 240, 255, 0.3); }
        .status-cancelled, .status-rejected { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }

        /* ===== CARD STYLES ===== */
        .card {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        /* ===== FORM STYLES ===== */
        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--text);
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 14px 16px;
            background: rgba(8, 11, 26, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius);
            font-size: 1rem;
            font-family: 'Outfit', sans-serif;
            color: white;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 15px rgba(0, 240, 255, 0.2);
            background: rgba(8, 11, 26, 0.9);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        /* ===== SECTION STYLES ===== */
        .section {
            padding: 6rem 0;
            position: relative;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: white;
            font-weight: 900;
            letter-spacing: 0px;
        }

        .section-title span {
            color: var(--primary);
            text-shadow: 0 0 20px rgba(0, 240, 255, 0.3);
        }

        .section-subtitle {
            text-align: center;
            color: var(--text-muted);
            font-size: 1.1rem;
            margin-bottom: 4rem;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 24px;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            background: radial-gradient(circle at top right, rgba(0, 240, 255, 0.05), transparent 40%),
                        radial-gradient(circle at bottom left, rgba(255, 0, 127, 0.05), transparent 40%);
            z-index: -1;
            pointer-events: none;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 900px) {
            .navbar {
                padding: 0 1.5rem;
                flex-wrap: wrap;
                height: auto;
                padding-top: 16px;
                padding-bottom: 16px;
            }

            nav ul {
                display: none;
                width: 100%;
                flex-direction: column;
                gap: 0;
                padding: 16px 0;
                background: rgba(8, 11, 26, 0.95);
                border-radius: 12px;
                margin-top: 16px;
                border: 1px solid rgba(255,255,255,0.05);
            }

            nav ul.active {
                display: flex;
            }

            nav li {
                border-bottom: 1px solid rgba(255,255,255,0.05);
            }

            nav a {
                display: block;
                padding: 16px;
                border-radius: 0;
            }

            .mobile-menu-toggle {
                display: block;
            }

            .nav-buttons {
                width: 100%;
                padding-top: 20px;
                margin-top: 10px;
                border-top: 1px solid rgba(255,255,255,0.05);
                justify-content: space-between;
            }

            .user-name {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .btn {
                padding: 10px 16px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="navbar">
            <a href="index.php" class="logo">
                <div class="logo-icon"><i class="fas fa-bolt"></i></div>
                NEXUS
            </a>

            <button class="mobile-menu-toggle" id="mobileMenuToggle">
                <i class="fas fa-bars"></i>
            </button>

            <nav>
                <ul id="navMenu">
                    <li><a href="events.php" <?php echo basename($_SERVER['PHP_SELF']) === 'events.php' ? 'class="active"' : ''; ?>><i class="fas fa-database"></i> Database</a></li>
                    <li><a href="event-calendar.php" <?php echo basename($_SERVER['PHP_SELF']) === 'event-calendar.php' ? 'class="active"' : ''; ?>><i class="fas fa-satellite-dish"></i> Matrix</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="dashboard.php" <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'class="active"' : ''; ?>><i class="fas fa-terminal"></i> Terminal</a></li>
                        <li><a href="create_event.php" <?php echo basename($_SERVER['PHP_SELF']) === 'create_event.php' ? 'class="active"' : ''; ?>><i class="fas fa-code-branch"></i> Deploy</a></li>
                    <?php endif; ?>
                </ul>
            </nav>

            <div class="nav-buttons">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="user-menu">
                        <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?></div>
                        <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    </div>
                    <a href="logout.php" class="btn btn-danger btn-sm"><i class="fas fa-power-off"></i> Disconnect</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-secondary btn-sm"><i class="fas fa-sign-in-alt"></i> Auth</a>
                    <a href="register.php" class="btn btn-primary btn-sm"><i class="fas fa-user-plus"></i> Init</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <script>
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const navMenu = document.getElementById('navMenu');

        if (mobileMenuToggle) {
            mobileMenuToggle.addEventListener('click', () => {
                navMenu.classList.toggle('active');
            });
            navMenu.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
                    navMenu.classList.remove('active');
                });
            });
        }
    </script>
</body>
</html>
