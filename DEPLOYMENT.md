# Hostinger Deployment Guide

This guide explains how to deploy the ARossi ArtWork portfolio to your Hostinger account via FTP.

## Initial Server Setup (One-time steps)

Before the automated FTP deployment will work, you need to set up your Hostinger server:

### 1. Set Document Root
In Hostinger's hPanel:
- Go to your domain/subdomain settings
- Set the document root to `public_html/public` (or wherever you're deploying)
- This ensures the `public/` directory is served as the web root

### 2. Configure PHP Version
- Go to hPanel → Advanced → PHP Configuration
- Select PHP version 8.1 or higher
- Click "Apply"

### 3. Configure PHP Settings
In hPanel → PHP Configuration → Options, set:
```
upload_max_filesize = 16M
post_max_size = 16M
memory_limit = 256M
max_execution_time = 120
```

### 4. Create Required Directories
Via SSH or File Manager, create these directories with write permissions:
```bash
mkdir -p public_html/storage/uploads
mkdir -p public_html/storage/thumbs
mkdir -p public_html/storage/webp
chmod -R 775 public_html/storage
chmod -R 775 public_html/public/uploads
```

### 5. Create .env File on Server
The `.env` file is not deployed via FTP for security. Create it manually on the server:

1. In File Manager, create `public_html/.env`
2. Copy contents from `.env.example`
3. Update with production values:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://arossiartwork.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your@email.com
MAIL_FROM_NAME="ARossi Artwork"
```

### 6. Set Up Database
1. Create MySQL database via hPanel
2. Import the schema:
   - Go to phpMyAdmin
   - Select your database
   - Import `database/schema.sql`
3. Optionally import sample data from `database/sample_data.sql`

### 7. Set Up GitHub Secrets
In your GitHub repository settings (Settings → Secrets and variables → Actions), add:
- `FTP_USERNAME`: Your Hostinger FTP username
- `FTP_PASSWORD`: Your Hostinger FTP password

## How the Automated Deployment Works

Once the initial setup is complete, the deployment workflow automatically:

1. **Builds the project**: Compiles Tailwind CSS and installs PHP dependencies
2. **Deploys via FTP**: Syncs only necessary files to your server
3. **Excludes unnecessary files**: Development files, source files, and config files are not uploaded

The workflow runs automatically when you push to the `main` branch.

## What Gets Deployed

✅ Deployed to server:
- `/public/` - Web root with index.php and assets
- `/src/` - PHP application code
- `/config/` - Application configuration
- `/vendor/` - PHP dependencies (Composer packages)
- `/storage/` - Upload directories (empty structure)

❌ NOT deployed:
- `.github/` - GitHub workflows
- `node_modules/` - Node dependencies
- `resources/` - Tailwind source files
- `database/` - Schema files (should be imported manually)
- `.env` - Environment file (must be created on server)
- Development files (README, composer.json, etc.)

## Troubleshooting

### Deployment fails with FTP errors
- Verify FTP credentials in GitHub Secrets
- Check that the FTP server address is correct: `ftp.arossiartwork.com`
- Ensure `server-dir` matches your actual path (default is `/public_html/`)

### Site shows blank page or 500 error
- Check `.env` file exists and has correct values
- Verify database connection settings
- Check PHP version is 8.1+
- Review error logs in hPanel

### CSS not loading
- Verify `public/assets/css/tailwind.css` exists
- Check file permissions (should be 644)
- Clear browser cache

### Images not uploading
- Check storage directories exist and are writable (775)
- Verify PHP upload limits in hPanel
- Check `.env` has correct MAX_UPLOAD_MB value

### Database connection errors
- Verify DB credentials in `.env`
- Check database exists in hPanel → Databases
- Ensure DB user has proper permissions

## Manual Deployment (If needed)

If GitHub Actions deployment isn't working, you can deploy manually:

1. **Build locally**:
   ```bash
   npm install
   npm run build:css
   composer install --no-dev --optimize-autoloader
   ```

2. **Upload via FTP** (exclude these):
   - `.git/`, `.github/`
   - `node_modules/`
   - `resources/`, `tailwind.config.js`, `package*.json`
   - `database/`, `README.md`, `composer.json`, `composer.lock`
   - `.env` (create separately on server)

3. **Complete server setup** as described above

## Post-Deployment Checklist

After deployment, verify:
- [ ] Site loads at your domain
- [ ] Gallery displays correctly
- [ ] Admin login works at `/admin`
- [ ] Can upload artwork through admin
- [ ] WhatsApp inquiry redirects work
- [ ] Email notifications are sent
- [ ] Mobile responsive layout works

## Support

For issues:
1. Check GitHub Actions logs for deployment errors
2. Review Hostinger error logs in hPanel
3. Verify all initial setup steps were completed
4. Check `.env` configuration matches your server

---
Last updated: 2025-11-11
