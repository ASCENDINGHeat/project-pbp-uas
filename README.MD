-----

## 🚀 Panduan Pemula: Pengembangan Kolaboratif (Laragon Edition)

Proyek ini menggunakan arsitektur *monorepo* sederhana, memisahkan kode **Backend (Laravel API)** dan **Frontend (Svelte)** ke dalam subfolder yang berbeda. Ikuti langkah-langkah di bawah ini untuk menyiapkan lingkungan kerja Anda.

-----

## Documentation Reference
Referensi dokumentasi framework dan lain lain **(PENTING)**
- [git](https://git-scm.com/docs)
- [laravel](https://laravel.com/docs/12.x/installation)
- [svelte](https://svelte.dev/docs/svelte/overview)
- [tailwind](https://tailwindcss.com/docs/)

-----

## 🤝 1. Memahami Git dan Kolaborasi

Git melacak perubahan kode, dan kita akan menggunakan alur kerja *branching* untuk memastikan setiap kontributor dapat bekerja secara independen dan aman.

### 1.1. Cloning (Mendapatkan Kode) ⬇️

1.  **Buka Terminal** (*Command Prompt*, **Git Bash**, atau Terminal di Laragon).
2.  **Clone (salin) seluruh repositori:**
    ```bash
    git clone [repository_url]
    ```
3.  **Masuk ke folder proyek utama:**
    ```bash
    cd project-pbp-uas # Ganti sesuai nama folder proyek Anda
    ```
4.  **Pastikan Anda berada di cabang `master`** (cabang utama yang stabil):
    ```bash
    git checkout master
    ```

### 1.2. Alur Kerja Branching (Bekerja dengan Aman) 🌳

Setiap fitur atau perbaikan harus dikerjakan di cabang terpisah, bukan di `main`.

1.  **Buat Cabang Baru (Branch):** Pilih nama yang jelas.

    ```bash
    git checkout -b feature/nama-tugas-anda
    ```

2.  **Commit Perubahan Anda (Menyimpan Pekerjaan):** Lakukan ini secara rutin.

      * **Stage (Pilih) file:**
        ```bash
        git add .
        ```
      * **Commit (Simpan) dengan pesan yang jelas:**
        ```bash
        git commit -m "feat: Menambahkan komponen tombol Login pada UI"
        ```

3.  **Push Cabang Anda (Kirim ke Server) ⬆️:**

    ```bash
    git push origin feature/nama-tugas-anda
    ```

4.  **Pull Request (PR):** Setelah selesai, buka PR di *platform* repositori untuk ditinjau dan digabungkan ke `main`.

5.  **Sinkronisasi `master`:** Selalu perbarui `master` Anda setelah ada penggabungan (*merge*).

    ```bash
    git checkout master
    git pull origin master
    ```

-----

## 🛠️ 2. Penyiapan Backend (Laravel API) - Khusus Laragon

Backend berada di dalam folder **`backend-API/`**. Ini menggunakan **PHP** dan **Composer**.

### 2.1. Instalasi dan Konfigurasi

1.  **Masuk ke folder Backend:**

    ```bash
    cd backend-API/
    ```

2.  **Instal Dependensi PHP (Composer):**

    ```bash
    composer install
    ```

3.  **Buat File Lingkungan (`.env`):**

    ```bash
    cp .env.example .env
    ```

4.  **Konfigurasi Database (Laragon Default):** **Buka file `.env`** dan sesuaikan pengaturan basis data Anda. Jika Anda menggunakan pengaturan *default* Laragon, kredensialnya sangat sederhana.

    ```env
    DB_CONNECTION=pgsql
    DB_HOST=127.0.0.1
    DB_PORT=5432
    DB_DATABASE=nama_db_proyek_anda   # <--- PASTIKAN DB INI SUDAH DIBUAT DI pgAdmin / psql
    DB_USERNAME=postgres              # <--- GANTI sesuai user PostgreSQL Anda
    DB_PASSWORD=your_password_here    # <--- GANTI sesuai password PostgreSQL Anda
    ```
    <details>
    <summary>meng-install postgresql ke laragon</summary>

    1. Unduh dan pasang PostgreSQL (Windows)
        - Buka https://www.postgresql.org/download/windows/ dan pilih installer (EnterpriseDB).
        - Jalankan installer, tentukan:
        - Password untuk user `postgres` (ingat/password ini untuk .env).
        - Port (default 5432).
        - Sertakan pgAdmin untuk manajemen GUI.

    2. Verifikasi layanan berjalan
        - Buka Services (services.msc) → cari "postgresql-x64-XX" → pastikan Status = Running.
        - Atau buka pgAdmin dan hubungkan ke server lokal dengan user `postgres`.

    3. Buat database proyek
        - Dengan pgAdmin: klik kanan → Create → Database → isi nama (mis. nama_db_proyek_anda).
        - Atau pakai psql di CMD:
        ```bash
        psql -U postgres
        CREATE DATABASE nama_db_proyek_anda;
        \q
        ```

    4. Konfigurasi di Laragon / proyek Laravel
        - Jika Laragon dan PostgreSQL berjalan di mesin yang sama, gunakan konfigurasi .env seperti:
        ```
        DB_CONNECTION=pgsql
        DB_HOST=127.0.0.1
        DB_PORT=5432
        DB_DATABASE=nama_db_proyek_anda
        DB_USERNAME=postgres
        DB_PASSWORD=your_password_here
        ```
        - Pastikan tidak ada konflik port. Jika Laragon menampilkan shortcut DB, Anda dapat mengakses pgAdmin atau psql secara terpisah.

    5. (Opsional) Integrasi PostgreSQL ke Laragon menus
        - Untuk pengelolaan lewat menu Laragon, Anda bisa menyalin folder instalasi PostgreSQL ke: C:\laragon\bin\postgresql\{versi}\ (tidak wajib; instalasi EnterpriseDB sudah cukup).
        - Restart Laragon jika perlu.

    6. Tes koneksi dari Laravel
        - Jalankan migrasi untuk memastikan koneksi:
        ```bash
        php artisan migrate
        ```
        - Jika ada error, periksa kredensial .env, service PostgreSQL, dan log Laravel.

    Catatan singkat: installer resmi (EnterpriseDB) + pgAdmin paling mudah untuk pemula.
    </details>

5.  **Hasilkan Kunci Aplikasi:**

    ```bash
    php artisan key:generate
    ```

6.  **Jalankan Migrasi dan Seeder:** Ini akan membuat tabel dan mengisi data awal.

    ```bash
    php artisan migrate --seed
    ```

### 2.2. Menjalankan Server Backend (Metode Laragon)

Karena Anda menggunakan Laragon, kita akan memanfaatkan **Virtual Host** Laragon untuk menjalankan server, bukan perintah `php artisan serve`.

1.  **Pindahkan Proyek:** Pastikan folder utama Anda (`project-pbp-uas`) berada di dalam direktori **`www`** Laragon (misalnya, `C:\laragon\www\project-pbp-uas`). Atau pindahkan document root pada laragon menjadi folder yang menyimpan folder project (misalnya, `D:\Project\project-pbp-uas`).
4.  **Restart Laragon:** Klik **Stop** lalu **Start All** di Laragon.

> **Akses API:** Sekarang, API Backend Anda dapat diakses melalui URL kustom Laragon, seperti **`http://localhost/backend-API/public/api/`**.

-----

## 🎨 3. Penyiapan Frontend (Svelte)

Frontend berada di dalam folder **`frontend-user/`**. Ini menggunakan **Node.js** dan **npm**.

### 3.1. Instalasi

1.  **Pindah ke folder Frontend:**

    ```bash
    cd ../frontend-user/
    ```

2.  **Instal Dependensi JavaScript (npm):**

    ```bash
    npm install
    ```

### 3.2. Menjalankan Server Frontend

Jalankan server pengembangan SvelteKit.

```bash
npm run dev
```

Aplikasi Svelte akan berjalan di: **`http://localhost:5173/`** (atau port yang tertera di Terminal).

-----

## ✅ Daftar Periksa Akhir

1.  ✅ **Git:** Sudah berada di **cabang fitur baru**?
2.  ✅ **Laragon:** Sudah **Start All**? *Virtual Host* sudah dibuat dan mengarah ke `backend-API/public/`?
3.  ✅ **Frontend (UI):** Server Svelte berjalan? (`cd frontend-user/` lalu `npm run dev`)
4.  ✅ **Komunikasi:** Pastikan kode Svelte Anda membuat permintaan ke URL *Api* Laragon yang benar (misalnya, **`http://localhost/backend-api/public/api/`**), BUKAN ke `http://127.0.0.1:8000`.
