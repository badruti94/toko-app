<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Toko App — Setup & Cara Menjalankan

Aplikasi toko sederhana berbasis **Laravel 13 + Laravel Breeze** (autentikasi) dengan **CRUD produk**.

Yang sudah ada di proyek ini:

- Autentikasi lengkap dari Breeze: login, register, lupa/reset password, konfirmasi password, verifikasi email.
- Dashboard dan halaman profil (ubah nama/email, ubah password, hapus akun).
- CRUD produk: model `Product` + tabel `products` + controller resource + view index/create/edit.
  Semua route produk dilindungi middleware `auth`, jadi harus login dulu.
- Frontend: **Vite + Tailwind CSS + Alpine.js**.

| Komponen | Versi (terpasang di mesin ini) |
| --- | --- |
| Laravel Framework | 13.40.0 |
| PHP | ^8.3 (di mesin ini 8.3.6) |
| Composer | 2.x (di mesin ini 2.7.1) |
| Database | PostgreSQL 16.11 — database `toko_app` (lihat `.env`) |
| Node.js + npm | v22.22.3 / 10.9.8 |
| Vite | 8.3.7 (`laravel-vite-plugin` 3.2.1) |
| Tailwind CSS | 3.4.19 (mode v3 + PostCSS) |
| Alpine.js | 3.x (di-import dari `resources/js/app.js`) |

---

## 1. Prasyarat

```sh
php -v          # butuh >= 8.3
composer -V
node -v && npm -v
psql --version  # PostgreSQL
```

Ekstensi PHP yang dipakai: `pdo_pgsql`, `mbstring`, `openssl`, `curl`, `fileinfo`, `tokenizer`,
`xml`, `ctype`, `dom`, `filter`, `hash`, `session`.

```sh
# cek ekstensi yang sudah terpasang
php -m | grep -iE "pdo_pgsql|mbstring|openssl|curl|fileinfo|tokenizer|xml"

# kalau ada yang kurang (Ubuntu/Debian)
sudo apt install php8.3-pgsql php8.3-mbstring php8.3-xml php8.3-curl php8.3-bcmath

# HANYA kalau mau memakai SQLite (lihat bagian 3.2 dan bagian 8)
sudo apt install php8.3-sqlite3
```

> **Catatan untuk mesin ini:** yang terpasang hanya `pdo_pgsql`. `pdo_sqlite`/`sqlite3`
> **belum ada**, sehingga (a) opsi database SQLite gagal dengan `could not find driver`, dan
> (b) `php artisan test` ikut gagal karena `phpunit.xml` memakai SQLite in-memory.
> Karena itu proyek ini berjalan di PostgreSQL — lihat bagian 8 untuk solusi test-nya.

---

## 2. Setup pertama kali (jalankan berurutan)

```sh
# 1. Dependensi PHP
composer install

# 2. File environment — .env TIDAK ikut ter-commit (ada di .gitignore)
cp .env.example .env

# 3. Kunci aplikasi (wajib, kalau kosong semua halaman error 500)
php artisan key:generate

# 4. Atur kredensial database di .env   -> bagian 3
# 5. Buat database-nya                  -> bagian 3
# 6. Jalankan migrasi                   -> bagian 4

# 7. Dependensi frontend
npm install          # sama dengan: npm i

# 8. Build aset CSS/JS (wajib, kalau tidak: "Vite manifest not found")
npm run build
```

Alternatif satu perintah — script `setup` sudah ada di `composer.json`:

```sh
composer run setup
```

Isi script itu: `composer install` → copy `.env` → `key:generate` → `migrate --force` →
`npm install --ignore-scripts` → `npm run build`.
Perlu diperhatikan: `migrate --force` **tidak membuat database**, jadi database tetap harus dibuat
lebih dulu (bagian 3).

---

## 3. Atur database

