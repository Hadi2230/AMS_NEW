<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'config.php';

// دریافت اطلاعات دستگاه‌ها از دیتابیس
$assets = [];
try {
    $stmt = $pdo->query("SELECT id, name, serial_number, model FROM assets");
    $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("خطا در دریافت اطلاعات دستگاه‌ها: " . $e->getMessage());
}

// دریافت اطلاعات مشتریان از دیتابیس
$customers = [];
try {
    $stmt = $pdo->query("SELECT id, customer_type, full_name, company, address FROM customers");
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("خطا در دریافت اطلاعات مشتریان: " . $e->getMessage());
}

// پردازش فرم هنگام ارسال
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $issue_date = $_POST['issue_date'] ?? '';
    $asset_id = $_POST['asset_id'] ?? '';
    $coupler_company = $_POST['coupler_company'] ?? '';
    $customer_id = $_POST['customer_id'] ?? '';
    $alternator_model = $_POST['alternator_model'] ?? '';
    $alternator_serial = $_POST['alternator_serial'] ?? '';

    if (empty($issue_date) || empty($asset_id) || empty($customer_id) || empty($coupler_company)) {
        $error = 'لطفاً همه فیلدهای الزامی را تکمیل کنید.';
    } else {
        // دریافت اطلاعات دستگاه انتخاب شده
        $stmt = $pdo->prepare("SELECT * FROM assets WHERE id = ?");
        $stmt->execute([$asset_id]);
        $asset = $stmt->fetch(PDO::FETCH_ASSOC);

        // دریافت اطلاعات مشتری انتخاب شده
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
        $stmt->execute([$customer_id]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$asset) {
            $error = 'دستگاه انتخاب شده یافت نشد.';
        } elseif (!$customer) {
            $error = 'مشتری انتخاب شده یافت نشد.';
        } else {
            // تولید شماره کارت گارانتی
            $guaranty_number = 'GRT-' . date('Ymd') . '-' . rand(1000, 9999);

            // ذخیره اطلاعات گارانتی در دیتابیس
            try {
                $pdo->exec("CREATE TABLE IF NOT EXISTS guaranty_cards (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    issue_date DATE NOT NULL,
                    asset_id INT NOT NULL,
                    coupler_company VARCHAR(255) NOT NULL,
                    customer_id INT NOT NULL,
                    guaranty_number VARCHAR(50) NOT NULL UNIQUE,
                    alternator_model VARCHAR(255),
                    alternator_serial VARCHAR(255),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
                    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;");

                $stmt = $pdo->prepare("INSERT INTO guaranty_cards (issue_date, asset_id, coupler_company, customer_id, guaranty_number, alternator_model, alternator_serial) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$issue_date, $asset_id, $coupler_company, $customer_id, $guaranty_number, $alternator_model, $alternator_serial]);

                $success = "کارت گارانتی با موفقیت صادر شد. شماره کارت: " . $guaranty_number;
            } catch (PDOException $e) {
                $error = "خطا در ذخیره اطلاعات گارانتی: " . $e->getMessage();
            }
        }
    }
}

// تابع کمکی برای خروجی ایمن
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// مسیر PDF ثابت
$pdfTemplatePath = __DIR__ . '/assets/documents/Guaranty Certificate.pdf';
?>

<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>صدور کارت گارانتی - سامانه مدیریت اعلا نیرو</title>
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
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .form-label {
            font-weight: bold;
        }
        .guaranty-preview {
            background-color: #fff;
            border: 2px solid #ddd;
            padding: 20px;
            margin-top: 20px;
            display: none;
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo h2 {
            color: #2c3e50;
            font-weight: bold;
        }
        .preview-header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="<?php echo isset($_COOKIE['theme']) && $_COOKIE['theme']==='dark' ? 'dark-mode' : ''; ?>">
<?php include 'navbar.php'; ?>

<div class="container mt-5">
    <div class="logo">
        <h2>صدور کارت گارانتی</h2>
        <p class="text-muted">فرم ثبت اطلاعات کارت گارانتی دستگاه</p>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo h($success); ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo h($error); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-file-contract me-2"></i>اطلاعات کارت گارانتی</h5>
        </div>
        <div class="card-body">
            <form method="POST" id="guarantyForm">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="issue_date" class="form-label">تاریخ صدور</label>
                        <input type="date" class="form-control" id="issue_date" name="issue_date" required value="<?php echo isset($_POST['issue_date'])?h($_POST['issue_date']):''; ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="asset_id" class="form-label">دستگاه</label>
                        <select class="form-select" id="asset_id" name="asset_id" required>
                            <option value="">انتخاب دستگاه</option>
                            <?php foreach ($assets as $asset): ?>
                                <option value="<?php echo $asset['id']; ?>" 
                                        data-serial="<?php echo h($asset['serial_number']); ?>" 
                                        data-model="<?php echo h($asset['model']); ?>">
                                    <?php echo h($asset['name'] . ' - ' . $asset['serial_number']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="coupler_company" class="form-label">شرکت کوپل کننده</label>
                        <input type="text" class="form-control" id="coupler_company" name="coupler_company" required value="<?php echo isset($_POST['coupler_company'])?h($_POST['coupler_company']):''; ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="customer_id" class="form-label">خریدار</label>
                        <select class="form-select" id="customer_id" name="customer_id" required>
                            <option value="">انتخاب خریدار</option>
                            <?php foreach ($customers as $customer): ?>
                                <?php
                                    $displayName = $customer['customer_type'] === 'حقوقی' ? $customer['company'] : $customer['full_name'];
                                    if (trim($displayName) === '') $displayName = '(بدون نام)';
                                ?>
                                <option value="<?php echo $customer['id']; ?>" data-address="<?php echo h($customer['address']); ?>">
                                    <?php echo h($displayName); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">شماره سریال پکیج موتور</label>
                        <input type="text" class="form-control" id="package_serial" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">برند موتور</label>
                        <input type="text" class="form-control" id="motor_brand" readonly>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">شماره سریال موتور</label>
                        <input type="text" class="form-control" id="motor_serial" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">مدل آلترناتور</label>
                        <input type="text" class="form-control" id="alternator_model" name="alternator_model" required value="<?php echo isset($_POST['alternator_model'])?h($_POST['alternator_model']):''; ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">شماره سریال آلترناتور</label>
                        <input type="text" class="form-control" id="alternator_serial" name="alternator_serial" required value="<?php echo isset($_POST['alternator_serial'])?h($_POST['alternator_serial']):''; ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">محل نصب</label>
                        <input type="text" class="form-control" id="installation_place" readonly>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-check-circle me-2"></i>صدور کارت گارانتی
                        </button>
                        <button type="button" class="btn btn-info btn-lg" id="previewBtn">
                            <i class="fas fa-eye me-2"></i>پیش نمایش
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- پیش نمایش کارت گارانتی -->
    <div class="guaranty-preview card" id="guarantyPreview">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0"><i class="fas fa-file-pdf me-2"></i>پیش نمایش کارت گارانتی</h5>
        </div>
        <div class="card-body">
            <div class="preview-header text-center">
                <h3>اعلا نیرو (سهامی خاص)</h3>
                <h4>کارت گارانتی</h4>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6"><strong>تاریخ صدور:</strong> <span id="preview_issue_date"></span></div>
                <div class="col-md-6"><strong>شماره کارت:</strong> <span id="preview_guaranty_number"></span></div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6"><strong>شماره سریال پکیج موتور:</strong> <span id="preview_package_serial"></span></div>
                <div class="col-md-6"><strong>مشخصات موتور:</strong> <span id="preview_motor_brand"></span></div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6"><strong>شماره سریال موتور:</strong> <span id="preview_motor_serial"></span></div>
                <div class="col-md-6"><strong>مشخصات آلترناتور:</strong> <span id="preview_alternator_model"></span></div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6"><strong>شماره سریال آلترناتور:</strong> <span id="preview_alternator_serial"></span></div>
                <div class="col-md-6"><strong>نام خریدار:</strong> <span id="preview_customer_name"></span></div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6"><strong>محل نصب:</strong> <span id="preview_installation_place"></span></div>
                <div class="col-md-6"><strong>شرکت کوپل کننده:</strong> <span id="preview_coupler_company"></span></div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <p class="text-center border-top pt-3">
                        تاریخ اتمام گارانتی: 18 ماه از زمان تحویل فیزیکی، 12 ماه از زمان نصب و راه اندازی یا 1200 ساعت کارکرد (هرکدام زودتر به پایان برسد).
                    </p>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-12 text-center">
                    <p>021-88837242 | info@aalaniroo.com | www.aalaniroo.com</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // پر کردن خودکار فیلدها بر اساس انتخاب دستگاه
    document.getElementById('asset_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            document.getElementById('package_serial').value = selectedOption.getAttribute('data-serial');
            document.getElementById('motor_serial').value = selectedOption.getAttribute('data-serial');
            const text = selectedOption.text;
            document.getElementById('motor_brand').value = text.split(' - ')[0];
        } else {
            document.getElementById('package_serial').value = '';
            document.getElementById('motor_serial').value = '';
            document.getElementById('motor_brand').value = '';
        }
    });

    // پر کردن خودکار محل نصب بر اساس انتخاب مشتری
    document.getElementById('customer_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            document.getElementById('installation_place').value = selectedOption.getAttribute('data-address');
        } else {
            document.getElementById('installation_place').value = '';
        }
    });

    // پیش نمایش
    document.getElementById('previewBtn').addEventListener('click', function() {
        document.getElementById('preview_issue_date').textContent = document.getElementById('issue_date').value;
        document.getElementById('preview_package_serial').textContent = document.getElementById('package_serial').value;
        document.getElementById('preview_motor_brand').textContent = document.getElementById('motor_brand').value;
        document.getElementById('preview_motor_serial').textContent = document.getElementById('motor_serial').value;
        document.getElementById('preview_alternator_model').textContent = document.getElementById('alternator_model').value;
        document.getElementById('preview_alternator_serial').textContent = document.getElementById('alternator_serial').value;
        const customerSelect = document.getElementById('customer_id');
        document.getElementById('preview_customer_name').textContent = customerSelect.options[customerSelect.selectedIndex].text;
        document.getElementById('preview_installation_place').textContent = document.getElementById('installation_place').value;
        document.getElementById('preview_coupler_company').textContent = document.getElementById('coupler_company').value;
        document.getElementById('preview_guaranty_number').textContent = 'GRT-' + new Date().toISOString().slice(0,10).replace(/-/g,"") + '-' + Math.floor(1000 + Math.random() * 9000);
        document.getElementById('guarantyPreview').style.display = 'block';
    });
});
</script>
</body>
</html>
