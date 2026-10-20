# Hasil verifikasi

Validasi akhir dilakukan 20 Oktober 2026 pada lingkungan development Windows, PHP 8.3.33, Node 22.23.2, MariaDB portable 11.4.13, dan Chromium Playwright. Tes menggunakan database khusus dan data berlabel demo; tidak memakai data produksi.

| Pemeriksaan | Hasil |
| --- | --- |
| PHPUnit lengkap, SQLite in-memory | 28 tes lulus, 199 assertions |
| Suite fitur pada MariaDB 11.4.13 | 26 tes lulus, 197 assertions |
| Playwright Chromium | 2 skenario lulus |
| `npm run typecheck` | Lulus |
| `npm run lint` (ESLint 10) | Lulus |
| `npm run format:check` | Format Vue, TypeScript, CSS, dan konfigurasi konsisten |
| `npm run build` | Bundle client dan SSR berhasil |
| `composer validate --strict` | Valid, lockfile sesuai |
| `composer audit` | Tidak ada advisory kerentanan yang ditemukan saat pemeriksaan |
| `npm audit` | 0 vulnerabilities saat pemeriksaan |
| `php artisan route:cache` | Berhasil; cache lokal dibersihkan kembali |
| `php artisan view:cache` | Berhasil |
| `php artisan inertia:check-ssr` | SSR berjalan |
| Laravel Pint | Format PHP diperbaiki dan pemeriksaan akhir lulus |

## Perilaku yang diperiksa

- Pengunjung diarahkan ke login saat mengakses admin; akun non-admin tidak dapat mengubah situs atau menaikkan hak akses.
- Login/logout, penolakan password salah, registrasi publik nonaktif, perubahan password, serta reset password saat diaktifkan.
- Create/update/publish/soft-delete/restore untuk proyek, riset, dan artikel. Restore menjadi draft.
- Draft tidak bocor melalui detail, pencarian, sitemap, BibTeX, atau lampiran. Unpublish segera mencabut akses unduhan publik.
- Slug duplikat, status tidak valid, metadata tambahan, URL berbahaya, format DOI salah, dan input login/slug berupa array ditolak.
- Markdown menyaring HTML script dan URL JavaScript. Kartu daftar tidak mengirim seluruh body atau metadata lampiran.
- Filter publik, pagination, sitasi dengan urutan penulis, serta escaping BibTeX.
- Pengubahan profil, entri, dan taksonomi.
- Penolakan SVG dan upload terlalu besar. Gambar dioptimalkan menjadi WebP lebar 1920 dan tetap privat selama draft.
- Form kontak deduplikasi UUID, honeypot, rate limit, antrean notifikasi, serta persistensi pesan ketika SMTP gagal.
- Inbox: baca, arsip, dan hapus.
- Browser: admin login → membuat draft → publish → tampil di beranda/detail → pencarian → unpublish → publik mendapat 404.
- HTML awal detail memuat isi dan Open Graph; JSON-LD dapat diparse sebagai JSON. SSR browser test menggunakan mode throw-on-error.
- Request kontak tanpa CSRF ditolak 419; pengiriman dari form browser berhasil.
- Tema tersimpan setelah reload, halaman publik dan editor pada viewport 390 piksel tidak overflow horizontal, dan link internal yang diuji dapat dibuka.
- Tidak ada JavaScript page error dalam kedua skenario; skenario utama juga memeriksa console error.
- Screenshot beranda mobile dan editor desktop diperiksa secara visual.

## Batas verifikasi

- Server aaPanel pengguna, domain, sertifikat, akun database produksi, dan SMTP nyata belum dikonfigurasi atau diuji. Panduan dan konfigurasi contoh tersedia pada [deploy-aapanel.md](deploy-aapanel.md).
- Konfigurasi Nginx/Supervisor perlu disesuaikan dengan path, socket, dan user server. Tidak ada klaim bahwa `nginx -t` telah dijalankan pada server pengguna.
- Tes lokal tidak menjamin ketiadaan seluruh bug atau kerentanan. Audit dependensi menunjukkan kondisi saat diperiksa.
- Tidak ada benchmark trafik, skor Lighthouse, atau audit aksesibilitas formal yang diklaim.
- Pengiriman notifikasi email menggunakan model retry; crash setelah provider menerima email dapat mengulang notifikasi. Pesan kontak tetap dideduplikasi di database.
- Preview Markdown menampilkan versi yang sudah disimpan. Pemilihan bahasa tambahan dan publikasi terjadwal belum termasuk fitur.

Perintah untuk mengulangi pemeriksaan dan penjelasan isolasi database tersedia di [README](../README.md). Workflow `.github/workflows/ci.yml` mengulangi build, tes, audit, MariaDB, dan browser pada GitHub Actions; status run remote harus dibaca dari GitHub, bukan diasumsikan dari hasil lokal ini.
