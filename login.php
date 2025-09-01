<?php
session_start();
include 'config.php'; // اتصال به پایگاه داده و $pdo

// تولید CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// شمارش تلاش‌ها و قفل موقت
if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;
if (!isset($_SESSION['lock_time'])) $_SESSION['lock_time'] = 0;

$error = '';
$success = '';
$locked = false;
$lock_duration = 60; // مدت قفل حساب به ثانیه

if ($_SESSION['login_attempts'] >= 5 && (time() - $_SESSION['lock_time'] < $lock_duration)) {
    $locked = true;
    $remaining = $lock_duration - (time() - $_SESSION['lock_time']);
    $error = "تعداد تلاش ورود بیش از حد مجاز است. لطفا $remaining ثانیه دیگر تلاش کنید.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$locked) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $error = "درخواست نامعتبر.";
    } elseif ($username === '' || $password === '') {
        $error = "لطفا نام کاربری و رمز عبور را وارد کنید.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['login_attempts'] = 0;
            $_SESSION['lock_time'] = 0;

            // ثبت آخرین ورود
            try {
                $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
            } catch (Throwable $e) {
                error_log("Login update error: " . $e->getMessage());
            }

            $success = "ورود موفقیت‌آمیز بود! در حال انتقال...";
            echo "<script>
                setTimeout(function(){ window.location.href='dashboard.php'; }, 1500);
            </script>";
        } else {
            $_SESSION['login_attempts']++;
            if ($_SESSION['login_attempts'] >= 5) {
                $_SESSION['lock_time'] = time();
                $locked = true;
                $error = "تعداد تلاش ورود بیش از حد مجاز است. لطفا $lock_duration ثانیه دیگر تلاش کنید.";
            } else {
                $error = "نام کاربری یا رمز عبور اشتباه است!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ورود به سامانه - اعلا نیرو</title>

<link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
:root {
    --primary-color:#2c3e50;
    --secondary-color:#3498db;
    --accent-color:#e74c3c;
    --success-color:#28a745;
    --gradient-start:#2c3e50;
    --gradient-end:#3498db;
}
body {
    margin:0; padding:0;
    font-family:'Vazirmatn',sans-serif;
    background: linear-gradient(135deg,var(--gradient-start),var(--gradient-end));
    height:100vh; display:flex; justify-content:center; align-items:center; position:relative;
    transition: background 0.3s ease;
}
body.dark-mode { background:#1e1e2f; }
body::before {
    content:""; position:absolute; width:100%; height:100%;
    background-image: radial-gradient(circle at 20% 80%, rgba(255,255,255,0.1) 0%, transparent 20%),
                      radial-gradient(circle at 80% 20%, rgba(255,255,255,0.1) 0%, transparent 20%),
                      radial-gradient(circle at 40% 40%, rgba(255,255,255,0.05) 0%, transparent 20%);
    z-index:0;
}
.login-container {
    width:95%; max-width:480px;
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(12px);
    border-radius:20px; padding:30px;
    box-shadow:0 15px 35px rgba(0,0,0,0.25);
    position:relative; z-index:1;
    animation: fadeIn 0.5s ease-out;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
body.dark-mode .login-container { background: rgba(20,20,35,0.95); color:#eee; }
.login-container:hover { transform:translateY(-5px); box-shadow:0 20px 40px rgba(0,0,0,0.4); }
.logo { text-align:center; margin-bottom:20px; }
.logo i { font-size:3rem; color: var(--primary-color); background:linear-gradient(135deg,var(--gradient-start),var(--gradient-end)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; margin-bottom:10px; }
.logo h2 { margin:0; font-size:1.6rem; font-weight:700; }
.logo p { margin-top:5px; font-size:0.9rem; color:#666; }
.input-icon { position:relative; }
.input-icon i { position:absolute; left:15px; top:50%; transform:translateY(-50%); color:#6c757d; z-index:5; }
.input-icon .form-control { padding-left:45px; border-radius:10px; border:2px solid #e1e5eb; padding:16px 20px; transition:0.3s; }
.input-icon .form-control:focus { border-color: var(--secondary-color); box-shadow:0 0 0 0.25rem rgba(52,152,219,0.25); }
.btn-login { background: linear-gradient(135deg,var(--gradient-start),var(--gradient-end)); border:none; color:#fff; padding:12px; border-radius:10px; width:100%; font-weight:600; margin-top:10px; position:relative; overflow:hidden; transition: all 0.3s; }
.btn-login:disabled { opacity:0.6; cursor:not-allowed; }
.btn-login .spinner { position:absolute; left:50%; top:50%; transform:translate(-50%, -50%); display:none; }
.btn-login.loading .spinner { display:inline-block; }
.alert { border-radius:10px; padding:12px 16px; margin-bottom:20px; }
.alert-success { background-color: rgba(40,167,69,0.1); color: var(--success-color); border:1px solid var(--success-color);}
.alert-danger { background-color: rgba(231,76,60,0.1); color: var(--accent-color); border:1px solid var(--accent-color);}
.login-container.shake { animation: shake 0.5s; }
@keyframes fadeIn { from {opacity:0; transform:translateY(20px);} to {opacity:1; transform:translateY(0);} }
@keyframes shake { 0%,100%{transform:translateX(0);} 20%,60%{transform:translateX(-10px);} 40%,80%{transform:translateX(10px);} }
.theme-switch{position:absolute;top:20px;left:20px;cursor:pointer; z-index:10; color:white; font-size:1.3rem;}
</style>
</head>
<body>

<div class="theme-switch" onclick="toggleTheme()"><i class="fas fa-moon"></i></div>

<div class="login-container <?= $error ? 'shake' : '' ?>">
    <div class="logo">
        <i class="fas fa-bolt"></i>
        <h2>سیستم مدیریت اعلا نیرو</h2>
        <p>لطفا برای ادامه وارد شوید</p>
    </div>

    <!-- پیام‌ها -->
    <?php if($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST" id="loginForm" novalidate>
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <div class="input-icon mb-3">
            <i class="fas fa-user"></i>
            <input type="text" name="username" class="form-control" placeholder="نام کاربری" required <?= $locked ? 'disabled' : '' ?>>
        </div>
        <div class="input-icon mb-3">
            <i class="fas fa-lock"></i>
            <input type="password" name="password" class="form-control" placeholder="رمز عبور" required <?= $locked ? 'disabled' : '' ?>>
        </div>
        <button type="submit" class="btn btn-login" <?= $locked ? 'disabled' : '' ?>>
            <span class="btn-text"><i class="fas fa-sign-in-alt me-2"></i>ورود به سیستم</span>
            <span class="spinner"><i class="fas fa-spinner fa-spin"></i></span>
        </button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleTheme(){
    const body=document.body;
    const icon=document.querySelector('.theme-switch i');
    if(body.classList.contains('dark-mode')){
        body.classList.remove('dark-mode');
        icon.classList.replace('fa-sun','fa-moon');
        document.cookie="theme=light; path=/; max-age=31536000";
    }else{
        body.classList.add('dark-mode');
        icon.classList.replace('fa-moon','fa-sun');
        document.cookie="theme=dark; path=/; max-age=31536000";
    }
}

document.addEventListener('DOMContentLoaded',()=>{
    const savedTheme=document.cookie.match('(^|;)\\s*theme\\s*=\\s*([^;]+)')?.pop()||'';
    const icon=document.querySelector('.theme-switch i');
    if(savedTheme==='dark'){
        document.body.classList.add('dark-mode');
        icon.classList.replace('fa-moon','fa-sun');
    }

    const container=document.querySelector('.login-container');
    if(container.classList.contains('shake')){
        container.addEventListener('animationend',()=>{ container.classList.remove('shake'); });
    }

    // لودینگ هنگام submit
    const form=document.getElementById('loginForm');
    form.addEventListener('submit', function(){
        const btn=form.querySelector('.btn-login');
        btn.classList.add('loading');
    });
});
</script>
</body>
</html>
