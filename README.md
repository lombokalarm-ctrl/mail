# APLI Mail

APLI Mail adalah aplikasi web catch-all email viewer untuk domain `email.apli.my.id`. Email ke inbox yang sudah didaftarkan ke group SaaS akan diterima, disimpan ke PostgreSQL, lalu ditampilkan melalui viewer web bertoken dan dashboard admin.

## Fitur Utama

- Group SaaS dengan token viewer manual per customer.
- Satu group dapat memiliki banyak inbox terdaftar.
- URL viewer bertoken dengan format `/view/{inbox_name}-{token}`.
- Daftar email terbaru, pencarian sender/subject, pagination, dan indikator lampiran.
- Detail email dengan HTML yang telah disanitasi dan fallback text content.
- Download lampiran dengan validasi akses viewer atau admin login.
- Dashboard admin: total inbox, total email, total lampiran, statistik email per hari.
- API inbox dan email.
- Dark mode, layout responsive, dan UI bernuansa modern ala mailbox client.

## Stack

- Backend: Laravel 12, PHP 8.4
- Frontend: Laravel Blade, Tailwind CSS, Alpine.js
- Database: PostgreSQL
- Queue/Cache: Redis
- Mail parsing: `zbateson/mail-mime-parser`
- HTML sanitization: `symfony/html-sanitizer`
- Web server: Nginx
- Deployment: Docker Compose

## Struktur Data

- `groups`: customer/subscriber, token viewer, dan status group.
- `inboxes`: inbox terdaftar yang terhubung ke group.
- `emails`: metadata email, body HTML, body teks, dan waktu diterima.
- `attachments`: file lampiran, path storage, ukuran, dan MIME type.

## Menjalankan Secara Lokal

1. Salin environment:

```bash
cp .env.example .env
```

2. Sesuaikan nilai penting di `.env`:

- `APP_URL`
- `DB_*`
- `REDIS_*`
- `APLI_MAIL_DOMAIN`
- `APLI_MAIL_ADMIN_EMAIL`
- `APLI_MAIL_ADMIN_PASSWORD`

3. Install dependency:

```bash
composer install
npm install
```

4. Generate key dan jalankan migrasi:

```bash
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

5. Jalankan aplikasi:

```bash
composer run dev
```

6. Login admin:

- Email: nilai `APLI_MAIL_ADMIN_EMAIL`
- Password: nilai `APLI_MAIL_ADMIN_PASSWORD`

## Menjalankan dengan Docker Compose

1. Salin environment:

```bash
cp .env.example .env
```

2. Build dan jalankan container:

```bash
docker compose up --build -d
```

3. Untuk deployment Docker, pastikan nilai penting di `.env` sudah sesuai:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://email.apli.my.id` untuk produksi
- `DB_HOST=postgres`
- `REDIS_HOST=redis`

4. Jalankan migrasi dan seed:

```bash
docker compose exec app php artisan migrate:fresh --seed --force
docker compose exec app php artisan storage:link
```

5. Akses aplikasi:

- Landing page: `http://localhost:8080`
- Admin login: `http://localhost:8080/login`

Catatan deployment:

- Service `postgres` dan `redis` tidak diekspos ke host agar lebih aman.
- `Dockerfile` sudah mengaktifkan ekstensi `redis` PHP untuk queue dan cache.
- Migration `attachments` harus berjalan setelah `emails`; file timestamp di repo sudah diurutkan untuk itu.

## Pipeline Catch-All

APLI Mail menyediakan command berikut untuk menerima raw email dari Postfix.
Inbox penerima harus sudah terdaftar lebih dulu pada group yang benar:

```bash
php artisan mail:ingest --sync --file=/path/to/message.eml
```

Atau lewat stdin:

```bash
cat /path/to/message.eml | php artisan mail:ingest --sync
```

Mode default akan mengirim email ke queue Redis:

```bash
cat /path/to/message.eml | php artisan mail:ingest
```

## Konfigurasi Postfix Catch-All

Repo ini menyediakan snippet dan script host-friendly di:

