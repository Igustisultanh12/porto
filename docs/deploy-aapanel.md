# Deployment Laravel + Vue 3 ke aaPanel

Panduan ini memasang **aplikasi PHP Laravel**, bukan hanya direktori hasil build Vue. Laravel melayani request melalui Nginx/PHP-FPM; proses Node SSR berjalan di loopback. Tidak perlu Redis atau Docker.

Contoh memakai domain `example.com`, direktori `/www/wwwroot/porto`, PHP 8.3, dan user web `www`. Ganti domain/path/user/socket sesuai server. Perintah dalam dokumen ini ditujukan untuk terminal Linux server. Deployment server sungguhan belum dilakukan oleh proses pengembangan lokal.

## 1. Prasyarat server

- Rekomendasi awal VPS: 2 vCPU, RAM 2 GB atau lebih, ruang kosong 10 GB. Kebutuhan akhir bergantung trafik, media, dan ukuran database; ini bukan hasil benchmark kapasitas.
- Instal **Nginx**, **PHP 8.3 atau lebih baru yang kompatibel**, serta **MariaDB 10.11+ / MySQL 8** melalui App Store aaPanel. MariaDB 11.4 adalah versi yang telah diuji pada aplikasi ini.
- Instal Git, Composer 2, dan Node.js 22 terbaru yang kompatibel (minimum 22.18) beserta npm. Node diperlukan selama runtime SSR, bukan hanya saat build.
- Aktifkan extension PHP `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `hash`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `session`, `tokenizer`, `xml`, `zip`, `bcmath`, dan **gd dengan WebP**. `pdo_sqlite` hanya diperlukan jika menjalankan tes SQLite di server.
- Gunakan `memory_limit=256M` atau lebih, `upload_max_filesize=10M`, `post_max_size=12M`. Aplikasi membatasi berkas 10 MB; Nginx harus mengizinkan request multipart sedikit lebih besar.
- Pastikan PHP CLI sama dengan versi PHP-FPM situs:

```bash
/www/server/php/83/bin/php -v
/www/server/php/83/bin/php --ini
/www/server/php/83/bin/php --ri gd
node --version
npm --version
composer --version
```

Pada contoh berikut `php` berarti `/www/server/php/83/bin/php`. Gunakan path absolut tersebut bila `php -v` sistem menunjuk versi lain. Composer juga harus berjalan dengan PHP yang sama; misalnya `/www/server/php/83/bin/php /path/to/composer install ...`. Ganti `/path/to/composer` dengan lokasi executable/PHAR Composer pada server.

## 2. DNS, situs, dan database

1. Arahkan record A domain ke IP VPS. Pasang AAAA hanya jika IPv6 server benar-benar siap.
2. aaPanel → **Website → Add site / PHP Project**: daftarkan domain, pilih PHP 8.3, gunakan direktori `/www/wwwroot/porto`.
3. Jika aaPanel membuat file contoh, simpan dahulu atau gunakan direktori kosong lain sebelum clone. Jangan menimpa situs yang sudah ada.
4. aaPanel → **Databases**: buat database `porto`, user aplikasi `porto`, dan password acak. Batasi akses user ke database tersebut dan host lokal. Jangan memakai root database untuk aplikasi.
5. Buka port publik 80 dan 443. Database, queue, dan port SSR 13714 tidak perlu terbuka ke internet. Pertahankan akses panel/SSH sesuai konfigurasi operator.

## 3. Ambil kode dan konfigurasi

Lakukan clone pada direktori yang kosong. Jalankan Git, Composer, dan npm sebagai user deployment biasa yang memiliki direktori aplikasi; gunakan hak administratif hanya untuk konfigurasi service dan permission.

```bash
git clone https://github.com/Igustisultanh12/porto.git /www/wwwroot/porto
cd /www/wwwroot/porto
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
cp .env.example .env
```

Jika `.env` sudah tersedia dari instalasi sebelumnya, **jangan timpa**. Isi menggunakan editor server:

```dotenv
APP_NAME=Portofolio
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://example.com
APP_LOCALE=id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=porto
DB_USERNAME=porto
DB_PASSWORD="GANTI_DENGAN_PASSWORD_DATABASE"

SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_DOMAIN=null
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local

MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME="ISI_USERNAME_SMTP"
MAIL_PASSWORD="ISI_PASSWORD_SMTP"
MAIL_FROM_ADDRESS=portfolio@example.com
MAIL_FROM_NAME=Portofolio
CONTACT_NOTIFICATION_EMAIL=owner@example.com
PASSWORD_RESET_ENABLED=false

INERTIA_SSR_ENABLED=true
INERTIA_SSR_URL=http://127.0.0.1:13714
INERTIA_SSR_THROW_ON_ERROR=false
LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=warning
```

`smtp` port 587 memakai STARTTLS bila didukung provider. Untuk implicit TLS gunakan `MAIL_SCHEME=smtps` dan port yang ditentukan provider, biasanya 465. Jangan menebak kredensial atau endpoint. Tanpa SMTP, gunakan `MAIL_MAILER=log`, kosongkan `CONTACT_NOTIFICATION_EMAIL`, dan pertahankan reset password nonaktif; form kontak tetap menyimpan pesan ke inbox.

Setelah konfigurasi:

```bash
php artisan key:generate --force
php artisan migrate --force
php artisan storage:link
php artisan app:create-admin
npm ci
npm run build
```

**`key:generate` hanya untuk instalasi pertama ketika APP_KEY belum ada.** Simpan key tetap pada update/restore. Kehilangan atau mengganti key memutus kemampuan membuka data terenkripsi dan sesi lama.

Build menghasilkan `public/build/manifest.json` dan bundle `bootstrap/ssr/`. Jangan menghapus `node_modules` pada deployment ini: SSR masih menggunakannya. Dev dependencies diperlukan saat build, meskipun Composer memakai `--no-dev`.

`storage:link` membuat symlink untuk disk public standar Laravel. Upload aplikasi ini berada di `storage/app/private` dan disajikan melalui controller; **jangan membuat symlink dari folder private ke public**.

## 4. Document root, Nginx, dan open_basedir

aaPanel → Website → domain → **Site directory / Running directory**:

- Site directory: `/www/wwwroot/porto`
- Running directory: `/public`
- Document root akhir: `/www/wwwroot/porto/public`

Root repository tidak boleh menjadi document root karena berisi `.env`, vendor, dan konfigurasi.

Contoh vhost tersedia di [nginx.conf.example](nginx.conf.example). Gabungkan aturan Laravel ke konfigurasi situs aaPanel. Pertahankan blok SSL, ACME, logging, dan pengaturan domain yang dikelola panel. Jangan menduplikasi `location /` atau blok PHP dengan include PHP bawaan panel.

Aturan inti:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

Contoh mengizinkan eksekusi hanya `/index.php`, menolak PHP lain, melindungi dotfiles, menambahkan security headers, dan memberi cache immutable pada aset `/build/`. Sesuaikan `fastcgi_pass unix:/tmp/php-cgi-83.sock;` dengan socket FPM yang sebenarnya.

Jika `open_basedir` aktif, beri akses situs ini ke `/www/wwwroot/porto/` dan `/tmp/` beserta path session/temp yang benar-benar digunakan PHP. Laravel perlu membaca parent `public`, vendor, dan storage. Periksa `.user.ini` serta konfigurasi FPM situs; jangan menonaktifkan open_basedir secara global. Pada aaPanel, ubah melalui pengaturan situs jika berkas diproteksi panel.

Uji konfigurasi sebelum reload:

```bash
nginx -t
```

Reload Nginx melalui aaPanel hanya jika pemeriksaan berhasil.

## 5. Ownership dan permission

Contoh memakai akun deployment `deploy` dan group web `www`. Buat/pilih akun deployment sesuai kebijakan server. Pastikan path benar sebelum menjalankan operasi rekursif berikut.

```bash
cd /www/wwwroot/porto
pwd
sudo chown -R deploy:www /www/wwwroot/porto
sudo chmod -R u=rwX,g=rX,o= /www/wwwroot/porto
sudo find /www/wwwroot/porto/storage /www/wwwroot/porto/bootstrap/cache -type d -exec chmod 2770 {} \;
sudo find /www/wwwroot/porto/storage /www/wwwroot/porto/bootstrap/cache -type f -exec chmod 660 {} \;
sudo chmod 640 /www/wwwroot/porto/.env
```

Mode `X` mempertahankan executable yang memang diperlukan binary dependensi dan memberi traverse pada direktori. Jangan meratakan seluruh file menjadi mode non-executable karena build memerlukan binary paket. PHP menjalankan `artisan` melalui interpreter sehingga file itu tidak memerlukan executable bit.

User FPM, worker, dan SSR harus dapat membaca kode serta `.env`; hanya `storage` dan `bootstrap/cache` yang memerlukan tulis dari proses web. Direktori induk `/www` dan `/www/wwwroot` harus dapat dilalui user tersebut. Jangan gunakan `777`. Pastikan akun `deploy` anggota group `www` agar update tetap dapat menulis cache yang dibuat worker.

## 6. SSL, HTTPS, dan proxy

aaPanel → domain → **SSL**: terbitkan sertifikat Let's Encrypt setelah DNS benar dan challenge dapat diakses. Aktifkan **Force HTTPS** setelah sertifikat terpasang. Atur `APP_URL=https://domain` dan `SESSION_SECURE_COOKIE=true`, lalu cache ulang konfigurasi.

