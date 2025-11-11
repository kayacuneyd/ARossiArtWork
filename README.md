# Alexandre Mike Rossi Artworks Portfolioo

A minimalist PHP + Tailwind portfolio for Bournemouth-based painter Alexandre Mike Rossi. The site provides a responsive gallery, WhatsApp-powered inquiries, and an admin dashboard to manage artworks, media processing, and incoming leads.

## Features
- **Responsive gallery** with lazy-loaded images, modal detail view, and dedicated artwork pages.
- **WhatsApp inquiry flow** saves submissions to MySQL, redirects with a prefilled message, and emails the artist.
- **Admin dashboard** with authentication, media uploads (EXIF strip, 2048px max, 600px thumbnail, WebP copy), publish/feature toggles, reordering, and settings.
- **Settings panel** to manage WhatsApp number, artist email, and upload limits.
- **Image processing fallback** automatically chooses Imagick or GD via Intervention Image.
- Built-in CSRF protection, prepared statements (PDO), and session-based auth.

## Tech Stack
- PHP 8.1+
- MySQL 8 (5.7 compatible)
- Tailwind CSS (CLI build)
- Composer + PHPMailer + Intervention Image + Respect/Validation

## Project Structure
```
.
├── public/             # Document root (serve this directory)
├── src/                # PHP source (controllers, services, views)
├── storage/            # Private originals + derivatives (mirrored in public/uploads)
├── database/schema.sql # MySQL schema and default settings seed
├── resources/css/      # Tailwind input styles
├── package.json        # Tailwind CLI scripts
├── composer.json       # PHP dependencies & autoloading
└── README.md
```

## Getting Started (Local)
1. **Clone & install dependencies**
   ```bash
   composer install
   npm install
   ```
2. **Compile Tailwind**
   ```bash
   npm run dev:css      # watch mode
   npm run build:css    # production/minified
   ```
   The built CSS is output to `public/assets/css/tailwind.css`.
3. **Environment setup**
   - Copy `.env.example` → `.env` and update credentials.
   - Required values for MySQL: `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `APP_URL`.
   - Local shortcut: set `DB_CONNECTION=sqlite` (and optionally `DB_DATABASE=storage/database.sqlite`) to boot with a self-contained SQLite file that seeds sample artworks + settings automatically.
   - Optional email SMTP: `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION` (`tls` or `ssl`).
4. **Database**
   - Create the database: `CREATE DATABASE arotsi_portfolio CHARACTER SET utf8mb4;`
   - Import schema: `mysql -u user -p arotsi_portfolio < database/schema.sql`
   - Schema includes tables: `admins`, `artworks`, `inquiries`, `settings` (with default artist email, WhatsApp, upload MB).
   - Optional placeholders: `mysql -u user -p arotsi_portfolio < database/sample_data.sql` seeds three sample artworks pointing to color-block images in `public/uploads/sample-0*.png`.
5. **Default admin credentials**
   - Username: `admin`
   - Password: `ChangeMe123!`
   - First login is enforced via `Installer::ensureDefaultAdmin()`; change the password immediately via Settings (coming soon) or database.
6. **Serve locally**
   ```bash
   php -S localhost:8080 -t public
   ```
   Visit http://localhost:8080 and http://localhost:8080/admin

## Image Handling
- Uploads limited by `MAX_UPLOAD_MB` (env or settings). Default 8MB.
- Accepted MIME types: JPG, PNG, WebP (PNG/WebP re-encoded without EXIF).
- Originals resized to max width 2048px, thumbnail 600px, WebP copy when supported.
- Stored twice: private copy under `storage/` and served copy under `public/uploads/` (make sure these directories are writable).

## WhatsApp Flow
- Contact modal and `/contact` page post to `/inquiry`.
- Inquiry saved to DB (`inquiries` table) with WhatsApp-ready message.
- Redirects to `https://api.whatsapp.com/send?phone=...&text=...` using number stored in settings.
- Optional email notification using PHPMailer (skipped silently if SMTP unavailable).

## Deployment

### Automated Deployment (Recommended)
This repository includes automated FTP deployment via GitHub Actions. When you push to the `main` branch:
- Code is automatically built (Tailwind CSS + Composer dependencies)
- Files are deployed to your Hostinger account via FTP
- Only production files are uploaded (development files are excluded)

**See [DEPLOYMENT.md](DEPLOYMENT.md) for complete setup instructions** and [TROUBLESHOOTING.md](TROUBLESHOOTING.md) for common issues.

### Hostinger Initial Setup (Required Before First Deploy)
1. **Document root**: point the domain/subdomain to the `public/` directory (e.g. move files so `public` → `public_html`).
2. **PHP version**: set to 8.1+ via hPanel → Advanced → PHP Configuration.
3. **php.ini overrides** (hPanel > PHP Configuration > Options):
   - `upload_max_filesize = 16M`
   - `post_max_size = 16M`
   - `memory_limit = 256M`
   - `max_execution_time = 120`
4. **Folder permissions** (via SSH or File Manager):
   ```bash
   chmod -R 775 storage public/uploads
   chmod -R 775 storage/uploads storage/thumbs storage/webp
   ```
5. **Composer on Hostinger**: run `composer install` from the project root (SSH). If Composer unavailable, upload the `vendor/` directory from local.
6. **Tailwind build**: run `npx tailwindcss -i ./resources/css/input.css -o ./public/assets/css/tailwind.css --minify`. If Node is unavailable, compile locally and upload the generated CSS.
7. **Environment**: create `.env` (not tracked) with production credentials. Ensure `APP_URL=https://yourdomain.com`.
8. **Cron / background**: not required, but consider setting up periodic DB backups.

## Testing Checklist
Manual smoke tests (recommended before and after deploy):
1. Upload an artwork (JPG/PNG ≤ 8MB). Confirm thumbnail, WebP copy, featured/published toggles, and ordering updates.
2. View gallery on desktop and mobile widths; verify lazy loading and modal detail data.
3. Submit WhatsApp inquiry:
   - Check row inserted into `inquiries` table.
   - Ensure browser opens WhatsApp with prefilled message.
   - Verify email delivered (if SMTP configured).
4. Update settings (WhatsApp number/email/upload size) and confirm they persist in dashboard & `.env` overrides the defaults.
5. Logout/login flow and CSRF protection (resubmit forms with expired token to confirm rejection).

## Screenshots / Video Guidance
- Record an upload & inquiry walkthrough (QuickTime, Loom, or similar).
- Capture: login → upload artwork → view gallery modal → submit inquiry → WhatsApp redirect.
- Store assets in `/docs/` (not committed yet) for the final deliverable.

## Useful Scripts
- `composer install` — install PHP dependencies.
- `composer dump-autoload` — rebuild classmap after adding classes.
- `npm run dev:css` / `npm run build:css` — Tailwind.

## Future Enhancements
- Password change UI & multi-admin management.
- Drag-and-drop ordering (current UI uses numeric fields).
- Multi-language support.
- Audit logging for uploads & settings changes.

---
**Support**: Update `README.md` and `.env` after deploying. Remember to replace placeholder text & imagery with final assets supplied by the artist.
