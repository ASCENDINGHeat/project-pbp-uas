<script lang="ts">
    import { slide, fly, fade } from 'svelte/transition'; // Tambah fly/fade untuk animasi overlay
    import { goto } from '$app/navigation';

    // Logika PC Ready dihapus sesuai permintaan
    
    let isCatalogOpen = false; // State untuk mengontrol tampilan full screen

    const catalogItems = [
        { label: 'Processor', slug: 'processor', icon: 'cpu' },
        { label: 'Motherboard', slug: 'motherboard', icon: 'grid' },
        { label: 'Memory (RAM)', slug: 'ram', icon: 'memory' },
        { label: 'Storage (SSD/HDD)', slug: 'storage', icon: 'hard-drive' },
        { label: 'Graphics Card', slug: 'vga', icon: 'monitor' },
        { label: 'Power Supply', slug: 'psu', icon: 'zap' },
        { label: 'Casing', slug: 'casing', icon: 'box' },
        { label: 'Fan & Cooling', slug: 'fan', icon: 'wind' },
        { label: 'CPU Cooler', slug: 'cooler', icon: 'thermometer' },
        { label: 'Keyboard & Mouse', slug: 'peripherals', icon: 'mouse' },
        { label: 'Monitor', slug: 'monitor', icon: 'tv' }
    ];

    function closeSidebar() {
        // Logika tutup sidebar standar
        history.length > 1 ? history.back() : goto('/web');
    }

    function toggleCatalog() {
        isCatalogOpen = !isCatalogOpen;
    }

    function openCategory(slug: string) {
        goto(`/web/categories/${slug}`);
        isCatalogOpen = false; // Tutup overlay setelah memilih
    }
</script>

<div class="backdrop" on:click={closeSidebar} role="presentation" />

<aside class="sidebar" in:slide={{ x: -300, duration: 220 }}>
    <header class="sb-header">
        <div class="sb-title">Menu</div>
        <button class="sb-close" aria-label="Tutup" on:click={closeSidebar}>✕</button>
    </header>

    <nav class="sb-nav">
        <a class="sb-link" href="/web/pc-ready" on:click|preventDefault={() => goto('/web/pc-ready')}>
            PC Ready
        </a>

        <button class="sb-link special" on:click={toggleCatalog}>
            Katalog Komponen
            <span class="arrow">➔</span>
        </button>

        <a class="sb-link" href="/web/simulasi-pc" on:click|preventDefault={() => goto('/web/simulasi-pc')}>
            Simulasi PC
        </a>
        
        </nav>

    <footer class="sb-footer">
        <a href="/web" on:click|preventDefault={() => goto('/web')}>Beranda</a>
        <a href="/web/contact" on:click|preventDefault={() => goto('/web/contact')}>Contact</a>
    </footer>
</aside>

{#if isCatalogOpen}
    <div class="catalog-overlay" transition:fly={{ y: 50, duration: 300 }}>
        <header class="overlay-header">
            <h2 class="overlay-title">Kategori Komponen</h2>
            <button class="overlay-close" on:click={toggleCatalog}>Tutup ✕</button>
        </header>

        <div class="catalog-grid">
            {#each catalogItems as item}
                <button class="cat-card" on:click={() => openCategory(item.slug)}>
                    <div class="cat-icon">
                        <span class="dot"></span>
                    </div>
                    <span class="cat-label">{item.label}</span>
                </button>
            {/each}
        </div>
    </div>
{/if}

<style>
    /* Backdrop */
    .backdrop {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 1200;
    }

    /* Sidebar Styles */
    .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        bottom: 0;
        width: 320px;
        max-width: 85%;
        background: linear-gradient(180deg, #0f172a, #111827);
        color: #e6eef8;
        z-index: 1250;
        display: flex;
        flex-direction: column;
        padding: 16px;
        box-shadow: 8px 0 30px rgba(0,0,0,0.4);
    }

    .sb-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
    .sb-title { font-weight: 800; font-size: 1.5rem; letter-spacing: -0.5px; }
    .sb-close { background: transparent; border: none; color: #fff; font-size: 1.25rem; cursor: pointer; padding: 4px; }

    .sb-nav { display: flex; flex-direction: column; gap: 8px; }
    
    .sb-link {
        display: flex; 
        justify-content: space-between;
        align-items: center;
        background: transparent;
        border: none;
        color: #cbd5e1;
        text-align: left;
        padding: 14px 12px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.2s;
    }
    
    .sb-link:hover { background: rgba(255,255,255,0.05); color: #fff; }
    
    /* Highlight tombol katalog agar user tahu ini menu utama */
    .sb-link.special {
        background: rgba(56, 189, 248, 0.1);
        color: #38bdf8;
        border: 1px solid rgba(56, 189, 248, 0.2);
    }
    .sb-link.special:hover {
        background: rgba(56, 189, 248, 0.2);
    }

    .sb-footer { margin-top: auto; padding-top: 20px; display: flex; gap: 16px; border-top: 1px solid rgba(255,255,255,0.05); }
    .sb-footer a { color: #64748b; cursor: pointer; font-weight: 500; text-decoration: none; font-size: 0.9rem; }
    .sb-footer a:hover { color: #fff; }

    /* --- FULL SCREEN OVERLAY STYLES --- */
    .catalog-overlay {
        position: fixed;
        inset: 0; /* Full Screen */
        background: #0f172a; /* Solid background matching theme */
        z-index: 1300; /* Di atas sidebar */
        display: flex;
        flex-direction: column;
        padding: 20px;
        overflow-y: auto;
    }

    .overlay-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    .overlay-title {
        font-size: 1.5rem;
        font-weight: 800;
        color: #fff;
        margin: 0;
    }

    .overlay-close {
        background: rgba(255,255,255,0.1);
        border: none;
        color: #fff;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }
    .overlay-close:hover { background: rgba(255,255,255,0.2); }

    /* Grid Layout untuk Kategori */
    .catalog-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr); /* 2 kolom di HP */
        gap: 12px;
        max-width: 800px;
        margin: 0 auto;
        width: 100%;
    }

    @media (min-width: 640px) {
        .catalog-grid { grid-template-columns: repeat(3, 1fr); gap: 16px; }
    }

    .cat-card {
        background: rgba(30, 41, 59, 0.6);
        border: 1px solid rgba(255,255,255,0.05);
        border-radius: 12px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 12px;
        cursor: pointer;
        transition: transform 0.2s, background 0.2s;
        aspect-ratio: 1/0.8; /* Kotak agak persegi panjang */
        color: #e2e8f0;
    }

    .cat-card:hover {
        background: rgba(56, 189, 248, 0.1);
        border-color: rgba(56, 189, 248, 0.3);
        transform: translateY(-2px);
        color: #fff;
    }

    .cat-icon {
        width: 40px; 
        height: 40px; 
        background: rgba(0,0,0,0.3); 
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .cat-icon .dot {
        width: 8px; height: 8px; background: #38bdf8; border-radius: 50%;
        box-shadow: 0 0 8px #38bdf8;
    }

    .cat-label {
        font-weight: 600;
        font-size: 0.95rem;
        text-align: center;
    }
</style>