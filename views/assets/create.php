<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ایجاد دارایی جدید</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php @include __DIR__ . '/../../navbar.php'; ?>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="m-0">ایجاد دارایی جدید</h4>
        <a class="btn btn-outline-secondary" href="/assets">بازگشت</a>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <form method="post" action="/assets/store" enctype="multipart/form-data">
                <?= function_exists('csrf_field') ? csrf_field() : '' ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">نام</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">نوع</label>
                        <select name="type_id" class="form-select" required>
                            <option value="">انتخاب کنید</option>
                            <?php foreach (($types ?? []) as $t): ?>
                                <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['display_name'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">سریال</label>
                        <input type="text" name="serial_number" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">وضعیت</label>
                        <select name="status" class="form-select">
                            <option value="فعال">فعال</option>
                            <option value="غیرفعال">غیرفعال</option>
                            <option value="در حال تعمیر">در حال تعمیر</option>
                            <option value="آماده بهره‌برداری">آماده بهره‌برداری</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">برند</label>
                        <input type="text" name="brand" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">مدل</label>
                        <input type="text" name="model" class="form-control">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">تصویر دستگاه (اختیاری)</label>
                        <input type="file" name="device_image" class="form-control" accept="image/*,application/pdf">
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">ثبت</button>
                    <a class="btn btn-light" href="/assets">لغو</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