Untuk Cloudflare gunakan SSL **Full (strict)** dengan sertifikat valid di origin. Jika memakai reverse proxy/load balancer lain, konfigurasikan trusted proxy di `bootstrap/app.php` hanya untuk IP/range proxy yang benar. Contoh penyesuaian Laravel:

```php
$middleware->trustProxies(at: ['IP_PROXY_YANG_TERVERIFIKASI']);
```

Jangan memakai wildcard semua proxy tanpa memahami sumber trafik; IP asli memengaruhi rate limiting. Aplikasi memaksa skema HTTPS pada URL yang dihasilkan dalam production, sementara pengaturan trusted proxy tetap diperlukan untuk interpretasi request yang benar.

## 7. Queue worker dan SSR

Instal pengelola proses **Supervisor** melalui plugin aaPanel atau paket sistem. Gunakan [supervisor.conf.example](supervisor.conf.example). Sesuaikan path PHP, direktori, user, dan PATH Node.

Konfirmasi Node tersedia sebagai user web:

```bash
sudo -u www /usr/bin/env node --version
```

Jika Node dipasang melalui plugin aaPanel atau version manager, PATH shell interaktif belum tentu tersedia di Supervisor. Isi `environment=PATH="..."` dengan direktori executable Node yang sebenarnya. Versi Node harus memenuhi syarat Vite/Inertia.

Masukkan dua program:

- `porto-queue`: `php artisan queue:work database --sleep=3 --tries=3 --timeout=60`
- `porto-ssr`: `php artisan inertia:start-ssr`

Aktifkan `autostart`, `autorestart`, `stopasgroup`, dan `killasgroup`. Reload konfigurasi melalui plugin, atau jika memakai Supervisor sistem:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl status
```

Keduanya harus berstatus RUNNING. SSR bind ke `127.0.0.1:13714`. Jangan proxy port itu ke internet. Worker timeout 60 detik berada di bawah `retry_after` database queue bawaan 90 detik.

Setelah update kode/build:

```bash
php artisan queue:restart
sudo supervisorctl restart porto-ssr
```

Jika menggunakan UI plugin, restart program dengan nama yang sesuai di UI. Worker akan keluar setelah job aktif selesai dan dihidupkan kembali oleh Supervisor.

## 8. Scheduler dan cache produksi

Tambahkan cron setiap menit, dijalankan sebagai user web `www`:

```cron
* * * * * cd /www/wwwroot/porto && /www/server/php/83/bin/php artisan schedule:run >> /www/wwwroot/porto/storage/logs/scheduler.log 2>&1
```

Pada menu Cron aaPanel, pilih Shell Script dan isi bagian setelah lima kolom jadwal. Pastikan user eksekusi memiliki permission yang sesuai. Jangan menjalankan `schedule:work` bersamaan dengan cron produksi.

Scheduler melakukan pembersihan job gagal berumur lebih dari 7 hari dan retry notifikasi inbox yang belum terkirim setiap 10 menit. Aktifkan log rotation atau periksa ukuran `scheduler.log` secara berkala.

Cache aplikasi:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Jalankan setelah `.env` final. Perubahan `.env` tidak terbaca proses yang masih memakai config cache lama; jalankan `config:cache`, lalu restart worker/SSR. Tidak ada cache konten publik persisten yang harus dihapus ketika admin menerbitkan konten.

## 9. Smoke test setelah deploy

1. Buka `/up`, beranda, tentang, ketiga arsip, kontak, dan `/admin/login` melalui HTTPS.
2. Gunakan View Source atau `curl https://example.com` dan pastikan judul/konten terlihat sebagai HTML; jangan hanya memeriksa JSON `data-page`. Periksa meta description, canonical, Open Graph, dan JSON-LD.
3. Masuk admin dan buat draft. Buka URL detail dan PDF dari jendela privat: keduanya harus 404.
4. Terbitkan konten; periksa detail, filter, sitemap, dan lampiran. Unpublish; pastikan akses publik tertutup lagi.
5. Upload gambar dan PDF, verifikasi preview/unduhan. Pastikan unggahan SVG ditolak.
6. Kirim form kontak. Pesan harus masuk inbox walaupun email tidak dikonfigurasi. Bila SMTP aktif, periksa pengiriman melalui worker sebelum mengaktifkan reset password.
7. Uji login/logout, tema terang/gelap, layar mobile, serta tautan internal.
8. Periksa log dan job gagal:

