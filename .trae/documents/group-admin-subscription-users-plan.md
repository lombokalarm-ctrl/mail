# Rencana Manajemen User Berlangganan

## Summary

Tambahkan sistem manajemen user berbasis role agar:

- admin SaaS dapat membuat dan mengelola akun admin group untuk pelanggan berlangganan,
- admin group login menggunakan `email + password`,
- admin group hanya dapat melihat dan mengelola data milik group-nya sendiri,
- password awal yang dibuat admin SaaS wajib diganti saat login pertama.

Scope versi pertama mengikuti keputusan yang sudah dikunci:

- login tetap memakai email, bukan username terpisah,
- model akun memakai satu tabel `users` dengan pembeda role,
- admin SaaS mendapat CRUD lengkap untuk akun admin group,
- admin group dibatasi ke group sendiri.

## Current State Analysis

### Auth Saat Ini

- `users` saat ini hanya memiliki `name`, `email`, `password`, dan belum punya `role`, `group_id`, atau flag first-login password change:
  - [0001_01_01_000000_create_users_table.php](file:///workspace/database/migrations/0001_01_01_000000_create_users_table.php)
- Model `User` belum memiliki relasi ke `Group` maupun helper role:
  - [User.php](file:///workspace/app/Models/User.php)
- Login Breeze saat ini murni berbasis `email`:
  - [LoginRequest.php](file:///workspace/app/Http/Requests/Auth/LoginRequest.php)
  - [login.blade.php](file:///workspace/resources/views/auth/login.blade.php)
- Setelah login, semua user diarahkan ke dashboard global yang sama:
  - [AuthenticatedSessionController.php](file:///workspace/app/Http/Controllers/Auth/AuthenticatedSessionController.php)

### Hak Akses Saat Ini

- Semua halaman dashboard admin memakai middleware global `auth` + `verified`, tanpa role check:
  - [web.php](file:///workspace/routes/web.php#L29-L42)
- Dashboard menampilkan statistik global seluruh group/inbox/email:
  - [DashboardController.php](file:///workspace/app/Http/Controllers/DashboardController.php)
  - [dashboard.blade.php](file:///workspace/resources/views/dashboard.blade.php)
- Controller admin `Group`, `Inbox`, dan `Email` belum memfilter data berdasarkan user yang login:
  - [GroupController.php](file:///workspace/app/Http/Controllers/Admin/GroupController.php)
  - [InboxController.php](file:///workspace/app/Http/Controllers/Admin/InboxController.php)
  - [EmailController.php](file:///workspace/app/Http/Controllers/Admin/EmailController.php)
- Navigasi saat ini selalu menampilkan menu global `Group`, `Inbox`, `Email`:
  - [navigation.blade.php](file:///workspace/resources/views/layouts/navigation.blade.php)

### Password Saat Ini

- Flow ubah password sudah ada di profile, tetapi belum mendukung kewajiban ganti password saat login pertama:
  - [PasswordController.php](file:///workspace/app/Http/Controllers/Auth/PasswordController.php)
  - [profile/edit.blade.php](file:///workspace/resources/views/profile/edit.blade.php)

## Assumptions & Decisions

### Keputusan Produk

- Login tetap menggunakan `email + password`.
- Semua akun tetap memakai tabel `users` yang sama.
- Role minimal:
  - `saas_admin`
  - `group_admin`
- `group_admin` harus terkait ke tepat satu `group`.
- `saas_admin` tidak terikat ke satu group dan dapat mengelola semua group serta semua user pelanggan.
- Password awal dibuat oleh admin SaaS dan user admin group wajib menggantinya pada login pertama.
- Karena akun dibuat internal oleh admin SaaS, akun baru akan langsung dianggap terverifikasi (`email_verified_at` diisi saat create) agar tidak terblokir middleware `verified`.

### Batas Akses Yang Akan Diterapkan

- `saas_admin`:
  - akses penuh ke dashboard global,
  - CRUD `group`,
  - CRUD inbox lintas group,
  - lihat/hapus email lintas group,
  - CRUD user admin group.
- `group_admin`:
  - akses dashboard hanya untuk data group miliknya,
  - akses halaman inbox dan email hanya untuk group miliknya,
  - tidak bisa membuka atau memodifikasi halaman `Group Manager` global,
  - tidak bisa mengelola user lain,
  - tetap bisa mengakses halaman profile dan mengganti password sendiri.

### Scope CRUD User V1

- List user admin group
- Create user admin group
- Edit data dasar user admin group
- Reset password user admin group dari panel admin SaaS
- Hapus atau nonaktifkan user admin group

Keputusan implementasi: gunakan `status` boolean/enum ringan di tabel `users` agar akun dapat dinonaktifkan tanpa wajib dihapus permanen. Jika ingin minimal perubahan, dapat memakai `is_active` boolean.

## Proposed Changes

### 1. Perluas Schema `users`

#### File Baru

- `database/migrations/<timestamp>_add_role_group_and_access_flags_to_users_table.php`

#### Perubahan

- Tambahkan kolom:
  - `role` string default `group_admin` atau `saas_admin` sesuai kebutuhan seed/admin lama
  - `group_id` nullable foreign key ke `groups`
  - `must_change_password` boolean default `false`
  - `is_active` boolean default `true`
- Tambahkan index untuk `role`, `group_id`, dan `is_active`.

#### Aturan Data

- Admin lama hasil seed/default akan dimigrasikan menjadi `saas_admin`.
- User `group_admin` wajib punya `group_id`.
- User `saas_admin` harus `group_id = null`.

### 2. Tambahkan Relasi dan Helper Pada Model `User`

#### File

- `app/Models/User.php`

#### Perubahan

- Tambahkan relasi `belongsTo(Group::class)`.
- Tambahkan field baru ke `$fillable`.
- Tambahkan cast untuk:
  - `must_change_password`
  - `is_active`
- Tambahkan helper:
  - `isSaasAdmin(): bool`
  - `isGroupAdmin(): bool`
  - `canAccessGroup(int $groupId): bool`

### 3. Tambahkan Infrastruktur Otorisasi Berbasis Role dan Scope Group

#### File Baru

- `app/Http/Middleware/EnsureSaasAdmin.php`
- `app/Http/Middleware/EnsureActiveUser.php`
- `app/Http/Middleware/EnsurePasswordHasBeenChanged.php`

#### Perubahan

- `EnsureSaasAdmin` membatasi route global manajemen `group` dan `user`.
- `EnsureActiveUser` mencegah login/akses user nonaktif.
- `EnsurePasswordHasBeenChanged` memaksa user yang `must_change_password = true` diarahkan ke profile/password update sebelum mengakses dashboard lain.

#### Catatan

- Middleware `EnsurePasswordHasBeenChanged` harus mengizinkan route berikut:
  - `profile.edit`
  - `password.update`
  - `logout`
- Middleware role harus dipasang hanya pada route yang relevan agar `group_admin` tetap bisa ke dashboard scoped.

### 4. Ubah Flow Login Agar Menghormati Status User

#### File

- `app/Http/Requests/Auth/LoginRequest.php`
- `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
- `resources/views/auth/login.blade.php`

#### Perubahan

- Login tetap memakai `email`, tetapi setelah autentikasi:
  - tolak akun nonaktif,
  - arahkan ke dashboard biasa,
  - middleware akan memaksa ganti password bila perlu.
- Copy teks login diperbarui agar mencerminkan dua tipe akun:
  - admin SaaS
  - admin group pelanggan

### 5. Tambahkan Halaman Manajemen User Untuk Admin SaaS

#### File Baru

- `app/Http/Controllers/Admin/UserController.php`
- `resources/views/admin/users/index.blade.php`

#### File Diubah

- `routes/web.php`
- `resources/views/layouts/navigation.blade.php`

#### Fitur Halaman

- daftar user admin group,
- filter berdasarkan:
  - nama
  - email
  - group
  - role
  - status aktif/nonaktif
- form create user:
  - nama
  - email
  - role
  - group
  - password awal
  - checkbox/flag `must_change_password`
- form edit user:
  - nama
  - email
  - group
  - status aktif/nonaktif
- aksi reset password:
  - admin SaaS set password baru
  - otomatis set `must_change_password = true`
- aksi hapus/nonaktifkan.

#### Keputusan UI

- Buat halaman `User Manager` terpisah, bukan ditanam di `Group Manager`, agar CRUD user tidak bercampur dengan CRUD group/inbox.

### 6. Tambahkan Validasi dan Service Layer Untuk User Admin Group

#### File Baru

- `app/Services/AdminUserService.php`

#### Tanggung Jawab

- Membuat akun admin group dengan aturan konsisten.
- Menjamin:
  - `saas_admin` tidak bisa terikat ke group,
  - `group_admin` wajib punya `group_id`,
  - email unik,
  - password di-hash,
  - `email_verified_at` diisi otomatis saat user dibuat admin SaaS.
- Menangani reset password dan toggle active state.

#### Alasan

- Aturan ini lebih aman bila tidak tersebar di banyak controller.

### 7. Scope Data Dashboard Berdasarkan User Login

#### File

- `app/Http/Controllers/DashboardController.php`
- `resources/views/dashboard.blade.php`

#### Perubahan

- Jika user `saas_admin`, dashboard tetap global seperti sekarang.
- Jika user `group_admin`, semua query dashboard dibatasi ke `group_id` user.
- Statistik untuk group admin menjadi:
  - total inbox group sendiri,
  - total email group sendiri,
  - total lampiran group sendiri,
  - email hari ini group sendiri.
- `totalGroups` untuk group admin dapat:
  - disembunyikan, atau
  - ditampilkan sebagai `1`.

Keputusan implementasi: untuk group admin, sembunyikan metrik global yang tidak relevan dan tampilkan konteks “Group Anda”.

### 8. Scope Controller Inbox dan Email Berdasarkan Group User

#### File

- `app/Http/Controllers/Admin/InboxController.php`
- `app/Http/Controllers/Admin/EmailController.php`

#### Perubahan

- Query `index` dibatasi ke group user bila `group_admin`.
- Aksi `store/update/destroy` pada inbox:
  - `group_admin` hanya boleh mengelola inbox di group miliknya,
  - `group_admin` tidak boleh memindahkan inbox ke group lain.
- Aksi `destroy` email:
  - `group_admin` hanya bisa menghapus email milik inbox di group sendiri.

#### Keputusan

- `group_admin` tetap boleh mengelola inbox miliknya sendiri.
- `group_admin` tidak diberi akses ke CRUD `group`.

### 9. Batasi Akses Halaman `Group Manager`

#### File

- `routes/web.php`
- `resources/views/layouts/navigation.blade.php`

#### Perubahan

- Route `admin.groups.*` dibungkus middleware `saas_admin`.
- Menu `Group` dan nanti `User` hanya muncul untuk `saas_admin`.
- Menu `Inbox` dan `Email` tetap tampil untuk keduanya, tetapi datanya sudah scoped untuk `group_admin`.

### 10. Integrasikan Kewajiban Ganti Password Saat Login Pertama

#### File

- `app/Http/Controllers/Auth/PasswordController.php`
- `resources/views/profile/edit.blade.php`
- `resources/views/profile/partials/update-password-form.blade.php`

#### Perubahan

- Setelah password berhasil diubah, set:
  - `must_change_password = false`
- Tambahkan banner/info khusus bila user sedang diwajibkan mengganti password.
- Untuk user `must_change_password = true`, form profile email/nama tetap bisa diakses, tetapi fokus UX diarahkan ke penggantian password.

### 11. Seeder dan Factory

#### File

- `database/seeders/DatabaseSeeder.php`
- `database/factories/UserFactory.php`

#### Perubahan

- Seed admin default menjadi `saas_admin`.
- Tambahkan contoh `group_admin` pada sample data bila membantu testing manual.
- Factory `User` mendukung state:
  - `saasAdmin()`
  - `groupAdmin(Group $group)`

### 12. Testing

#### File Baru

- `tests/Feature/AdminUserManagementTest.php`
- `tests/Feature/GroupAdminAccessTest.php`

#### File Diubah

- `tests/Feature/Auth/AuthenticationTest.php` jika perlu menyesuaikan copy/login behavior
- `tests/Feature/AdminGroupManagementTest.php`
- `tests/Feature/GroupApiTest.php` bila route web/admin berubah

#### Skenario Test Minimum

- admin SaaS bisa membuat user `group_admin`
- user `group_admin` bisa login via email/password
- user `group_admin` dipaksa ganti password saat login pertama
- setelah ganti password, akses dashboard normal
- user `group_admin` hanya melihat inbox/email group sendiri
- user `group_admin` tidak bisa membuka `admin.groups.*`
- user `group_admin` tidak bisa mengakses/memodifikasi data group lain via URL langsung
- admin SaaS bisa reset password user group dan flag `must_change_password` aktif lagi
- user nonaktif tidak bisa login

## File-Level Implementation Map

### Schema / Model

- `database/migrations/0001_01_01_000000_create_users_table.php`
  - tidak diubah
- `database/migrations/<timestamp>_add_role_group_and_access_flags_to_users_table.php`
  - migrasi fitur baru
- `app/Models/User.php`
  - relasi, cast, helper role

### Auth / Middleware

- `app/Http/Requests/Auth/LoginRequest.php`
  - validasi dan auth flow tetap email-based
- `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
  - redirect pasca login
- `app/Http/Controllers/Auth/PasswordController.php`
  - clear `must_change_password`
- `app/Http/Middleware/EnsureSaasAdmin.php`
- `app/Http/Middleware/EnsureActiveUser.php`
- `app/Http/Middleware/EnsurePasswordHasBeenChanged.php`

### Admin UI / Controllers

- `app/Http/Controllers/Admin/UserController.php`
- `app/Http/Controllers/Admin/GroupController.php`
  - bisa ditambah link atau relasi user group bila diperlukan
- `app/Http/Controllers/Admin/InboxController.php`
  - scope group admin
- `app/Http/Controllers/Admin/EmailController.php`
  - scope group admin
- `app/Http/Controllers/DashboardController.php`
  - scope dashboard
- `resources/views/admin/users/index.blade.php`
- `resources/views/dashboard.blade.php`
- `resources/views/layouts/navigation.blade.php`
- `resources/views/auth/login.blade.php`
- `resources/views/profile/edit.blade.php`
- `resources/views/profile/partials/update-password-form.blade.php`
- `routes/web.php`

### Services / Helpers

- `app/Services/AdminUserService.php`
  - create/update/reset/deactivate logic

### Tests

- `tests/Feature/AdminUserManagementTest.php`
- `tests/Feature/GroupAdminAccessTest.php`
- `tests/Feature/AdminGroupManagementTest.php`

## Verification Steps

### Automated

Jalankan minimal:

```bash
php artisan test tests/Feature/AdminUserManagementTest.php
php artisan test tests/Feature/GroupAdminAccessTest.php
php artisan test tests/Feature/AdminGroupManagementTest.php
```

Jika ada perubahan auth yang luas, jalankan juga:

```bash
php artisan test
```

### Manual

1. Login sebagai `saas_admin`
2. Buka `User Manager`
3. Buat akun `group_admin` untuk satu `group`
4. Logout
5. Login memakai akun `group_admin`
6. Pastikan langsung dipaksa ke flow ganti password
7. Ganti password
8. Pastikan dashboard hanya menampilkan data group sendiri
9. Pastikan menu `Group` dan `User` tidak muncul
10. Coba buka URL `dashboard/groups` secara langsung dan pastikan ditolak
11. Coba buka inbox/email milik group lain lewat URL langsung dan pastikan ditolak
12. Login kembali sebagai `saas_admin`
13. Reset password user `group_admin`
14. Pastikan login berikutnya kembali dipaksa ganti password

### Deploy Notes

- Setelah implementasi nanti, deployment wajib menjalankan migration baru:

```bash
docker compose exec app php artisan migrate --force
```

- Jika middleware/route baru ditambahkan, bersihkan cache:

```bash
docker compose exec app php artisan optimize:clear
```
