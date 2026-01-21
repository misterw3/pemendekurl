# 🔗 Minimalist URL Shortener

Modern, premium URL shortener dengan desain glassmorphism dan fitur lengkap.

## 🚀 Fitur Utama

- ✨ **Desain Premium** - Glassmorphism dengan animasi smooth
- 🔐 **Sistem Autentikasi** - Login, Register, dan Role Management (Admin/User)
- 📊 **Dashboard Analytics** - Tracking klik dan statistik URL
- 📱 **Fully Responsive** - Optimized untuk Desktop, Tablet, dan Mobile
- ⚡ **Real-time Validation** - Validasi URL instant
- 🎨 **Modern UI/UX** - Micro-animations dan smooth transitions
- 🔒 **Secure** - Password hashing dengan bcrypt
- 📈 **Click Analytics** - Detail tracking setiap klik

## 📋 Persyaratan Sistem

- **XAMPP** (atau stack LAMP/WAMP lainnya)
- **PHP** 7.4 atau lebih tinggi
- **MySQL** 5.7 atau lebih tinggi
- **Apache** dengan mod_rewrite enabled
- **PDO MySQL Extension** (harus diaktifkan)

## 🛠️ Instalasi

### Step 1: Aktifkan PDO MySQL Extension

**PENTING:** Jika Anda mendapat error "could not find driver", ikuti langkah ini:

1. Buka file `php.ini`:
   - Windows: `C:\xampp\php\php.ini`
   - Linux: `/etc/php/7.x/apache2/php.ini`

2. Cari baris berikut:
   ```ini
   ;extension=pdo_mysql
   ```

3. Hapus tanda semicolon (;) di depannya:
   ```ini
   extension=pdo_mysql
   ```

4. Simpan file dan **restart Apache** di XAMPP Control Panel

5. Verifikasi dengan command:
   ```bash
   php -m | findstr pdo_mysql
   ```
   Jika berhasil, akan muncul: `pdo_mysql`

### Step 2: Setup Database

**Opsi A - Otomatis (Recommended):**
1. Pastikan MySQL sudah running di XAMPP
2. Akses `http://localhost/project-pemendek-url/`
3. Database dan tabel akan dibuat otomatis

**Opsi B - Manual:**
1. Buka phpMyAdmin: `http://localhost/phpmyadmin`
2. Import file `database.sql`
3. Atau jalankan query di SQL tab

### Step 3: Konfigurasi (Opsional)

Edit file `db.php` jika perlu mengubah kredensial database:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pemendek_url');
```

### Step 4: Akses Aplikasi

Buka browser dan akses:
```
http://localhost/project-pemendek-url/
```

## 👤 Default Login Credentials

### Admin Account
- **Username:** `admin`
- **Password:** `admin123`
- **Email:** `admin@urlshortener.com`

### User Account
- **Username:** `user`
- **Password:** `user123`
- **Email:** `user@example.com`

## 📁 Struktur File

```
project-pemendek-url/
├── .htaccess              # URL rewriting rules
├── index.php              # Homepage
├── login.php              # Login page
├── register.php           # Registration page
├── dashboard.php          # User dashboard
├── logout.php             # Logout handler
├── api.php                # API endpoint untuk shorten URL
├── redirect.php           # URL redirect handler
├── auth_action.php        # Authentication handler
├── db.php                 # Database configuration
├── style.css              # Stylesheet (responsive)
├── script.js              # JavaScript (validation & AJAX)
├── database.sql           # Database schema
└── README.md              # Documentation
```

## 🎯 Cara Penggunaan

### 1. Shorten URL (Tanpa Login)
1. Buka homepage
2. Paste URL panjang Anda
3. Klik "Shorten URL"
4. Copy short URL yang dihasilkan

### 2. Dengan Login (Tracking & Management)
1. Register akun baru atau login
2. Shorten URL seperti biasa
3. Akses Dashboard untuk melihat:
   - Semua URL yang Anda buat
   - Jumlah klik per URL
   - Statistik total

### 3. Admin Features
Admin dapat:
- Melihat semua URL dari semua user
- Menghapus URL apapun
- Melihat statistik global

## 🔧 Troubleshooting

### Error: "Database Error: could not find driver"

**Solusi:**
1. Aktifkan extension `pdo_mysql` di `php.ini`
2. Restart Apache
3. Verifikasi dengan: `php -m | findstr pdo_mysql`

### Error: "404 Not Found" saat akses short URL

**Solusi:**
1. Pastikan file `.htaccess` ada
2. Aktifkan `mod_rewrite` di Apache:
   - Edit `httpd.conf`
   - Uncomment: `LoadModule rewrite_module modules/mod_rewrite.so`
   - Restart Apache

### Error: "Access Denied" saat koneksi database

**Solusi:**
1. Cek kredensial di `db.php`
2. Pastikan MySQL running
3. Cek user permissions di MySQL

### Short URL tidak redirect

**Solusi:**
1. Cek `.htaccess` file exists
2. Pastikan `AllowOverride All` di Apache config
3. Restart Apache

## 🎨 Customization

### Mengubah Warna Tema

Edit file `style.css` di bagian `:root`:

```css
:root {
    --neon-violet: #bd00ff;    /* Primary color */
    --neon-cyan: #00f7ff;      /* Secondary color */
    --neon-pink: #ff00aa;      /* Accent color */
    --bg-dark: #0f0f13;        /* Background */
}
```

### Mengubah Panjang Alias

Edit file `api.php`, function `generateAlias()`:

```php
function generateAlias($length = 6) {  // Ubah angka 6
    // ...
}
```

## 📊 Database Schema

### Table: users
- `id` - Primary key
- `username` - Unique username
- `email` - Unique email
- `password` - Hashed password (bcrypt)
- `role` - 'user' or 'admin'
- `created_at` - Registration timestamp

### Table: urls
- `id` - Primary key
- `user_id` - Foreign key to users (nullable)
- `alias` - Short URL code (unique)
- `original_url` - Original long URL
- `clicks` - Click counter
- `created_at` - Creation timestamp
- `last_clicked_at` - Last click timestamp
- `is_active` - Active status

### Table: click_analytics
- `id` - Primary key
- `url_id` - Foreign key to urls
- `ip_address` - Visitor IP
- `user_agent` - Browser info
- `referer` - Referrer URL
- `clicked_at` - Click timestamp

## 🔐 Security Features

- ✅ Password hashing dengan bcrypt
- ✅ Prepared statements (SQL injection prevention)
- ✅ XSS protection dengan htmlspecialchars
- ✅ CSRF protection ready
- ✅ Input validation & sanitization
- ✅ Session management

## 📱 Responsive Breakpoints

- **Desktop:** > 1024px
- **Tablet:** 768px - 1024px
- **Mobile:** < 768px
- **Small Mobile:** < 480px
- **Landscape Mobile:** < 600px height

## 🚀 Performance

- Optimized CSS dengan minimal dependencies
- Vanilla JavaScript (no jQuery)
- Lazy loading animations
- Efficient database queries dengan indexes
- Caching ready

## 📝 License

Free to use for personal and commercial projects.

## 🤝 Support

Jika ada masalah atau pertanyaan:
1. Cek section Troubleshooting di atas
2. Pastikan semua requirements terpenuhi
3. Cek error log di XAMPP

## 🎉 Credits

Developed with ❤️ using modern web technologies.

---

**Enjoy your URL Shortener! 🔗✨**
