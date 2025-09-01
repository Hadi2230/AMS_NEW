<?php
// فعال کردن نمایش خطاها
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

session_start();

// بررسی لاگین
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// اتصال به دیتابیس
try {
    $host = 'localhost:3307';
    $dbname = 'aala_niroo';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("خطا در اتصال به دیتابیس: " . $e->getMessage());
}

$user_id = $_SESSION['user_id'];
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'ادمین';

$error = '';
$success = '';

// ثبت سوال جدید توسط ادمین
if ($is_admin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_question'])) {
    $question_text = trim($_POST['question_text']);
    $answer_type = $_POST['answer_type'];
    
    if (empty($question_text)) {
        $error = 'متن سوال نمی‌تواند خالی باشد.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO survey_questions (question_text, answer_type, created_by_admin) VALUES (?, ?, ?)");
            if ($stmt->execute([$question_text, $answer_type, $user_id])) {
                $success = 'سوال جدید با موفقیت اضافه شد.';
            }
        } catch (PDOException $e) {
            $error = 'خطا در اضافه کردن سوال: ' . $e->getMessage();
        }
    }
}

// جستجوی مشتری یا دستگاه
$search_results = [];
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])) {
    $search_term = trim($_GET['search']);
    
    if (!empty($search_term)) {
        try {
            // جستجوی مشتریان
            $stmt = $pdo->prepare("SELECT id, name, phone, address FROM customers 
                                  WHERE name LIKE ? OR phone LIKE ? OR address LIKE ?");
            $stmt->execute(["%$search_term%", "%$search_term%", "%$search_term%"]);
            $customers = $stmt->fetchAll();
            
            // جستجوی دستگاه‌ها
            $stmt = $pdo->prepare("SELECT id, name, serial_number, model FROM assets 
                                  WHERE name LIKE ? OR serial_number LIKE ? OR model LIKE ?");
            $stmt->execute(["%$search_term%", "%$search_term%", "%$search_term%"]);
            $assets = $stmt->fetchAll();
            
            $search_results = [
                'customers' => $customers,
                'assets' => $assets
            ];
        } catch (PDOException $e) {
            $error = 'خطا در جستجو: ' . $e->getMessage();
        }
    }
}

// دریافت همه سوالات
try {
    $questions = $pdo->query("SELECT * FROM survey_questions ORDER BY created_at DESC")->fetchAll();
} catch (PDOException $e) {
    $error = 'خطا در دریافت سوالات: ' . $e->getMessage();
    $questions = [];
}
?>

<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظرسنجی - اعلا نیرو</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <style>
        body {
            font-family: Vazirmatn, sans-serif;
            background-color: #f8f9fa;
            padding-top: 80px;
        }
        .card {
            margin-bottom: 20px;
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .card-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-start;
        }
        .star-rating input {
            display: none;
        }
        .star-rating label {
            font-size: 1.5rem;
            color: #ddd;
            cursor: pointer;
            transition: color 0.2s;
            margin: 0 2px;
        }
        .star-rating input:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #ffc107;
        }
    </style>
</head>
<body class="<?php echo isset($_COOKIE['theme']) && $_COOKIE['theme']==='dark' ? 'dark-mode' : ''; ?>">
    <?php 
    // بررسی وجود فایل navbar
    if (file_exists('navbar.php')) {
        include 'navbar.php'; 
    } else {
        echo '<nav class="navbar navbar-dark bg-primary"><div class="container"><a class="navbar-brand" href="#">اعلا نیرو</a></div></nav>';
    }
    ?>
    
    <div class="container mt-4">
        <h2 class="text-center mb-4">نظرسنجی پیشرفته</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <!-- فرم جستجو -->
        <div class="card mb-4">
            <div class="card-header">جستجوی مشتری یا دستگاه</div>
            <div class="card-body">
                <form method="GET">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="نام مشتری، شماره تماس یا شماره سریال دستگاه" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> جستجو</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- نتایج جستجو -->
        <?php if (!empty($search_results)): ?>
            <div class="card mb-4">
                <div class="card-header">نتایج جستجو</div>
                <div class="card-body">
                    <?php if (!empty($search_results['customers'])): ?>
                        <h5>مشتریان:</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>نام</th>
                                        <th>تلفن</th>
                                        <th>آدرس</th>
                                        <th>عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($search_results['customers'] as $customer): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($customer['name']); ?></td>
                                            <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                                            <td><?php echo htmlspecialchars($customer['address']); ?></td>
                                            <td>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="customer_id" value="<?php echo $customer['id']; ?>">
                                                    <button type="submit" name="start_survey" class="btn btn-success btn-sm">شروع نظرسنجی</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($search_results['assets'])): ?>
                        <h5>دستگاه‌ها:</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>نام دستگاه</th>
                                        <th>شماره سریال</th>
                                        <th>مدل</th>
                                        <th>عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($search_results['assets'] as $asset): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($asset['name']); ?></td>
                                            <td><?php echo htmlspecialchars($asset['serial_number']); ?></td>
                                            <td><?php echo htmlspecialchars($asset['model']); ?></td>
                                            <td>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="device_id" value="<?php echo $asset['id']; ?>">
                                                    <button type="submit" name="start_survey" class="btn btn-success btn-sm">شروع نظرسنجی</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (empty($search_results['customers']) && empty($search_results['assets'])): ?>
                        <p class="text-center text-muted">هیچ نتیجه‌ای یافت نشد.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- فرم اضافه کردن سوال (فقط برای ادمین) -->
        <?php if ($is_admin): ?>
            <div class="card mb-4">
                <div class="card-header">اضافه کردن سوال جدید</div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">متن سوال</label>
                            <input type="text" name="question_text" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">نوع پاسخ</label>
                            <select name="answer_type" class="form-select" required>
                                <option value="boolean">بله / خیر</option>
                                <option value="rating">امتیازی (1 تا 5)</option>
                                <option value="text">تشریحی</option>
                            </select>
                        </div>
                        <button type="submit" name="add_question" class="btn btn-primary"><i class="fas fa-plus"></i> افزودن سوال</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- نمایش سوالات موجود -->
        <div class="card mb-4">
            <div class="card-header">سوالات موجود</div>
            <div class="card-body">
                <?php if (!empty($questions)): ?>
                    <div class="list-group">
                        <?php foreach ($questions as $index => $question): ?>
                            <div class="list-group-item">
                                <h6>سوال <?php echo $index + 1; ?>:</h6>
                                <p><?php echo htmlspecialchars($question['question_text']); ?></p>
                                <span class="badge bg-info">
                                    <?php
                                    if ($question['answer_type'] == 'boolean') {
                                        echo 'بله/خیر';
                                    } elseif ($question['answer_type'] == 'rating') {
                                        echo 'امتیازی';
                                    } else {
                                        echo 'تشریحی';
                                    }
                                    ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-center text-muted">هیچ سوالی وجود ندارد.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>