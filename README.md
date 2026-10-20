# Portofolio & Riset — Studio

Aplikasi portofolio pribadi, publikasi riset, dan catatan berbasis Laravel + Vue 3. Konten dikelola melalui `/admin`. Halaman publik menggunakan server-side rendering (SSR), dengan tampilan gradient, bento grid, glassmorphism, serta tema gelap/terang.

**Panduan server:** [Deploy ke aaPanel](docs/deploy-aapanel.md). **Hasil pemeriksaan:** [Validasi](docs/verification.md).

## Fitur

- Beranda, profil, pengalaman, pendidikan, keahlian, dan tautan sosial.
- Proyek dengan studi kasus, teknologi, galeri, demo, dan repository.
- Riset dengan abstrak, penulis berurutan, afiliasi, DOI, venue, status akademik, PDF, dataset, kode, sitasi, dan BibTeX.
- Catatan dengan Markdown yang disanitasi dan preview versi tersimpan.
- Pencarian, filter, pagination, featured, pengaturan urutan, draft/published, soft delete, dan restore sebagai draft.
- Admin: login, reset password opsional melalui SMTP, penggantian password, CRUD konten/taksonomi/profil, inbox, dan activity log.
- Upload privat: gambar diubah menjadi WebP maksimal lebar 1920 piksel. File maksimal 10 MB, gambar maksimal 24 megapiksel. SVG ditolak.
- Lampiran draft, konten dalam sampah, dan preview hanya dapat diakses admin. Semua unduhan dicek ulang oleh backend.
- Kontak dengan validasi, honeypot, rate limiting, deduplikasi submission UUID, serta notifikasi queue. Pesan tersimpan sebelum email dikirim.
- SEO: HTML awal melalui SSR, canonical, Open Graph, Twitter card, JSON-LD WebPage, sitemap published-only, dan noindex admin.
- Responsif, focus indicator, keyboard navigation, reduced-motion, dan halaman error 403/404/419/429/500/503.

Tidak ada akun/password produksi bawaan. Instalasi baru tidak mengarang profil atau memublikasikan konten contoh.

## Stack yang dikunci

| Komponen | Versi yang diuji |
| --- | --- |
| PHP | 8.3.33; aplikasi mensyaratkan PHP 8.3+ |
| Laravel | 13.37.0 |
| Inertia server / Vue adapter | 3.5.1 / 3.9.1 |
| Vue | 3.5.44 |
| TypeScript | 5.9.3 |
| Vite / Tailwind | 8.3.4 / 4.3.3 |
| Node.js / npm | 22.23.2 / 10.9.8 |
| MariaDB | 11.4.13 |
| PHPUnit / Playwright | 12.5.40 / 1.64.1 |

`composer.lock` dan `package-lock.json` menjadi sumber versi pasti. Gunakan `composer install` dan `npm ci`, bukan update dependensi saat deployment rutin. SQLite tersedia untuk development/test; produksi menggunakan MySQL 8 atau MariaDB 10.11+ (jalur yang diuji: MariaDB 11.4).

Extension PHP: ctype, curl, dom, fileinfo, filter, hash, mbstring, openssl, pdo, pdo_mysql, session, tokenizer, xml, zip, bcmath, dan **gd dengan dukungan WebP**. Untuk tes SQLite tambahkan pdo_sqlite. Gunakan memory_limit minimal 256M untuk pemrosesan gambar. Node minimal 22.18; gunakan versi terbaru yang kompatibel pada cabang Node 22.

## Instalasi lokal

```bash
git clone https://github.com/Igustisultanh12/porto.git
cd porto
composer install
cp .env.example .env
php artisan key:generate
```

Pada PowerShell, ganti `cp` dengan `Copy-Item .env.example .env`. Jalankan `key:generate` hanya pada instalasi baru. Untuk SQLite, buat file kosong `database/database.sqlite`; pada PowerShell: `New-Item database/database.sqlite -ItemType File` jika belum ada. Untuk MySQL/MariaDB, buat database kosong lalu atur:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=porto
DB_USERNAME=porto
DB_PASSWORD="ISI_PASSWORD_DATABASE"
```

Selanjutnya:

```bash
php artisan migrate
php artisan storage:link
php artisan app:create-admin
npm ci
npm run build
```

`app:create-admin` meminta nama, email, dan password secara interaktif. Password tidak tampil di layar dan harus minimal 12 karakter dengan huruf besar, huruf kecil, dan angka. Tidak ada registrasi publik.

Untuk preview build produksi, jalankan di terminal terpisah:

```bash
php artisan serve --host=127.0.0.1 --port=8000
php artisan inertia:start-ssr
php artisan queue:work --tries=3
php artisan schedule:work
```

Atur `APP_URL=http://127.0.0.1:8000`. Buka situs di alamat tersebut dan admin di `/admin/login`. Untuk mengembangkan frontend gunakan `npm run dev` sebagai pengganti build + proses SSR terpisah; plugin Inertia menyediakan SSR development. Jangan menjalankan Vite development pada produksi. Setelah build baru, restart proses SSR.

