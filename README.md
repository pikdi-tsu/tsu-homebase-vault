# <p align="center"> TSU Homebase <br> (Secured Folder Edition) </p>

## 📢 Description

Aplikasi TSU Homebase berbasis Laravel yang telah dikustomisasi mengikuti standar arsitektur direktori TSU. Proyek ini dirancang dengan tingkat keamanan ekstra (Subfolder Protection), di mana seluruh *core logic* dan dependensi aplikasi disembunyikan di balik direktori khusus untuk menghindari eksposur publik.

## 📋 Project Overview

Proyek ini merupakan hasil *refactoring* dari struktur standar Laravel. Struktur utama (`app/`, `vendor/`, `node_modules/`, `.env`, dll) telah dipindahkan ke dalam direktori kustom `sources/`.

Sementara itu, *entry point* aplikasi (seperti `index.php`, `.htaccess`, dan aset hasil *build* Vite) diletakkan tepat di *root* direktori. Hal ini memungkinkan aplikasi berjalan dengan aman di *environment* server tanpa perlu mengekspos direktori sistem ke publik.

## 🏗️ Struktur Direktori & Arsitektur

Perbedaan mendasar pada aplikasi ini adalah pemisahan antara area publik (*Root*) dan area terisolasi (*Sources*).

```text
root/
├── build/              # Aset terkompilasi (hasil Vite & Tailwind)
├── css/, images/, js/  # Aset statis publik murni
├── index.php           # Entry point utama (Jantung aplikasi)
├── .htaccess           # Gerbang keamanan Apache
└── sources/            # Direktori Utama Logika Aplikasi (Terisolasi)
    ├── app/            # Logika Global (Controllers, Models, dll)
    ├── bootstrap/      # Bootstrap & Cache aplikasi
    ├── routes/         # Definisi rute aplikasi
    └── vendor/         # Dependensi Composer
```

## 🛠️ Spesifikasi Teknis (Tech Stack)

- **Framework Core:** Laravel
- **Architecture Pattern:** Subfolder Protection / Shared Hosting Style
- **Frontend Stack:**
  - Vite (Asset Bundler)
  - Tailwind CSS (Ekosistem Oxide)
  - Blade Templating Engine
- **System Requirements:**
  - PHP >= 8.1
  - Node.js >= 20.x (Wajib untuk kompilasi Vite & Tailwind)

## ⚙️ Panduan Instalasi Lokal

Ikuti langkah berikut untuk mengatur lingkungan pengembangan lokal. **PENTING:** Hampir seluruh eksekusi *command line* dilakukan di dalam folder `sources/`.

1. **Clone Repository**
   ```bash
   git clone <repository_url> tsu-homebase
   ```

2. **Install Dependensi Backend (Composer)**
   Masuk ke direktori `sources/` dan jalankan instalasi. Pastikan menjalankan `dump-autoload` agar *namespace* aplikasi terbaca dengan benar.
   ```bash
   cd tsu-homebase/sources
   composer install
   composer dump-autoload
   ```

3. **Konfigurasi Environment**
   Salin *file* konfigurasi di dalam folder `sources/` dan sesuaikan URL serta kredensial *database*.
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Install Dependensi Frontend (Node.js)**
   Pastikan Node.js versi 20 ke atas sudah terinstal di sistem, lalu rakit aset *frontend*.
   ```bash
   npm install
   npm run build
   ```

5. **Setup Symlink & Cache**
   Bersihkan *cache* mesin dan buat jalur pintas untuk *storage* gambar/dokumen.
   ```bash
   php artisan optimize:clear
   php artisan storage:link
   ```

6. **Menjalankan Aplikasi**
   Jika menggunakan Laragon/XAMPP, pastikan *Virtual Host* atau *DocumentRoot* diarahkan langsung ke *root* folder `tsu-homebase` (BUKAN ke folder `public`).
   Akses via browser: `http://tsu-homebase.test`

## 📝 Catatan Pengembang

- **Modifikasi Root:** Jika ada penambahan aset statis publik (*favicon*, *robots.txt*), letakkan di luar (sejajar dengan `index.php`).
- **Terminal:** Pastikan terminal selalu diarahkan ke dalam direktori `sources/` sebelum menjalankan perintah `php artisan` atau `npm`.

---

<div style="text-align: center; font-weight: bold"> Pusat Informasi, Komunikasi dan Digital (PIKDI) <br> Tiga Serangkai University </div>