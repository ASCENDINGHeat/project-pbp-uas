<script lang="ts">
    import type { PageData } from './$types';
    import { goto } from '$app/navigation';
    import { addToCart } from '$lib/stores/cart';
    
    export let data: PageData;

    // --- State & Logic ---
    let quantity = 1;

    const product = {
        id: data.product.id ?? '',
        name: data.product.name ?? 'Unnamed Product',
        price: Number(data.product.price ?? 0),
        shop_name: data.product.shop_name ?? 'Official Store',
        image: data.product.image ?? data.product.imagePlaceholder ?? 'https://via.placeholder.com/800x800?text=No+Image',
        description: data.product.description ?? 'Tidak ada deskripsi.',
        rating: Number(data.product.rating ?? 0),
        sold: Number(data.product.sold ?? 0),
        stock: Number(data.product.stock ?? 50), 
        category: (data.product.category ?? 'Intel Core i9').toString(),
        get status() { return this.stock > 0 ? 'Tersedia' : 'Habis'; }
    };

    const categorySlug = product.category.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9\-]/g, '');

    function goToCategory() {
        goto(`/web/categories/${categorySlug}`);
    }

    const formatIDR = (num: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num);

    function increaseQty() { if (quantity < product.stock) quantity++; }
    function decreaseQty() { if (quantity > 1) quantity--; }

    function goBack() {
        const currentPath = location.pathname + location.search;
        let refPath: string | null = null;
        if (document.referrer) {
            try {
                const u = new URL(document.referrer);
                refPath = u.pathname + u.search;
            } catch { refPath = null; }
        }

        if (history.length > 1) {
            history.back();
            setTimeout(() => {
                const now = location.pathname + location.search;
                if (now === currentPath) {
                    if (refPath) goto(refPath);
                    else goto('/web');
                }
            }, 220);
            return;
        }

        if (refPath) goto(refPath);
        else goto('/web');
    }

    function capitalizeFirstLetter(str: string) {
        if (!str) return str;
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    function addAndGoCart() {
        addToCart({
            id: product.id,
            name: product.name,
            price: product.price,
            image: product.image,
            quantity
        });
        goto('/web/cart');
    }
</script>

<svelte:head>
    <title>{product.name} | Store</title>
</svelte:head>

<div class="page-content">
    
    <div class="nav-container">
        <button class="btn-back" on:click={goBack} aria-label="Kembali">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span>Kembali</span>
        </button>
    </div>

    <main class="product-grid">
        
        <section class="image-section">
            <div class="main-image-card">
                <img src={product.image} alt={product.name} class="hero-image" />
            </div>
        </section>

        <div class="info-wrapper">
            <section class="info-card">
                <div class="shop-badge">
                    <span class="shop-icon">🏪</span>
                    <span>{product.shop_name}</span>
                </div>

                <h1 class="product-title">{product.name}</h1>
                
                <div class="rating-row">
                    <span class="stars">★★★★★</span>
                    <span class="rating-val">{product.rating}</span>
                    <span class="divider">|</span>
                    <span class="sold-count">Terjual {product.sold}+</span>
                </div>

                <div class="price-tag">
                    {formatIDR(product.price)}
                </div>

                <div class="specs-box-dark">
                    <h3 class="specs-title">Informasi Produk</h3>
                    <div class="specs-row">
                        <span class="specs-label">Kategori:</span>
                        <a href={`/web/categories/${categorySlug}`} class="specs-value category-link" on:click|preventDefault={goToCategory}>
                            {capitalizeFirstLetter(product.category)}
                        </a>
                    </div>
                    <div class="specs-row">
                        <span class="specs-label">Status:</span>
                        <span class="specs-value">{product.status}</span>
                    </div>
                </div>
            </section>

            <section class="action-card">
                <div class="qty-row">
                    <span class="label">Atur Jumlah:</span>
                    <div class="stepper">
                        <button on:click={decreaseQty} disabled={quantity <= 1}>−</button>
                        <input type="text" readonly value={quantity} />
                        <button on:click={increaseQty} disabled={quantity >= product.stock}>+</button>
                    </div>
                    <span class="stock-info">Sisa {product.stock} pcs</span>
                </div>

                <div class="total-preview">
                    <span>Subtotal:</span>
                    <strong>{formatIDR(product.price * quantity)}</strong>
                </div>

                <div class="button-group">
                    <button class="btn btn-outline"><span>♡</span> Wishlist</button>
                    <button class="btn btn-primary" on:click={addAndGoCart}>+ Keranjang</button>
                </div>

                </section>
        </div> 
        
        <section class="description-section">
            <div class="tabs">
                <button class="tab-active">Deskripsi Produk</button>
                <button>Spesifikasi</button>
            </div>
            <div class="desc-content">
                <p>{product.description}</p>
            </div>
        </section>

    </main>
</div>

<style>
    /* --- Layout Dasar --- */
    .page-content {
        background-color: #f8f9fa;
        min-height: 100vh;
        
        /* PERBAIKAN UTAMA: Ubah padding-top jadi 10px saja. 
           Sebelumnya 25px/40px terlalu besar, makanya tombol turun jauh.
           Dengan 10px, tombol akan 'duduk' manis tepat di bawah garis ungu. */
        padding: 10px 20px 40px 20px; 
        
        box-sizing: border-box;
    }

    /* --- CONTAINER NAVIGASI --- */
    .nav-container {
        /* Tetap 1200px agar LURUS dengan "All Categories" & Gambar Produk */
        max-width: 1200px;
        width: 100%;
        margin: 0 auto; 
        
        display: flex;
        justify-content: flex-start;
        
        /* Hapus margin-top, dan beri jarak bawah sedikit saja (15px) 
           agar tidak nempel dengan gambar produk */
        margin-top: 0;
        padding-bottom: 15px; 
    }

    /* --- TOMBOL KEMBALI --- */
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        
        /* Pastikan Margin 0 agar tidak terdorong turun */
        margin: 0; 
        
        padding: 8px 16px; /* Sedikit diperkecil padding-nya agar lebih pas */
        
        background: #111111; 
        color: #ef4444;      
        border: none;
        border-radius: 50px; 
        
        font-weight: 700;
        font-size: 0.9rem;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s, background 0.2s;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .btn-back:hover {
        background: #000000;
        transform: translateY(-2px); 
        box-shadow: 0 8px 20px rgba(0,0,0,0.25);
    }
    
    .btn-back svg { stroke: currentColor; }

    /* --- Grid Layout (Produk) - Tidak Berubah --- */
    .product-grid {
        max-width: 1200px;
        margin: 0 auto;
        width: 100%;
        display: grid;
        grid-template-columns: 1.2fr 1fr; 
        gap: 40px;
        align-items: start;
    }

    .image-section { display: flex; flex-direction: column; }
    .main-image-card {
        background: #fff;
        border-radius: 20px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        aspect-ratio: 1/1; 
        display: flex; align-items: center; justify-content: center;
    }
    .hero-image { width: 100%; height: 100%; object-fit: cover; }
    
    .info-wrapper {
        display: flex; flex-direction: column; gap: 24px;
        position: sticky; top: 20px; 
    }

    /* --- Styles Lainnya (Tetap) --- */
    .specs-box-dark {
        background: linear-gradient(180deg, #7B4BFF 0%, #8E42E1 60%);
        color: #fff;
        border-radius: 8px;
        padding: 20px;
        margin-top: 20px;
        border: 1px solid rgba(142,66,225,0.18);
        box-shadow: 0 8px 30px rgba(142,66,225,0.08);
    }
    .specs-title { margin: 0 0 16px 0; font-size: 1.1rem; font-weight: 700; color: #fff; }
    .specs-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 0.95rem; }
    .specs-row:last-child { margin-bottom: 0; }
    .specs-label { color: rgba(255,255,255,0.8); font-weight: 500; }
    .specs-value { color: #fff; font-weight: 600; text-align: right; }

    .shop-badge { 
        display: inline-flex; align-items: center; gap: 8px;
        background: #eff6ff; color: #1e40af; border: 1px solid #dbeafe;
        font-size: 0.85rem; font-weight: 700; padding: 6px 12px; 
        border-radius: 100px; margin-bottom: 16px; width: fit-content;
    }
    .product-title { margin: 0 0 12px; font-size: 2rem; line-height: 1.3; color: #111; letter-spacing: -0.5px; }
    .price-tag { font-size: 2.2rem; font-weight: 800; color: #111; }
    .rating-row { display: flex; align-items: center; gap: 8px; color: #6b7280; margin-bottom: 20px; }
    .stars { color: #fbbf24; }

    .action-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05);
    }
    .qty-row { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; }
    .stepper { display: flex; border: 1px solid #d1d5db; border-radius: 8px; overflow: hidden; }
    .stepper button { width: 35px; height: 35px; background: #fff; border: none; cursor: pointer; font-size: 1.2rem; }
    .stepper input { width: 40px; text-align: center; border: none; border-left: 1px solid #d1d5db; border-right: 1px solid #d1d5db; outline: none; font-weight: bold;}
    .total-preview { display: flex; justify-content: space-between; margin-bottom: 20px; padding-top: 15px; border-top: 1px dashed #e5e7eb; font-weight: bold; }
    
    .button-group { display: flex; gap: 12px; }
    .btn { flex: 1; padding: 14px; border-radius: 10px; font-weight: 600; cursor: pointer; display: flex; justify-content: center; gap: 8px; border: none;}
    .btn-outline { border: 2px solid rgba(142,66,225,0.12); background: #fff; color: #3b2b5c; }
    .btn-primary { background: linear-gradient(135deg,#8E42E1 0%, #6B3BFF 100%); color: #fff; box-shadow: 0 8px 20px rgba(107,59,255,0.12); }

    .description-section { grid-column: 1 / -1; background: #fff; border-radius: 20px; padding: 40px; border: 1px solid #e5e7eb; margin-top: 20px; }
    .tabs { display: flex; gap: 30px; border-bottom: 1px solid #e5e7eb; margin-bottom: 25px; }
    .tabs button { background: none; border: none; padding: 0 0 15px 0; cursor: pointer; color: #6b7280; font-weight: 500; }
    .tabs .tab-active { color: #3b82f6; font-weight: 700; border-bottom: 3px solid #3b82f6; }
    .category-link { color: rgba(255,255,255,0.95); font-weight: 700; text-decoration: none; cursor: pointer; }
    .category-link:hover { color: #fff; opacity: 1; }

    @media (max-width: 850px) {
        .product-grid { grid-template-columns: 1fr; gap: 20px; }
        .info-wrapper { position: static; }
        .btn-back { width: auto; margin-bottom: 0; }
    }
</style>