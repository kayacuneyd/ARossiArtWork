# 🚀 Hostinger Deployment Guide

**Artist Portfolio - Complete Deployment Instructions**

Built in Kornwestheim | Developed by Cüneyt Kaya — https://kayacuneyt.com

---

## 📋 Pre-Deployment Checklist

- [ ] Hostinger hosting account active
- [ ] Domain configured (optional)
- [ ] FTP/SFTP credentials ready
- [ ] Database access via cPanel

---

## 🔧 Step 1: Upload Files

### Via FileZilla (Recommended)
1. **Download FileZilla:** https://filezilla-project.org/
2. **Connect to Hostinger:**
   - Host: `ftp.yourdomain.com`
   - Username: Your Hostinger FTP username
   - Password: Your FTP password
   - Port: 21 (FTP) or 22 (SFTP)

3. **Upload all files to:**
   ```
   /public_html/
   ```
   Or if using subdomain:
   ```
   /public_html/subdomain_name/
   ```

### Via cPanel File Manager
1. Login to Hostinger cPanel
2. Go to **File Manager**
3. Navigate to `public_html`
4. Click **Upload**
5. Upload the ZIP file
6. Extract the ZIP file
7. Move all files from extracted folder to `public_html`

---

## 🗄️ Step 2: Create Database

1. **Login to Hostinger cPanel**
2. **Go to "MySQL Databases"**
3. **Create New Database:**
   - Database name: `u123456789_portfolio` (example)
   - Click "Create"

4. **Create Database User:**
   - Username: `u123456789_admin` (example)
   - Password: Generate strong password
   - Click "Create User"

5. **Add User to Database:**
   - Select user and database
   - Grant ALL PRIVILEGES
   - Click "Add"

6. **Import Schema:**
   - Go to **phpMyAdmin**
   - Select your database
   - Click **Import** tab
   - Choose `schema.sql`
   - Click **Go**

---

## ⚙️ Step 3: Configure Database Connection

1. **Edit `includes/config.php`:**
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'u123456789_portfolio');
   define('DB_USER', 'u123456789_admin');
   define('DB_PASS', 'your_secure_password');
   ```

2. **Update Site URL:**
   ```php
   define('SITE_URL', 'https://yourdomain.com');
   // Or if in subdirectory:
   define('SITE_URL', 'https://yourdomain.com/portfolio');
   ```

3. **Save and upload the file again**

---

## 🔐 Step 4: Set Folder Permissions

### Via cPanel File Manager
Right-click each folder → Change Permissions:

```
/uploads/           → 755
/uploads/artworks/  → 755
/uploads/thumbnails/→ 755
/uploads/webp/      → 755
/includes/          → 755
All .php files      → 644
.htaccess          → 644
```

### Via SSH (if available)
```bash
chmod 755 uploads/
chmod 755 uploads/artworks/
chmod 755 uploads/thumbnails/
chmod 755 uploads/webp/
chmod 644 *.php
chmod 644 .htaccess
```

---

## 🐘 Step 5: PHP Configuration

### Check PHP Version
1. Go to cPanel → **Select PHP Version**
2. Set to **PHP 8.1** or higher
3. Enable extensions:
   - ✅ mysqli
   - ✅ gd (or imagick)
   - ✅ mbstring
   - ✅ curl

### Create `.user.ini` in public_html
```ini
upload_max_filesize = 10M
post_max_size = 12M
memory_limit = 128M
max_execution_time = 60
```

---

## 👤 Step 6: Create Admin Account

1. **Visit:** `https://yourdomain.com/admin/setup.php`
2. **Fill in the form:**
   - Username: admin (or your choice)
   - Email: your@email.com
   - Password: Strong password (min 8 chars)
3. **Click "Create Admin Account"**
4. **IMPORTANT:** Delete `/admin/setup.php` immediately!

### Delete setup.php via cPanel
1. Go to File Manager
2. Navigate to `/public_html/admin/`
3. Select `setup.php`
4. Click "Delete"

---

## ✅ Step 7: Test Everything

### Test Admin Login
1. Go to `https://yourdomain.com/admin/login.php`
2. Login with your credentials
3. Should redirect to dashboard

