# 👥 User Management - Admin Feature

## Overview
Fitur User Management memungkinkan administrator untuk mengelola semua user dalam sistem URL Shortener.

## Akses
**URL:** `http://localhost/project-pemendek-url/admin_users.php`

**Requirement:** Harus login sebagai **Admin**

## Fitur-Fitur

### 1. 📊 Dashboard Statistics
- **Total Users** - Jumlah semua user
- **Administrators** - Jumlah admin
- **Regular Users** - Jumlah user biasa

### 2. 👀 View All Users
Tabel menampilkan:
- ID User
- Username
- Email
- Role (Admin/User)
- Jumlah URL yang dibuat
- Total clicks dari semua URL user
- Tanggal bergabung
- Action buttons

### 3. ➕ Add New User
**Fitur:**
- Tambah user baru langsung dari admin panel
- Set username, email, password
- Pilih role (User atau Admin)
- Validasi otomatis

**Cara Menggunakan:**
1. Klik tombol **"+ Add New User"**
2. Isi form:
   - Username (required, unique)
   - Email (required, unique, valid format)
   - Password (required, min 6 karakter)
   - Role (User/Admin)
3. Klik **"Add User"**

**Validasi:**
- ✅ Semua field wajib diisi
- ✅ Email harus valid
- ✅ Password minimal 6 karakter
- ✅ Username dan email harus unique
- ✅ Error message jika gagal

### 4. ✏️ Edit User Role
**Fitur:**
- Ubah role user (User ↔ Admin)
- Tidak bisa mengubah role sendiri

**Cara Menggunakan:**
1. Klik tombol **"Edit Role"** pada user yang ingin diubah
2. Pilih role baru (User atau Admin)
3. Klik **"Save Changes"**

**Proteksi:**
- ❌ Admin tidak bisa mengubah role sendiri
- ✅ Perubahan langsung tersimpan
- ✅ Halaman auto-refresh setelah berhasil

### 5. 🗑️ Delete User
**Fitur:**
- Hapus user dari sistem
- Otomatis menghapus semua URL milik user tersebut
- Tidak bisa menghapus diri sendiri

**Cara Menggunakan:**
1. Klik tombol **"Delete"** pada user yang ingin dihapus
2. Konfirmasi penghapusan
3. User dan semua URL-nya akan terhapus

**Proteksi:**
- ❌ Admin tidak bisa menghapus diri sendiri
- ⚠️ Konfirmasi sebelum hapus
- ⚠️ Penghapusan bersifat permanen (tidak bisa undo)
- ✅ Cascade delete - semua URL user juga terhapus

## API Endpoints

### Base URL
`admin_users_api.php`

### Available Actions

#### 1. Update Role
```
POST admin_users_api.php?action=update_role
Body: {
  "user_id": 123,
  "role": "admin"
}
```

#### 2. Delete User
```
POST admin_users_api.php?action=delete_user
Body: {
  "user_id": 123
}
```

#### 3. Add User
```
POST admin_users_api.php?action=add_user
Body: {
  "username": "newuser",
  "email": "user@example.com",
  "password": "password123",
  "role": "user"
}
```

#### 4. Reset Password
```
POST admin_users_api.php?action=reset_password
Body: {
  "user_id": 123,
  "new_password": "newpassword123"
}
```

#### 5. Get User Details
```
POST admin_users_api.php?action=get_user
Body: {
  "user_id": 123
}
```

#### 6. Get All Users (Paginated)
```
POST admin_users_api.php?action=get_all_users
Body: {
  "page": 1,
  "limit": 50
}
```

## Security Features

### Authentication
- ✅ Hanya admin yang bisa akses
- ✅ Session-based authentication
- ✅ Auto-redirect jika bukan admin

### Authorization
- ✅ Admin tidak bisa hapus/edit diri sendiri
- ✅ Validasi role (hanya user/admin)
- ✅ CSRF protection ready

### Data Protection
- ✅ Password di-hash dengan bcrypt
- ✅ Prepared statements (SQL injection prevention)
- ✅ Input validation & sanitization
- ✅ XSS protection

## Database Schema

### Users Table
```sql
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE,
  email VARCHAR(100) UNIQUE,
  password VARCHAR(255),
  role ENUM('user', 'admin'),
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

### Relationships
- **One-to-Many:** User → URLs
- **Cascade Delete:** Hapus user = hapus semua URL-nya

## UI/UX Features

### Modal Dialogs
- ✨ Smooth animations
- ✨ Glassmorphism design
- ✨ Click outside to close
- ✨ Keyboard support (ESC to close)

### Success Messages
- ✅ Auto-show setelah action berhasil
- ✅ Auto-hide setelah 3 detik
- ✅ Slide-down animation

### Responsive Design
- 📱 Mobile-friendly
- 💻 Tablet optimized
- 🖥️ Desktop full-featured
- ↔️ Horizontal scroll untuk tabel di mobile

### Loading States
- ⏳ Button disabled saat proses
- ⏳ Loading indicator
- ⏳ Prevent double-submit

## Error Handling

### Client-Side
- ❌ Form validation
- ❌ Empty field check
- ❌ Email format validation
- ❌ Password length check

### Server-Side
- ❌ Database errors
- ❌ Duplicate username/email
- ❌ Invalid role
- ❌ User not found
- ❌ Unauthorized access

### User Feedback
- ✅ Clear error messages
- ✅ Success notifications
- ✅ Confirmation dialogs
- ✅ Visual feedback

## Best Practices

### When to Use
✅ Menambah admin baru
✅ Mengubah role user yang sudah ada
✅ Menghapus user yang tidak aktif
✅ Monitoring aktivitas user

### When NOT to Use
❌ Jangan hapus user yang masih aktif tanpa backup
❌ Jangan ubah role sembarangan
❌ Jangan share kredensial admin

### Recommendations
1. **Backup Database** sebelum hapus user penting
2. **Komunikasi** dengan user sebelum hapus akun mereka
3. **Monitor** aktivitas admin di log
4. **Limit** jumlah admin (principle of least privilege)

## Troubleshooting

### "Unauthorized" Error
**Solusi:** Pastikan Anda login sebagai admin

### User Tidak Bisa Dihapus
**Kemungkinan:**
- Anda mencoba hapus diri sendiri
- User tidak ditemukan
- Database error

### Role Tidak Berubah
**Kemungkinan:**
- Mencoba ubah role sendiri
- Database error
- Session expired

## Future Enhancements

Fitur yang bisa ditambahkan:
- [ ] Bulk actions (delete multiple users)
- [ ] Export user list to CSV
- [ ] User activity log
- [ ] Email notification saat user dibuat/dihapus
- [ ] Password reset via email
- [ ] User suspension (soft delete)
- [ ] Advanced filtering & search
- [ ] User permissions (granular access control)

## Testing

### Test Cases

1. **Add User**
   - ✅ Valid data → Success
   - ❌ Duplicate username → Error
   - ❌ Invalid email → Error
   - ❌ Short password → Error

2. **Edit Role**
   - ✅ Change user to admin → Success
   - ✅ Change admin to user → Success
   - ❌ Change own role → Error

3. **Delete User**
   - ✅ Delete other user → Success
   - ❌ Delete self → Error
   - ✅ URLs also deleted → Success

## Support

Jika ada masalah:
1. Cek console browser untuk error JavaScript
2. Cek PHP error log
3. Verifikasi database connection
4. Pastikan session aktif

---

**Happy Managing! 👥✨**
