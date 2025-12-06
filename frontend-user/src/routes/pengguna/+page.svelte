<script lang="ts">
    import { onMount } from 'svelte';
    import { goto } from '$app/navigation';

    // --- KODE BARU (SATPAM) ---
    onMount(() => {
        const isLoggedIn = localStorage.getItem("userLoggedIn");
        if (!isLoggedIn) {
            goto('/login');
        }
    });
    // --- AKHIR KODE SATPAM ---

    const portalLinks = [
        {
            id: "web",
            label: "Link Web Utama",
            url: "/web",
            icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>`,
        },
        {
            id: "user",
            label: "Area Pengguna",
            url: "/pengguna",
            icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>`,
        },
        {
            id: "vendor",
            label: "Login Vendor",
            url: "/vendor",
            icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>`,
        },
    ];

    // Tambahkan/ubah fungsi logout jika ada tombol Logout
    function handleLogout() {
        localStorage.removeItem("userLoggedIn"); // Hapus status login
        localStorage.removeItem("userName");
        goto('/login');
    }
</script>

<div class="portal-container">
    <div class="center-box">
        <div class="logo-wrapper">
            <img src="https://cdn-icons-png.flaticon.com/512/1048/1048953.png" alt="PC Store Logo" class="logo" />
        </div>
        
        <div class="text-wrapper">
            <h1>PC Store</h1>
            <p>Indonesia's Largest PC Community</p>
        </div>
        
        <div class="portal-links">
            {#each portalLinks as link}
                <a class="portal-link {link.id}" href={link.url}>
                    <span class="icon">{@html link.icon}</span>
                    <span class="label">{link.label}</span>
                </a>
            {/each}
        </div>

        <!-- Tombol Logout -->
        <button on:click={handleLogout} class="btn-logout">Keluar Akun</button>
    </div>

    <footer class="portal-footer">
        PC Store &copy; 2025
    </footer>
</div>

<style>
    /* Reset agar body tidak ada margin bawaan */
    :global(body) {
        margin: 0;
        padding: 0;
        overflow: hidden; /* Mencegah scrollbar muncul sama sekali */
    }

    .portal-container {
        height: 100vh; /* Tinggi pas 1 layar */
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: space-between; /* Distribusi atas-tengah-bawah */
        background: linear-gradient(135deg, #f6f7ff 0%, #e9eeff 100%);
        color: #333;
        padding: 2vh 20px; /* Padding vertikal pakai satuan vh (persen layar) */
        box-sizing: border-box;
    }

    .center-box {
        flex: 1; /* Mengambil sisa ruang tengah */
        display: flex;
        flex-direction: column;
        justify-content: center; /* Konten benar-benar di tengah vertikal */
        align-items: center;
        width: 100%;
        max-width: 450px; /* Batas lebar agar rapi */
        gap: 2vh; /* Jarak antar elemen dinamis sesuai tinggi layar */
    }

    /* Logo - Ukuran dinamis (Maksimal 120px, atau 15% tinggi layar) */
    .logo-wrapper {
        flex-shrink: 0;
    }
    .logo {
        height: 15vh; 
        max-height: 120px;
        width: auto;
        padding: 10px;
        background: #fff;
        border-radius: 50%;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }

    /* Teks Header */
    .text-wrapper {
        text-align: center;
        flex-shrink: 0;
    }
    h1 {
        font-size: 5vh; /* Ukuran font mengikuti tinggi layar */
        max-font-size: 3rem;
        font-weight: 800;
        margin: 0;
        background: linear-gradient(to right, #8e2de2, #4a00e0);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        line-height: 1.2;
    }
    p {
        font-size: 2vh;
        margin: 0.5vh 0 0 0;
        color: #666;
    }

    /* Links Container */
    .portal-links {
        display: flex;
        flex-direction: column;
        gap: 1.5vh; /* Jarak antar tombol dinamis */
        width: 100%;
        margin-top: 2vh;
    }

    /* Tombol */
    .portal-link {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        height: 8vh; /* Tinggi tombol sekitar 8% dari layar */
        max-height: 70px; /* Tidak boleh lebih besar dari 70px */
        min-height: 50px;
        border-radius: 12px;
        text-decoration: none;
        color: #fff;
        font-weight: 700;
        font-size: 2.2vh; /* Font tombol juga dinamis */
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }

    .portal-link:hover {
        transform: scale(1.02);
    }

    /* Warna Tombol */
    .portal-link.web { background: linear-gradient(to right, #8e2de2, #4a00e0); }
    .portal-link.user { background: #f43f5e; }
    .portal-link.vendor { 
        background: #fff; 
        color: #333; 
        border: 2px solid #e5e7eb;
        box-shadow: none;
    }
    .portal-link.vendor:hover {
        border-color: #4a00e0;
        color: #4a00e0;
    }

    /* Footer */
    .portal-footer {
        flex-shrink: 0;
        font-size: 1.5vh;
        color: #999;
        margin-top: 1vh;
    }

    /* Tombol Logout */
    .btn-logout {
        margin-top: 2vh;
        padding: 10px 20px;
        border: none;
        border-radius: 12px;
        background: #e63946;
        color: #fff;
        font-weight: 700;
        font-size: 2vh;
        cursor: pointer;
        transition: background 0.3s;
    }

    .btn-logout:hover {
        background: #d62839;
    }
</style>