```bash
php artisan queue:failed
php artisan schedule:list
php artisan inertia:check-ssr
tail -n 80 storage/logs/laravel.log
tail -n 80 storage/logs/ssr.log
```

Jangan menganggap status HTTP 200 membuktikan SSR aktif: Inertia dapat fallback ke CSR saat SSR tidak tersedia. Jika tes HTML gagal, periksa proses SSR dan manifest build.

## 10. Backup dan uji restore

Backup minimal: database, `.env` termasuk APP_KEY, serta seluruh `storage/app`. Catat commit Git yang berjalan. Simpan backup di luar document root, batasi akses, enkripsi salinan off-server, dan tetapkan retensi sesuai kebutuhan.

Contoh membuat snapshot konsisten dengan menghentikan penulisan sementara:

```bash
cd /www/wwwroot/porto
php artisan down
sudo supervisorctl stop porto-queue
mkdir -p /www/backup/porto/2026-10-20
chmod 700 /www/backup/porto/2026-10-20
git rev-parse HEAD > /www/backup/porto/2026-10-20/commit.txt
mariadb-dump --single-transaction --routines --triggers -u porto -p porto > /www/backup/porto/2026-10-20/database.sql
tar -czf /www/backup/porto/2026-10-20/files.tar.gz .env storage/app
chmod 600 /www/backup/porto/2026-10-20/*
sudo supervisorctl start porto-queue
php artisan up
```

Ganti nama direktori dengan label snapshot unik; jangan menimpa backup lama. Pada MySQL gunakan `mysqldump`. Jika user aplikasi tidak berhak membaca routines/triggers, gunakan akun backup dengan izin yang sesuai atau sesuaikan opsi dengan schema aktual (aplikasi awal tidak membuat routine/trigger). Jangan menaruh password pada argumen CLI atau riwayat shell. Jadwal backup otomatis memerlukan penyimpanan kredensial dengan permission ketat sesuai fasilitas backup aaPanel.

Untuk uji restore, gunakan direktori dan database staging baru:

1. Checkout commit pada `commit.txt` ke direktori staging; pasang dependensi dari lockfiles dan build.
2. Ekstrak `files.tar.gz` ke staging. Pertahankan APP_KEY backup, tetapi ubah koneksi DB, APP_URL, dan penerima SMTP supaya staging tidak memakai layanan produksi.
3. Import SQL ke database staging kosong: `mariadb -u staging_user -p porto_restore < database.sql`.
4. Pastikan permission dan konfigurasi SSR port staging terpisah, atau jalankan SSR bergantian.
5. Jalankan smoke test, login, baca konten, dan unduh media. Catat tanggal serta hasilnya. Keberadaan berkas backup saja bukan bukti restore berhasil.

## 11. Update tanpa kehilangan data

1. Pastikan tes/CI untuk commit baru lulus. Review migration dan catatan perubahan; uji pada staging terlebih dahulu jika perubahan schema signifikan.
2. Catat SHA saat ini dan pastikan `git status --short` tidak berisi perubahan kode lokal yang belum disimpan. Buat backup database, `.env`, dan upload seperti di atas.
3. Aktifkan maintenance dan stop queue. Jangan reset database.

```bash
cd /www/wwwroot/porto
git rev-parse HEAD
git status --short
php artisan down
sudo supervisorctl stop porto-queue
git pull --ff-only origin main
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo supervisorctl restart porto-ssr
sudo supervisorctl start porto-queue
php artisan up
```