PHP CLI Windows kadang belum mengaktifkan GD. Aktifkan `extension=gd` dalam php.ini CLI dan FPM yang sesuai. Periksa dengan `php --ini` dan `php --ri gd`. Untuk satu proses tes dapat menggunakan `php -d extension=gd vendor/bin/phpunit`; opsi ini tidak otomatis diteruskan ke proses PHP anak.

### Demo opsional

```bash
php artisan db:seed --class=DemoSeeder
```

Membuat tiga konten `[DEMO]` **draft**. Demo tidak membuat akun admin atau profil palsu. Seeder default kosong. Ganti konten demo dengan karya asli sebelum diterbitkan.

## Variabel lingkungan

| Variabel | Penggunaan |
| --- | --- |
| `APP_ENV`, `APP_DEBUG`, `APP_URL` | `production`, `false`, dan URL HTTPS domain di server |
| `APP_KEY` | Kunci enkripsi permanen; backup bersama `.env` |
| `APP_LOCALE` | `id` untuk UI/validasi Indonesia |
| `DB_*` | Koneksi database; gunakan akun aplikasi tersendiri |
| `SESSION_DRIVER` | `database`; tabel tersedia melalui migration |
| `SESSION_ENCRYPT` | `true` pada contoh konfigurasi |
| `SESSION_SECURE_COOKIE` | `true` pada HTTPS produksi; `false` pada localhost HTTP |
| `CACHE_STORE` | `database`; digunakan rate limit dan lock queue/scheduler |
| `QUEUE_CONNECTION` | `database`; tidak membutuhkan Redis |
| `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_SCHEME`, `MAIL_USERNAME`, `MAIL_PASSWORD` | SMTP; development menggunakan mailer log |
| `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` | Pengirim email yang diverifikasi provider |
| `CONTACT_NOTIFICATION_EMAIL` | Penerima notifikasi inbox; kosong menonaktifkan email tanpa mematikan form |
| `PASSWORD_RESET_ENABLED` | Aktifkan hanya setelah SMTP diuji |
| `INERTIA_SSR_ENABLED`, `INERTIA_SSR_URL` | `true`, `http://127.0.0.1:13714` |
| `INERTIA_SSR_THROW_ON_ERROR` | `true` dalam tes browser; produksi biasanya false dengan pemantauan log |

Kredensial tidak disimpan lewat admin dan tidak masuk Git. Email kontak publik pada profil terpisah dari email notifikasi privat di `.env`.

## Mengelola situs

1. Masuk ke `/admin`, lalu lengkapi **Profil & situs**: nama, headline, bio, lokasi, minat riset, email publik, SEO, foto, logo, favicon, dan CV.
2. Tambahkan keahlian, pengalaman, pendidikan, atau tautan sosial lewat entri profil. Angka urutan lebih kecil ditampilkan lebih awal.
3. Siapkan kategori, tag, teknologi, dan topik melalui **Kategori & tag**.
4. Buat konten, pilih jenisnya, isi metadata, lalu simpan draft. Sesudah tersimpan, upload cover/galeri/PDF.
5. Preview menampilkan versi terakhir yang disimpan. HTML mentah Markdown dihapus dan link berbahaya ditolak.
6. Ubah status situs ke **Published** lalu simpan. Pilih **Tampilkan di beranda** untuk konten unggulan.
7. Status akademik riset seperti `submitted` atau `accepted` terpisah dari status situs. Sitasi memakai metadata yang diisi; ekspor BibTeX menggunakan tipe netral `misc` agar tidak mengklaim artikel jurnal secara otomatis.
8. Bulk action tersedia setelah memilih baris konten. Restore selalu menjadi draft. Pengubahan urutan juga tersedia lewat tombol −/+ dan input angka pada editor.
9. Inbox menyediakan penandaan dibaca, belum dibaca, arsip, pemulihan arsip, dan penghapusan permanen dengan konfirmasi.

