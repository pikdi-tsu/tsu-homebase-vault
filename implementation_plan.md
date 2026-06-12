# Deployment Optimization Plan & Best Practices (tsu-homebase-vault)

Dokumen ini berisi rencana perubahan kode pada pipeline CI/CD (`deploy-dev.yml` dan `deploy.yml`) serta panduan struktur folder untuk proyek `tsu-homebase-vault`. Anda dapat menggunakan dokumen ini sebagai referensi di sesi/chat baru.

---

## 1. Perbaikan CI/CD Development (`.github/workflows/deploy-dev.yml`)

Skrip deployment untuk VPS perlu sedikit penyesuaian agar lebih tangguh dan menghindari *error permission*.

### Proposed Changes:
#### [MODIFY] `.github/workflows/deploy-dev.yml`
*   **Perbaikan Exclude Rsync:**
    Ubah bagian `EXCLUDE` untuk mencegah tertimpanya file lokal server seperti log dan cache, serta mengabaikan folder IDE.
    ```yaml
    EXCLUDE: "/.git/, /.github/, /sources/node_modules/, *.sqlite, /sources/vendor/, /.vscode/, /.idea/, /sources/storage/logs/"
    ```
*   **Perbaikan Hak Akses (Chown):**
    Jika SSH User bukan `root`, jalankan `chown` menggunakan `sudo`.
    ```bash
    sudo chown -R www-data:www-data storage bootstrap/cache
    ```
*   **Menambahkan Storage Link:**
    Wajib dijalankan agar gambar/file upload dapat diakses publik.
    ```bash
    php artisan storage:link
    ```

---

## 2. Perbaikan CI/CD Production (`.github/workflows/deploy.yml`)

Skrip deployment DirectAdmin FTP sudah sangat baik pada bagian `exclude`, namun perlu melengkapi perintah `artisan` pasca-deploy.

### Proposed Changes:
#### [MODIFY] `.github/workflows/deploy.yml`
*   **Optimalisasi Filament:**
    Tambahkan perintah cache untuk Filament.
    ```bash
    php artisan filament:optimize
    ```
*   **Menambahkan Storage Link:**
    Sama seperti di dev, wajib dijalankan.
    ```bash
    php artisan storage:link
    ```
*   **Penyederhanaan Cache Commands:**
    Ganti eksekusi cache satu-per-satu (`config:cache`, `route:cache`) menjadi `optimize` agar sama dengan Development.
    ```bash
    php artisan optimize:clear
    php artisan optimize
    ```
*   *(Opsional)* **Database Migration:**
    Jika ingin migrasi otomatis di production, tambahkan:
    ```bash
    php artisan migrate --force
    ```

---

## 3. Catatan Migrasi ke Struktur "Level Tertinggi" (Opsional)

Saat ini proyek Anda menggunakan pendekatan `sources/` di dalam `public_html/`. Ini sudah aman dan praktis. Namun, jika Anda ingin beralih ke *Best Practice* paling mutlak (menyimpan Core Laravel sejajar/di luar `public_html`), berikut langkah yang harus dilakukan tim dev Anda:

1.  **Pemisahan Folder di Server:**
    *   Buat folder `tsu_homebase_core/` sejajar dengan `public_html/`.
    *   Isi `public_html/` HANYA dengan file dari dalam folder `public/` Laravel (seperti `css`, `js`, `build`, `index.php`).
    *   Pindahkan sisa *core* aplikasi (termasuk folder `sources/`) ke dalam `tsu_homebase_core/`.
2.  **Ubah `index.php` (di dalam `public_html/`):**
    Arahkan path ke folder *core* yang baru:
    ```php
    require __DIR__.'/../tsu_homebase_core/vendor/autoload.php';
    $app = require_once __DIR__.'/../tsu_homebase_core/bootstrap/app.php';
    ```
3.  **Ubah AppServiceProvider:**
    Beritahu Laravel lokasi folder public yang baru:
    ```php
    public function register(): void
    {
        $this->app->bind('path.public', function() {
            return base_path('../public_html');
        });
    }
    ```
4.  **Penyesuaian CI/CD:**
    Anda harus merombak file `.yml` agar FTP/Rsync melakukan *deploy* ke dua target folder yang berbeda (satu ke `public_html/` dan satu lagi ke `tsu_homebase_core/`).

> [!TIP]
> **Rekomendasi:** Jika tim Anda merasa struktur saat ini (menyimpan `sources/` di dalam root dengan `index.php` modifikasi) sudah cukup aman dan mudah dikelola melalui CI/CD, Anda **TIDAK PERLU** merombaknya ke tingkat paling mutlak. Cukup implementasikan perbaikan di Poin 1 dan Poin 2 saja.
