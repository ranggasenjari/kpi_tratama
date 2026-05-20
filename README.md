# KPI Tratama

Aplikasi Laravel + Vue/Inertia untuk menggantikan Google Form KPI dan form operasional Tratama.

## 📄 Dokumen Pedoman & Skema Penilaian

Pedoman lengkap mengenai skema penilaian KPI Tratama dapat diakses secara langsung melalui tautan berikut:
- **[Panduan Pedoman Skema Penilaian KPI](/docs/skema-penilaian-kpi.html)**

Dokumen ini berisi rumus bobot, target frekuensi harian/bulanan, kriteria penilaian kebersihan, serta perhitungan reward berdasarkan ketercapaian masing-masing indikator KPI.

---

## 🛠️ Stack Teknologi

- **Laravel 13** (PHP 8.4+)
- **Vue 3 + Inertia.js** (Sistem SPA yang dinamis dan interaktif)
- **Tailwind CSS 4** (Desain modern dan responsif)
- **SQLite** (Untuk pengembangan lokal yang cepat)
- **MariaDB/MySQL** (Untuk lingkungan produksi/live server)

> **Catatan Alat Portabel**: PHP dan Composer portabel tersedia di folder workspace `../.tools`. Wrapper perintah yang mudah digunakan dapat ditemukan di direktori `bin/`.

---

## 🚀 Jalankan Secara Lokal

Untuk memulai pengembangan di komputer lokal Anda, jalankan perintah berikut:

```bat
cd kpi-tratama
bin\artisan.cmd migrate:fresh --seed
npm.cmd install
npm.cmd run build
bin\dev.cmd
```

- **URL Default**: `http://127.0.0.1:8000`
- Jika PowerShell memblokir eksekusi `npm`, gunakan `npm.cmd` seperti contoh di atas.
- Jika ingin menjalankan mode lengkap dengan hot reloading Vite watcher, jalankan `composer dev` dari root proyek.

---

## 🔑 Akun Demo (Development)

Semua kata sandi untuk akun demo adalah: `password`

- **Owner**: `OWNER001`
- **Admin**: `ADM001`
- **Karyawan**: `EMP001`, `EMP002`, `EMP003`, `EMP004`, `EMP005`, `EMP006`, `EMP007`

---

## 📦 Modul Utama

1. **Login Berbasis NIK**: Keamanan login karyawan tanpa email menggunakan NIK unik.
2. **Form Builder Dinamis**: Membuat dan merilis templat form operasional dengan versioning.
3. **Portal Karyawan**: Pengisian formulir secara instan dan pengunggahan berkas bukti operasional.
4. **Review & Approval Admin**: Tinjauan mendalam oleh admin untuk menyetujui (*approve*) atau menolak (*reject*) pengajuan karyawan.
5. **Indikator KPI Dinamis**: Konfigurasi target, bobot bobot penilaian, tugas (assignment), dan sistem reward per tugas.
6. **Laporan Bulanan & Estimasi Reward**: Penghitungan skor KPI bulanan otomatis beserta estimasi nominal reward/insentif yang diperoleh.
7. **Pemuat Awal (Seeders)**: Membaca skema data secara otomatis dari 11 file ekstraksi markdown Google Form di folder `../gform_extracted`.

---

## 🔒 Keamanan & Pengamanan Kredensial

Proyek ini menerapkan standar keamanan terbaik untuk melindungi kredensial:
- Seluruh rahasia lingkungan (database, app key, debug mode) dikelola sepenuhnya di dalam file `.env` yang diabaikan oleh Git (`.gitignore`).
- File `.env.example` disediakan sebagai acuan konfigurasi tanpa mengandung nilai sensitif apapun.
- Di server produksi, app key diisolasi secara unik dan mode debug dinonaktifkan (`APP_DEBUG=false`).

---

## 🌐 Panduan Deployment Produksi

Aplikasi dideploy ke server remote:
- **Server**: `ssh pondokkomputerlangkat@ns1.langkatkab.go.id`
- **Subdomain**: `kpi.pondokkomputerlangkat.web.id`
- **Database**: MariaDB dengan nama DB `kpi`

### Langkah Verifikasi Lokal & Server

Sebelum mendeploy, jalankan verifikasi lokal:
```bat
bin\artisan.cmd test
npm.cmd run build
```

Saat dideploy, direktori web root subdomain di server diarahkan ke direktori `public` milik Laravel menggunakan *symbolic link*:
```bash
# Di server remote
mv public_html public_html_backup
ln -s public public_html
```
Hal ini memastikan struktur sistem internal Laravel tetap aman di luar jangkauan akses publik.