Saat berkas dilepas/diganti, record aksesnya dihapus tetapi byte privat lama dipertahankan untuk pemulihan backup. Berkas tanpa record tidak bisa diunduh dari web. Pantau penggunaan disk; tidak ada pembersihan otomatis yang berisiko menghapus aset referensi.

## Struktur

```text
app/Http/Controllers/       Publik, admin, autentikasi, media, kontak
app/Http/Requests/          Validasi konten, profil, kontak
app/Models/                Konten, taksonomi, media, profil, inbox, log
app/Policies/              Otorisasi konten
app/Jobs/                  Notifikasi inbox dengan retry
app/Console/Commands/      Pembuatan admin interaktif
database/migrations/       Schema dan indeks
database/seeders/          Demo opsional dan fixture browser terisolasi
resources/js/pages/        Halaman Vue publik dan admin
resources/js/components/   Komponen bersama
resources/js/i18n.ts        Label domain untuk persiapan penerjemahan
resources/css/             Tema dan layout responsif
storage/app/private/       Semua upload (jangan dibuka ke web)
docs/                      aaPanel, Nginx, Supervisor, hasil pemeriksaan
tests/                     Feature, unit, dan Playwright
.github/workflows/ci.yml   Build, tes, MariaDB, audit, dan browser
```

String antarmuka memakai bahasa Indonesia. Label domain sudah dipisahkan; pemilihan bahasa kedua belum diimplementasikan. Tidak ada analytics pihak ketiga atau cookie tracking tambahan.

## Pemeriksaan

```bash
php vendor/bin/phpunit
npm run typecheck
npm run lint
npm run format:check
npm run build
composer audit
npm audit
```

Tes default menggunakan SQLite in-memory dan tidak mengubah database aplikasi. Untuk MariaDB, arahkan variabel `DB_*` proses tes ke **database pengujian kosong tersendiri**, lalu jalankan `php vendor/bin/phpunit tests/Feature/PortfolioTest.php`. Jangan gunakan database produksi karena tes menjalankan migrasi dan rollback data.

Tes browser:

```bash
npx playwright install chromium
php artisan inertia:start-ssr
# Terminal lain:
npm run test:e2e
```

Runner browser menyiapkan `.env.e2e`, database `database/e2e.sqlite`, password acak sementara, dan server port 8011. File database khusus ini direset setiap run. Seeder browser menolak lingkungan selain `e2e`. Screenshot dan trace kegagalan tersimpan di `test-results/`, tidak masuk Git. Pastikan port 8011 kosong dan SSR sesuai build terbaru. Stop SSR setelah selesai.

CI menjalankan SQLite dan MariaDB serta browser Chromium. Keberhasilan tes lokal bukan bukti bahwa server aaPanel pengguna sudah terpasang; deployment memerlukan konfigurasi domain, database, akun admin, dan SMTP di server.

## Operasional

Tidak ada cache konten publik persisten, sehingga publish/unpublish langsung memengaruhi daftar, detail, unduhan, dan sitemap. Query memakai eager loading, indeks, dan pagination; aset build Vite dapat dicache immutable di Nginx.

Notifikasi email dicoba tiga kali. Scheduler juga mengantrekan kembali pesan tanpa `notified_at` setiap 10 menit; jika SMTP terus gagal, perbaiki SMTP atau kosongkan `CONTACT_NOTIFICATION_EMAIL`. Pantau `queue:failed` dan log. Seperti pengiriman email umumnya, crash persis setelah provider menerima email dapat menghasilkan notifikasi ulang; pesan kontak tetap satu record berdasarkan submission UUID.

Backup database, `.env`/APP_KEY, dan `storage/app` sebagai satu kesatuan. Prosedur deploy, update, pemulihan, rollback, SSL, permission, dan troubleshooting tersedia pada [panduan aaPanel](docs/deploy-aapanel.md).

Rujukan: [Laravel deployment](https://laravel.com/framework/docs/deployment), [Inertia SSR](https://inertiajs.com/docs/v3/advanced/server-side-rendering), [Vue](https://vuejs.org/guide/quick-start.html), [aaPanel PHP Project](https://www.aapanel.com/docs/Function/php.html).