Konfigurasi dibaca dari `.env` oleh `config/database.php` (nilai default di file itu `sqlite`,
tetapi `.env` proyek ini memakai `pgsql`).

### 3.1 Opsi A — PostgreSQL (yang dipakai proyek ini)

Isi `.env`:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=toko_app
DB_USERNAME=postgres
DB_PASSWORD=
```

> Isi `DB_PASSWORD` dengan password user PostgreSQL-mu (kosongkan bila server memakai
> `trust`/peer auth). Jangan menaruh komentar di belakang baris nilai pada `.env`.

Buat database-nya (**`php artisan migrate` tidak membuat database** — langkah ini sering terlewat):

```sh
# cara 1
createdb -h 127.0.0.1 -U postgres toko_app

# cara 2 — lewat psql
psql -h 127.0.0.1 -U postgres -c "CREATE DATABASE toko_app;"

# cek sudah ada
psql -h 127.0.0.1 -U postgres -lqt | cut -d'|' -f1 | grep -w toko_app
```

### 3.2 Opsi B — SQLite

```dotenv
DB_CONNECTION=sqlite
# DB_DATABASE=            # kosongkan -> otomatis memakai database/database.sqlite
```

```sh
touch database/database.sqlite
```

Butuh ekstensi `pdo_sqlite` (belum terpasang di mesin ini). File `database/database.sqlite` yang
0 byte di repo adalah sisa skeleton dan **tidak dipakai** selama `DB_CONNECTION=pgsql`.

### 3.3 Setelah mengubah `.env`

```sh
php artisan config:clear    # supaya perubahan .env langsung terbaca
```

---

## 4. Migrasi database

### Cek status

```sh
php artisan migrate:status
```

Keluaran saat semua sudah jalan (kondisi proyek ini sekarang):

```
 Migration name ................................... Batch / Status
 0001_01_01_000000_create_users_table ............. [1] Ran
 0001_01_01_000001_create_cache_table ............. [1] Ran
 0001_01_01_000002_create_jobs_table .............. [1] Ran
 2026_11_10_132046_create_products_table .......... [2] Ran
```

Status lain yang mungkin muncul: `Pending` (belum dijalankan) dan `Not found` (file migrasinya
sudah tidak ada, tetapi catatan di tabel `migrations` masih tertinggal).

### Jalankan

```sh
php artisan migrate
```

Kalau sudah pernah jalan:

```
 INFO  Nothing to migrate.
```

Tabel yang terbentuk (10 tabel):

| Tabel | Isi |
| --- | --- |
| `users`, `password_reset_tokens` | akun & reset password |
| `sessions`, `cache`, `cache_locks` | dipakai karena `SESSION_DRIVER=database`, `CACHE_STORE=database` |
| `jobs`, `job_batches`, `failed_jobs` | dipakai karena `QUEUE_CONNECTION=database` |
| `products` | data produk (`id`, `name`, `price`, `description`, `timestamps`) |
| `migrations` | pencatat migrasi yang sudah jalan |

### Perintah lain yang sering dipakai

| Perintah | Fungsi | Aman? |
| --- | --- | --- |
| `php artisan migrate --force` | migrasi tanpa konfirmasi (produksi/CI) | aman, hanya menjalankan yang `Pending` |
| `php artisan db:seed` | isi data awal (`DatabaseSeeder` membuat user `test@example.com`) | menambah data |
| `php artisan migrate:rollback` | batalkan batch migrasi terakhir | menghapus tabel batch terakhir |
| `php artisan migrate:refresh` | rollback semua lalu migrate ulang | **menghapus semua data** |
| `php artisan migrate:fresh --seed` | drop semua tabel → migrate → seed | **menghapus semua data** |
| `php artisan migrate:reset` | rollback semua migrasi | **menghapus semua tabel** |
| `php artisan db:show` / `db:table products` | lihat info database/tabel | hanya baca |
| `php artisan db:wipe` | drop semua tabel/view/type | **destruktif, hindari** |

`migrate:fresh` **menghapus akun dan produk** yang sudah ada. Kalau hanya ingin menyiapkan tabel
baru, cukup `php artisan migrate`.

Memeriksa hasil migrasi:

```sh
php artisan db:show
psql -h 127.0.0.1 -U postgres -d toko_app -c "\dt"
psql -h 127.0.0.1 -U postgres -d toko_app -c "select * from migrations order by batch, id;"
psql -h 127.0.0.1 -U postgres -d toko_app -c "select id, name, price from products;"
```

---

## 5. Frontend: `npm install` dan `npm run build`

Aset CSS/JS **harus** dibangun, karena `layouts/app.blade.php`, `layouts/guest.blade.php`, dan
`welcome.blade.php` memakai `@vite([...])`. Kalau `public/build/manifest.json` tidak ada, Laravel
melempar error `Vite manifest not found at: .../public/build/manifest.json`.

```sh
npm install          # alias: npm i  -> memasang dependensi dari package.json
npm run build        # menjalankan `vite build` -> hasil ke public/build/
```

Keluaran nyata di mesin ini:

```
$ npm i
up to date, audited 182 packages in 1s
found 0 vulnerabilities