Jika salah satu langkah gagal, perbaiki atau rollback sebelum membuka maintenance. `git pull --ff-only` berhenti saat history berbeda; jangan menggantinya dengan force reset untuk menimpa kode lokal. **Jangan menjalankan `key:generate`, `migrate:fresh`, atau seeder demo pada update produksi.** Jalankan smoke test setelah situs dibuka kembali.

## 12. Rollback

Rollback kode tidak otomatis membatalkan migration. Migration awal `create_portfolio_tables` memiliki `down()` yang menghapus tabel aplikasi, sehingga menjalankan rollback migration awal pada produksi akan menghapus konten. Jangan menjalankannya untuk rollback rutin.

- Jika schema baru kompatibel dengan kode sebelumnya: maintenance → stop worker → checkout SHA sebelum update (`git switch --detach SHA_SEBELUM_UPDATE`) → `composer install` + `npm ci` + build → cache ulang → restart SSR/worker → smoke test → buka maintenance.
- Jika perubahan schema tidak kompatibel: gunakan snapshot database dan file yang diambil sebelum update, restore ke database pengganti, checkout commit yang cocok, dan arahkan `.env` ke database hasil restore setelah verifikasi. Pertahankan APP_KEY dari snapshot. Data yang masuk setelah snapshot memerlukan rekonsiliasi manual; jangan diam-diam membuangnya.
- Setelah rollback detached HEAD, kembalikan ke branch yang sesuai hanya setelah masalah rilis diperbaiki. Simpan backup gagal untuk investigasi.

## Troubleshooting

| Gejala | Periksa dan tindakan |
| --- | --- |
| 403 | Document root `/public`, traverse permission direktori induk, owner/group FPM, dan open_basedir. Admin 403 juga berarti akun tidak memiliki `is_admin`. |
| 404 semua URL selain `/` | Aktifkan front-controller `try_files`; jangan memakai rewrite SPA yang mengarah ke `index.html`. |
| 419 saat submit | Domain cookie, HTTPS/secure cookie, sesi database, APP_KEY konsisten, trusted proxy; muat ulang halaman untuk token baru. |
| 500 | Baca `storage/logs/laravel.log`; periksa APP_KEY, DB, extension, migration, permission, dan config cache. Jangan membuka debug produksi ke publik. |
| 502 | PHP-FPM tidak aktif atau socket `fastcgi_pass` salah. SSR mati biasanya fallback CSR, bukan 502 dari Nginx. |
| Vite manifest hilang | Jalankan `npm ci` + `npm run build`; pastikan `public/build/manifest.json` ada. Hapus file `public/hot` yang tertinggal dari development jika ada. |
| Halaman kosong tanpa JavaScript / metadata tidak lengkap | Periksa SSR process, versi Node, PATH Supervisor, bundle `bootstrap/ssr`, dan `INERTIA_SSR_URL`; restart SSR sesudah build. |
| Gambar gagal diunggah | GD/WebP aktif di PHP-FPM, memory_limit cukup, berkas ≤10 MB dan ≤24 MP; periksa batas Nginx/PHP. |
| PDF/gambar 404 | Periksa status konten, apakah berada di sampah, file `storage/app/private`, dan permission. Jangan membuka storage private sebagai solusi. |
| Worker tidak berjalan | Nama program Supervisor, path PHP, group, DB queue, `queue:failed`, dan `storage/logs/queue-worker.log`. |
| Email tidak tiba | SMTP provider, kredensial, port outbound, alamat pengirim, SPF/DKIM, queue worker, dan `CONTACT_NOTIFICATION_EMAIL`. Pesan tetap tersedia di inbox. |
| PHP CLI berbeda dari situs | Jalankan `/www/server/php/83/bin/php` secara eksplisit pada Composer, Artisan, cron, dan Supervisor. |
| Perubahan `.env` tidak terlihat | `php artisan config:cache`, restart worker dan SSR. |

Rujukan resmi: [Laravel deployment](https://laravel.com/framework/docs/deployment), [Inertia SSR](https://inertiajs.com/docs/v3/advanced/server-side-rendering), [aaPanel PHP Project](https://www.aapanel.com/docs/Function/php.html), [aaPanel site FAQ](https://www.aapanel.com/docs/faq/Site_Related.html).
