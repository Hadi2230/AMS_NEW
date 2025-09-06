<?php
// فعال کردن نمایش خطاها (توسعه)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/config.php';

$user_role = $_SESSION['role'] ?? 'کاربر عادی';
$is_admin  = $user_role === 'ادمین';

// دریافت لیست نظرسنجی‌ها
$all_surveys = [];
try {
    $q = $pdo->query("SELECT id, title FROM surveys ORDER BY id DESC");
    $all_surveys = $q ? $q->fetchAll() : [];
} catch (Throwable $e) {}

$activeSurveyId = isset($_GET['survey_id']) && $_GET['survey_id'] !== '' ? (int)$_GET['survey_id'] : null;
if ($activeSurveyId === null && !empty($all_surveys)) {
    $activeSurveyId = (int)$all_surveys[0]['id'];
}
?>

<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظرسنجی - اعلا نیرو</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php if (file_exists('navbar.php')) include 'navbar.php'; ?>

<div class="container mt-4">
    <h4 class="mb-3">نظرسنجی</h4>

    <div class="row g-3">
        <!-- کارت 1: نظر سنجی -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">نظر سنجی</div>
                <div class="card-body">
                    <form method="get" class="mb-3">
                        <label class="form-label">انتخاب نظرسنجی</label>
                        <select name="survey_id" class="form-select" onchange="this.form.submit()">
                            <?php foreach ($all_surveys as $s): ?>
                                <option value="<?php echo (int)$s['id']; ?>" <?php echo ($activeSurveyId == (int)$s['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($s['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>

                    <form method="post" action="survey_answer.php">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="survey_id" value="<?php echo (int)($activeSurveyId ?? 0); ?>">
                        <label class="form-label">شماره مشتری (تلفن)</label>
                        <input type="text" name="customer_phone" class="form-control" placeholder="مثال: 0912xxxxxxx">
                        <div class="form-text mb-2">یا</div>
                        <label class="form-label">شماره دستگاه (مدل ژنراتور یا سریال موتور برق)</label>
                        <input type="text" name="device_code" class="form-control" placeholder="مثال: مدل ژنراتور یا سریال موتور برق">
                        <button class="btn btn-primary w-100 mt-3" name="start_survey" type="submit">شروع نظرسنجی</button>
                    </form>
                    <p class="text-muted mt-2 mb-0">اگر شناسه واردشده موجود نباشد، پیام خطا نمایش داده می‌شود.</p>
                </div>
            </div>
        </div>

        <!-- کارت 2: مشاهده نظرسنجی‌ها -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">مشاهده نظرسنجی‌ها</div>
                <div class="card-body">
                    <p class="mb-2">مشاهده ثبت‌های قبلی، چاپ و حذف/ویرایش.</p>
                    <a class="btn btn-outline-primary w-100" href="survey_list.php">ورود به لیست ثبت‌ها</a>
                </div>
            </div>
        </div>

        <!-- کارت 3: مدیریت نظرسنجی (فقط ادمین) -->
        <div class="col-md-4">
            <div class="card h-100 <?php echo $is_admin ? '' : 'opacity-50'; ?>">
                <div class="card-header">مدیریت نظرسنجی</div>
                <div class="card-body">
                    <p class="mb-2">افزودن سوال برای نظرسنجی انتخابی.</p>
                    <?php if ($is_admin): ?>
                        <form method="get" action="survey_admin.php">
                            <input type="hidden" name="survey_id" value="<?php echo (int)($activeSurveyId ?? 0); ?>">
                            <button class="btn btn-outline-secondary w-100" type="submit">افزودن/مدیریت سوالات</button>
                        </form>
                    <?php else: ?>
                        <p class="text-muted">فقط ادمین دسترسی دارد.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>