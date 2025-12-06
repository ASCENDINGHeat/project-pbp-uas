<script lang="ts">
    import { goto } from '$app/navigation';
    import Breadcrumb from '$lib/components/Breadcrumb.svelte'; 
</script>

<svelte:head>
    <title>Register - PC Store</title>
</svelte:head>

<!-- Menggunakan style container yang SAMA PERSIS dengan halaman PC Ready -->
<div class="breadcrumb-wrapper">
    <Breadcrumb items={[
        { label: 'Home', href: '/web' },
        { label: 'Register', active: true }
    ]} />
</div>

<main class="auth-page">
    <div class="container">
        <div class="auth-card">
            <h1>Daftar</h1>
            <p class="subtitle">Buat akun baru untuk mulai berbelanja</p>

            <form on:submit|preventDefault>
                <div class="form-group">
                    <label for="name">Nama Lengkap</label>
                    <input type="text" id="name" placeholder="Masukkan nama lengkap" />
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" placeholder="Masukkan email Anda" />
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" placeholder="Masukkan password" />
                </div>

                <div class="form-group">
                    <label for="confirm">Konfirmasi Password</label>
                    <input type="password" id="confirm" placeholder="Konfirmasi password" />
                </div>

                <button type="submit" class="btn-submit">Daftar</button>
            </form>

            <p class="auth-link">Sudah punya akun? <a href="/web/login">Login sekarang</a></p>
        </div>
    </div>
</main>

<style>
/* --- Responsive --- */
    @media (max-width: 1024px) { 
        .container { padding: 18px; } 
    }

    @media (max-width: 768px) {
        .container, .breadcrumb-container {
            padding: 0 16px;
        }
        
        .header-section h1 { font-size: 2rem; }
        
        .breadcrumb-pill {
            padding: 8px 16px;
            font-size: 0.85rem;
        }
    }
    /* --- AUTH PAGE STYLE --- */
    .auth-page {
        background: #f7f7f7;
        min-height: 85vh; 
        display: flex;
        
        /* Posisi form agak ke atas */
        align-items: flex-start; 
        justify-content: center;
        
        /* Padding atas disesuaikan agar tidak menabrak breadcrumb */
        padding-top: 10px; 
        padding-bottom: 60px;
        padding-left: 20px;
        padding-right: 20px;
    }

    .container {
        /* Ukuran form lebar */
        max-width: 550px; 
        width: 100%;
    }

    .auth-card {
        background: #fff;
        padding: 50px; 
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }

    .auth-card h1 {
        font-size: 2.5rem; 
        font-weight: 800;
        color: #1f2d3d;
        margin: 0 0 8px;
        text-align: center;
    }

    .subtitle {
        text-align: center;
        color: #64748b;
        margin-bottom: 32px;
        font-size: 1.1rem;
    }

    .form-group {
        margin-bottom: 24px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        color: #334155;
        margin-bottom: 10px;
        font-size: 1rem;
    }

    .form-group input {
        width: 100%;
        padding: 16px; 
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 1.05rem;
        box-sizing: border-box;
        transition: all 0.2s;
        background-color: #f8fafc;
    }

    .form-group input:focus {
        outline: none;
        border-color: #8E42E1;
        background-color: #fff;
        box-shadow: 0 0 0 4px rgba(142, 66, 225, 0.1);
    }

    .btn-submit {
        width: 100%;
        padding: 16px; 
        background: linear-gradient(90deg, #8E42E1, #6b3bff);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-weight: 700;
        font-size: 1.1rem;
        cursor: pointer;
        transition: all 0.2s;
        margin-top: 10px;
        letter-spacing: 0.5px;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(142, 66, 225, 0.25);
    }

    .auth-link {
        text-align: center;
        margin-top: 24px;
        color: #64748b;
        font-size: 1rem;
    }

    .auth-link a {
        color: #8E42E1;
        text-decoration: none;
        font-weight: 600;
    }

    .auth-link a:hover {
        text-decoration: underline;
    }
</style>