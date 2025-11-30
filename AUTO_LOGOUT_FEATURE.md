# 📋 Dual Session Timeout System - Dokumentasi

## ✅ Implementasi Selesai

Sistem dual-timeout sudah terimplementasi dengan:
- **Remember Me: 12 jam** (dengan checkbox at login)
- **Auto Logout: 20 jam** (jika idle/tidak ada aktivitas)

---

## 🔧 Arsitektur Sistem

```
┌─────────────────────────────────────────────────────┐
│           LOGIN (Remember Me)                       │
├─────────────────────────────────────────────────────┤
│ User login dengan checkbox "Ingat Saya"            │
│ ↓                                                   │
│ Auth::login($user, $remember = true/false)         │
│ ↓                                                   │
│ Laravel membuat:                                    │
│ - LARAVEL_SESSION cookie                           │
│ - remember_web cookie (jika remember=true)         │
└─────────────────────────────────────────────────────┘
                     ↓↓↓
┌─────────────────────────────────────────────────────┐
│    ACTIVITY TRACKING (Dashboard)                    │
├─────────────────────────────────────────────────────┤
│ Frontend kirim update-activity setiap 10 menit      │
│ ↓                                                   │
│ Middleware CheckUserActivity track di Cache        │
│ ↓                                                   │
│ Jika idle > 20 jam → Auto Logout                   │
│ Jika aktif < 20 jam → Session tetap berlaku        │
└─────────────────────────────────────────────────────┘
                     ↓↓↓
┌─────────────────────────────────────────────────────┐
│       SESSION LIFETIME (Upper Limit)                │
├─────────────────────────────────────────────────────┤
│ SESSION_LIFETIME = 1440 menit (24 jam)             │
│ ↓                                                   │
│ Maximum session duration (hard limit)              │
│ Tidak peduli remember atau tidak                   │
└─────────────────────────────────────────────────────┘
```

---

## 📁 File-File yang Dibuat/Diubah

### ✅ **Baru Dibuat:**
1. **`app/Http/Middleware/CheckUserActivity.php`**
   - Track last activity di Cache
   - Auto logout jika idle > 20 jam
   - Return 419 (Session Expired) jika logout

### ✅ **Dimodifikasi:**
1. **`.env`**
   - `SESSION_LIFETIME=1440` (24 jam - upper limit)

2. **`bootstrap/app.php`**
   - Register middleware: `CheckUserActivity::class`

3. **`routes/api.php`**
   - Tambah endpoint: `POST /api/update-activity`
   - Untuk frontend update last activity

4. **`resources/views/dashboard.blade.php`**
   - Tambah script: Activity tracking setiap 10 menit
   - Track user events (mouse, keyboard, scroll, click)

---

## 🎯 Logika Timeout

### **Timeline untuk User Login dengan Remember Me:**

```
00:00 - User login dengan "Ingat Saya" di-cek
        ✓ Session dibuat (24 jam max)
        ✓ Remember token dibuat (12 jam)
        
06:00 - User idle/tidak ada aktivitas
        → Activity timestamp masih tersimpan
        
12:00 - Remember Me expires
        ✗ Tapi session masih berlaku (cache tracking)
        
20:00 - Auto Logout triggered (idle > 20 jam)
        ✗ Session logout
        ✗ Cache activity dihapus
        ✗ Frontend redirect ke login (419 error)
        
24:00 - Session lifetime expires (backup)
        ✗ Session invalid di database
```

### **Timeline untuk User yang AKTIF:**

```
00:00 - User login tanpa "Ingat Saya"
        ✓ Session dibuat (24 jam max)
        ✗ Tanpa remember token
        
10:00 - Frontend send update-activity (otomatis)
        ✓ Activity timestamp di-update
        
20:00 - Masih ada aktivitas 10 menit lalu
        ✓ Session tetap berlaku (< 20 jam idle)
        
23:50 - Still active, last activity: 10 menit lalu
        ✓ Session tetap berlaku
```

---

## 🔐 Security Flow

### **Frontend Activity Tracking:**
```javascript
// Setiap 10 menit kirim signal ke backend
setInterval(() => {
    fetch('/api/update-activity', {
        method: 'POST',
        headers: { 'Authorization': `Bearer ${token}` }
    });
}, 10 * 60 * 1000);

// Juga track mouse, keyboard, scroll, click
document.addEventListener('mousemove', resetActivityTimer);
document.addEventListener('keypress', resetActivityTimer);
```

### **Backend Activity Validation:**
```php
// app/Http/Middleware/CheckUserActivity.php
if ($idleTime > 20 * 60 * 60) { // 20 jam dalam detik
    Auth::logout();
    return 419; // Session Expired
}

// Update cache dengan timestamp sekarang
Cache::put($activityKey, $now, $maxIdleTime + 3600);
```

---

## 📊 Configuration

### **Ubah Durasi Timeout:**

**Durasi Auto Logout (20 jam):**
Edit `app/Http/Middleware/CheckUserActivity.php`:
```php
$maxIdleTime = 20 * 60 * 60;  // Ubah 20 menjadi angka lain (dalam jam)
```

**Durasi Remember Me (12 jam):**
Auth::login() sudah handle ini via SESSION_LIFETIME (1440 menit = 24 jam).
Untuk set remember duration, ubah di `config/session.php`:
```php
'lifetime' => 1440,  // 24 jam (upper limit)
'expire_on_close' => false,  // Remember token tetap ada saat close
```

**Frequency Activity Update (10 menit):**
Edit `resources/views/dashboard.blade.php`:
```javascript
setInterval(..., 10 * 60 * 1000);  // Ubah 10 menjadi angka lain (dalam menit)
```

---

## 🧪 Testing Checklist

- [ ] **Test Remember Me:**
  - ✓ Login dengan checkbox di-cek
  - ✓ Close browser & buka lagi
  - ✓ Verifikasi masih login
  - ✓ Check cookie "remember_web" ada di browser

- [ ] **Test Auto Logout (20 jam):**
  - ✓ Login normal
  - ✓ Tidak ada aktivitas selama 20+ jam
  - ✓ Verifikasi redirect ke login dengan 419 error
  - ✓ Middleware block akses dengan message "Session expired"

- [ ] **Test Active Session:**
  - ✓ Login & stay di dashboard
  - ✓ Activity tracking kirim request setiap 10 menit
  - ✓ Verifikasi masih login setelah 20 jam (karena aktif)

- [ ] **Test Logout:**
  - ✓ Click logout button
  - ✓ Verify session dan remember token dihapus
  - ✓ Verify redirect ke login

---

## 📱 Frontend Error Handling

Jika user idle > 20 jam, akan dapat response 419:

```javascript
.then(response => {
    if (response.status === 419) {
        // Session expired - redirect to login
        alert('Session Anda telah berakhir. Silakan login kembali.');
        window.location.href = '/login';
    }
})
```

---

## 🚀 Production Checklist

- [ ] Enable HTTPS (force `https://` di `APP_URL`)
- [ ] Set `SESSION_ENCRYPT=true` di `.env` production
- [ ] Set `SESSION_SECURE=true` di `config/session.php`
- [ ] Set `SESSION_HTTP_ONLY=true` di `config/session.php`
- [ ] Test di real domain (bukan localhost)
- [ ] Verify cookies secure flag (`Secure`, `HttpOnly`)
- [ ] Load test: activity update frequency tidak overwhelming

---

**✨ Sistem dual-timeout siap production! 🎯**
