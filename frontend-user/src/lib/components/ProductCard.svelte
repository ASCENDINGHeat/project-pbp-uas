<script lang="ts">
    import { PUBLIC_API_URL, PUBLIC_STORAGE_URL } from '$env/static/public';
    import { goto } from '$app/navigation';
    import { browser } from '$app/environment';

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
    let isWishlistLoading = false;

    // State management untuk wishlist - PERBAIKAN: Tidak auto-reset
    let isWishlisted = product.is_wishlisted || false;
    let currentProductId = product.id;

    // Reset state hanya jika produk ID berubah
    $: if (product.id !== currentProductId) {
        isWishlisted = product.is_wishlisted || false;
        currentProductId = product.id;
    }

    // Image URL handling
    const STORAGE_BASE = PUBLIC_STORAGE_URL.endsWith('/') 
        ? PUBLIC_STORAGE_URL 
        : `${PUBLIC_STORAGE_URL}/`;
    
    $: imageUrl = product.image && product.image.startsWith('http')
        ? product.image
        : `${STORAGE_BASE}storage/${product.image}`;

    // Helper function untuk mendapatkan token
    function getAuthToken(): string | null {
        if (!browser) return null;
        return localStorage.getItem('auth_token') || localStorage.getItem('token');
    }

    // Navigasi ke detail produk
    function goToDetail(e: MouseEvent | KeyboardEvent) {
        const target = e.target as HTMLElement;
        // Jangan navigate jika klik tombol
        if (target.closest('button') || target.closest('.action-blocker')) {
            return;
        }
        goto(`/web/product/${product.id}`);
    }

    // FUNGSI ADD TO CART - DIPERBAIKI
    async function handleAddToCart(event: MouseEvent) {
        event.stopPropagation();
        
        const token = getAuthToken();
        
        if (!token) {
            alert('Silakan login terlebih dahulu');
            goto('/login');
            return;
        }

        // Validasi stok
        if (product.stock_quantity <= 0) {
            alert('Maaf, stok barang ini sedang habis');
            return;
        }

        isLoading = true;
        
        try {
            console.log('Adding to cart:', {
                url: `${PUBLIC_API_URL}/cart/${product.id}`,
                productId: product.id,
                quantity: 1
            });

            const response = await fetch(`${PUBLIC_API_URL}/cart/${product.id}`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ quantity: 1 })
            });

            const data = await response.json();
            console.log('Cart response:', data);

            if (response.ok) {
                alert('Berhasil ditambahkan ke keranjang!');
                // Optional: redirect ke cart atau tetap di halaman
                // goto('/web/cart');
            } else {
                // Handle error dari backend
                if (response.status === 401) {
                    alert('Sesi Anda telah berakhir. Silakan login kembali.');
                    localStorage.removeItem('auth_token');
                    localStorage.removeItem('token');
                    goto('/login');
                } else if (response.status === 404) {
                    alert('Produk tidak ditemukan');
                } else if (response.status === 400) {
                    alert(data.message || 'Stok tidak mencukupi');
                } else {
                    alert(data.message || 'Gagal menambahkan ke keranjang');
                }
            }
        } catch (error) {
            console.error('Error adding to cart:', error);
            alert('Terjadi kesalahan koneksi. Pastikan backend Anda berjalan.');
        } finally {
            isLoading = false;
        }
    }

    // FUNGSI TOGGLE WISHLIST - DIPERBAIKI
    async function handleToggleWishlist(event: MouseEvent) {
        event.stopPropagation();
        
        const token = getAuthToken();
        
        if (!token) {
            alert('Silakan login terlebih dahulu');
            goto('/login');
            return;
        }

        // Simpan state sebelumnya untuk rollback jika error
        const previousState = isWishlisted;
        
        // Optimistic UI update
        isWishlisted = !isWishlisted;
        isWishlistLoading = true;

        try {
            console.log('Toggling wishlist:', {
                url: `${PUBLIC_API_URL}/wishlist/${product.id}`,
                productId: product.id,
                currentState: isWishlisted
            });

            const response = await fetch(`${PUBLIC_API_URL}/wishlist/${product.id}`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            console.log('Wishlist response:', data);

            if (!response.ok) {
                // Rollback jika error
                isWishlisted = previousState;
                
                if (response.status === 401) {
                    alert('Sesi Anda telah berakhir. Silakan login kembali.');
                    localStorage.removeItem('auth_token');
                    localStorage.removeItem('token');
                    goto('/login');
                } else {
                    alert(data.message || 'Gagal mengubah wishlist');
                }
            } else {
                // Berhasil - state sudah diupdate di optimistic UI
                console.log('Wishlist updated successfully:', data.status);
            }
        } catch (error) {
            console.error('Error toggling wishlist:', error);
            // Rollback jika koneksi error
            isWishlisted = previousState;
            alert('Terjadi kesalahan koneksi. Pastikan backend Anda berjalan.');
        } finally {
            isWishlistLoading = false;
        }
    }
</script>

<div 
    class="product-card" 
    on:click={goToDetail} 
    on:keydown={(e) => e.key === 'Enter' && goToDetail(e)}
    role="button"
    tabindex="0"
>
    <div class="product-image">
        <img 
            src={imageUrl} 
            alt={product.name} 
            on:error={(e) => {
                const target = e.target as HTMLImageElement;
                target.src = '/images/placeholder.jpg';
            }}
        />
        
        <!-- Wishlist Button -->
        <div 
            class="action-blocker wishlist-pos" 
            on:click|stopPropagation 
            on:keydown|stopPropagation 
            role="none"
        >
            <button 
                type="button" 
                class="watchlist-btn" 
                on:click={handleToggleWishlist}
                disabled={isWishlistLoading}
                title={isWishlisted ? "Hapus dari Wishlist" : "Tambah ke Wishlist"}
            >
                {#if isWishlistLoading}
                    <span style="font-size: 1.2rem;">⏳</span>
                {:else if isWishlisted}
                    <span style="color: red; font-size: 1.5rem; line-height: 1;">❤️</span>
                {:else}
                    <span style="color: gray; font-size: 1.5rem; line-height: 1;">🤍</span>
                {/if}
            </button>
        </div>

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

        <!-- Add to Cart Button -->
        <div class="action-blocker" on:click|stopPropagation role="none">
            <button 
                type="button"    
                class="btn-add-cart" 
                on:click={handleAddToCart} 
                disabled={isLoading || product.stock_quantity <= 0}
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
</div>

<style>
    .product-card {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
        position: relative;
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
        z-index: 5;
        pointer-events: none;
    }

    .product-info {
        padding: 16px;
        position: relative;
        z-index: 2;
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

    /* Action Blocker - Penting untuk mencegah event bubbling */
    .action-blocker {
        position: relative;
        z-index: 20;
        display: block;
    }

    .wishlist-pos {
        position: absolute;
        top: 10px;
        right: 10px;
        width: auto;
    }

    .watchlist-btn {
        background: rgba(255, 255, 255, 0.9);
        border: none;
        padding: 8px;
        border-radius: 50%;
        cursor: pointer;
        transition: transform 0.2s ease, background 0.2s;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        min-height: 40px;
    }

    .watchlist-btn:hover:not(:disabled) {
        transform: scale(1.15);
        background: white;
    }

    .watchlist-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
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
    }

    .btn-add-cart:hover:not(:disabled) { 
        opacity: 0.9; 
    }
    
    .btn-add-cart:disabled { 
        opacity: 0.5; 
        cursor: not-allowed; 
    }
</style>