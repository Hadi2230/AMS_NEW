# ===============================================
# Aala Niroo AMS - راهنمای نصب و راه‌اندازی
# ===============================================

## 📋 **پیش‌نیازها**

قبل از نصب سیستم، اطمینان حاصل کنید که موارد زیر روی سرور شما نصب شده‌اند:

### **سرور وب**
- Apache 2.4+ یا Nginx 1.18+
- PHP 8.0+ با ماژول‌های زیر:
  - PDO MySQL
  - OpenSSL
  - Mbstring
  - JSON
  - cURL
  - GD یا Imagick
  - ZIP
  - XML

### **پایگاه داده**
- MySQL 8.0+ یا MariaDB 10.5+

### **ابزارهای دیگر**
- Composer 2.0+
- Git (برای دریافت کد)

## 🔧 **مراحل نصب**

### **1. دریافت کد پروژه**

```bash
# کلون کردن پروژه
git clone https://github.com/hadi2230/aala-niroo-ams.git
cd aala-niroo-ams

# یا دانلود و استخراج فایل ZIP
```

### **2. تنظیم مجوزها**

```bash
# تنظیم مجوزهای پوشه‌ها
chmod -R 755 storage/
chmod -R 755 public/uploads/
chmod -R 755 logs/

# تنظیم مالکیت (در صورت نیاز)
chown -R www-data:www-data storage/
chown -R www-data:www-data public/uploads/
chown -R www-data:www-data logs/
```

### **3. نصب وابستگی‌ها**

```bash
# نصب Composer dependencies
composer install --no-dev --optimize-autoloader

# یا برای محیط توسعه
composer install
```

### **4. تنظیم فایل محیط**

```bash
# کپی کردن فایل نمونه
cp .env.example .env

# ویرایش فایل .env
nano .env
```

**محتویات فایل .env:**

```env
# تنظیمات برنامه
APP_NAME="سامانه مدیریت اعلا نیرو"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://your-domain.com
APP_TIMEZONE=Asia/Tehran
APP_LOCALE=fa

# تنظیمات دیتابیس
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=aala_niroo
DB_USERNAME=your_username
DB_PASSWORD=your_password

# تنظیمات امنیت
JWT_SECRET=your-super-secret-jwt-key-here
SESSION_SECRET=your-super-secret-session-key-here
CSRF_SECRET=your-super-secret-csrf-key-here

# تنظیمات آپلود
UPLOAD_MAX_SIZE=5242880
UPLOAD_ALLOWED_TYPES=jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx

# تنظیمات لاگ
LOG_CHANNEL=file
LOG_LEVEL=info

# تنظیمات کش
CACHE_DRIVER=file
CACHE_TTL=3600

# تنظیمات پشتیبان‌گیری
BACKUP_ENABLED=true
BACKUP_RETENTION_DAYS=30
```

### **5. ایجاد پایگاه داده**

```sql
-- اتصال به MySQL
mysql -u root -p

-- ایجاد دیتابیس
CREATE DATABASE aala_niroo CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci;

-- ایجاد کاربر (اختیاری)
CREATE USER 'aala_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON aala_niroo.* TO 'aala_user'@'localhost';
FLUSH PRIVILEGES;

-- خروج
EXIT;
```

### **6. اجرای مهاجرت‌ها**

```bash
# اجرای مهاجرت‌های دیتابیس
php database/migrate.php

# یا به صورت دستی
php -r "
require_once 'bootstrap.php';
use App\Core\Application;
use App\Core\Database;
use App\Core\Logger;

\$database = new Database();
\$logger = new Logger();
\$app = new Application(\$database->getConnection(), \$logger);
echo 'Database initialized successfully!';
"
```

### **7. تنظیم وب سرور**

#### **Apache Configuration**

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /path/to/aala-niroo-ams/public
    
    <Directory /path/to/aala-niroo-ams/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/aala-niroo-error.log
    CustomLog ${APACHE_LOG_DIR}/aala-niroo-access.log combined
</VirtualHost>
```

#### **Nginx Configuration**

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/aala-niroo-ams/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### **8. تنظیم SSL (HTTPS)**

```bash
# نصب Certbot
sudo apt install certbot python3-certbot-apache