$ npm run build
vite v8.3.7 building client environment for production...
✓ 4 modules transformed.
public/build/manifest.json             0.33 kB │ gzip:  0.16 kB
public/build/assets/app-7n1lRSbA.css  47.58 kB │ gzip:  8.70 kB
public/build/assets/app-Dr67-uCa.js   54.32 kB │ gzip: 19.13 kB
✓ built in 1.52s
```

### Mode development (hot reload)

```sh
npm run dev          # vite dev server, menulis file public/hot
```

Selama `public/hot` ada, Laravel mengambil aset dari dev server. Kalau `npm run dev` dimatikan
tetapi `public/hot` masih tertinggal, aset gagal dimuat (404) — solusinya jalankan `npm run dev`
lagi, atau hapus `public/hot` lalu `npm run build`.

### Catatan Tailwind

Proyek ini memakai **Tailwind v3** (`tailwind.config.js` + `postcss.config.js`, dan
`resources/css/app.css` berisi `@tailwind base; @tailwind components; @tailwind utilities;`).
Di `package.json` masih ada `@tailwindcss/vite` v4.3.5 (sisa percobaan Tailwind v4) tetapi
**tidak dipakai**, karena `vite.config.js` tidak memuat plugin tersebut. Jadi wajar kalau ada dua
versi Tailwind terpasang — yang aktif cuma v3. Kalau nanti mau pindah ke Tailwind v4:
`resources/css/app.css` diganti menjadi `@import "tailwindcss";`, daftarkan `@tailwindcss/vite`
di `vite.config.js`, dan `tailwind.config.js` tidak diperlukan lagi.

---

## 6. Menjalankan aplikasi

### Cara cepat: satu perintah untuk semua proses

```sh
php artisan dev
```

Menjalankan 4 proses sekaligus (`php artisan dev:list`):

| Nama | Perintah | Kegunaan |
| --- | --- | --- |
| server | `php artisan serve` | web server http://localhost:8000 |
| queue | `php artisan queue:listen --tries=1 --timeout=0` | memproses job |
| logs | `php artisan pail --timeout=0` | log realtime |
| vite | `npm run dev` | hot reload CSS/JS |

Buka **http://localhost:8000** (sesuai `APP_URL`).

### Cara manual

```sh
# terminal 1
php artisan serve                 # default 127.0.0.1:8000
# port sudah dipakai? -> php artisan serve --port=8001

