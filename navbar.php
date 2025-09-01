<?php
// navbar.php - Partial: only the nav markup; no HTML/HEAD/BODY wrappers.
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'ادمین';
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'کاربر';
?>
<style>
:root { --primary-color: #2c3e50; --dark-bg: #1a1a1a; --dark-text: #ffffff; }
.dark-mode { background-color: var(--dark-bg) !important; color: var(--dark-text) !important; }
.navbar-custom { background: linear-gradient(135deg, var(--primary-color) 0%, #34495e 100%); box-shadow: 0 2px 20px rgba(0,0,0,0.1); padding: 0.8rem 1rem; }
.nav-link { color: rgba(255,255,255,0.85) !important; border-radius: 8px; margin: 0 2px; }
.nav-link:hover, .nav-link.active { background-color: rgba(255,255,255,0.15); color: #fff !important; }
.navbar-toggler { border: 1px solid rgba(255,255,255,0.3); }
.navbar-toggler-icon { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 255, 255, 0.85)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e"); }
.user-section { display: flex; align-items: center; gap: 15px; }
.clock, .theme-switch { background: rgba(255,255,255,0.1); padding: 0.5rem 1rem; border-radius: 20px; color: #fff; }
.theme-switch { cursor: pointer; }
</style>
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom fixed-top">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            <i class="fas fa-bolt me-2"></i>اعلا نیرو
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt me-1"></i>داشبورد</a></li>
                <li class="nav-item"><a class="nav-link" href="assets.php"><i class="fas fa-server me-1"></i>مدیریت دارایی‌ها</a></li>
                <li class="nav-item"><a class="nav-link" href="customers.php"><i class="fas fa-users me-1"></i>مدیریت مشتریان</a></li>
                <li class="nav-item"><a class="nav-link" href="assignments.php"><i class="fas fa-link me-1"></i>مدیریت انتساب‌ها</a></li>
                <li class="nav-item"><a class="nav-link" href="create_guaranty.php"><i class="fas fa-file-contract me-1"></i>مدیریت گارانتی</a></li>
                <li class="nav-item"><a class="nav-link" href="reports.php"><i class="fas fa-chart-bar me-1"></i>گزارشات</a></li>
                <li class="nav-item"><a class="nav-link" href="survey.php"><i class="fas fa-poll me-1"></i>نظر سنجی</a></li>
                <?php if ($is_admin): ?>
                <li class="nav-item"><a class="nav-link" href="system_logs.php"><i class="fas fa-clipboard-list me-1"></i>لاگ سیستم</a></li>
                <li class="nav-item"><a class="nav-link" href="users.php"><i class="fas fa-user-cog me-1"></i>مدیریت کاربران</a></li>
                <?php endif; ?>
            </ul>
            <div class="user-section">
                <div class="clock"><i class="fas fa-clock me-1"></i><span id="clockTime"><?php echo date('H:i:s'); ?></span></div>
                <div class="theme-switch" onclick="toggleTheme()"><i class="fas <?php echo isset($_COOKIE['theme']) && $_COOKIE['theme']==='dark' ? 'fa-sun' : 'fa-moon'; ?>"></i></div>
                <div class="dropdown">
                    <a class="user-dropdown dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user me-1"></i>
                        <?php echo $username; ?>
                        <?php if ($is_admin): ?><span class="badge bg-warning ms-1">ادمین</span><?php endif; ?>
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
<script>
function toggleTheme(){const body=document.body;const icon=document.querySelector('.theme-switch i');if(body.classList.contains('dark-mode')){body.classList.remove('dark-mode');if(icon) icon.classList.replace('fa-sun','fa-moon');document.cookie="theme=light; path=/; max-age=31536000";}else{body.classList.add('dark-mode');if(icon) icon.classList.replace('fa-moon','fa-sun');document.cookie="theme=dark; path=/; max-age=31536000";}}
setInterval(()=>{var el=document.getElementById('clockTime'); if(el){ el.textContent = new Date().toLocaleTimeString('fa-IR');}},1000);
</script>