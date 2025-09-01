<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فهرست دارایی‌ها (مدرن)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php @include __DIR__ . '/../../navbar.php'; ?>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="m-0">دارایی‌ها</h2>
        <a href="/assets.php" class="btn btn-outline-secondary">رفتن به نسخه قدیمی</a>
    </div>
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-striped table-hover m-0">
                <thead class="table-light">
                    <tr>
                        <th>نام</th>
                        <th>نوع</th>
                        <th>سریال</th>
                        <th>وضعیت</th>
                        <th>مدل</th>
                        <th>برند</th>
                        <th>تاریخ</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach (($assets ?? []) as $asset): ?>
                    <tr>
                        <td><?= htmlspecialchars($asset['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($asset['type_display_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($asset['serial_number'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($asset['status'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($asset['model'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($asset['brand'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($asset['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

