<script lang="ts">
    import { goto } from '$app/navigation';
    import Breadcrumb from '$lib/components/Breadcrumb.svelte';
    import { onMount } from 'svelte';
    import { PUBLIC_API_URL } from '$env/static/public';
    import { PUBLIC_STORAGE_URL } from '$env/static/public';

    interface Product {
        id: string;
        name: string;
        price: number;
        image: string;
        rating: number;
    }

    let pcReady: Product[] = [];
    let isLoading = true;

    const breadcrumbItems = [
        { label: 'Home', href: '/web' },
        { label: 'PC Ready', active: true }
    ];

    onMount(async () => {
        try {
            // Fetch produk dengan kategori 'pc-ready'
            // Pastikan Anda sudah menambahkan produk dengan kategori ini di database via API/Vendor Dashboard
            const res = await fetch(`${PUBLIC_API_URL}/product?category=pc-ready`);
            
            if (res.ok) {
                const data = await res.json();
                const rawProducts = data.data || [];

                // Mapping data API (Backend) -> Format Frontend
                pcReady = rawProducts.map((item: any) => ({
                    id: String(item.id),
                    name: item.title, // Backend 'title' -> Frontend 'name'
                    price: Number(item.price),
                    // Gunakan image_url dari backend atau placeholder
                    image: item.image_url || '/images/placeholder.png',
                    rating: 5.0 // Default rating
                }));
            } else {
                console.error("Gagal mengambil data PC Ready");
            }
        } catch (err) {
            console.error("Error fetching API:", err);
        } finally {
            isLoading = false;
        }
    });

    // --- LOGIC RESPONSIVE KOLOM (Sesuai kode Anda) ---
    let innerWidth = 0;
    $: responsiveColumns = innerWidth < 640 ? 2 : (innerWidth < 1200 ? 4 : 6);

    function handleAnchorClick(event: MouseEvent, product: Product) {
        if ((event.target as HTMLElement).closest('.wishlist-btn')) {
            return;
        }

        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || (event as any).button !== 0) {
            return;
        }
        event.preventDefault();
        goto(`/web/product-pcready/${product.id}`);
    }

    const formatPrice = (price: number) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(price);
    };

    function handleWishlistClick() {
        console.log('Wishlist clicked');
        // Tambahkan logika wishlist store di sini jika diperlukan
    }
</script>

<svelte:window bind:innerWidth={innerWidth} />

<Breadcrumb items={breadcrumbItems} />

<main class="category-page">
    <div class="container">
        <header class="header-section">
            <h1>PC Ready</h1>
            <p class="subtitle">Komputer siap pakai dan rakitan pilihan untuk berbagai kebutuhan</p>
        </header>

        <div class="grid" style={`display:grid; gap:20px; grid-template-columns: repeat(${responsiveColumns}, 1fr);`}>
            {#each pcReady as product (product.id)}
                <a
                    href={`/web/product-pcready/${product.id}`}
                    class="prod-card"
                    data-sveltekit-preload-data="hover"
                    on:click={(e) => handleAnchorClick(e as MouseEvent, product)}
                >
                    <div class="prod-thumb">
                        <img src={product.image || '/images/placeholder.png'} alt={product.name} />
                        
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

                        <div class="price">{product.price ? formatPrice(product.price) : 'Hubungi Kami'}</div>
                    </div>

                    <div class="prod-footer">
                        <div class="btn-lookalike">
                            Lihat Detail
                        </div>
                    </div>
                </a>
            {/each}
        </div>
    </div>
</main>

<style>
    /* --- Layout Dasar --- */
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

    /* --- BOX CART STYLING --- */
    
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
    }

    .prod-card:hover { 
        transform: translateY(-5px); 
        box-shadow: 0 10px 20px rgba(0,0,0,0.08); 
        border-color: #cbd5e1;
    }

    /* Gambar */
    .prod-thumb { 
        width: 100%;
        aspect-ratio: 1/1; 
        background: #f8fafc; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        padding: 20px;
        box-sizing: border-box;
        position: relative; /* Wajib ada agar tombol love bisa absolute */
    }

    .prod-thumb img { 
        max-width: 100%; 
        max-height: 100%; 
        object-fit: contain; 
        mix-blend-mode: multiply; 
    }

    /* --- TOMBOL WISHLIST (LOVE) --- */
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
        color: #ef4444; /* Merah */
        transition: transform 0.2s, box-shadow 0.2s;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        z-index: 2;
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

    .btn-lookalike { 
        width: 100%;
        padding: 10px 0;
        background-color: #3b82f6; 
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

    /* --- Responsive Styling --- */
    @media (max-width: 768px) {
        .container { padding: 0 16px; }
        .header-section h1 { font-size: 2rem; }
        
        .prod-body { padding: 12px; }
        .prod-title { font-size: 0.85rem; }
        .price { font-size: 1rem; }
        .btn-lookalike { font-size: 0.8rem; padding: 8px 0; }
    }

    @media (max-width: 640px) {
        .grid { gap: 10px !important; }
    }
</style>