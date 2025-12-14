<script lang="ts">
    import { PUBLIC_API_URL, PUBLIC_STORAGE_URL } from '$env/static/public';
    import { goto } from '$app/navigation'; // Import goto untuk redirect

    export let product: {
        id: number;
        name: string;
        price: number;
        stock_quantity: number; 
        image: string; 
        rating?: number;
        is_wishlisted?: boolean;
    };

    let isLoading = false;
    
    // Inisialisasi status wishlist dari props produk
    let isWishlisted = product.is_wishlisted || false; 
    
    // Agar status update jika data produk dari parent berubah (misal saat search/filter)
    $: isWishlisted = product.is_wishlisted || false;

    const STORAGE_BASE = PUBLIC_STORAGE_URL.endsWith('/') ? PUBLIC_STORAGE_URL : `${PUBLIC_STORAGE_URL}/`;
    
    $: imageUrl = product.image && product.image.startsWith('http') 
        ? product.image 
        : `${STORAGE_BASE}storage/${product.image}`;

    // --- NAVIGASI KE DETAIL PRODUK ---
    function goToDetail() {
        goto(`/web/product/${product.id}`);
    }

    // --- FUNGSI ADD TO CART (DIPERBAIKI) ---
    async function handleAddToCart() {
        const token = localStorage.getItem('auth_token');
        if (!token) return alert('Silakan login terlebih dahulu');

        isLoading = true;
        try {
            const res = await fetch(`${PUBLIC_API_URL}/cart/${product.id}`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ quantity: 1 }) 
            });
            
            const data = await res.json();

            if (res.ok) {
                // FIX: Redirect ke halaman keranjang setelah sukses
                alert("Berhasil masuk keranjang!");
                goto('/web/cart'); 
            } else {
                alert(data.message || 'Gagal menambahkan');
            }
        } catch (err) {
            console.error(err);
            alert('Terjadi kesalahan koneksi');
        } finally {
            isLoading = false;
        }
    }

    // --- FUNGSI TOGGLE WISHLIST (DIPERBAIKI) ---
    async function handleToggleWishlist() {
        const token = localStorage.getItem('auth_token');
        if (!token) return alert('Silakan login terlebih dahulu');

        // 1. Simpan state lama buat jaga-jaga kalau error
        const previousState = isWishlisted;

        // 2. Optimistic UI: Ubah warna LANGSUNG sebelum fetch agar terasa cepat
        isWishlisted = !isWishlisted; 

        try {
            // URL Backend: /api/wishlist/{id}
            const res = await fetch(`${PUBLIC_API_URL}/wishlist/${product.id}`, {
                method: 'POST',
                headers: { 
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await res.json();
            
            if (!res.ok) {
                 // 3. Jika server error, KEMBALIKAN warna ke semula
                 isWishlisted = previousState;
                 alert(data.message || "Gagal update wishlist");
            }
        } catch (err) {
            console.error(err);
            // Jika koneksi putus, kembalikan warna
            isWishlisted = previousState; 
            alert("Gagal menghubungi server");
        }
    }
</script>

<div 
    class="product-card" 
    on:click={goToDetail} 
    on:keydown={(e) => e.key === 'Enter' && goToDetail()}
    role="button"
    tabindex="0"
>
    <div class="product-image">
        <img src={imageUrl} alt={product.name} on:error={(e) => e.target.src = '/images/placeholder.jpg'} />
        
        <button 
            type="button" 
            class="watchlist-btn" 
            on:click|stopPropagation={handleToggleWishlist}
            title={isWishlisted ? "Hapus dari Wishlist" : "Tambah ke Wishlist"}
        >
            {#if isWishlisted}
                ❤️ {:else}
                🤍 {/if}
        </button>

        {#if product.stock_quantity <= 0}
            <div class="out-of-stock">Habis Stok</div>
        {/if}
    </div>

    <div class="product-info">
        <h3 class="product-name">{product.name}</h3>

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
            on:click|stopPropagation={handleAddToCart} 
            disabled={product.stock_quantity <= 0 || isLoading}
        >
            {#if isLoading}
                Loading...
            {:else if product.stock_quantity <= 0}
                Habis Stok
            {:else}
                Tambah ke Keranjang
            {/if}
        </button>
    </div>
</div>

<style>
    /* CSS TETAP SAMA SEPERTI SEBELUMNYA */
    .product-card {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
    }

    .product-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
    }

    .product-image {
        position: relative;
        width: 100%;
        padding-top: 100%;
        background: #f5f5f5;
        overflow: hidden;
    }

    .product-image img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .out-of-stock {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(0, 0, 0, 0.6);
        color: #fff;
        font-weight: 700;
    }

    .product-info {
        padding: 16px;
    }

    .product-name {
        margin: 0 0 8px;
        font-size: 1rem;
        font-weight: 600;
        color: #1f2d3d;
        line-height: 1.3;
        min-height: 2.6em;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .product-rating {
        display: flex;
        align-items: center;
        gap: 4px;
        margin-bottom: 8px;
        font-size: 0.85rem;
    }

    .stars {
        color: #ffc107;
    }

    .rating-value {
        color: #666;
        font-weight: 600;
    }

    .product-price {
        margin-bottom: 12px;
    }

    .price {
        font-size: 1.2rem;
        font-weight: 700;
        color: #8E42E1;
    }

    .btn-add-cart {
        width: 100%;
        padding: 10px;
        background: linear-gradient(135deg, #7B4BFF, #4BB1FF);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: opacity 0.2s;
        position: relative; 
        z-index: 2;
    }

    .btn-add-cart:hover:not(:disabled) {
        opacity: 0.9;
    }

    .btn-add-cart:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .watchlist-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(255, 255, 255, 0.85);
        border: none;
        font-size: 1.4rem;
        padding: 4px 6px;
        border-radius: 50%;
        cursor: pointer;
        transition: transform 0.2s ease, background 0.2s;
        z-index: 2; 
    }

    .watchlist-btn:hover {
        transform: scale(1.15);
        background: white;
    }
</style>