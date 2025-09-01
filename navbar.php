<?php
// navbar.php - نسخه اصلاح شده

// بررسی اینکه session قبلاً شروع نشده باشد
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// بررسی نقش کاربر
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'ادمین';
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'کاربر';
?>

<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سامانه مدیریت اعلا نیرو</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Vazirmatn Font -->
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    
    <style>
    :root {
        --primary-color: #2c3e50;
        --secondary-color: #3498db;
        --accent-color: #e74c3c;
        --success-color: #10b981;
        --warning-color: #f59e0b;
        --dark-bg: #1a1a1a;
        --dark-text: #ffffff;
    }

    /* Dark Mode */
    .dark-mode { 
        background-color: var(--dark-bg) !important; 
        color: var(--dark-text) !important; 
    }
    
    .dark-mode .navbar-custom { 
        background: linear-gradient(135deg, #1a1a1a 0%, #2d3748 100%) !important;
    }

    /* Navbar */
    .navbar-custom { 
        background: linear-gradient(135deg, var(--primary-color) 0%, #34495e 100%); 
        box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        padding: 0.8rem 1rem;
    }
    
    .navbar-brand {
        font-weight: 700;
        font-size: 1.4rem;
    }
    
    .nav-link {
        color: rgba(255,255,255,0.85) !important;
        padding: 0.5rem 1rem !important;
        border-radius: 8px;
        margin: 0 2px;
        transition: all 0.3s ease;
    }
    
    .nav-link:hover, .nav-link.active { 
        transform: translateY(-2px); 
        background-color: rgba(255,255,255,0.15); 
        color: #fff !important;
    }

    /* Navbar toggler custom icon */
    .navbar-toggler {
        border: 1px solid rgba(255,255,255,0.3);
    }
    .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 255, 255, 0.85)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }

    /* User Section */
    .clock, .theme-switch { 
        background: rgba(255,255,255,0.1); 
        padding: 0.5rem 1rem; 
        border-radius: 20px; 
        transition: all 0.3s ease; 
        color: #fff;
    }
    
    .theme-switch {
        cursor: pointer;
    }
    
    /* Dropdown menu */
    .dropdown-menu {
        background-color: #2c3e50;
        border: none;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    .dropdown-item {
        color: rgba(255,255,255,0.85);
        transition: all 0.3s ease;
    }
    
    .dropdown-item:hover {
        background-color: rgba(255,255,255,0.1);
        color: #fff;
    }
    
    .logout-btn {
        color: #e74c3c !important;
    }
    
    .logout-btn:hover {
        background-color: rgba(231, 76, 60, 0.2) !important;
    }
    
    /* User dropdown */
    .user-dropdown {
        background: rgba(255,255,255,0.1);
        padding: 0.5rem 1rem;
        border-radius: 20px;
        transition: all 0.3s ease;
        color: #fff;
        display: flex;
        align-items: center;
        text-decoration: none !important;
    }
    
    .user-dropdown:hover {
        background: rgba(255,255,255,0.2);
        color: #fff;
    }
    
    /* Dark mode adjustments for dropdown */
    .dark-mode .dropdown-menu {
        background-color: #2d3748;
    }
    
    .dark-mode .dropdown-item {
        color: rgba(255,255,255,0.85);
    }
    
    .dark-mode .dropdown-item:hover {
        background-color: rgba(255,255,255,0.1);
    }
    
    /* User section flex */
    .user-section {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    /* Responsive */
    @media (max-width: 991px) {
        .navbar-nav {
            margin-top: 15px;
        }
        
        .nav-link {
            margin: 3px 0;
        }
        
        .user-section {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(255,255,255,0.2);
            width: 100%;
            flex-direction: column;
            gap: 10px;
        }
        
        .clock, .theme-switch, .dropdown {
            width: 100%;
            text-align: center;
            margin: 5px 0;
        }
        
        .user-dropdown {
            width: 100%;
            justify-content: center;
        }
        
        .dropdown-menu {
            width: 100%;
            text-align: center;
        }
    }
    </style>
</head>
<body class="<?php echo isset($_COOKIE['theme']) && $_COOKIE['theme']==='dark' ? 'dark-mode' : ''; ?>">
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom fixed-top">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            <i class="fas fa-bolt me-2"></i>اعلا نیرو
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" 
                aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-1"></i>داشبورد
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="assets.php">
                        <i class="fas fa-server me-1"></i>مدیریت دارایی‌ها
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="customers.php">
                        <i class="fas fa-users me-1"></i>مدیریت مشتریان
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="assignments.php">
                        <i class="fas fa-link me-1"></i>مدیریت انتساب‌ها
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="create_guaranty.php">
                        <i class="fas fa-file-contract me-1"></i>مدیریت گارانتی
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reports.php">
                        <i class="fas fa-chart-bar me-1"></i>گزارشات
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="survey.php">
                        <i class="fas fa-poll me-1"></i>نظر سنجی
                    </a>
                </li>
                <?php if ($is_admin): ?>
                <li class="nav-item">
                    <a class="nav-link" href="system_logs.php">
                        <i class="fas fa-clipboard-list me-1"></i>لاگ سیستم
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="users.php">
                        <i class="fas fa-user-cog me-1"></i>مدیریت کاربران
                    </a>
                </li>
                <?php endif; ?>
            </ul>
            
            <div class="user-section">
                <div class="clock">
                    <i class="fas fa-clock me-1"></i>
                    <span id="clockTime"><?php echo date('H:i:s'); ?></span>
                </div>
                
                <div class="theme-switch" onclick="toggleTheme()">
                    <i class="fas <?php echo isset($_COOKIE['theme']) && $_COOKIE['theme']==='dark' ? 'fa-sun' : 'fa-moon'; ?>"></i>
                </div>
                
                <div class="dropdown">
                    <a class="user-dropdown dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user me-1"></i>
                        <?php echo $username; ?>
                        <?php if ($is_admin): ?>
                        <span class="badge bg-warning ms-1">ادمین</span>
                        <?php endif; ?>
                    </a>
                    
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-edit me-2"></i>پروفایل کاربری</a></li>
                        <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i>تنظیمات</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item logout-btn" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>خروج از سیستم</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
function toggleTheme() {
    const body = document.body;
    const icon = document.querySelector('.theme-switch i');
    if(body.classList.contains('dark-mode')) {
        body.classList.remove('dark-mode');
        icon.classList.replace('fa-sun', 'fa-moon');
        document.cookie = "theme=light; path=/; max-age=31536000";
    } else {
        body.classList.add('dark-mode');
        icon.classList.replace('fa-moon', 'fa-sun');
        document.cookie = "theme=dark; path=/; max-age=31536000";
    }
}

// به روز رسانی ساعت به صورت زنده
setInterval(() => {
    document.getElementById('clockTime').textContent = new Date().toLocaleTimeString('fa-IR');
}, 1000);

// بررسی تم ذخیره شده در کوکی
document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = document.cookie.match('(^|;)\\s*theme\\s*=\\s*([^;]+)')?.pop() || '';
    const icon = document.querySelector('.theme-switch i');
    if(savedTheme === 'dark') {
        document.body.classList.add('dark-mode');
        icon.classList.replace('fa-moon', 'fa-sun');
    }
});
</script>
</body>
</html>