<script lang="ts">
    import { goto } from '$app/navigation';
    import { onMount } from 'svelte';
    import { PUBLIC_API_URL } from '$env/static/public';

    // 1. Types
    interface Slide {
        id: string;
        image: string;
        title: string;
        caption: string;
    }

    interface Promo {
        id: string;
        image: string;
        title: string;
        caption?: string; 
    }

    // --- DATA SLIDER KIRI (UTAMA) ---
    let slides: Slide[] = [
        { id: 's1', image: '/images/banner-utama.jpg', title: 'GeForce RTX', caption: 'GPU terbaru' },
        { id: 's2', image: '/images/GPU-NVIDIA-GeForce-RTX-5090.jpg', title: 'RTX Series', caption: 'Performa maksimal' },
        { id: 's3', image: '/images/pc-ready.jpg', title: 'PC Ready', caption: 'Siap pakai' }
    ];

    // --- DATA SLIDER KANAN (PROMO) ---
    const promos: Promo[] = [
        { id: 'p1', image: '/images/promo.jpg', title: 'Promo Besar', caption: 'Diskon 50%' },
        { id: 'p2', image: '/images/pc-ready1.jpg', title: 'GTR ZERO', caption: 'Edisi Terbatas' },
        { id: 'p3', image: '/images/promosi.jpg', title: 'Why PC Store', caption: 'Garansi Resmi' }
    ];

    // --- LOGIC SLIDER ---
    let idx = 0;
    let timer: ReturnType<typeof setInterval> | null = null;
    let promoIdx = 0;
    let promoTimer: ReturnType<typeof setInterval> | null = null;

    function next() { idx = (idx + 1) % slides.length; }
    function prev() { idx = (idx - 1 + slides.length) % slides.length; }
    function go(i: number) { idx = i; }

    function nextPromo() { promoIdx = (promoIdx + 1) % promos.length; }
    function prevPromo() { promoIdx = (promoIdx - 1 + promos.length) % promos.length; }
    function goPromo(i: number) { promoIdx = i; }

    function startAuto() {
        if (timer) clearInterval(timer);
        timer = setInterval(next, 4500);
    }
    function stopAuto() { if (timer) clearInterval(timer); }

    function startPromoAuto() {
        if (promoTimer) clearInterval(promoTimer);
        promoTimer = setInterval(nextPromo, 5000); 
    }
    function stopPromoAuto() { if (promoTimer) clearInterval(promoTimer); }

    let featured: any[] = [];
    let isLoading = true;

    onMount(async () => {
        startAuto();
        startPromoAuto();

        const y = sessionStorage.getItem('pc-list-scroll');
        if (y) {
            requestAnimationFrame(()=>{
                window.scrollTo(0, Number(y));
                sessionStorage.removeItem('pc-list-scroll');
            });
        }

        try {
            const endpoint = PUBLIC_API_URL ? `${PUBLIC_API_URL}/product` : 'http://127.0.0.1:8000/api/product';

            const res = await fetch(endpoint);
            if (res.ok) {
                const data = await res.json();
                
                // Backend return: { current_page: ..., data: [ ...products... ], ... }
                // Kita ambil array 'data' dari paginator
                const productsFromApi = data.data || [];

                // Mapping format Backend (Laravel) -> Frontend (Svelte)
                featured = productsFromApi.map((item: any) => {
                    return {
                        id: String(item.id),
                        name: item.title, // 'title' di DB -> 'name' di Frontend
                        // Gunakan accessor image_url dari Product Model atau placeholder
                        image: item.image_url || item.imagePlaceholder || '/images/placeholder.png',
                        price: Number(item.price),
                        rating: 5.0, // Default rating (karena belum ada di DB)
                        reviews: 0
                    };
                }).slice(0, 8); // Ambil 8 item saja untuk featured
            } else {
                console.error("Gagal mengambil data produk");
            }
        } catch (err) {
            console.error("Error fetching API:", err);
        } finally {
            isLoading = false;
        }

        return () => {
            stopAuto();
            stopPromoAuto();
        };
    });

    function handleImgError(e: Event) {
        const img = e.target as HTMLImageElement;
        img.src = 'https://via.placeholder.com/1200x600?text=Image+Not+Found';
        img.onerror = null;
    }
    
    // --- LOGIC RESPONSIVE KOLOM ---
    let innerWidth = 0; 
    $: responsiveColumns = innerWidth < 640 ? 2 : (innerWidth < 1200 ? 4 : 6);

    // navigate ke listing PC Ready
    function navigateToPcReady(event: MouseEvent | PointerEvent, id: string) {
        if (event instanceof MouseEvent || event instanceof PointerEvent) {
            if ((event as any).metaKey || (event as any).ctrlKey || (event as any).shiftKey || (event as any).altKey || (event as any).button !== 0) {
                return; // biarkan modifier/middle-click/default behavior
            }
        }

        // save current scroll position so we can restore it when user goes back
        try {
            sessionStorage.setItem('pc-list-scroll', String(window.scrollY || window.pageYOffset || document.documentElement.scrollTop || 0));
        } catch (e) {
            // ignore sessionStorage errors (e.g. private mode)
        }
        
        event.preventDefault();
        const href = `/web/product-pcready/${id}`;
        try {
            const p = goto(href);
            if (p && typeof (p as Promise<any>).catch === 'function') {
                (p as Promise<any>).catch(() => window.location.assign(href));
            }
        } catch {
            window.location.assign(href);
        }
    }

    // Helper format harga
    const formatPrice = (price: number) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(price);
    };

    function handleWishlistClick(e: MouseEvent) {
        // Fungsi ini akan dipanggil saat tombol love diklik
        // stopPropagation() di HTML mencegah link parent tereksekusi
        console.log('Wishlist clicked!'); 
        // Tambahkan logika wishlist di sini nanti
    }
