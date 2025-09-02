<?php
// چاپ کارت گارانتی بر اساس قالب PDF موجود در assets/documents
// نیازمندی پیشنهادی: composer require tecnickcom/tcpdf setasign/fpdi setasign/fpdi-tcpdf

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/config.php';

// دریافت شناسه انتساب
$assignmentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($assignmentId <= 0 && isset($_SESSION['last_assignment_id'])) {
    $assignmentId = (int)$_SESSION['last_assignment_id'];
}

if ($assignmentId <= 0) {
    http_response_code(400);
    echo '<div style="font-family:Tahoma;padding:20px">شناسه انتساب نامعتبر است.</div>';
    exit();
}

// واکشی اطلاعات لازم برای پر کردن کارت
$stmt = $pdo->prepare("SELECT aa.*, a.name AS asset_name, a.model AS asset_model, a.serial_number AS asset_serial,
                               c.full_name AS customer_name, c.phone AS customer_phone, c.address AS customer_address,
                               ad.*
                        FROM asset_assignments aa
                        JOIN assets a ON aa.asset_id = a.id
                        JOIN customers c ON aa.customer_id = c.id
                        LEFT JOIN assignment_details ad ON aa.id = ad.assignment_id
                        WHERE aa.id = ?");
$stmt->execute([$assignmentId]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    http_response_code(404);
    echo '<div style="font-family:Tahoma;padding:20px">رکورد انتساب پیدا نشد.</div>';
    exit();
}

// یافتن فایل قالب PDF
$templatePath = '';
$candidates = [
    __DIR__ . '/assets/documents/warranty_template.pdf',
    __DIR__ . '/assets/documents/template.pdf',
];
foreach ($candidates as $cand) {
    if (is_file($cand)) { $templatePath = $cand; break; }
}
if ($templatePath === '') {
    $glob = glob(__DIR__ . '/assets/documents/*.pdf');
    if (!empty($glob)) { $templatePath = $glob[0]; }
}

if ($templatePath === '' || !is_readable($templatePath)) {
    http_response_code(500);
    echo '<div style="font-family:Tahoma;padding:20px">قالب PDF یافت نشد. مسیر را بررسی کنید: assets/documents/*.pdf</div>';
    exit();
}

// بررسی وجود کتابخانه‌های لازم
$hasTcpdfFpdi = class_exists('setasign\\Fpdi\\Tcpdf\\Fpdi');
if (!$hasTcpdfFpdi) {
    // تلاش برای لود شدن از vendor/autoload اگر وجود دارد
    $autoload = __DIR__ . '/vendor/autoload.php';
    if (is_file($autoload)) {
        require_once $autoload;
        $hasTcpdfFpdi = class_exists('setasign\\Fpdi\\Tcpdf\\Fpdi');
    }
}

if (!$hasTcpdfFpdi) {
    // نمایش راهنمای نصب
    http_response_code(500);
    echo '<div style="font-family:Tahoma;padding:20px;direction:rtl;text-align:right">'
       . '<h3>پیش‌نیازها نصب نشده است</h3>'
       . '<p>برای چاپ کارت روی قالب PDF، این دستورات را در ریشه پروژه اجرا کنید:</p>'
       . '<pre style="background:#f7f7f7;padding:10px;border:1px solid #ddd">'
       . 'composer require tecnickcom/tcpdf setasign/fpdi setasign/fpdi-tcpdf'
       . '</pre>'
       . '<p>سپس دوباره این صفحه را باز کنید.</p>'
       . '</div>';
    exit();
}

// استفاده از FPDI + TCPDF برای پشتیبانی UTF-8 و راست‌به‌چپ
use setasign\Fpdi\Tcpdf\Fpdi;

// مقادیر جهت درج
$warrantySerial = (string)($data['warranty_serial'] ?? '');
$customerName   = (string)($data['customer_name'] ?? '');
$customerPhone  = (string)($data['customer_phone'] ?? '');
$customerAddr   = (string)($data['customer_address'] ?? '');
$assetNameModel = trim((string)($data['asset_name'] ?? '') . ' ' . (string)($data['asset_model'] ?? ''));
$assetSerial    = (string)($data['asset_serial'] ?? '');
$installationDt = (string)($data['installation_date'] ?? ($data['assignment_date'] ?? ''));
$warrantyStart  = (string)($data['warranty_start_date'] ?? '');
$warrantyEnd    = (string)($data['warranty_end_date'] ?? '');

// ایجاد PDF
$pdf = new Fpdi('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(false, 0);
$pdf->AddPage();

// بارگذاری صفحه قالب
$pageCount = $pdf->setSourceFile($templatePath);
$tplIdx = $pdf->importPage(1);
$pdf->useTemplate($tplIdx, 0, 0, 210, 297, true);

// تنظیمات قلم (فونت DejaVuSans برای پشتیبانی فارسی)
$pdf->SetFont('dejavusans', '', 11, '', true);
$pdf->setRTL(true);
$pdf->SetTextColor(0, 0, 0);

// نقشه مختصات (میلی‌متر) - این‌ها را بر اساس قالب خود دقیق تنظیم کنید
// نکته: شروع صفحه A4 از (0,0) گوشه بالا-چپ است؛ عرض 210mm، ارتفاع 297mm
$fields = [
    //  x,   y,  w,  h, value, align
    ['x' => 150, 'y' => 40,  'w' => 50, 'h' => 7,  'val' => $warrantySerial, 'align' => 'R'],
    ['x' => 25,  'y' => 70,  'w' => 80, 'h' => 7,  'val' => $customerName,   'align' => 'R'],
    ['x' => 25,  'y' => 78,  'w' => 80, 'h' => 7,  'val' => $customerPhone,  'align' => 'R'],
    ['x' => 25,  'y' => 86,  'w' => 160,'h' => 7,  'val' => $customerAddr,   'align' => 'R'],
    ['x' => 25,  'y' => 104, 'w' => 120,'h' => 7,  'val' => $assetNameModel, 'align' => 'R'],
    ['x' => 160, 'y' => 104, 'w' => 30, 'h' => 7,  'val' => $assetSerial,    'align' => 'R'],
    ['x' => 25,  'y' => 122, 'w' => 40, 'h' => 7,  'val' => $installationDt, 'align' => 'R'],
    ['x' => 90,  'y' => 122, 'w' => 40, 'h' => 7,  'val' => $warrantyStart,  'align' => 'R'],
    ['x' => 155, 'y' => 122, 'w' => 40, 'h' => 7,  'val' => $warrantyEnd,    'align' => 'R'],
];

foreach ($fields as $f) {
    $pdf->SetXY($f['x'], $f['y']);
    $pdf->MultiCell($f['w'], $f['h'], $f['val'], 0, $f['align'], false, 1, '', '', true, 0, false, true, $f['h'], 'M');
}

// خروجی
$fileName = 'warranty_' . ($warrantySerial !== '' ? $warrantySerial : $assignmentId) . '.pdf';
$pdf->Output($fileName, 'I');
exit();
<?php

