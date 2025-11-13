# Artist Portfolio - PHP + Tailwind

**A minimalist portfolio website for artists with WhatsApp integration**

Built in Kornwestheim | Developed by Cüneyt Kaya — https://kayacuneyt.com

---

## 🚀 Features

- ✅ Responsive gallery with lazy loading
- ✅ Admin dashboard with image management
- ✅ WhatsApp inquiry form integration
- ✅ Image processing (resize, thumbnails, WebP)
- ✅ EXIF stripping for privacy
- ✅ Secure authentication & CSRF protection
- ✅ Settings panel for configuration
- ✅ Hostinger-optimized

---

## 📋 Requirements

- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- GD or Imagick extension
- Apache with mod_rewrite

---

## 🔧 Installation

### 1. Upload Files
Upload all files to your Hostinger public_html directory via FTP/SFTP.

### 2. Create Database
```sql
-- In Hostinger cPanel > MySQL Databases
-- Create a new database and user
-- Import schema.sql file
```

### 3. Configure Environment Variables
1. Duplicate `.env.example` and rename it to `.env`.
2. Fill in your database, SMTP, and site details:

```
APP_ENV=production
SITE_URL=https://arossiartwork.com
SITE_NAME="ARossi Artwork"

DB_HOST=localhost
DB_PORT=3306
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASS=strong_password

SMTP_HOST=smtp.hostinger.com
SMTP_USERNAME=artist@arossiartwork.com
SMTP_PASSWORD=app_password
```

> The app reads everything from `.env` at runtime, so you no longer have to edit `includes/config.php` when deploying to Hostinger.

### 4. Set Folder Permissions
```bash
chmod 755 uploads/
chmod 755 uploads/artworks/
chmod 755 uploads/thumbnails/
chmod 755 uploads/webp/
chmod 644 *.php
```

### 5. Check PHP Settings
In Hostinger, edit `php.ini` or `.user.ini`:
```ini
upload_max_filesize = 10M
post_max_size = 12M
memory_limit = 128M
max_execution_time = 60
```

### 6. Create Admin Account
Visit `/admin/setup.php` (one-time setup)
- Username: admin
- Password: (set your password)
- **Delete setup.php after first run!**

---

## 🧪 Testing

### Test Admin Login
1. Go to `/admin/login.php`
2. Login with credentials
3. Upload a test image
4. Check gallery at `/index.php`

### Test WhatsApp Form
1. Go to `/index.php`
2. Click "Request Artwork"
3. Fill form and submit
4. Should redirect to WhatsApp with prefilled message
5. Check admin dashboard for saved inquiry

### Test Image Processing
1. Upload images > 2048px width
2. Check if resized properly
3. Verify thumbnail generation (600px)
4. Verify WebP conversion
5. Check EXIF data stripped

---

## 📱 Hostinger-Specific Notes

### File Manager
- Use cPanel File Manager to upload files
- Set permissions via right-click > Change Permissions

### Database
- Use phpMyAdmin to import SQL
- Database name format: `u123456789_portfolio`

### PHP Version
- Set PHP 8.1+ in cPanel > Select PHP Version
- Enable extensions: mysqli, gd, mbstring

### .htaccess
Already configured for:
- Clean URLs
- Upload folder protection
- Admin area security

### Email (PHPMailer)
- Use Hostinger SMTP settings
- Host: smtp.hostinger.com
- Port: 587
- Username: your@domain.com

---

## 🎨 Customization

### Change Colors
Edit `assets/css/style.css` (Tailwind classes)

### Logos
Admin Panel → Settings → Brand Logos bölümünden header, hero ve footer için üç ayrı logo yükleyebilirsin. PNG/JPG/WebP dosyaları `uploads/site/` klasörüne kaydedilir ve ön yüzde otomatik olarak ilgili bölümde gösterilir.

### Change Fonts
Update Google Fonts in header:
- Playfair Display (headlines)
- Inter (body text)

### WhatsApp Number
Admin > Settings > WhatsApp Phone (E.164 format: +447123456789)

### Upload Limits
Admin > Settings > Max Upload Size (MB)

---

## 🔒 Security Features

✅ CSRF token protection
✅ Prepared statements (SQL injection prevention)
✅ Password hashing (bcrypt)
✅ File upload validation (MIME type + size)
✅ EXIF stripping
✅ Input sanitization
✅ Session security (httponly, secure flags)
✅ Admin area protection

---

## 📞 Support

Built by **Cüneyt Kaya**
- Website: https://kayacuneyt.com
- Location: Built in Kornwestheim

---

## 📄 License

Proprietary - All rights reserved
