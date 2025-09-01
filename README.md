# ===============================================
# Aala Niroo Asset Management System
# ===============================================

## 📋 **توضیحات پروژه**

سامانه مدیریت دارایی‌ها و مشتریان شرکت اعلا نیرو - نسخه حرفه‌ای و مدرن

## 🚀 **ویژگی‌های اصلی**

- ✅ مدیریت کامل دارایی‌ها (ژنراتور، موتور برق، اقلام مصرفی)
- ✅ مدیریت مشتریان (حقیقی و حقوقی)
- ✅ سیستم انتساب دارایی به مشتریان
- ✅ مدیریت گارانتی و ضمانت‌نامه‌ها
- ✅ سیستم نظرسنجی و ارزیابی
- ✅ گزارشات پیشرفته و آمار
- ✅ مدیریت کاربران و دسترسی‌ها
- ✅ لاگ سیستم و نظارت
- ✅ پشتیبان‌گیری خودکار

## 🛠 **تکنولوژی‌های استفاده شده**

- **Backend:** PHP 8.0+, MySQL 8.0+
- **Frontend:** Bootstrap 5, Font Awesome 6, Vazirmatn Font
- **Security:** JWT, CSRF Protection, Password Hashing
- **Logging:** Monolog
- **Email:** PHPMailer
- **PDF:** DOMPDF
- **Testing:** PHPUnit

## 📁 **ساختار پروژه**

```
aala-niroo-ams/
├── app/                    # کدهای اصلی برنامه
│   ├── Controllers/        # کنترلرها
│   ├── Models/            # مدل‌های دیتابیس
│   ├── Services/          # سرویس‌های تجاری
│   ├── Middleware/        # میدلورها
│   └── Helpers/           # توابع کمکی
├── config/                # فایل‌های تنظیمات
├── database/              # مهاجرت‌ها و seeders
├── public/                # فایل‌های عمومی
│   ├── css/              # استایل‌ها
│   ├── js/               # جاوااسکریپت
│   └── images/           # تصاویر
├── resources/             # منابع
│   ├── views/            # قالب‌ها
│   └── lang/             # فایل‌های زبان
├── storage/               # فایل‌های ذخیره‌سازی
│   ├── logs/             # لاگ‌ها
│   ├── uploads/          # فایل‌های آپلود شده
│   └── cache/            # کش
└── tests/                 # تست‌ها
```

## 🔧 **نصب و راه‌اندازی**

### پیش‌نیازها
- PHP 8.0 یا بالاتر
- MySQL 8.0 یا بالاتر
- Composer
- Apache/Nginx

### مراحل نصب

1. **کلون کردن پروژه**
```bash
git clone https://github.com/hadi2230/aala-niroo-ams.git
cd aala-niroo-ams
```

2. **نصب وابستگی‌ها**
```bash
composer install
```

3. **تنظیم فایل محیط**
```bash
cp .env.example .env
# ویرایش فایل .env با تنظیمات دیتابیس
```

4. **ایجاد دیتابیس**
```bash
mysql -u root -p
CREATE DATABASE aala_niroo CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci;
```

5. **اجرای مهاجرت‌ها**
```bash
php database/migrate.php
```

6. **تنظیم مجوزها**
```bash
chmod -R 755 storage/
chmod -R 755 public/uploads/
```

7. **تنظیم وب سرور**
- Document Root را به پوشه `public/` تغییر دهید
- URL Rewriting را فعال کنید

## 👤 **حساب‌های پیش‌فرض**

- **مدیر سیستم:** admin / admin
- **کاربر عادی:** user / password

## 🔒 **امنیت**

- تمام ورودی‌ها اعتبارسنجی می‌شوند
- از Prepared Statements استفاده می‌شود
- CSRF Protection فعال است
- رمزهای عبور با bcrypt هش می‌شوند
- Session Management امن
- Rate Limiting برای ورود

## 📊 **گزارشات**

- گزارش دارایی‌ها
- گزارش مشتریان
- گزارش انتساب‌ها
- گزارش گارانتی‌ها
- آمار کلی سیستم
- گزارش فعالیت کاربران

## 🧪 **تست**

```bash
# اجرای تمام تست‌ها
vendor/bin/phpunit

# اجرای تست‌های خاص
vendor/bin/phpunit --filter AssetTest
```

## 📝 **لاگ‌ها**

لاگ‌های سیستم در پوشه `storage/logs/` ذخیره می‌شوند:
- `app.log` - لاگ‌های عمومی
- `error.log` - خطاها
- `security.log` - رویدادهای امنیتی
- `database.log` - عملیات دیتابیس

## 🔄 **پشتیبان‌گیری**

سیستم پشتیبان‌گیری خودکار دارد:
- پشتیبان‌گیری روزانه دیتابیس
- پشتیبان‌گیری هفتگی فایل‌ها
- نگهداری 30 روز پشتیبان‌ها

## 🤝 **مشارکت**

برای مشارکت در پروژه:
1. Fork کنید
2. Branch جدید ایجاد کنید
3. تغییرات را commit کنید
4. Pull Request ارسال کنید

## 📄 **لایسنس**

این پروژه تحت لایسنس MIT منتشر شده است.

## 📞 **پشتیبانی**

برای پشتیبانی و گزارش مشکلات:
- Email: support@aala-niroo.com
- GitHub Issues: [اینجا](https://github.com/hadi2230/aala-niroo-ams/issues)

---

**توسعه‌دهنده:** Hadi2230  
**نسخه:** 2.0.0  
**تاریخ آخرین به‌روزرسانی:** 2024