### Test Image Upload
1. Go to Admin → Upload Artwork
2. Upload a test image
3. Fill in title and metadata
4. Click "Upload Artwork"
5. Check if image appears on homepage

### Test WhatsApp Form
1. Go to homepage
2. Click "Request Artwork"
3. Fill in the form
4. Click "Send via WhatsApp"
5. Should open WhatsApp with prefilled message
6. Check Admin → Inquiries to see if it was saved

### Test Gallery
1. Visit homepage: `https://yourdomain.com`
2. Images should load with lazy loading
3. WebP format should work on modern browsers
4. Responsive design should work on mobile

---

## 🔧 Step 8: Final Configuration

### Update Settings
Go to Admin → Settings and configure:

1. **WhatsApp Phone Number:**
   - Format: `+447123456789` (E.164)
   - UK example: `+447123456789`
   - Turkey example: `+905321234567`

2. **Artist Email:**
   - For inquiry notifications
   - Use your actual email

3. **Site Title & Description:**
   - Update with artist name
   - Add proper description for SEO

4. **Enable/Disable Features:**
   - ✅ Show prices
   - ✅ Enable inquiry form

---

## 🛡️ Security Checklist

- [x] `setup.php` deleted
- [x] Strong admin password set
- [x] Database credentials in `config.php` updated
- [x] Folder permissions set correctly (755/644)
- [x] `.htaccess` file uploaded
- [x] PHP version set to 8.1+
- [x] Error reporting disabled in production

### Disable Error Display (Production)
In `includes/config.php`, change:
```php
error_reporting(0);
ini_set('display_errors', 0);
```

---

## 🌐 SSL Certificate (HTTPS)

1. Go to Hostinger cPanel
2. Navigate to **SSL/TLS**
3. Click **Install SSL Certificate**
4. Choose **Let's Encrypt (Free)**
5. Select your domain
6. Click **Install**

Wait 5-10 minutes for activation.

### Force HTTPS
Uncomment in `.htaccess`:
```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## 📧 Email Configuration (Optional)

If you want email notifications for inquiries:

1. Create email in cPanel: `noreply@yourdomain.com`
2. Update in `includes/config.php`:
   ```php
   define('SMTP_HOST', 'mail.yourdomain.com');
   define('SMTP_PORT', 587);
   define('SMTP_USERNAME', 'noreply@yourdomain.com');
   define('SMTP_PASSWORD', 'email_password');
   ```

---

## 🐛 Troubleshooting

### Database Connection Error
**Problem:** Can't connect to database
**Solution:**
- Check credentials in `includes/config.php`
- Verify database exists in cPanel
- Ensure user has ALL PRIVILEGES

### Images Not Uploading
**Problem:** Upload fails
**Solution:**
- Check folder permissions (755)
- Verify `upload_max_filesize` in `.user.ini`
- Check if GD or Imagick is enabled

### WhatsApp Redirect Not Working
**Problem:** Form submits but no WhatsApp
**Solution:**
- Check WhatsApp phone format: `+447123456789`
- Verify `api/submit-inquiry.php` is accessible
- Check browser console for errors

### 500 Internal Server Error
**Problem:** White screen or 500 error
**Solution:**
- Check `.htaccess` syntax
- Verify PHP version is 8.1+
- Check error logs in cPanel

### Images Show Broken Link
**Problem:** Images don't display
**Solution:**
- Verify `SITE_URL` in config.php is correct
- Check folder permissions
- Ensure images are in correct directories

---

## 📊 Performance Optimization

### Enable Caching
Add to `.htaccess`:
```apache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/webp "access plus 1 year"
</IfModule>
```

### Enable Gzip Compression
Already in `.htaccess` - just verify it's working

---

## 🎉 You're Done!

Your artist portfolio is now live! 

**Next Steps:**
1. Upload actual artworks
2. Customize site title and description
3. Test on mobile devices
4. Share with the world! 🎨

---

## 📞 Support

**Built in Kornwestheim**
**Developed by Cüneyt Kaya**
Website: https://kayacuneyt.com

For technical support or custom development, contact via website.

---

**Last Updated:** November 2024
