<?php
// Get current user info
$currentUser = [
    'id' => $_SESSION['user_id'] ?? null,
    'username' => $_SESSION['username'] ?? 'کاربر',
    'role' => $_SESSION['role'] ?? 'کاربر عادی',
    'is_admin' => ($_SESSION['role'] ?? '') === 'ادمین'
];

// Get current page for active navigation
$currentPage = $_SERVER['REQUEST_URI'] ?? '/';
?>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom fixed-top">
    <div class="container">
        <a class="navbar-brand" href="/dashboard">
            <i class="fas fa-bolt me-2"></i>اعلا نیرو
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" 
                aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($currentPage, '/dashboard') !== false ? 'active' : ''; ?>" href="/dashboard">
                        <i class="fas fa-tachometer-alt me-1"></i>داشبورد
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($currentPage, '/assets') !== false ? 'active' : ''; ?>" href="/assets">
                        <i class="fas fa-server me-1"></i>مدیریت دارایی‌ها
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($currentPage, '/customers') !== false ? 'active' : ''; ?>" href="/customers">
                        <i class="fas fa-users me-1"></i>مدیریت مشتریان
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($currentPage, '/assignments') !== false ? 'active' : ''; ?>" href="/assignments">
                        <i class="fas fa-link me-1"></i>مدیریت انتساب‌ها
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($currentPage, '/reports') !== false ? 'active' : ''; ?>" href="/reports">
                        <i class="fas fa-chart-bar me-1"></i>گزارشات
                    </a>
                </li>
                
                <?php if ($currentUser['is_admin']): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-cog me-1"></i>مدیریت سیستم
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/admin/users"><i class="fas fa-user-cog me-2"></i>مدیریت کاربران</a></li>
                        <li><a class="dropdown-item" href="/admin/logs"><i class="fas fa-clipboard-list me-2"></i>لاگ سیستم</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/admin/settings"><i class="fas fa-sliders-h me-2"></i>تنظیمات</a></li>
                    </ul>
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
                        <?php echo htmlspecialchars($currentUser['username']); ?>
                        <?php if ($currentUser['is_admin']): ?>
                        <span class="badge bg-warning ms-1">ادمین</span>
                        <?php endif; ?>
                    </a>
                    
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/profile"><i class="fas fa-user-edit me-2"></i>پروفایل کاربری</a></li>
                        <li><a class="dropdown-item" href="/settings"><i class="fas fa-cog me-2"></i>تنظیمات</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item logout-btn" href="/logout"><i class="fas fa-sign-out-alt me-2"></i>خروج از سیستم</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>