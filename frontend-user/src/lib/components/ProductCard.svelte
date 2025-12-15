<script lang="ts">
    import { PUBLIC_API_URL, PUBLIC_STORAGE_URL } from '$env/static/public';
    import { goto } from '$app/navigation';
    import { isLoggedIn } from '$lib/stores/auth';
    
    // Import Store Cart & Wishlist
    import { addToCart as addToLocalCart} from '$lib/stores/cart';
    import { wishlist, addToWishlistStore, removeFromWishlistStore } from '$lib/stores/wishlist';

    // Props dari parent
    export let product: any;

    let isLoadingCart = false; // Disamakan variabelnya dengan +page
    let isWishlistLoading = false;

    // Cek status wishlist (Sinkron dengan Store Global)
    $: isWishlisted = $wishlist.some(i => i.id === product.id) || product.is_wishlisted || false;

    // Helper URL Gambar
    const STORAGE_BASE = PUBLIC_STORAGE_URL.endsWith('/') ? PUBLIC_STORAGE_URL : `${PUBLIC_STORAGE_URL}/`;
    $: imageUrl = product.image && product.image.startsWith('http') 
        ? product.image 
        : `${STORAGE_BASE}storage/${product.image}`;


    // --- LOGIKA TAMBAH KE KERANJANG (SAMA SEPERTI +PAGE) ---
    async function addToCartBackend() {
        // 1. Cek Login
        if (!$isLoggedIn) {
            if (confirm("Anda perlu login untuk berbelanja. Ke halaman login?")) {
                goto('/login');
            }
            return;
        }

        if (isLoadingCart) return;
        isLoadingCart = true;

        try {
            const token = localStorage.getItem('auth_token');
            
            // 2. Request ke Backend
            const res = await fetch(`${PUBLIC_API_URL}/cart/${product.id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    quantity: 1 // Default 1 untuk di card
                })
            });

            const result = await res.json();

            if (res.ok) {
                // 3. Update Store Lokal (untuk badge real-time)
                addToLocalCart({
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    image: imageUrl,
                    quantity: 1
                });
                
                // 4. Redirect ke halaman Cart (Sesuai logika +page.svelte)
                goto('/web/cart'); 
            } else {
                alert(result.message || "Gagal menambahkan ke keranjang.");
            }
        } catch (error) {
            console.error("Error add to cart:", error);
            alert("Terjadi kesalahan saat menghubungi server.");
        } finally {
            isLoadingCart = false;
        }
    }

    // --- LOGIKA WISHLIST (SAMA SEPERTI +PAGE dengan Optimistic UI) ---
    async function handleToggleWishlist() {
        if (!$isLoggedIn) {
            alert("Silakan login terlebih dahulu untuk menyimpan ke wishlist.");
            goto('/login'); // Redirect login sesuai +page [cite: 15]
            return;
        }

        const token = localStorage.getItem('auth_token');
        const wasWishlisted = isWishlisted;

        // Optimistic UI: Update tampilan duluan agar terasa cepat
        if (wasWishlisted) {
            removeFromWishlistStore(product.id);
        } else {
            addToWishlistStore({
                id: product.id,
                name: product.name,
                price: product.price,
                image: imageUrl
            });
        }

        isWishlistLoading = true;

        try {
            const res = await fetch(`${PUBLIC_API_URL}/wishlist/${product.id}`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            if (!res.ok) {
                throw new Error("Gagal update backend");
            }
            // Jika sukses, biarkan state yang sudah diupdate secara optimis
        } catch (e) {
            console.error(e);
            // Rollback jika error koneksi (Kembalikan ke status awal)
            if (wasWishlisted) addToWishlistStore({ id: product.id, name: product.name, price: product.price, image: imageUrl });
            else removeFromWishlistStore(product.id);
            alert("Terjadi kesalahan koneksi.");
        } finally {
            isWishlistLoading = false;
        }
    }
</script>

<div class="product-card">
    
    <div class="product-image-area">
        <a href="/web/product/{product.id}" class="image-link" aria-label="Lihat detail {product.name}">
            <img src={imageUrl} alt={product.name} on:error={(e) => e.currentTarget.src = '/images/placeholder.jpg'} />
            {#if product.stock_quantity <= 0}
                <div class="out-of-stock">Habis Stok</div>
            {/if}
        </a>

        <button 
            type="button" 
            class="wishlist-btn" 
            on:click={handleToggleWishlist}
            disabled={isWishlistLoading}
            title={isWishlisted ? "Hapus dari Wishlist" : "Tambah ke Wishlist"}
        >
            <span class="heart-icon" class:active={isWishlisted}>
                {isWishlisted ? '❤️' : '🤍'}
            </span>
        </button>
    </div>

    <div class="product-info">
        <a href="/web/product/{product.id}" class="title-link">
            <h3 class="product-name">{product.name}</h3>
        </a>

        <div class="product-rating">
            <span class="stars">★★★★★</span> <span class="rating-value">{product.rating || 0}</span>
        </div>

        <div class="product-price">
            <span class="price">Rp {product.price.toLocaleString('id-ID')}</span>
        </div>

        <button 
            type="button" 
            class="btn-add-cart" 
            on:click={addToCartBackend}
            disabled={isLoadingCart || product.stock_quantity <= 0}
        >
            {isLoadingCart ? 'Memproses...' : (product.stock_quantity <= 0 ? 'Habis Stok' : '+ Keranjang')}
        </button>
    </div>
</div>

<style>
    .product-card {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s;
        position: relative;
        display: flex;
        flex-direction: column;
        border: 1px solid #e5e7eb; /* Ditambah border tipis agar mirip card di page */
    }
    
    .product-card:hover { transform: translateY(-4px); }

    /* Image Area */
    .product-image-area { position: relative; width: 100%; padding-top: 100%; background: #f5f5f5; }
    .image-link { position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: block; }
    .image-link img { width: 100%; height: 100%; object-fit: cover; }

    /* Wishlist Button */
    .wishlist-btn {
        position: absolute;
        top: 10px; right: 10px; z-index: 10;
        background: rgba(255,255,255,0.9); border: none; border-radius: 50%;
        width: 36px; height: 36px; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.15); transition: transform 0.2s;
    }
    .wishlist-btn:hover { transform: scale(1.1); }
    .heart-icon { font-size: 1.2rem; line-height: 1; }

    /* Info Area */
    .product-info { padding: 14px; flex: 1; display: flex; flex-direction: column; }
    
    .title-link { text-decoration: none; color: inherit; }
    .product-name { margin: 0 0 8px; font-size: 0.95rem; font-weight: 600; color: #1f2d3d; 
                    display: -webkit-box; -webkit-line-clamp: 2;
                    -webkit-box-orient: vertical; overflow: hidden; }
    .title-link:hover .product-name { color: #8E42E1; }

    .product-rating { display: flex; align-items: center; gap: 4px; font-size: 0.8rem; margin-bottom: 6px; color: #6b7280; }
    .stars { color: #fbbf24; letter-spacing: -2px; } /* Warna bintang disamakan */
    
    .product-price { margin-bottom: 12px; }
    .price { font-size: 1.1rem; font-weight: 700; color: #111; } /* Warna harga disamakan ke hitam seperti page */

    /* Add Cart Button - STYLE DISAMAKAN DENGAN +PAGE.SVELTE */
    .btn-add-cart {
        margin-top: auto;
        width: 100%; padding: 10px;
        /* Menggunakan gradient ungu yang sama dengan btn-primary di +page.svelte  */
        background: linear-gradient(135deg, #8E42E1 0%, #6B3BFF 100%);
        color: #fff; border: none; border-radius: 8px;
        font-weight: 600; cursor: pointer; font-size: 0.9rem;
        box-shadow: 0 4px 10px rgba(107,59,255,0.12);
    }
    .btn-add-cart:hover:not(:disabled) { opacity: 0.95; box-shadow: 0 6px 15px rgba(107,59,255,0.25); }
    .btn-add-cart:disabled { background: #d1d5db; cursor: not-allowed; box-shadow: none; }

    .out-of-stock {
        position: absolute; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.6); color: white;
        display: flex; align-items: center; justify-content: center;
        font-weight: bold; pointer-events: none;
    }
</style>