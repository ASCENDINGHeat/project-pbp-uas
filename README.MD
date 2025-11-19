-----

## 🚀 Panduan Pemula: Pengembangan Kolaboratif (Laragon Edition)

Proyek ini menggunakan arsitektur *monorepo* sederhana, memisahkan kode **Backend (Laravel API)** dan **Frontend (Svelte)** ke dalam subfolder yang berbeda. Ikuti langkah-langkah di bawah ini untuk menyiapkan lingkungan kerja Anda.

-----

## Documentation Reference
Referensi dokumentasi framework dan lain lain **(PENTING)**
- [git](https://git-scm.com/docs)
- [laravel](https://laravel.com/docs/12.x/installation)
- [svelte](https://svelte.dev/docs/svelte/overview)
- [tailwind](https://tailwindcss.com/docs/installation/using-vite)

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
4.  **Pastikan Anda berada di cabang `main`** (cabang utama yang stabil):
    ```bash
    git checkout main
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

5.  **Sinkronisasi `main`:** Selalu perbarui `main` Anda setelah ada penggabungan (*merge*).

    ```bash
    git checkout main
    git pull origin main
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

4.  **Konfigurasi Basis Data (Laragon Default):** **Buka file `.env`** dan sesuaikan pengaturan basis data Anda. Jika Anda menggunakan pengaturan *default* Laragon, kredensialnya sangat sederhana.

    ```
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=nama_db_proyek_anda # <--- PASTIKAN DB INI SUDAH DIBUAT DI PHPMYADMIN/HEIDI SQL
    DB_USERNAME=root             # <--- DEFAULT LARAGON
    DB_PASSWORD=                  # <--- DEFAULT LARAGON (KOSONG/BLANK)
    ```

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

1.  **Pindahkan Proyek:** Pastikan folder utama Anda (`project-pbp-uas`) berada di dalam direktori **`www`** Laragon (misalnya, `C:\laragon\www\project-pbp-uas`).
2.  **Buat Virtual Host:** Di Laragon, klik **Menu \> Apache \> Virtual Hosts \> Enable**. Kemudian klik **Menu \> Apache \> Virtual Hosts \> Create**.
      * Laragon akan mendeteksi `project-pbp-uas` dan Anda bisa membuat *virtual host* (misalnya: `project-pbp-uas.test`).
3.  **Konfigurasi Domain Laravel:** Karena Laravel berada di subfolder, Anda mungkin perlu melakukan sedikit penyesuaian *Virtual Host* di Laragon agar mengarah ke **`project-pbp-uas/backend-API/public`**.
4.  **Restart Laragon:** Klik **Stop** lalu **Start All** di Laragon.

> **Akses API:** Sekarang, API Backend Anda dapat diakses melalui URL kustom Laragon, seperti **`http://project-pbp-uas.test/api/endpoint`**.

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
4.  ✅ **Komunikasi:** Pastikan kode Svelte Anda membuat permintaan ke URL *Virtual Host* Laragon yang benar (misalnya, **`http://project-pbp-uas.test`**), BUKAN ke `http://127.0.0.1:8000`.