</script>

<svelte:window bind:innerWidth={innerWidth} />

<div class="hero-section">
    <div class="hero-grid">
        
        <div class="carousel-wrap main-carousel" 
                role="region" 
                aria-label="Main Carousel"
                on:mouseenter={stopAuto} 
                on:mouseleave={startAuto}>
            
            <div class="slides" style="transform:translateX({-idx * 100}%);">
                {#each slides as s (s.id)}
                    <div class="slide">
                        <img src={s.image} alt={s.title} on:error={handleImgError} />
                        <div class="slide-overlay">
                            <h3>{s.title}</h3>
                            <p>{s.caption}</p>
                        </div>
                    </div>
                {/each}
            </div>
            <button class="nav prev" on:click={prev}>‹</button>
            <button class="nav next" on:click={next}>›</button>
            <div class="indicators">
                {#each slides as _, i}
                    <button class="dot {i === idx ? 'active' : ''}" on:click={() => go(i)}></button>
                {/each}
            </div>
        </div>

        <aside class="carousel-wrap promo-carousel"
                role="region"
                aria-label="Promo Carousel"
                on:mouseenter={stopPromoAuto}
                on:mouseleave={startPromoAuto}>

            <div class="slides" style="transform:translateX({-promoIdx * 100}%);">
                {#each promos as p (p.id)}
                    <div class="slide">
                        <img src={p.image} alt={p.title} on:error={handleImgError} />
                        <div class="slide-overlay promo-overlay">
                            <h3>{p.title}</h3>
                            {#if p.caption}<p>{p.caption}</p>{/if}
                        </div>
                    </div>
                {/each}
            </div>
            <button class="nav prev" on:click={prevPromo}>‹</button>
            <button class="nav next" on:click={nextPromo}>›</button>
            <div class="indicators">
                {#each promos as _, i}
                    <button class="dot {i === promoIdx ? 'active' : ''}" on:click={() => goPromo(i)}></button>
                {/each}
            </div>
        </aside>

    </div>
</div>

<div class="container">
    
    <div class="product-grid-custom" style={`display:grid; gap:20px; grid-template-columns: repeat(${responsiveColumns}, 1fr);`}>
        {#each featured as product (product.id)}
            <a
                href="/web/pc-ready"
                class="prod-card"
                data-sveltekit-preload-data="hover"
                aria-label={"Lihat PC Ready"}
                on:pointerdown|capture={(e) => navigateToPcReady(e as PointerEvent, String(product.id))}
                on:click|capture={(e) => navigateToPcReady(e as MouseEvent, String(product.id))}
            >
                <div class="prod-thumb">
                    <img src={product.image || product.imagePlaceholder || '/images/placeholder.png'} alt={product.name} on:error={handleImgError} />
                    
                    <button class="wishlist-btn" on:click|stopPropagation={handleWishlistClick} aria-label="Tambah ke wishlist">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01L8 2.748zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143c.06.055.119.112.176.171a3.12 3.12 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15z"/>
                        </svg>
                    </button>
                </div>

                <div class="prod-body">
                    <div class="prod-title">{product.name}</div>
                    
                    <div class="prod-rating">
                        <span class="star">★</span> 
                        <span class="score">5.0</span>
                    </div>

                    <div class="price">{product.price ? formatPrice(product.price) : '-'}</div>
                </div>

                <div class="prod-footer">
                    <div class="btn-lookalike">
                        Lihat Series
                    </div>
                </div>
            </a>
        {/each}
    </div>
 
    <div class="pagination">
        <button class="pg-btn">‹ Previous</button>
        <span class="pg-number active">1</span>
        <button class="pg-btn">Next ›</button>
    </div>
</div>

<style>
    :root {
        --hero-height: 480px; 
    }

    /* --- STYLES HERO SECTION (TETAP SAMA) --- */
    .hero-section {
        padding: 20px;
        background: transparent; 
    }

    .hero-grid {
        max-width: 95%; 
        margin: 0 auto;
        display: grid;
        grid-template-columns: 3fr 1fr;
        gap: 20px;
        height: var(--hero-height);
    }

    .carousel-wrap {
        position: relative;
        overflow: hidden;
        border-radius: 12px;
        background: #0f1724; 
        width: 100%;
        height: 100%; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .slides {
        display: flex;
        transition: transform 0.5s cubic-bezier(0.25, 1, 0.5, 1);
        height: 100%;
        width: 100%;
    }

    .slide {
        min-width: 100%;
        position: relative;
        height: 100%;
    }

    .slide img {
        width: 100%;
        height: 100%;
        object-fit: cover; 
        display: block;
    }

    .slide-overlay {
        position: absolute;
        left: 30px;
        bottom: 40px;
        color: #fff;
        text-shadow: 0 4px 12px rgba(0,0,0, 0.8);
        z-index: 2;
        pointer-events: none;
    }

    .slide-overlay h3 {
        margin: 0 0 6px;
        font-size: 2.8rem;
        font-weight: 800;
        letter-spacing: -0.5px;
    }

    .slide-overlay p { margin: 0; font-size: 1.1rem; }
    .promo-overlay h3 { font-size: 1.8rem; }
    .promo-overlay p { font-size: 0.9rem; }

    .nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(4px);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.3);
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 10;
        transition: background 0.2s;
        opacity: 0;
    }
    .carousel-wrap:hover .nav { opacity: 1; }
    .nav:hover { background: rgba(255, 255, 255, 0.4); }
    .prev { left: 12px; }
    .next { right: 12px; }

    .indicators {
        position: absolute;
        bottom: 12px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 6px;
        z-index: 10;
    }
    .dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.4);
        border: none;
        cursor: pointer;
        transition: all 0.3s;
        padding: 0;
    }
    .dot.active { background: #fff; width: 20px; border-radius: 4px; }


    /* --- STYLES CONTAINER BAWAH --- */
    .container {
        max-width: 95%; 
        margin: 40px auto;
        padding: 0 20px 70px;
    }

    /* --- PRODUCT CARD STYLES --- */
    
    .prod-card { 
        display: flex; 
        flex-direction: column;
        background: #fff; 
        border-radius: 12px; 
        border: 1px solid #e2e8f0; 
        text-decoration: none;
        transition: transform 0.2s, box-shadow 0.2s; 
        overflow: hidden;
        position: relative;
        height: 100%;
        color: inherit;
    }

    .prod-card:hover { 
        transform: translateY(-5px); 
        box-shadow: 0 10px 20px rgba(0,0,0,0.08); 
        border-color: #cbd5e1;
    }

    /* Gambar 1:1 di tengah */
    .prod-thumb { 
        width: 100%;
        aspect-ratio: 1/1; 
        background: #f8fafc; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        padding: 20px;
        box-sizing: border-box;
        position: relative; /* Penting untuk posisi absolute button love */
    }

    .prod-thumb img { 
        max-width: 100%; 
        max-height: 100%; 
        object-fit: contain; 
        mix-blend-mode: multiply;
    }

    /* STYLES TOMBOL LOVE (WISHLIST) DIKEMBALIKAN */
    .wishlist-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: #ef4444; /* Warna merah */
        transition: transform 0.2s, box-shadow 0.2s;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        z-index: 2; /* Agar berada di atas link card */
    }

    .wishlist-btn:hover { 
        transform: scale(1.1); 
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }


    /* Info Produk */
    .prod-body { 
        padding: 16px;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .prod-title { 
        font-weight: 500; 
        color: #334155; 
        font-size: 0.95rem; 
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2; 
        -webkit-box-orient: vertical;
        overflow: hidden;
        height: 42px; 
    }

    .prod-rating {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 0.85rem;
        color: #64748b;
        margin-bottom: 4px;
    }
    
    .star { color: #fbbf24; }

    .price { 
        font-weight: 800; 
        font-size: 1.1rem;
        color: #9333ea; 
        margin-top: auto; 
    }

    /* Footer & Tombol */
    .prod-footer { padding: 0 16px 16px 16px; }

    /* Tombol Style Baru */
    .btn-lookalike { 
        width: 100%;
        padding: 10px 0;
        background-color: #3b82f6; /* Warna Biru */
        color: white;
        font-weight: 600;
        font-size: 0.9rem;
        border-radius: 8px;
        text-align: center;
        transition: background-color 0.2s;
        display: block;
    }

    .prod-card:hover .btn-lookalike { 
        background-color: #2563eb; 
    }

    /* --- Pagination --- */
    .pagination {
        margin-top: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 16px;
    }
    
    .pg-btn, .pg-number {
        padding: 10px 18px;
        border-radius: 8px;
        border: none;
        background: #e2e8f0;
        color: #475569;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.2s;
    }
    
    .pg-btn:hover, .pg-number:hover { background: #cbd5e1; }
    .pg-number.active { background: #2563eb; color: white; }

    /* --- Responsive --- */
    @media (max-width: 1400px) {
        :root { --hero-height: 400px; }
        .hero-grid { grid-template-columns: 2.5fr 1fr; }
    }

    @media (max-width: 900px) {
        :root { --hero-height: auto; }
        .hero-grid { 
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .main-carousel { height: 350px; }
        .promo-carousel { height: 250px; }
        .nav { opacity: 1; }
        .slide-overlay h3 { font-size: 2rem; }
    }

    @media (max-width: 768px) {
        .prod-body { padding: 12px; }
        .prod-title { font-size: 0.85rem; }
        .price { font-size: 1rem; }
        .btn-lookalike { font-size: 0.8rem; padding: 8px 0; }
    }

    @media (max-width: 640px) {
        .main-carousel { height: 250px; }
        .slide-overlay h3 { font-size: 1.5rem; }
        .container { padding: 0 10px 50px; }
        
        /* Force grid gap smaller on mobile */
        .product-grid-custom { gap: 10px !important; }
    }
</style>