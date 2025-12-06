<script lang="ts">
    import { goto } from '$app/navigation';
    // Pastikan path component Breadcrumb sudah benar
    // Jika Anda belum punya component Breadcrumb.svelte yang terpisah, 
    // Anda bisa menghapus import ini dan menggunakan HTML manual seperti sebelumnya.
    import Breadcrumb from '$lib/components/Breadcrumb.svelte'; 
</script>

<svelte:head>
    <title>Login - PC Store</title>
</svelte:head>

<div class="breadcrumb-container">
    <Breadcrumb items={[
        { label: 'Home', href: '/web' },
        { label: 'Login', active: true }
    ]} />
</div>

<main class="auth-page">
    <div class="container">
        <div class="auth-card">
            <h1>Login</h1>
            <p class="subtitle">Selamat datang kembali!</p>

            <form on:submit|preventDefault>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" placeholder="Masukkan email Anda" />
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" placeholder="Masukkan password" />
                </div>

                <button type="submit" class="btn-submit">Masuk</button>
            </form>

            <p class="auth-link">Belum punya akun? <a href="/web/register">Daftar sekarang</a></p>
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

    .auth-page {
        background: #f7f7f7;
        /* Ubah min-height agar memperhitungkan ruang breadcrumb/header */
        min-height: 85vh; 
        display: flex;
        
        /* --- PERUBAHAN POSISI --- */
        /* Sebelumnya 'center', diubah jadi 'flex-start' agar naik ke atas */
        align-items: flex-start; 
        justify-content: center;
        
        /* Memberi jarak dari atas agar tidak terlalu mepet (naik tapi ada jarak) */
        padding-top: 60px; 
        padding-bottom: 40px;
        padding-left: 20px;
        padding-right: 20px;
    }

    .container {
        /* --- PERUBAHAN UKURAN --- */
        /* Diperbesar dari 400px menjadi 550px */
        max-width: 550px; 
        width: 100%;
    }

    .auth-card {
        background: #fff;
        /* Padding diperbesar agar terlihat lega */
        padding: 50px; 
        border-radius: 16px; /* Radius lebih halus */
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }

    .auth-card h1 {
        /* Font diperbesar */
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
        /* Input diperbesar (padding lebih tebal) */
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
        /* Tombol diperbesar */
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

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .auth-page {
            /* Di HP, jarak atas dikurangi sedikit agar pas */
            padding-top: 40px; 
        }
        
        .auth-card { 
            padding: 30px; 
        }
        
        .auth-card h1 { 
            font-size: 1.8rem; 
        }
    }
</style>