# 🔧 CARA MEMPERBAIKI ERROR "could not find driver"

## Masalah
Error: **"Database Error: could not find driver"**

Ini berarti **PDO MySQL driver** belum diaktifkan di PHP Anda.

---

## ✅ SOLUSI LENGKAP

### **Step 1: Buka File php.ini**

Lokasi file php.ini di XAMPP:
```
C:\xampp\php\php.ini
```

**Cara membuka:**
1. Buka XAMPP Control Panel
2. Klik tombol "Config" di sebelah Apache
3. Pilih "PHP (php.ini)"

ATAU

Buka file secara langsung dengan Notepad/Text Editor di:
```
C:\xampp\php\php.ini
```

---

### **Step 2: Cari dan Aktifkan Extension**

Di dalam file `php.ini`, tekan `Ctrl+F` dan cari:
```ini
;extension=pdo_mysql
```

**PENTING:** Ada tanda semicolon (;) di depannya!

Ubah menjadi (hapus tanda semicolon):
```ini
extension=pdo_mysql
```

**Juga pastikan ini aktif:**
```ini
extension=mysqli
```

---

### **Step 3: Simpan File**

Tekan `Ctrl+S` atau File → Save

---

### **Step 4: Restart Apache**

1. Buka XAMPP Control Panel
2. Klik tombol **"Stop"** di Apache
3. Tunggu sampai berhenti
4. Klik tombol **"Start"** di Apache

---

### **Step 5: Verifikasi**

Buka browser dan akses:
```
http://localhost/project-pemendek-url/check.php
```

File `check.php` akan menampilkan status sistem Anda.

Jika masih error, lanjut ke troubleshooting di bawah.

---

## 🔍 TROUBLESHOOTING

### Jika masih error setelah restart:

#### 1. **Cek apakah extension file ada**

Buka folder:
```
C:\xampp\php\ext\
```

Pastikan file ini ada:
- `php_pdo_mysql.dll`
- `php_mysqli.dll`

Jika tidak ada, install ulang XAMPP.

---

#### 2. **Cek extension_dir di php.ini**

Cari baris ini di `php.ini`:
```ini
extension_dir = "ext"
```

Pastikan tidak ada tanda semicolon (;) di depannya.

---

#### 3. **Gunakan PHP versi yang benar**

Di Command Prompt/Terminal, jalankan:
```bash
php -v
```

Pastikan versi PHP yang muncul adalah versi XAMPP (biasanya 7.4 atau 8.x).

Jika berbeda, berarti ada PHP lain terinstall di sistem Anda.

---

#### 4. **Cek loaded extensions**

Jalankan command ini:
```bash
php -m
```

Cari di output:
- `PDO`
- `pdo_mysql`

Jika tidak ada, berarti extension belum aktif.

---

#### 5. **Restart komputer**

Kadang Windows perlu restart agar perubahan php.ini berlaku.

---

## 📋 CHECKLIST

Pastikan semua ini sudah dilakukan:

- [ ] File `php.ini` sudah diedit
- [ ] Baris `;extension=pdo_mysql` sudah diubah jadi `extension=pdo_mysql`
- [ ] File sudah disimpan (Ctrl+S)
- [ ] Apache sudah direstart di XAMPP
- [ ] MySQL sudah running di XAMPP
- [ ] Browser sudah direfresh (Ctrl+F5)

---

## 🎯 QUICK FIX (Otomatis)

Jika Anda tidak ingin edit manual, jalankan command ini di Command Prompt **sebagai Administrator**:

```bash
cd C:\xampp\php
copy php.ini php.ini.backup
powershell -Command "(gc php.ini) -replace ';extension=pdo_mysql', 'extension=pdo_mysql' | Out-File -encoding ASCII php.ini"
```

Lalu restart Apache.

---

## 🆘 MASIH ERROR?

### Alternatif 1: Install ulang XAMPP
Download versi terbaru dari: https://www.apachefriends.org/

### Alternatif 2: Gunakan WAMP/MAMP
- **Windows:** WAMP Server
- **Mac:** MAMP

### Alternatif 3: Gunakan Docker
Lebih advanced, tapi lebih reliable.

---

## ✅ SETELAH BERHASIL

Setelah error hilang:

1. **Import Database:**
   - Buka phpMyAdmin: `http://localhost/phpmyadmin`
   - Import file `database.sql`

2. **Akses Aplikasi:**
   ```
   http://localhost/project-pemendek-url/
   ```

3. **Login dengan:**
   - Username: `admin`
   - Password: `admin123`

---

## 📞 BUTUH BANTUAN?

Jika masih error, screenshot error message dan kirim ke developer.

Include informasi:
- Versi XAMPP
- Versi PHP (`php -v`)
- Screenshot error
- Isi file php.ini (bagian extension)

---

**Good luck! 🚀**
