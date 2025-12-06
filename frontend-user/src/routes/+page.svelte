<script lang="ts">
    import { goto } from '$app/navigation';
    import { onMount } from 'svelte';
    import { fly, fade } from 'svelte/transition';

    let ready = false;
    onMount(() => ready = true);

    const portalLinks = [
        {
            id: "web",
            label: "Belanja Sekarang",
            sub: "Lihat Katalog Produk",
            url: "/web",
            primary: true,
            icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>`
        },
        {
            id: "user",
            label: "Area Pengguna", 
            sub: "Cek Pesanan & Profil",
            url: "/pengguna",
            primary: false,
            icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>`
        }
    ];

    function handlePenggunaClick(event: MouseEvent) {
        event.preventDefault();
        const isLoggedIn = localStorage.getItem("userLoggedIn");
        if (isLoggedIn) goto('/pengguna');
        else goto('/login');
    }
</script>

<div class="split-hero-page">
    
    <div class="bg-pattern"></div>

    <header class="navbar" in:fly={{ y: -20, duration: 800 }}>
        <div class="brand">
            <img src="https://cdn-icons-png.flaticon.com/512/1048/1048953.png" alt="Logo" />
            <span>PC Store</span>
        </div>
        <div class="nav-links">
            <a href="/login">Login</a>
            <a href="/register" class="btn-small">Daftar</a>
        </div>
    </header>

    <main class="hero-container">
        
        <div class="content-side">
            {#if ready}
                <div in:fly={{ y: 30, duration: 800, delay: 100 }}>
                    <span class="badge">🔥 Pusat Rakit PC #1 Indonesia</span>
                </div>

                <h1 in:fly={{ y: 30, duration: 800, delay: 200 }}>
                    Level Up Your <br> <span class="gradient-text">Gaming Setup.</span>
                </h1>
                
                <p class="description" in:fly={{ y: 30, duration: 800, delay: 300 }}>
                    Temukan komponen PC terbaik, rakitan custom, dan aksesoris gaming terlengkap dengan harga bersaing. Garansi resmi & terpercaya.
                </p>

                <div class="action-buttons" in:fly={{ y: 30, duration: 800, delay: 400 }}>
                    {#each portalLinks as link}
                        <a href={link.url} 
                           class="big-card-btn {link.primary ? 'primary' : 'secondary'}" 
                           on:click={link.id === 'user' ? handlePenggunaClick : null}>
                            
                            <div class="icon-box">{@html link.icon}</div>
                            <div class="text-box">
                                <span class="btn-label">{link.label}</span>
                                <span class="btn-sub">{link.sub}</span>
                            </div>
                            <div class="arrow">→</div>
                        </a>
                    {/each}
                </div>

                <div class="vendor-link" in:fly={{ y: 30, duration: 800, delay: 500 }}>
                    Ingin berjualan? <a href="/vendor/register">Daftar sebagai Vendor</a>
                </div>
            {/if}
        </div>

        <div class="visual-side">
            {#if ready}
                <div class="glow-effect" in:fade={{ duration: 1500 }}></div>

                <div class="img-wrapper" in:fly={{ x: 50, duration: 1000, delay: 300 }}>
                    <img src="src/routes/assets gambar/halaman utama.jpg" alt="Gaming Setup" class="hero-img" />
                    
                    <div class="glass-card">
                        <span class="check-icon">✓</span>
                        <div>
                            <strong>Ready Stock</strong>
                            <span class="small">Siap Kirim Hari Ini</span>
                        </div>
                    </div>
                </div>
            {/if}
        </div>

    </main>

    <footer class="footer-minimal">
        PC Store © 2025
    </footer>
</div>

<style>
    /* --- GLOBAL RESET (FIX SCROLL) --- */
    :global(html), :global(body) {
        margin: 0; 
        padding: 0; 
        width: 100%; 
        /* Jangan dikunci height 100%, biarkan auto */
        height: auto;
        min-height: 100vh;
        overflow-y: auto !important; /* WAJIB: Aktifkan Scroll */
        overflow-x: hidden; /* Matikan scroll samping */
        font-family: 'Segoe UI', sans-serif;
        background: #ffffff;
    }

    /* --- CONTAINER HALAMAN --- */
    .split-hero-page {
        width: 100%;
        min-height: 100vh; /* Minimal satu layar */
        display: flex;
        flex-direction: column;
        position: relative;
        overflow-x: hidden;
    }

    /* --- BACKGROUND TEXTURE --- */
    .bg-pattern {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background-image: radial-gradient(#e5e7eb 1px, transparent 1px);
        background-size: 24px 24px;
        z-index: 0;
        mask-image: linear-gradient(to bottom, rgba(0,0,0,1) 80%, rgba(0,0,0,0) 100%);
        pointer-events: none;
    }

    /* --- NAVBAR --- */
    .navbar {
        display: flex; justify-content: space-between; align-items: center;
        padding: 25px 40px; 
        position: relative; /* Ubah jadi relative agar ikut flow halaman */
        z-index: 10;
        width: 100%; box-sizing: border-box;
        max-width: 1920px; margin: 0 auto;
    }
    .brand { display: flex; align-items: center; gap: 12px; font-weight: 800; font-size: 1.5rem; color: #111; }
    .brand img { height: 40px; }
    
    .nav-links { display: flex; align-items: center; gap: 30px; }
    .nav-links a { text-decoration: none; color: #555; font-weight: 600; font-size: 1.1rem; }
    .btn-small {
        background: #111; color: white !important;
        padding: 10px 24px; border-radius: 30px; transition: transform 0.2s; font-size: 1rem;
    }
    .btn-small:hover { transform: translateY(-2px); }

    /* --- HERO CONTAINER (LAYOUT FLEX) --- */
    .hero-container {
        flex: 1; /* Mengisi sisa ruang */
        display: flex; 
        align-items: center; 
        justify-content: center;
        padding: 40px 20px 80px 20px; /* Tambah padding bawah agar bisa discroll lebih lega */
        gap: 20px; 
        position: relative; z-index: 2;
        width: 100%; box-sizing: border-box;
        max-width: 1920px; 
        margin: 0 auto;
        flex-wrap: wrap; /* PENTING: Biar turun ke bawah kalau layar sempit */
    }

    /* KIRI (TEKS) */
    .content-side { 
        flex: 0.8; 
        padding-left: 40px; 
        z-index: 5; 
        min-width: 500px; /* Minimal lebar segini, kalau kurang dia turun */
    }

    .badge {
        display: inline-block; background: #f3e8ff; color: #9333ea;
        padding: 8px 16px; border-radius: 30px; font-weight: 700; font-size: 0.9rem;
        margin-bottom: 25px; letter-spacing: 0.5px;
    }

    h1 { 
        font-size: 5rem; /* Font Raksasa */
        line-height: 1.05; 
        margin: 0 0 25px 0; 
        color: #111; 
        font-weight: 900; 
    }
    .gradient-text {
        background: linear-gradient(to right, #8e2de2, #4a00e0);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }

    .description { 
        color: #666; 
        font-size: 1.2rem; 
        line-height: 1.6; 
        margin-bottom: 50px; 
        max-width: 600px; 
    }

    /* BUTTONS */
    .action-buttons { display: flex; flex-direction: column; gap: 20px; width: 100%; max-width: 480px; }

    .big-card-btn {
        display: flex; align-items: center; gap: 20px;
        padding: 20px 30px; 
        border-radius: 20px;
        text-decoration: none; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid transparent; cursor: pointer; position: relative;
    }

    .big-card-btn.primary {
        background: linear-gradient(135deg, #8e2de2, #4a00e0);
        color: white; box-shadow: 0 10px 20px rgba(74, 0, 224, 0.2);
    }
    .big-card-btn.primary:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(74, 0, 224, 0.35); }

    .big-card-btn.secondary {
        background: white; color: #1e293b; border-color: #e2e8f0;
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
    }
    .big-card-btn.secondary:hover { border-color: #4a00e0; transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }

    .btn-label { display: block; font-weight: 800; font-size: 1.3rem; }
    .btn-sub { display: block; font-size: 0.95rem; opacity: 0.9; font-weight: 400; }
    .arrow { margin-left: auto; font-size: 1.5rem; transition: transform 0.2s; }
    .big-card-btn:hover .arrow { transform: translateX(5px); }

    .vendor-link { margin-top: 30px; font-size: 1rem; color: #888; }
    .vendor-link a { color: #4a00e0; text-decoration: none; font-weight: 600; }

    /* --- KANAN (VISUAL) --- */
    .visual-side {
        flex: 1.2; 
        display: flex; 
        justify-content: flex-start; 
        align-items: center;
        position: relative;
        min-width: 500px;
    }

    /* GLOW BACKGROUND */
    .glow-effect {
        position: absolute;
        width: 150%; 
        height: 150%;
        background: radial-gradient(circle, rgba(236,72,153,0.25) 0%, rgba(147,51,234,0.25) 50%, rgba(255,255,255,0) 80%);
        filter: blur(80px);
        z-index: 0;
        top: 50%; left: 30%;
        transform: translate(-50%, -50%);
        pointer-events: none;
    }

    .img-wrapper { 
        position: relative; z-index: 2; width: 100%; 
        display: flex; justify-content: center;
    }

    /* GAMBAR HERO */
    .hero-img {
        width: 130%; /* Dibuat lebih lebar */
        max-width: 1400px; 
        aspect-ratio: 16/10;
        object-fit: cover;
        border-radius: 30px;
        box-shadow: 0 30px 80px rgba(0,0,0,0.25);
        transform: perspective(1500px) rotateY(-3deg) translateX(-50px);
        transition: transform 0.5s ease;
    }
    .hero-img:hover { transform: perspective(1500px) rotateY(0deg) scale(1.01) translateX(-50px); }

    /* GLASS CARD */
    .glass-card {
        position: absolute; bottom: 50px; right: 80px;
        background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px);
        padding: 15px 30px; border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        display: flex; align-items: center; gap: 15px; z-index: 3;
        animation: float 4s ease-in-out infinite reverse;
    }
    .check-icon { background: #dcfce7; color: #16a34a; padding: 8px 12px; border-radius: 50%; font-weight: bold; font-size: 1.2rem; }
    .small { font-size: 0.9rem; color: #666; display: block; }

    @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }

    /* FOOTER */
    .footer-minimal {
        text-align: center; padding: 20px; color: #999; font-size: 0.8rem;
        background: #fff; margin-top: auto; position: relative; z-index: 10;
        width: 100%;
    }

    /* RESPONSIVE (LAPTOP KECIL & HP) */
    @media (max-width: 1200px) {
        /* Saat layar menyempit, izinkan scroll */
        .hero-container { 
            flex-direction: column-reverse; 
            text-align: center; 
            padding: 50px 20px;
            height: auto; /* Biarkan tinggi otomatis */
        }
        
        .content-side { 
            min-width: 100%; 
            padding-left: 0; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
        }
        
        h1 { font-size: 3.5rem; }
        
        .visual-side { 
            min-width: 100%; 
            justify-content: center; 
            margin-bottom: 40px; 
        }
        
        .hero-img { 
            width: 100%; 
            max-width: 800px; 
            transform: none; 
        }
        
        .glow-effect { left: 50%; width: 100%; }
        .glass-card { display: none; }
        .navbar { padding: 20px; }
    }
</style>