- `ops/postfix/main.cf.snippet`
- `ops/postfix/virtual_catchall`
- `ops/postfix/aliases.snippet`
- `scripts/postfix-ingest.sh`

Langkah yang disarankan pada VPS:

### `/etc/postfix/main.cf`

```conf
virtual_alias_domains = email.apli.my.id
virtual_alias_maps = regexp:/etc/postfix/virtual_catchall
```

### `/etc/postfix/virtual_catchall`

```conf
/^.+@email\.apli\.my\.id$/ apli-mail
```

### `/etc/aliases`

```conf
apli-mail: "|/var/www/email/scripts/postfix-ingest.sh"
```

Lalu jalankan:

```bash
chmod +x /var/www/email/scripts/postfix-ingest.sh
sudo newaliases
sudo systemctl restart postfix
```

Catatan:

- Pastikan MX record domain sudah mengarah ke VPS.
- Jalankan Postfix di host VPS lalu pipe ke container `app`.
- Sesuaikan `APLI_MAIL_PROJECT_DIR` jika project dipasang di path selain `/var/www/email`.
- Script `scripts/postfix-ingest.sh` memakai `docker exec` ke container `apli-mail-app` agar tetap bekerja saat Postfix berjalan dengan user non-root.

## Reverse Proxy Dan SSL

Template reverse proxy host Nginx tersedia di `ops/nginx/email.apli.my.id.conf`.

Contoh aktivasi:

```bash
sudo cp ops/nginx/email.apli.my.id.conf /etc/nginx/sites-available/email.apli.my.id
sudo ln -sf /etc/nginx/sites-available/email.apli.my.id /etc/nginx/sites-enabled/email.apli.my.id
sudo nginx -t
sudo systemctl reload nginx
```

Lalu terbitkan sertifikat:

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d email.apli.my.id
```

## Hardening Production

Checklist minimum:

- Jalankan aplikasi dengan `APP_ENV=production` dan `APP_DEBUG=false`.
- Gunakan `APP_URL=https://email.apli.my.id`.
- Simpan `postgres` dan `redis` hanya di jaringan Docker internal.
- Buka hanya port `22`, `80`, dan `443` di firewall host.
- Pasang reverse proxy host Nginx di depan container.
- Ganti password admin default dan simpan kredensial di `.env`.

Contoh command UFW tersedia di `ops/ufw/commands.sh`:

```bash
sudo bash ops/ufw/commands.sh
```

## Backup Database

Script backup PostgreSQL tersedia di `scripts/backup-postgres.sh`.

Contoh penggunaan manual:

```bash
chmod +x /var/www/email/scripts/backup-postgres.sh
/var/www/email/scripts/backup-postgres.sh
```

File backup akan disimpan ke `/var/backups/apli-mail` dan backup lama lebih dari 7 hari akan dihapus otomatis.

Contoh cron harian jam 02:30:

```cron
30 2 * * * /var/www/email/scripts/backup-postgres.sh >> /var/log/apli-mail-backup.log 2>&1
```

## API

- `GET /api/inboxes`
- `GET /api/inboxes/{id}`
- `GET /api/emails`
- `GET /api/emails/{id}`
- `DELETE /api/emails/{id}` untuk admin terautentikasi

## Data Sample

Seeder otomatis membuat:

- Akun admin awal.
- Inbox contoh: `ahmad-alhijrah`, `visa-alhijrah`, `tiket-alhijrah`.
- Email sample dan lampiran yang bisa diunduh.

## Security

- HTML email disanitasi sebelum dirender.
- Lampiran dibatasi default 25 MB per file.
- SQL injection dicegah melalui Eloquent dan query builder.
- CSRF protection aktif pada form.
- Rate limiting diterapkan untuk viewer, attachment download, dan API.
- Registrasi publik dimatikan; hanya admin seeded/internal yang dapat login.

## Verifikasi

Perintah yang digunakan selama development:

```bash
php artisan route:list
php artisan migrate:fresh --seed --force
php artisan test
npm run build
```
