# 🚀 DEPLOYMENT GUIDE - UEMKASolve ke Hosting

**Domain:** `https://uemkasolve.my.id/`

---

## ✅ Checklist Sebelum Deploy

- [x] Database sudah di-migrate di hosting
- [x] PHP version sudah tersedia di hosting
- [x] Vite assets sudah di-build locally (`npm run build`)
- [x] `.env` diperbarui untuk production

---

## 📝 Langkah-Langkah Deployment

### 1. Upload Files ke Hosting

**Via FTP/cPanel:**
```
Upload ke public_html/ (atau root sesuai konfigurasi hosting):
- app/
- bootstrap/
- config/
- database/
- public/ (termasuk public/build/ yang sudah di-build)
- resources/
- routes/
- storage/
- vendor/ (atau install via Composer di server)
- .env (create baru sesuai template di bawah)
- artisan
- composer.json
- composer.lock
- vite.config.js
- package.json
- package-lock.json (optional)
```

### 2. Setup `.env` di Hosting

**Via SSH/Terminal atau Edit via cPanel File Manager:**

Copy dari `.env` dan update untuk hosting:

```dotenv
APP_NAME=Laravel
APP_ENV=production
APP_KEY=base64:nm7mIHcedq+TYzWMQFyeo4INNWwMyyKU3lKkNp8K9JA=
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_URL=https://uemkasolve.my.id
FRONTEND_URL=https://uemkasolve.my.id

LOG_CHANNEL=stack
LOG_LEVEL=debug

# Database Config (Update sesuai hosting Anda)
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=uemkasolve_db
DB_USERNAME=uemkasolve_user
DB_PASSWORD=your_secure_password

# Session & Cache
SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database

# Mail Configuration (Gmail SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=axandiobiyanatullizan2@gmail.com
MAIL_PASSWORD=zqckepwnacgrkmtp
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="uemkasolve@gmail.com"
MAIL_FROM_NAME="Uemkasolve"

# Google OAuth (update sesuai kebutuhan)
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_REDIRECT_URI=https://uemkasolve.my.id/auth/google/callback
```

### 3. Install Dependencies (Jika belum ada vendor/)

**Via SSH:**
```bash
cd /home/username/public_html
composer install --no-dev --optimize-autoloader
```

### 4. Migrate Database (Jika belum selesai)

**Via SSH:**
```bash
php artisan migrate
php artisan db:seed  # Optional - jika ada seeders
```

### 5. Optimize untuk Production

**Via SSH:**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 6. File & Directory Permissions

**Via SSH:**
```bash
# Set directory permissions
chmod -R 755 storage bootstrap/cache
chmod -R 777 storage bootstrap/cache

# Set proper ownership
chown -R www-data:www-data .
```

### 7. Storage Link (Jika menggunakan file upload)

**Via SSH:**
```bash
php artisan storage:link
```

---

## 🐛 Troubleshooting

### ❌ Logo tidak muncul

**Solusi:**
1. Pastikan `npm run build` sudah dijalankan (output di `public/build/`)
2. Cek di browser apakah `https://uemkasolve.my.id/build/assets/...` bisa diakses
3. Jika tidak bisa, rebuild:
   ```bash
   npm run build
   ```

### ❌ Register gagal / Error "Terjadi kesalahan saat registrasi"

**Solusi:**
1. Cek `storage/logs/laravel.log` untuk error detail
2. Pastikan database sudah di-migrate:
   ```bash
   php artisan migrate
   ```
3. Cek mail configuration di `.env` - pastikan SMTP credentials benar
4. Jika masih error, cek dari browser:
   - Buka DevTools (F12) → Network
   - Coba register, lihat response dari `/api/register`
   - Share error message dari Console atau Network tab

### ❌ Email verification tidak terkirim

**Solusi:**
1. Pastikan mail config di `.env` sudah benar (SMTP credentials)
2. Cek `storage/logs/laravel.log` untuk mail sending errors
3. Test manual:
   ```bash
   php artisan tinker
   > Mail::raw('Test', function($m) { $m->to('test@example.com'); })
   ```

### ❌ APP_DEBUG error / 500 error

**Solusi:**
1. Buka `storage/logs/laravel.log` dan cari error message
2. Pastikan `.env` sudah benar
3. Jalankan:
   ```bash
   php artisan optimize:clear
   php artisan config:cache
   ```

---

## 📊 Monitoring

Setelah deploy, monitor dengan:

```bash
# Cek real-time logs
tail -f storage/logs/laravel.log

# Cek error khusus
grep -i error storage/logs/laravel.log | tail -20
```

---

## 🔄 Update/Redeploy

Jika ada update code:

```bash
git pull origin main
composer install --no-dev --optimize-autoloader
npm run build
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
```

---

## ✨ Notes

- **Database Connection:** Gunakan hostname yang benar dari hosting provider
- **File Permissions:** Storage & bootstrap/cache harus writable
- **SSL/HTTPS:** Sudah aktif di `https://uemkasolve.my.id/` ✅
- **Email:** Menggunakan Gmail SMTP - pastikan App Password aktif

---

**Disiapkan:** 1 Desember 2025
**Status:** Ready for Production
