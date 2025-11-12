# 🚀 Quick Start Guide - Local Testing

**Test the Artist Portfolio on your local machine before deploying**

---

## 📦 Requirements

- XAMPP, MAMP, or WAMP
- PHP 8.0+
- MySQL 5.7+
- Web browser

---

## ⚡ Quick Setup (5 minutes)

### 1. Extract Files
```
Unzip artist-portfolio.zip to:
- XAMPP: C:\xampp\htdocs\artist-portfolio
- MAMP: /Applications/MAMP/htdocs/artist-portfolio
```

### 2. Create Database
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create database: `artist_portfolio`
3. Import: Select `schema.sql` file
4. Click "Go"

### 3. Configure Database
Edit `includes/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'artist_portfolio');
define('DB_USER', 'root');
define('DB_PASS', ''); // Empty for XAMPP/MAMP
```

Update Site URL:
```php
define('SITE_URL', 'http://localhost/artist-portfolio');
```

### 4. Start Servers
- XAMPP: Start Apache & MySQL
- MAMP: Start Servers

### 5. Create Admin Account
Visit: `http://localhost/artist-portfolio/admin/setup.php`
- Username: admin
- Password: admin123 (or your choice)
- Email: your@email.com

**Delete setup.php after first run!**

### 6. Test!
- **Homepage:** `http://localhost/artist-portfolio`
- **Admin:** `http://localhost/artist-portfolio/admin/login.php`

---

## 📝 Test Checklist

- [ ] Admin login works
- [ ] Upload test image
- [ ] Image appears on homepage
- [ ] WhatsApp form opens (use your phone number)
- [ ] Inquiry saved in admin panel
- [ ] Responsive design on mobile (F12 → Device toolbar)

---

## 🎨 Test Data

### Sample Artwork Details
- **Title:** Sunset Dreams
- **Description:** Oil on canvas depicting a vibrant sunset
- **Year:** 2024
- **Technique:** Oil on Canvas
- **Dimensions:** 60x80 cm
- **Price:** 450

### Sample Inquiry
- **Name:** John Smith
- **Email:** john@example.com
- **Phone:** +447123456789
- **Message:** Interested in commissioning a landscape painting

---

## 🐛 Common Issues

### Database Connection Error
**Solution:** Check username/password in config.php

### Upload Directory Not Writable
**Solution:** 
```bash
# Windows (XAMPP)
Right-click uploads folder → Properties → Security → Edit → Add "Everyone" with Full Control

# Mac/Linux (MAMP)
chmod -R 755 uploads/
```

### Images Not Displaying
**Solution:** Verify SITE_URL in config.php matches your local URL

---

## 🎯 Next Steps

Once tested locally:
1. Follow DEPLOYMENT.md for Hostinger
2. Update WhatsApp phone number
3. Upload real artworks
4. Go live! 🚀

---

Built in Kornwestheim | Developed by Cüneyt Kaya — https://kayacuneyt.com
