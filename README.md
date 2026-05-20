# KPI Tratama

Aplikasi Laravel + Vue/Inertia untuk menggantikan Google Form KPI dan form operasional Tratama.

## Stack

- Laravel 13
- Vue 3 + Inertia
- Tailwind CSS 4
- SQLite untuk development lokal
- Siap diarahkan ke MySQL/MariaDB lewat `.env`

PHP dan Composer portable tersedia di folder workspace `../.tools`. Wrapper command ada di `bin/`.

## Jalankan Lokal

```bat
cd kpi-tratama
bin\artisan.cmd migrate:fresh --seed
npm.cmd install
npm.cmd run build
bin\dev.cmd
```

URL default: `http://127.0.0.1:8000`

Jika PowerShell memblokir `npm`, gunakan `npm.cmd` seperti contoh di atas.
Jika ingin mode lengkap dengan Vite watcher, jalankan `composer dev` dari root proyek.

## Akun Demo

Semua password demo: `password`

- Owner: `OWNER001`
- Admin: `ADM001`
- Karyawan: `EMP001`, `EMP002`, `EMP003`, `EMP004`, `EMP005`

## Modul

- Login berbasis NIK.
- Form builder dinamis dengan versioning template.
- Portal karyawan untuk submit form dan upload bukti.
- Review admin untuk approve/reject submission.
- KPI indicator dinamis dengan target, bobot, assignment, dan reward.
- Laporan bulanan skor KPI dan estimasi reward.
- Seeder membaca 11 file dari `../gform_extracted`.

## Verifikasi

```bat
bin\artisan.cmd test
npm.cmd run build
```
