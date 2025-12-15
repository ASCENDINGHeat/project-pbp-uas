<script lang="ts">
    import { page } from '$app/stores';
    import { goto } from '$app/navigation';
    import { PUBLIC_API_URL } from '$env/static/public';

    // --- LOGIC BARU: Mengambil Data dari API ---
    let categoryProducts: any[] = [];
    let loading = true;

    $: category = $page.params.category;
    // Get search term from URL query parameter
    $: search = $page.url.searchParams.get('search') || '';

    // Trigger fetch whenever category or search changes
    $: fetchProducts(category, search);

    async function fetchProducts(cat: string, searchTerm: string) {
        loading = true;
        try {
            // Build Query URL
            let url = `${PUBLIC_API_URL}/product?per_page=20`;
            
            // Filter by Category
            if (cat && cat !== 'all') {
                url += `&category=${cat}`;
            }

            // Filter by Search
            if (searchTerm) {
                url += `&search=${encodeURIComponent(searchTerm)}`;
            }

            const res = await fetch(url);
            if (res.ok) {
                const data = await res.json();
                // API pagination returns data in `data.data` or just `data` if not paginated. 
                // Based on Controller: return response()->json($products, 200); where $products is paginated.
                categoryProducts = data.data || [];
            } else {
                console.error('Failed to fetch products');
                categoryProducts = [];
            }
        } catch (error) {
            console.error('Error:', error);
            categoryProducts = [];
        } finally {
            loading = false;
        }
    }

    // Category names mapping
    const categoryNames: Record<string, string> = {
        all: 'Semua Kategori',
        processor: 'Processor',
        motherboard: 'Motherboard',
        vga: 'Kartu Grafis',
        ram: 'RAM',
        storage: 'Storage',
        casing: 'Casing',
        psu: 'Power Supply',
        monitor: 'Monitor',
        cooler: 'CPU Cooler (HSF)'
    };
    $: categoryName = categoryNames[category] || labelize(category);

    // Update title dynamic if searching
    $: displayTitle = search ? `Hasil Pencarian: "${search}"` : categoryName;

    function labelize(seg: string) {
        return decodeURIComponent(seg).replace(/[-_]/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
    }

    function buildBreadcrumbs(pathname: string) {
        const crumbs = [{ label: 'Home', path: '/web' }];
        crumbs.push({ label: 'All Categories', path: '/web/categories/all' });
        
        const match = pathname.match(/\/categories\/([^/]+)/);
        if (match) {
            const catParam = match[1];
            if (catParam !== 'all') {
                crumbs.push({ 
                    label: categoryNames[catParam] || labelize(catParam), 
                    path: `/web/categories/${catParam}` 
                });
            }
        }
        return crumbs;
    }

    let breadcrumbs: { label: string; path: string }[] = [];
    $: breadcrumbs = buildBreadcrumbs($page.url.pathname);

    // --- DATA KATEGORI (Static Menu Icons) ---
    const categoriesList = [
        { name: 'Processor', slug: 'processor', icon: '/images/icons/cpu.png' },
        { name: 'Motherboard', slug: 'motherboard', icon: '/images/icons/mobo.png' },
        { name: 'Kartu Grafis', slug: 'vga', icon: '/images/icons/gpu.png' },
        { name: 'RAM', slug: 'ram', icon: '/images/icons/ssd.png' },
        { name: 'Storage', slug: 'storage', icon: '/images/icons/ssd.png' },
        { name: 'Casing', slug: 'casing', icon: '/images/icons/case.png' },
        { name: 'Power Supply', slug: 'psu', icon: '/images/icons/psu.png' },
        { name: 'Monitor', slug: 'monitor', icon: '/images/icons/monitor.png' },
        { name: 'CPU Cooler (HSF)', slug: 'cooler', icon: '/images/icons/cooler.png' }
    ];

    function handleIconError(e: Event) {
        const target = e.target as HTMLImageElement;
        target.style.display = 'none';
    }

    function openProduct(id: string) {
        goto(`/web/product/${id}`);
    }
</script>

<div class="breadcrumb-wrapper">
    <div class="breadcrumb-container">
        <div class="breadcrumb-pill">
            {#each breadcrumbs as crumb, i}
                {#if i < breadcrumbs.length - 1}
                    <a href={crumb.path} class="breadcrumb-link">{crumb.label}</a>
                    <span class="breadcrumb-sep">›</span>
                {:else}
                    <span class="breadcrumb-current">{crumb.label}</span>
                {/if}
            {/each}
        </div>
    </div>
</div>

<main class="category-page">
    <div class="container">
        <header class="header-section">
            <h1>{displayTitle}</h1>
            <p class="subtitle">
                {#if search}
                    Menampilkan hasil pencarian untuk "{search}"
                {:else if category === 'all'}
                    Jelajahi berbagai kategori produk terbaik kami
                {:else}
                    Produk berkualitas untuk kebutuhan Anda
                {/if}
            </p>
        </header>

        {#if category === 'all' && !search}
            <div class="cat-grid-container">
                {#each categoriesList as cat}
                    <a href="/web/categories/{cat.slug}" class="cat-card">
                        <div class="cat-icon-box">
                            <img src={cat.icon} alt={cat.name} on:error={handleIconError} />
                        </div>
                        <span class="cat-name">{cat.name}</span>
                    </a>
                {/each}
            </div>
        {/if}

        <div class="product-grid" style="margin-top: 40px;">
            {#if loading}
                <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #666;">
                    Memuat produk...
                </div>
            {:else if categoryProducts.length > 0}
                {#each categoryProducts as product}
                    <div class="prod-card" role="button" tabindex="0" on:click={() => openProduct(product.id)}>
                        <div class="prod-thumb">
                            <img src={product.image_url || product.image || '/images/placeholder.png'} alt={product.title} on:error={handleIconError} />
                            <button class="wishlist-btn" on:click|stopPropagation={() => console.log('Wishlist clicked')}>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01L8 2.748zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143c.06.055.119.112.176.171a3.12 3.12 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15z"/>
                                </svg>
                            </button>
                        </div>

                        <div class="prod-body">
                            <div class="prod-title">{product.title}</div>
                            <div class="prod-rating">
                                <span class="star">★</span> 
                                <span class="score">4.8</span>
                            </div>
                            <div class="price">{new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(product.price)}</div>
                        </div>

                        <div class="prod-footer">
                            <button class="btn-add-cart" on:click|stopPropagation={() => openProduct(product.id)}>
                                Tambah ke Keranjang
                            </button>
                        </div>
                    </div>
                {/each}
            {:else}
                <div class="empty-state" style="grid-column: 1/-1; text-align: center; padding: 40px; color: #888;">
                    Tidak ada produk ditemukan.
                </div>
            {/if}
        </div>
    </div>
</main>

<style>
/* ... Styles preserved from original file ... */
    /* --- Layout & Breadcrumb Styles --- */
    .breadcrumb-wrapper {
        width: 100%;
        padding-top: 20px;
        padding-bottom: 10px;
        position: relative;
        z-index: 1;
    }

    .breadcrumb-container {
        max-width: 95%;
        margin: 0 auto;
        padding: 0 20px;
    }

    .breadcrumb-pill {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background-color: #0f172a;
        border-radius: 50px;
        padding: 10px 24px;
        font-size: 0.9rem;
        font-weight: 500;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: 1px solid rgba(255,255,255,0.05);
    }

    .breadcrumb-link {
        color: #94a3b8;
        text-decoration: none;
        cursor: pointer;
        transition: color 0.2s;
    }

    .breadcrumb-link:hover { color: #fff; }
    .breadcrumb-sep { color: #475569; font-size: 0.8rem; }
    .breadcrumb-current { color: #ff0055; font-weight: 700; }

    /* --- Page Content Styles --- */
    .category-page { 
        background: #f7f7f7;
        min-height: 100vh;
        font-family: 'Inter', sans-serif;
    }

    .container {
        max-width: 95%;
        margin: 0 auto;
        padding: 40px 20px;
    }

    .header-section {
        margin-bottom: 40px;
        text-align: center;
    }

    .header-section h1 {
        font-size: 2.5rem;
        font-weight: 800;
        color: #1f2d3d;
        margin: 0 0 12px;
    }

    .header-section .subtitle {
        font-size: 1.1rem;
        color: #64748b;
        margin: 0;
    }

    /* --- CATEGORY GRID STYLES --- */
    .cat-grid-container {
        display: grid;
        grid-template-columns: repeat(4, 1fr); 
        gap: 16px;
        width: 100%;
    }

    .cat-card {
        background: linear-gradient(135deg, #a855f7 0%, #d946ef 100%);
        display: flex;
        align-items: center;
        padding: 18px 20px;
        border-radius: 12px;
        text-decoration: none;
        transition: transform 0.2s, box-shadow 0.2s, filter 0.2s;
        border: 1px solid rgba(255, 255, 255, 0.1);
        overflow: hidden;
        position: relative;
    }

    .cat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(168, 85, 247, 0.4); 
        filter: brightness(1.1);
    }

    .cat-icon-box {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 16px;
        flex-shrink: 0;
        backdrop-filter: blur(4px);
    }

    .cat-icon-box img {
        width: 24px;
        height: 24px;
        object-fit: contain;
        filter: brightness(0) invert(1);
    }

    .cat-name {
        color: #ffffff; 
        font-weight: 600;
        font-size: 1rem;
        letter-spacing: 0.3px;
        text-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }

    /* --- PRODUCT GRID (UKURAN SEDANG / 6 KOLOM) --- */
    .product-grid { 
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 20px; 
        margin-top: 20px; 
    }

    .prod-card { 
        display: flex;
        flex-direction: column;
        background: #fff; 
        border-radius: 12px; 
        border: 1px solid #e2e8f0; 
        cursor: pointer; 
        transition: transform 0.2s, box-shadow 0.2s; 
        overflow: hidden;
        position: relative;
    }

    .prod-card:hover { 
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.08); 
    }

    /* Bagian Gambar */
    .prod-thumb { 
        width: 100%;
        aspect-ratio: 1/1; 
        background: #f8fafc; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        position: relative; 
        padding: 20px;
        box-sizing: border-box;
    }

    .prod-thumb img { 
        max-width: 100%; 
        max-height: 100%;
        object-fit: contain; 
        mix-blend-mode: multiply;
    }

    /* Tombol Wishlist */
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
        color: #ef4444; 
        transition: transform 0.2s;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .wishlist-btn:hover { transform: scale(1.1); }

    /* Bagian Info Produk */
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

    /* Bagian Tombol Bawah */
    .prod-footer { padding: 0 16px 16px 16px; }

    .btn-add-cart { 
        width: 100%;
        padding: 10px 0;
        background-color: #3b82f6; 
        color: white;
        font-weight: 600;
        font-size: 0.9rem;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .btn-add-cart:hover { background-color: #2563eb; }

    /* --- Responsive --- */
    @media (max-width: 1400px) {
        .product-grid { grid-template-columns: repeat(5, 1fr); }
    }

    @media (max-width: 1200px) {
        .cat-grid-container { grid-template-columns: repeat(3, 1fr); }
        .product-grid { grid-template-columns: repeat(4, 1fr); }
    }

    @media (max-width: 900px) {
        .product-grid { grid-template-columns: repeat(3, 1fr); }
    }

    @media (max-width: 768px) {
        .container, .breadcrumb-container { padding: 0 16px; }
        .header-section h1 { font-size: 2rem; }
        .cat-grid-container { grid-template-columns: repeat(2, 1fr); }
        .product-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .prod-body { padding: 12px; }
        .prod-title { font-size: 0.85rem; height: auto; -webkit-line-clamp: 2; }
        .price { font-size: 1rem; }
        .btn-add-cart { font-size: 0.8rem; padding: 8px 0; }
    }

    @media (max-width: 480px) {
        .cat-grid-container { grid-template-columns: 1fr; }
    }
</style>