# terminal 2
npm run dev
```

### Halaman yang bisa dibuka

| URL | Keterangan |
| --- | --- |
| `/` | halaman selamat datang (publik) |
| `/login`, `/register`, `/forgot-password` | autentikasi (publik) |
| `/up` | health check bawaan framework |
| `/dashboard` | **perlu login** |
| `/profile` | **perlu login** |
| `/products` | **perlu login** — daftar produk |
| `/products/create` | **perlu login** — form tambah produk |
| `/products/{id}/edit` | **perlu login** — form ubah produk |

Membuka `/products` tanpa login akan dialihkan otomatis ke `/login` (middleware `auth`).
Akun dibuat sendiri lewat `/register`; di database lokal sudah ada 1 akun terdaftar.

### Web server lain

- Apache/Nginx → document root harus diarahkan ke folder **`public/`** (`public/.htaccess` sudah ada).
- `php -S` langsung dari root proyek: `php -S localhost:8000 -t public`.

---

## 7. Ringkasan perintah harian

```sh
composer install                  # setelah pull / composer.json berubah
npm install                       # setelah pull / package.json berubah
php artisan migrate:status        # cek migrasi
php artisan migrate               # jalankan migrasi yang belum jalan
php artisan config:clear          # setelah mengubah .env
php artisan route:list            # daftar route
npm run build                     # build aset untuk mode produksi
npm run dev                       # vite dev server (hot reload)
php artisan dev                   # serve + queue + log + vite sekaligus
php artisan test                  # jalankan test (lihat bagian 8)
```

---

## 8. Menjalankan test (penting)

`phpunit.xml` memaksa `DB_CONNECTION=sqlite` + `DB_DATABASE=:memory:`. Karena ekstensi
`pdo_sqlite` belum terpasang, hasil `php artisan test` di mesin ini:

```
{"tool":"phpunit","result":"failed","tests":25,"passed":2,"assertions":2,
 "errors":23,"error_details":[{"message":"could not find driver
 (Connection: sqlite, Database: :memory:, SQL: ...)"}]}
```

23 test gagal dengan pesan yang sama (bukan karena kode aplikasinya salah). Ada dua jalan keluar:

**Opsi 1 — pasang ekstensi SQLite (paling praktis, test cepat)**

```sh
sudo apt install php8.3-sqlite3
php artisan test
```

**Opsi 2 — arahkan test ke PostgreSQL (sudah diuji di mesin ini, lulus semua)**

```sh
createdb -h 127.0.0.1 -U postgres toko_app_test

DB_CONNECTION=pgsql DB_DATABASE=toko_app_test php artisan test
# hasil: passed, 25 test, 61 assertion, ±4,4 detik

dropdb -h 127.0.0.1 -U postgres toko_app_test     # bersih-bersih setelah selesai
```

Variabel `DB_CONNECTION`/`DB_DATABASE` di depan perintah menimpa nilai di `phpunit.xml` (Laravel
tidak menimpa environment variable yang sudah ada), jadi `phpunit.xml` tidak perlu diubah.
Kalau ingin permanen: ubah `<env name="DB_CONNECTION" .../>` di `phpunit.xml` menjadi `pgsql`,
tambahkan `DB_USERNAME`/`DB_PASSWORD`, lalu jadikan `createdb toko_app_test` bagian dari setup.

---

## 9. Troubleshooting

Semua pesan error di bawah ini adalah kondisi nyata di mesin ini.

| Gejala | Penyebab | Solusi |
| --- | --- | --- |
| `SQLSTATE[08006] [7] ... database "toko_app" does not exist` | database belum dibuat; `migrate` dan `migrate:status` ikut gagal | `createdb -h 127.0.0.1 -U postgres toko_app` |
| `php artisan test` gagal, 23 test error `could not find driver` | `phpunit.xml` memakai SQLite in-memory, `pdo_sqlite` belum ada | bagian 8: pasang `php8.3-sqlite3`, atau jalankan test dengan `DB_CONNECTION=pgsql` |
| `could not find driver` dengan `DB_CONNECTION=sqlite`/`pgsql` | ekstensi PHP-nya belum terpasang | `sudo apt install php8.3-sqlite3` / `php8.3-pgsql` |
| `SQLSTATE[28P01] password authentication failed` | `DB_USERNAME`/`DB_PASSWORD` salah | perbaiki `.env` lalu `php artisan config:clear` |
| `No application encryption key has been specified` | `APP_KEY` kosong | `php artisan key:generate` |
| Error 500 `relation "sessions"/"cache"/"jobs" does not exist` | driver `database` dipakai tapi migrasi belum jalan | `php artisan migrate` |
| `Vite manifest not found at: .../public/build/manifest.json` | aset belum dibangun | `npm install` lalu `npm run build` |
| CSS/JS 404 padahal `npm run build` sudah jalan | `public/hot` tertinggal dari `npm run dev` yang sudah mati | hapus `public/hot`, atau jalankan `npm run dev` |
| `Failed to listen on ...:8000 (Address already in use)` | ada server lain di port 8000 | `ss -ltnp \| grep :8000`, matikan prosesnya, atau `php artisan serve --port=8001` |
| `Permission denied` pada `storage/` atau `bootstrap/cache/` | permission folder | `chmod -R ug+rwx storage bootstrap/cache` |
| Perubahan `.env` tidak berefek | config ter-cache | `php artisan config:clear` (atau `php artisan optimize:clear`) |
| Style Tailwind tidak berubah setelah mengedit view/CSS | aset belum dibangun ulang | `npm run build`, atau jalankan `npm run dev` saat mengembangkan |

Log aplikasi: `storage/logs/laravel.log`, dipantau realtime dengan `php artisan pail`
atau `tail -f storage/logs/laravel.log`.

---

## 10. Struktur proyek (yang penting)

```
app/
  Http/Controllers/
    Auth/                          controller autentikasi (Breeze)
    ProductController.php          CRUD produk (index, create, store, edit, update, destroy)
    ProfileController.php          halaman profil
  Http/Requests/                   validasi form (LoginRequest, ProfileUpdateRequest)
  Models/Product.php               model produk (fillable: name, price, description)
  Models/User.php
  View/Components/                 komponen layout Breeze (AppLayout, GuestLayout)
config/database.php                definisi koneksi database
database/migrations/
  0001_01_01_000000_create_users_table.php
  0001_01_01_000001_create_cache_table.php
  0001_01_01_000002_create_jobs_table.php
  2026_11_10_132046_create_products_table.php
database/seeders/DatabaseSeeder.php
public/
  index.php                        front controller
  .htaccess                        rewrite Apache
  build/                           hasil npm run build (di-gitignore)
resources/views/
  products/                        index, create, edit
  auth/                            login, register, reset password, verifikasi email
  layouts/                         app, guest, navigation
  components/                      tombol, input, dropdown, modal, dll
routes/web.php                     route utama (auth + profile + products)
routes/auth.php                    route autentikasi dari Breeze
tests/Feature/                     test Auth + Profile
tailwind.config.js, postcss.config.js    konfigurasi Tailwind v3
vite.config.js                     konfigurasi Vite
```

---

## 11. Catatan

- Repositori git sudah di-`init` (branch `main`) tetapi **belum ada satu pun commit** — semua file
  masih berstatus untracked.
- Yang di-gitignore: `.env`, `/vendor`, `/node_modules`, `/public/build`, `/public/hot`, `*.log`.
  Artinya setelah clone baru wajib menjalankan `composer install`, `npm install`, `npm run build`,
  dan membuat `.env`.
- `AGENTS.md` / `CLAUDE.md` berisi panduan untuk AI coding agent (Laravel Boost); boleh diabaikan
  kalau tidak memakainya.
- `ProductController::show()` sekarang hanya mengalihkan ke daftar produk (belum ada halaman detail).
- `public/favicon.ico` masih 0 byte (sisa skeleton).
- Materi resmi: <https://laravel.com/docs>, <https://laravel.com/docs/starter-kits>, <https://laracasts.com>.
