-----

## 🚀 Panduan Pemula: Pengembangan Kolaboratif (Laravel & Svelte)

Dokumen ini memberikan panduan langkah demi langkah yang penting untuk berkolaborasi dalam pengembangan proyek, yang menggunakan **Laravel** (untuk *backend*/API) dan **Svelte** (untuk antarmuka pengguna *frontend*).

-----

## 🤝 1. Memahami Git dan Kolaborasi

Git adalah "sistem kontrol versi" yang melacak perubahan pada kode kita. Kolaborasi berarti beberapa orang dapat mengerjakan kode yang sama tanpa saling mengganggu.

### 1.1. Cloning (Mendapatkan Kode) ⬇️

"Cloning" berarti membuat salinan lengkap proyek dari server pusat (seperti GitHub) ke komputer Anda.

1.  **Buka Terminal/Command Prompt** (di sinilah Anda mengetik perintah).
2.  **Jalankan perintah clone:**
    ```bash
    git clone [repository_url]
    ```
    *Contoh: `git clone https://github.com/my-org/my-project.git`*
3.  **Masuk ke folder proyek:**
    ```bash
    cd [nama-proyek]
    ```
4.  **Periksa lokasi Anda (cabang/branch):** Kode utama yang stabil berada di cabang `main`.
    ```bash
    git checkout main
    ```

### 1.2. Alur Kerja Branching (Bekerja dengan Aman) 🌳

Kita **tidak pernah** bekerja langsung di cabang `main`. Sebaliknya, kita membuat "cabang" terpisah untuk setiap tugas baru. Ini menjaga `main` tetap aman dari *bug* saat Anda bekerja.

1.  **Buat Cabang Baru:** Jalankan perintah ini untuk membuat cabang baru dan segera beralih ke sana.
    *Gunakan nama yang deskriptif seperti `feature/tambah-form-kontak` atau `fix/bug-tombol-navigasi`.*

    ```bash
    git checkout -b feature/nama-tugas-anda
    ```

    *Jika Anda hanya ingin beralih kembali ke cabang yang sudah ada:* `git checkout main`

2.  **Commit Perubahan Anda (Menyimpan Pekerjaan Secara Lokal):** **Commit** seperti menyimpan cuplikan (snapshot) pekerjaan Anda. Lakukan ini sesering mungkin\!

      * **Stage (Pilih) file:** Beri tahu Git file mana yang ingin Anda masukkan dalam cuplikan ini.
        ```bash
        git add .
        ```
        (Tanda `.` berarti "tambahkan semua file yang diubah di folder saat ini.")
      * **Commit (Simpan) perubahan:** Tulis pesan singkat dan jelas yang menjelaskan apa yang Anda lakukan.
        ```bash
        git commit -m "feat: mengimplementasikan desain form pendaftaran pengguna"
        ```

3.  **Push Cabang Anda (Mengirim Pekerjaan ke Server) ⬆️:** Setelah Anda siap berbagi pekerjaan atau istirahat, kirim cabang Anda ke server pusat.

    ```bash
    git push origin feature/nama-tugas-anda
    ```

4.  **Buat Pull Request (Meminta Penggabungan):** Ketika tugas Anda selesai, Anda membuka situs web repository (GitHub, GitLab, dll.) dan membuat **Pull Request (PR)**. Ini meminta rekan tim untuk meninjau perubahan Anda dan menggabungkan cabang Anda ke dalam `main`.

### 1.3. Menyinkronkan Cabang Utama (Tetap Terkini) 🔄

Setelah kode Anda (atau orang lain) digabungkan ke `main`, Anda perlu menarik perubahan tersebut ke komputer Anda.

1.  **Beralih ke cabang `main`:**
    ```bash
    git checkout main
    ```
2.  **Pull (Ambil dan Gabungkan) perubahan terbaru:**
    ```bash
    git pull origin main
    ```
    Sekarang cabang `main` lokal Anda sudah terkini\!

-----

## 🛠️ 2. Penyiapan Laravel (Backend/API)

Laravel adalah *framework* PHP yang menangani data, akun pengguna, dan menyediakan API yang digunakan oleh *frontend* Svelte.

### 2.1. Langkah-Langkah Instalasi Awal

1.  **Instal Dependensi PHP:** Laravel mengandalkan alat bernama **Composer** untuk mengelola paket PHP yang diperlukan.

    ```bash
    composer install
    ```

    *Ini mengunduh semua paket kode yang tercantum dalam `composer.lock`.*

2.  **Buat File Lingkungan (*Environment File*):** File ini (`.env`) menyimpan **rahasia** dan **konfigurasi** spesifik untuk mesin lokal Anda (seperti kata sandi basis data). File ini diabaikan oleh Git, jadi rahasia tetap aman.

    ```bash
    cp .env.example .env
    ```

3.  **Langkah Penting: Konfigurasi Basis Data:** **Buka file `.env` yang baru dibuat** di editor teks Anda dan ubah baris-baris ini agar sesuai dengan pengaturan basis data lokal Anda:

    ```
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=nama_proyek_saya # <--- GANTI INI
    DB_USERNAME=root           # <--- GANTI INI (contoh: 'root')
    DB_PASSWORD=               # <--- GANTI INI (contoh: kata sandi Anda)
    ```

4.  **Hasilkan Kunci Aplikasi (*Application Key*):** Kunci ini digunakan untuk keamanan (enkripsi).

    ```bash
    php artisan key:generate
    ```

5.  **Jalankan Migrasi dan Seeder:** "Migrasi" membuat tabel yang diperlukan di basis data Anda. "Seeder" mengisinya dengan data awal (misalnya, pengguna admin).

    ```bash
    php artisan migrate --seed
    ```

### 2.2. Menjalankan Server Backend

Mulai server lokal agar *frontend* Svelte Anda dapat berkomunikasi dengannya.

```bash
php artisan serve
```

API sekarang berjalan dan siap menerima permintaan, biasanya di: **`http://127.0.0.1:8000`**.

-----

## 🎨 3. Penyiapan Svelte (Frontend)

Svelte adalah *framework* JavaScript yang digunakan untuk membangun antarmuka pengguna (UI) yang responsif dan berjalan di *browser* pengguna.

### 3.1. Langkah-Langkah Instalasi Awal

1.  **Masuk ke Folder Frontend:** Kode Svelte Anda kemungkinan ada di dalam subfolder.

    ```bash
    cd [path_to_svelte_project]
    # Contoh: cd resources/js/
    ```

2.  **Instal Dependensi JavaScript:** Kita menggunakan **npm** (*Node Package Manager*) untuk mengunduh semua paket Svelte dan *styling* yang diperlukan.

    ```bash
    npm install
    ```

    *Ini mengunduh semua paket yang tercantum dalam `package.json` dan `package-lock.json`.*

### 3.2. Menjalankan Server Frontend

Mulai server pengembangan (*development server*). Ini mengawasi file Anda dan secara otomatis memuat ulang *browser* saat Anda menyimpan perubahan.

```bash
npm run dev
```

Aplikasi Svelte harus dapat dilihat di *browser* Anda, biasanya di: **`http://localhost:5173/`**.

-----

## ✅ Daftar Periksa Singkat untuk Mulai Bekerja

1.  ✅ **Periksa Backend:** Apakah server Laravel berjalan? (`php artisan serve`)
2.  ✅ **Periksa Frontend:** Apakah server Svelte berjalan? (`npm run dev`)
3.  ✅ **Periksa Git:** Apakah Anda berada di **cabang fitur baru**? (`git checkout -b feature/tugas`)