# دریافت گواهی SSL
sudo certbot --apache -d your-domain.com

# یا برای Nginx
sudo certbot --nginx -d your-domain.com
```

### **9. تنظیم Cron Jobs**

```bash
# ویرایش crontab
crontab -e

# اضافه کردن کارهای زمان‌بندی شده
# پشتیبان‌گیری روزانه
0 2 * * * /usr/bin/php /path/to/aala-niroo-ams/scripts/backup.php

# پاکسازی لاگ‌های قدیمی
0 3 * * 0 /usr/bin/php /path/to/aala-niroo-ams/scripts/cleanup.php

# به‌روزرسانی آمار
*/30 * * * * /usr/bin/php /path/to/aala-niroo-ams/scripts/update-stats.php
```

## 🔐 **امنیت**

### **تنظیمات امنیتی اضافی**

```bash
# تنظیم مجوزهای فایل‌های حساس
chmod 600 .env
chmod 600 storage/logs/*.log

# غیرفعال کردن نمایش خطاها در تولید
# در فایل .env
APP_DEBUG=false

# تنظیم فایروال
sudo ufw enable
sudo ufw allow 22
sudo ufw allow 80
sudo ufw allow 443
```

### **بررسی‌های امنیتی**

```bash
# بررسی مجوزهای فایل‌ها
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# بررسی فایل‌های حساس
ls -la .env
ls -la storage/logs/
```

## 🧪 **تست نصب**

### **1. تست اتصال دیتابیس**

```bash
php -r "
require_once 'bootstrap.php';
try {
    \$database = new App\Core\Database();
    echo 'Database connection: OK\n';
} catch (Exception \$e) {
    echo 'Database connection: FAILED - ' . \$e->getMessage() . '\n';
}
"
```

### **2. تست وب سرور**

```bash
# تست دسترسی به صفحه اصلی
curl -I http://your-domain.com

# تست دسترسی به API
curl -I http://your-domain.com/api/assets
```

### **3. تست عملکرد**

```bash
# اجرای تست‌ها
vendor/bin/phpunit

# یا تست‌های خاص
vendor/bin/phpunit --filter AssetTest
```

## 📊 **حساب‌های پیش‌فرض**

پس از نصب، حساب‌های زیر در دسترس خواهند بود:

- **مدیر سیستم:** admin / admin
- **کاربر عادی:** user / password

**⚠️ مهم:** حتماً رمزهای عبور پیش‌فرض را تغییر دهید!

## 🔧 **عیب‌یابی**

### **مشکلات رایج**

#### **خطای اتصال دیتابیس**
```bash
# بررسی تنظیمات دیتابیس
php -r "var_dump(extension_loaded('pdo_mysql'));"

# بررسی اتصال
mysql -u your_username -p -h your_host
```

#### **خطای مجوز فایل**
```bash
# تنظیم مجوزها
sudo chown -R www-data:www-data /path/to/aala-niroo-ams
sudo chmod -R 755 /path/to/aala-niroo-ams
```

#### **خطای URL Rewriting**
```bash
# بررسی ماژول mod_rewrite
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### **لاگ‌ها**

```bash
# مشاهده لاگ‌های برنامه
tail -f storage/logs/app.log

# مشاهده لاگ‌های خطا
tail -f storage/logs/error.log

# مشاهده لاگ‌های امنیت
tail -f storage/logs/security.log
```

## 📞 **پشتیبانی**

در صورت بروز مشکل:

1. **مستندات:** README.md
2. **Issues:** GitHub Issues
3. **ایمیل:** support@aala-niroo.com
4. **تلفن:** +98-XXX-XXXXXXX

## 🔄 **به‌روزرسانی**

```bash
# دریافت آخرین تغییرات
git pull origin main

# نصب وابستگی‌های جدید
composer install

# اجرای مهاجرت‌ها
php database/migrate.php

# پاکسازی کش
php scripts/clear-cache.php
```

---

**توسعه‌دهنده:** Hadi2230  
**نسخه:** 2.0.0  
**تاریخ آخرین به‌روزرسانی:** 2024