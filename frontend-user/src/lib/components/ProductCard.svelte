<script lang="ts">
    import { PUBLIC_API_URL, PUBLIC_STORAGE_URL } from '$env/static/public';
    import { goto } from '$app/navigation';
    import { browser } from '$app/environment';
    
    // Import Stores untuk update instan
    import { addToCart } from '$lib/stores/cart';
    import { wishlist, addToWishlistStore, removeFromWishlistStore } from '$lib/stores/wishlist';

    export let product: {
        id: number;
        title: string;
        price: number;
        stock_quantity: number;
        image: string;
        rating?: number;
        is_wishlisted?: boolean;
        name?: string;
    };

    $: displayTitle = product.title || product.name || 'Untitled Product';

    let isLoading = false;
    let isWishlistLoading = false;

    // Reactive state untuk wishlist
    $: isWishlisted = $wishlist.some(i => i.id === product.id) || product.is_wishlisted || false;

    const STORAGE_BASE = PUBLIC_STORAGE_URL.endsWith('/') ? PUBLIC_STORAGE_URL : `${PUBLIC_STORAGE_URL}/`;
    $: imageUrl = product.image && product.image.startsWith('http') 
        ? product.image 
        : `${STORAGE_BASE}storage/${product.image}`;

    function getAuthToken(): string | null {
        if (!browser) return null;
        return localStorage.getItem('auth_token');
    }

    // --- ADD TO CART ---
    async function handleAddToCart() {
        const token = getAuthToken();
        if (!token) {
            alert('Silakan login terlebih dahulu');
            return goto('/web/login');
        }

        if (product.stock_quantity <= 0) {
            return; // Stok habis, tidak perlu alert terus menerus
        }

        isLoading = true;
        try {
            const res = await fetch(`${PUBLIC_API_URL}/cart/${product.id}`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ quantity: 1 })
            });

            const data = await res.json();

            if (res.ok) {
                addToCart({
                    id: String(data.data?.id || Date.now()),
                    product_id: product.id,
                    name: product.title,
                    price: product.price,
                    image: imageUrl,
                    quantity: 1,
                    stock: product.stock_quantity
                });
                // alert('Produk masuk keranjang!'); // Opsional: Matikan jika mengganggu
            } else {
                console.error("Gagal tambah cart:", data.message);
                // alert(data.message || 'Gagal menambahkan'); // Ganti dengan console.error
            }
        } catch (e) {
            console.error("Error Cart:", e);
            // Alert koneksi dimatikan agar tidak spamming error
        } finally {
            isLoading = false;
        }
    }

    // --- WISHLIST ---
    async function handleToggleWishlist() {
        const token = getAuthToken();
        if (!token) {
            alert('Silakan login terlebih dahulu');
            return goto('/web/login');
        }

        const wasWishlisted = isWishlisted;
        
        // Optimistic Update
        if (wasWishlisted) {
            removeFromWishlistStore(product.id);
        } else {
            addToWishlistStore({
                id: product.id,
                name: product.title,
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
                // Rollback jika gagal
                if (wasWishlisted) addToWishlistStore({ id: product.id, name: product.title, price: product.price, image: imageUrl });
                else removeFromWishlistStore(product.id);
                console.error("Gagal update wishlist");
            }
        } catch (e) {
            // Rollback jika error koneksi
            if (wasWishlisted) addToWishlistStore({ id: product.id, name: product.title, price: product.price, image: imageUrl });
            else removeFromWishlistStore(product.id);
            console.error("Error Wishlist:", e);
        } finally {
            isWishlistLoading = false;
        }
    }

    // --- PERBAIKAN: Handler Gambar Error ---
    function handleImageError(e: Event) {
        const target = e.target as HTMLImageElement;
        // Hapus handler agar tidak looping
        target.onerror = null; 
        // Set ke gambar placeholder (Pastikan file ini ada di folder static!)
        target.src = '/images/placeholder.jpg'; 
    }
</script>

<div class="product-card">
    <div class="product-image-area">
        <a href="/web/product/{product.id}" class="image-link" aria-label="Lihat detail {product.title}">
            <img src={product.image} alt={product.title} on:error={handleImageError} />
            
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
            <span class="heart-icon" style:color={isWishlisted ? 'red' : 'gray'}>
                {isWishlisted ? '❤️' : '🤍'}
            </span>
        </button>
    </div>

    <div class="product-info">
        <a href="/web/product/{product.id}" class="title-link">
            <h3 class="product-name">{product.title}</h3>
        </a>

        <div class="product-rating">
            <span class="stars">★</span>
            <span class="rating-value">{product.rating || 0}</span>
        </div>

        <div class="product-price">
            <span class="price">Rp {product.price.toLocaleString('id-ID')}</span>
        </div>

        <button 
            type="button" 
            class="btn-add-cart" 
            on:click={handleAddToCart}
            disabled={isLoading || product.stock_quantity <= 0}
        >
            {isLoading ? 'Memproses...' : (product.stock_quantity <= 0 ? 'Habis Stok' : 'Tambah ke Keranjang')}
        </button>
    </div>
</div>

<style>
    /* Style tetap sama seperti sebelumnya */
    .product-card {
        background: #fff; border-radius: 12px; overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); transition: transform 0.2s;
        position: relative; display: flex; flex-direction: column;
    }
    .product-card:hover { transform: translateY(-4px); }

    .product-image-area { position: relative; width: 100%; padding-top: 100%; background: #f5f5f5; }
    .image-link { position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: block; }
    .image-link img { width: 100%; height: 100%; object-fit: cover; }

    .wishlist-btn {
        position: absolute; top: 10px; right: 10px; z-index: 10;
        background: rgba(255,255,255,0.9); border: none; border-radius: 50%;
        width: 36px; height: 36px; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.15); transition: transform 0.2s;
    }
    .wishlist-btn:hover { transform: scale(1.1); }
    .heart-icon { font-size: 1.2rem; line-height: 1; }

    .product-info { padding: 14px; flex: 1; display: flex; flex-direction: column; }
    .title-link { text-decoration: none; color: inherit; }
    .product-name { margin: 0 0 8px; font-size: 0.95rem; font-weight: 600; color: #1f2d3d; 
                    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .title-link:hover .product-name { color: #8E42E1; }

    .product-rating { display: flex; align-items: center; gap: 4px; font-size: 0.8rem; margin-bottom: 6px; }
    .stars { color: #ffc107; }
    
    .product-price { margin-bottom: 12px; }
    .price { font-size: 1.1rem; font-weight: 700; color: #8E42E1; }

    .btn-add-cart {
        margin-top: auto; width: 100%; padding: 10px;
        background: linear-gradient(135deg, #7B4BFF, #4BB1FF);
        color: #fff; border: none; border-radius: 8px;
        font-weight: 600; cursor: pointer; font-size: 0.9rem;
    }
    .btn-add-cart:hover:not(:disabled) { opacity: 0.9; }
    .btn-add-cart:disabled { background: #ccc; cursor: not-allowed; }

    .out-of-stock {
        position: absolute; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.6); color: white;
        display: flex; align-items: center; justify-content: center;
        font-weight: bold; pointer-events: none;
    }
</style>