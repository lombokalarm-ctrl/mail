# APLI Mail

APLI Mail adalah aplikasi web catch-all email viewer untuk domain `email.apli.my.id`. Semua email ke alamat `*@email.apli.my.id` dapat diterima tanpa membuat akun lebih dulu, disimpan ke PostgreSQL, lalu ditampilkan melalui viewer web bertoken dan dashboard admin.

## Fitur Utama

- Auto create inbox berdasarkan local-part email.
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

- `inboxes`: inbox dinamis, slug, dan access token viewer.
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

3. Generate key, migrasi, dan seed:

```bash
docker compose exec app php artisan key:generate --force
docker compose exec app php artisan migrate --seed --force
docker compose exec app php artisan storage:link
```

4. Akses aplikasi:

- Landing page: `http://localhost:8080`
- Admin login: `http://localhost:8080/login`

## Pipeline Catch-All

APLI Mail menyediakan command berikut untuk menerima raw email dari Postfix:

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

## Contoh Konfigurasi Postfix Catch-All

Contoh sederhana agar semua email ke `email.apli.my.id` diteruskan ke command Laravel:

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
apli-mail: "|/usr/bin/docker compose -f /opt/apli-mail/docker-compose.yml exec -T app php artisan mail:ingest"
```

Lalu jalankan:

```bash
sudo newaliases
sudo systemctl restart postfix
```

Catatan:

- Pastikan MX record domain sudah mengarah ke VPS.
- Gunakan path `docker compose` yang sesuai dengan lokasi project.
- Pada produksi, lebih aman menjalankan Postfix di host VPS lalu pipe ke container `app`.

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
