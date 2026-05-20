# KPI Tratama

Aplikasi KPI Tratama adalah sistem Laravel + Vue/Inertia untuk menggantikan Google Form KPI, HR, dan operasional yang sebelumnya dikumpulkan dari `gform_extracted`.

Target produksi aplikasi:

- URL aplikasi: `https://kpi.pondokkomputerlangkat.web.id`
- Server: `pondokkomputerlangkat@ns1.langkatkab.go.id`
- Root subdomain Virtualmin: `/home/pondokkomputerlangkat/domains/kpi.pondokkomputerlangkat.web.id`
- Web root publik: `public_html` diarahkan ke folder Laravel `public`

## Pedoman KPI

Dokumentasi skema penilaian KPI tersedia sebagai halaman HTML statis:

- Lokal: `/docs/skema-penilaian-kpi.html`
- Produksi: `https://kpi.pondokkomputerlangkat.web.id/docs/skema-penilaian-kpi.html`

Halaman ini menjelaskan rumus skor berbobot, status submission, tipe indikator, reward, dan contoh perhitungan bulanan.

## Stack

- Laravel 13
- PHP 8.3 atau lebih baru
- Vue 3 + Inertia.js
- Tailwind CSS 4
- SQLite untuk pengembangan lokal
- MySQL/MariaDB untuk produksi

## Modul Utama

- Login berbasis NIK dan password.
- Role aplikasi: `karyawan`, `admin`, dan `owner`.
- Portal karyawan untuk mengisi form dinamis, upload bukti, dan melihat status submission.
- Admin panel untuk review submission, approve/reject, koreksi skor/unit, form builder, KPI indicator, master outlet, jabatan, dan employee.
- Owner dashboard untuk laporan bulanan per karyawan dan outlet.
- Form template berversi agar perubahan field tidak merusak histori submission lama.
- Seed awal dari 11 file ekstraksi Google Form di `../gform_extracted`.

## Setup Lokal

```bat
cd kpi-tratama
composer install
npm.cmd install
copy .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm.cmd run build
php artisan serve --host=127.0.0.1 --port=8000
```

Untuk mode pengembangan dengan Vite:

```bat
npm.cmd run dev
```

Jika PowerShell memblokir perintah `npm`, gunakan `npm.cmd`.

## Akun Demo Lokal

Akun demo hanya dibuat ketika `APP_ENV=local` atau `APP_ENV=testing`.

- Owner: `OWNER001`
- Admin: `ADM001`
- Karyawan: `EMP001` sampai `EMP007`
- Password lokal: `password`

Akun demo tidak ditampilkan di halaman login publik dan tidak dibuat otomatis di produksi.

## Keamanan Kredensial

- File `.env`, `.env.production`, `auth.json`, `/vendor`, `/node_modules`, `/public/build`, dan `/public/hot` tidak masuk Git.
- Jangan commit credential database, app key, token, atau password server.
- Produksi memakai `APP_DEBUG=false` dan `APP_URL=https://kpi.pondokkomputerlangkat.web.id`.
- Akun owner produksi dapat dibuat atau dirotasi dari environment variable `SEED_OWNER_*`, tanpa default password.
- File `public/hot` harus dihapus di produksi. Jika file ini ada, Laravel akan memuat asset dari Vite dev server seperti `http://127.0.0.1:5173` dan halaman bisa blank karena CORS.

Contoh konfigurasi produksi tersedia di `.env.production.example`.

## Seed Owner Produksi

Untuk membuat atau merotasi owner produksi, isi variable berikut di `.env` server:

```dotenv
SEED_OWNER_NIK=OWNER001
SEED_OWNER_NAME="Owner Tratama"
SEED_OWNER_EMAIL=owner@kpi.pondokkomputerlangkat.web.id
SEED_OWNER_PASSWORD="gunakan-password-kuat"
```

Lalu jalankan:

```bash
php artisan db:seed --force
```

Seeder produksi akan menonaktifkan akun demo `ADM001` dan `EMP001` sampai `EMP007`, lalu membuat atau memperbarui akun owner sesuai variable di atas.

## Build dan Test

```bat
npm.cmd run build
php artisan test
php vendor\bin\pint
```

## Deploy Virtualmin

Deploy dilakukan ke subdomain, bukan domain utama:

```bash
cd /home/pondokkomputerlangkat/domains/kpi.pondokkomputerlangkat.web.id
git pull --ff-only origin main
composer install --no-dev --optimize-autoloader
npm ci
npm run build
rm -f public/hot
php artisan migrate --force
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
chmod -R ug+rw storage bootstrap/cache
```

Pastikan `.env` server tetap berada di server dan tidak ditimpa oleh proses deploy.

## Verifikasi Produksi

Setelah deploy, cek:

- HTML `https://kpi.pondokkomputerlangkat.web.id` tidak mengandung `127.0.0.1` atau `@vite`.
- Asset CSS/JS dimuat dari `/build/assets/...`.
- Halaman pedoman KPI aktif di `/docs/skema-penilaian-kpi.html`.
- Login tidak menampilkan akun demo atau password default.
