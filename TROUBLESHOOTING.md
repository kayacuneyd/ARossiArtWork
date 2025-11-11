# Quick FTP Deployment Troubleshooting

## Common Issues and Solutions

### 1. "FTP connection failed" in GitHub Actions
**Problem**: Workflow can't connect to FTP server

**Solutions**:
- Verify GitHub Secrets are set correctly:
  - `FTP_USERNAME` - Your Hostinger FTP username
  - `FTP_PASSWORD` - Your Hostinger FTP password
- Check FTP server address in workflow file: `ftp.arossiartwork.com`
- Verify server-dir path: `/public_html/` (may vary by hosting setup)
- Ensure FTP access is enabled in Hostinger hPanel

### 2. "Blank page" or "500 Internal Server Error" after deployment
**Problem**: Site deploys but doesn't work

**Solutions**:
- Create `.env` file on server (NOT deployed via FTP for security)
- Set PHP version to 8.1+ in hPanel
- Check vendor directory was deployed (composer install ran)
- Verify database connection in `.env`
- Check storage directory permissions: `chmod -R 775 storage`

### 3. "CSS not loading" or "Styles missing"
**Problem**: Site displays but without styling

**Solutions**:
- Verify `public/assets/css/tailwind.css` exists on server
- Check workflow completed successfully (green checkmark in Actions tab)
- Clear browser cache
- Verify file wasn't excluded accidentally

### 4. "Cannot upload images" in admin
**Problem**: Image uploads fail

**Solutions**:
- Create storage directories on server:
  ```
  storage/uploads/
  storage/thumbs/
  storage/webp/
  public/uploads/
  ```
- Set write permissions: `chmod -R 775 storage public/uploads`
- Check PHP upload limits in hPanel PHP Configuration
- Verify `.env` has MAX_UPLOAD_MB value

### 5. "Class not found" or "Autoload errors"
**Problem**: PHP class loading fails

**Solutions**:
- Verify `vendor/` directory exists on server
- Check workflow ran `composer install` successfully
- Ensure PHP 8.1+ is selected in hPanel
- Manually run `composer install --no-dev --optimize-autoloader` via SSH if needed

### 6. Workflow builds but doesn't deploy changes
**Problem**: GitHub Actions succeeds but site unchanged

**Solutions**:
- Check if changes were in excluded files (see `.ftp-deploy-exclude`)
- Verify FTP-Deploy-Action uploaded files (check workflow logs)
- Clear server-side cache if using caching
- Check file timestamps on server

### 7. "Database connection error"
**Problem**: Can't connect to MySQL

**Solutions**:
- Verify `.env` database credentials:
  ```
  DB_CONNECTION=mysql
  DB_HOST=localhost
  DB_DATABASE=your_db_name
  DB_USERNAME=your_db_user
  DB_PASSWORD=your_db_password
  ```
- Import database schema via phpMyAdmin
- Check database user has proper permissions
- Verify database exists in hPanel

### 8. GitHub Actions fails at "Install Composer dependencies"
**Problem**: Composer install fails in workflow

**Solutions**:
- Check `composer.lock` is committed to repo
- Verify PHP version in workflow matches requirements (8.1+)
- Check for rate limiting (unlikely with setup-php action)
- Review workflow logs for specific errors

### 9. GitHub Actions fails at "Build Tailwind CSS"
**Problem**: npm run build:css fails

**Solutions**:
- Verify `package-lock.json` is committed
- Check `resources/css/input.css` exists
- Verify `tailwind.config.js` is valid
- Review workflow logs for specific errors

### 10. Files deployed but document root is wrong
**Problem**: Server shows directory listing instead of site

**Solutions**:
- Set document root to `public_html/public/` in Hostinger hPanel
- Or adjust server-dir in workflow to match your setup
- Verify `index.php` exists in public directory

## Quick Checks Before Asking for Help

1. ✅ GitHub Secrets set (FTP_USERNAME, FTP_PASSWORD)
2. ✅ Workflow ran successfully (green checkmark)
3. ✅ `.env` file created on server with correct values
4. ✅ Database imported and credentials correct
5. ✅ PHP version 8.1+ selected in hPanel
6. ✅ Storage directories exist and writable
7. ✅ Document root points to public directory
8. ✅ Files were actually deployed (check timestamps)

## Getting More Help

1. **Check Workflow Logs**: GitHub repository → Actions tab → Click on failed run
2. **Check Server Logs**: Hostinger hPanel → Advanced → Error Logs
3. **Test Locally First**: Run `npm run build:css` and `composer install` locally
4. **Manual Deploy**: Follow DEPLOYMENT.md for manual FTP upload

## Useful Commands (via SSH)

```bash
# Check PHP version
php -v

# List storage permissions
ls -la storage/

# Test database connection
php -r "new PDO('mysql:host=localhost;dbname=your_db', 'user', 'pass');"

# Check if Composer installed
composer --version

# Rebuild autoloader
composer dump-autoload --optimize

# Check disk space
df -h
```

---
For detailed setup instructions, see DEPLOYMENT.md
