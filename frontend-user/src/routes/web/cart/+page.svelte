<script lang="ts">
    import { goto } from '$app/navigation';
    import Breadcrumb from '$lib/components/Breadcrumb.svelte';
    import { cart } from '$lib/stores/cart'; 
    import { onMount } from 'svelte';
    import { PUBLIC_API_URL } from '$env/static/public'; // Pastikan variabel env ini ada

    // Kita akan fetch data keranjang dari server agar mendapatkan data stok terbaru dan sinkron
    // Namun untuk sementara kita gunakan subscribe ke store cart, dan asumsikan kita perlu fetch ulang untuk aksi update
    
    // State lokal untuk menampung item keranjang agar bisa reaktif instan
    let cartItems: any[] = [];
    let loading = false;

    // Subscribe ke store, tapi kita utamakan data dari API untuk stok
    cart.subscribe(value => {
        cartItems = value;
    });

    // Fetch data keranjang dari backend saat mount untuk dapat data stok terbaru
    onMount(async () => {
        await fetchCart();
    });

    async function fetchCart() {
        loading = true;
        try {
            const token = localStorage.getItem('auth_token');
            // Ganti URL sesuai endpoint API Anda
            const res = await fetch(`${PUBLIC_API_URL}/cart`, {
                 headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            if (res.ok) {
                const data = await res.json();
                // Update local state dan store
                cart.set(data.data.map((item: any) => ({
                    id: item.id, // ID item keranjang
                    product_id: item.product_id,
                    name: item.product.title,
                    price: item.product.price,
                    image: item.product.image_url,
                    quantity: item.quantity,
                    stock: item.product.stock_quantity // Simpan info stok
                })));
            }
        } catch (e) {
            console.error("Gagal fetch keranjang", e);
        } finally {
            loading = false;
        }
    }

    // Total harga reaktif
    $: total = cartItems.reduce((sum, it) => sum + (Number(it.price ?? 0) * (it.quantity ?? 1)), 0);

    const formatCurrency = (val: number) =>
        new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);

    async function updateQuantity(item: any, change: number) {
        const newQty = item.quantity + change;

        // Validasi Minimum
        if (newQty < 1) return;

        // Validasi Stok (Maksimum)
        if (item.stock !== undefined && newQty > item.stock) {
            alert(`Maaf, stok hanya tersedia ${item.stock} item.`);
            return;
        }

        // Optimistic UI Update (Update tampilan dulu biar cepat)
        const oldQty = item.quantity;
        item.quantity = newQty;
        cartItems = [...cartItems]; // Trigger reactivity

        try {
            const token = localStorage.getItem('auth_token');
            const res = await fetch(`${PUBLIC_API_URL}/cart/update/${item.id}`, {
                method: 'PUT', // Sesuaikan method dengan CartController (PUT/PATCH)
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ quantity: newQty })
            });

            if (!res.ok) {
                // Revert jika gagal
                item.quantity = oldQty;
                cartItems = [...cartItems];
                const err = await res.json();
                alert(err.message || 'Gagal mengubah jumlah');
            } else {
                // Update store agar sinkron
                cart.set(cartItems);
            }
        } catch (e) {
            console.error(e);
            item.quantity = oldQty; // Revert
            cartItems = [...cartItems];
            alert('Terjadi kesalahan koneksi');
        }
    }

    async function removeItem(id: string) {
        if(!confirm("Hapus item ini?")) return;
        
        // Optimistic remove
        const oldItems = [...cartItems];
        cartItems = cartItems.filter(i => i.id !== id);
        cart.set(cartItems);

        try {
            const token = localStorage.getItem('auth_token');
            const res = await fetch(`${PUBLIC_API_URL}/cart/delete/${id}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            if(!res.ok) {
                cartItems = oldItems;
                cart.set(cartItems);
                alert("Gagal menghapus item");
            }
        } catch(e) {
            cartItems = oldItems;
            cart.set(cartItems);
            alert("Error koneksi");
        }
    }

    async function clearAll() {
        if (!confirm('Kosongkan seluruh keranjang?')) return;
        // Implementasi clear all via API jika ada, atau loop delete
        // Untuk saat ini kita hapus satu per satu atau panggil endpoint khusus jika backend menyediakan
        // Di CartController tidak ada destroyAll, jadi kita loop atau hapus lokal saja (tapi ini tidak sinkron dgn DB)
        // Idealnya backend punya endpoint delete all.
        // Simulasi loop delete:
        for(let item of cartItems) {
            await removeItem(item.id); 
        }
    }

    function handleCheckout() {
        if (cartItems.length === 0) {
            alert('Keranjang belanja kosong!');
            return;
        }
        goto('/web/co');
    }
</script>

<svelte:head>
    <title>Keranjang Belanja - PC Store</title>
</svelte:head>

<div class="breadcrumb-wrapper">
    <div class="breadcrumb-pill">
        <a class="breadcrumb-link" href="/web">Home</a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">Keranjang Belanja</span>
    </div>
</div>

<main class="category-page">
    <div class="container">
        <header class="header-section">
            <h1>Keranjang Belanja</h1>
            <p class="subtitle">Produk yang Anda tambahkan untuk dibeli</p>
        </header>

        {#if loading}
            <div class="loading-state">Memuat keranjang...</div>
        {:else if cartItems.length === 0}
            <section class="empty-state">
                <div class="empty-icon">🛒</div>
                <h2>Keranjang Anda Kosong</h2>
                <p>Belum ada produk yang ditambahkan ke keranjang.</p>
                <button class="btn-continue" on:click={() => goto('/web')}>Lanjut Belanja</button>
            </section>
        {:else}
            <section class="cart-list">
                {#each cartItems as item (item.id)}
                    <div class="cart-item">
                        <img src={item.image || '/images/placeholder.png'} alt={item.name} class="cart-item-img" />
                        
                        <div class="cart-item-info">
                            <h3>{item.name}</h3>
                            <div class="price-tag">{formatCurrency(Number(item.price))}</div>
                            
                            <div class="quantity-control">
                                <button class="qty-btn" on:click={() => updateQuantity(item, -1)} disabled={item.quantity <= 1}>−</button>
                                <span class="qty-val">{item.quantity}</span>
                                <button class="qty-btn" on:click={() => updateQuantity(item, 1)} disabled={item.stock !== undefined && item.quantity >= item.stock}>+</button>
                                {#if item.stock !== undefined}
                                    <span class="stock-info">(Stok: {item.stock})</span>
                                {/if}
                            </div>
                        </div>

                        <div class="cart-item-actions">
                            <div class="item-total">
                                {formatCurrency(Number(item.price) * item.quantity)}
                            </div>
                            <button class="btn-remove" on:click={() => removeItem(item.id)}>Hapus</button>
                        </div>
                    </div>
                {/each}

                <div class="cart-actions-footer">
                    <div class="cart-total">Total: <strong>{formatCurrency(total)}</strong></div>
                    <div class="cart-buttons">
                        <button class="btn-clear" on:click={clearAll}>Kosongkan</button>
                        <button class="btn-continue" on:click={() => goto('/web')}>+ Produk</button>
                        <button class="btn-checkout" on:click={handleCheckout}>
                            Checkout
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left:5px;"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                        </button>
                    </div>
                </div>
            </section>
        {/if}
    </div>
</main>

<style>
    /* ... (Style Breadcrumb & Layout Lama Tetap Sama) ... */
    .breadcrumb-wrapper { width: 95%; margin: 20px auto; padding: 0; display: flex; justify-content: flex-start; position: relative; z-index: 1; }
    .breadcrumb-pill { display: inline-flex; align-items: center; gap: 10px; background-color: #0f172a; border-radius: 50px; padding: 10px 24px; font-size: 0.9rem; font-weight: 500; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid rgba(255,255,255,0.05); }
    .breadcrumb-link { color: #94a3b8; text-decoration: none; cursor: pointer; transition: color 0.2s; }
    .breadcrumb-link:hover { color: #fff; }
    .breadcrumb-sep { color: #475569; font-size: 0.8rem; }
    .breadcrumb-current { color: #ff0055; font-weight: 700; }
    .category-page { background: #f7f7f7; min-height: 100vh; }
    .container { max-width: 95%; margin: 0 auto; padding: 40px 20px; }
    .header-section { margin-bottom: 40px; text-align: center; }
    .header-section h1 { font-size: 2.5rem; font-weight: 800; color: #1f2d3d; margin: 0 0 12px; }
    .header-section p { font-size: 1.1rem; color: #64748b; margin: 0; }
    .empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 80px 20px; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center; }
    .empty-icon { font-size: 4rem; margin-bottom: 20px; }
    .empty-state h2 { font-size: 1.8rem; font-weight: 800; color: #1f2d3d; margin: 0 0 12px; }
    .empty-state p { font-size: 1rem; color: #64748b; margin: 0 0 28px; max-width: 400px; }
    .btn-continue { padding: 12px 28px; background: linear-gradient(90deg, #7c3aed, #4f46e5); color: #fff; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; transition: all 0.2s; }
    .btn-continue:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3); }
    .btn-checkout { padding: 12px 28px; background: #0f172a; color: #fff; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; }
    .btn-checkout:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(15, 23, 42, 0.3); background: #1e293b; }
    .cart-list { display: flex; flex-direction: column; gap: 24px; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); padding: 40px 20px; margin-bottom: 32px; }
    
    .cart-item { display: flex; align-items: flex-start; gap: 24px; border-bottom: 1px solid #e5e7eb; padding-bottom: 24px; position: relative; }
    .cart-item:last-child { border-bottom: none; }
    
    .cart-item-img { width: 100px; height: 100px; object-fit: cover; border-radius: 10px; border: 1px solid #eee; }
    
    .cart-item-info { flex: 1; display: flex; flex-direction: column; gap: 8px; }
    .cart-item-info h3 { margin: 0; font-size: 1.1rem; color: #1f2d3d; line-height: 1.4; }
    .price-tag { font-weight: 600; color: #4f46e5; font-size: 1rem; }

    /* Style Quantity Control */
    .quantity-control { display: flex; align-items: center; gap: 10px; margin-top: 5px; }
    .qty-btn { width: 32px; height: 32px; border-radius: 6px; border: 1px solid #d1d5db; background: #f8fafc; color: #334155; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.1s; }
    .qty-btn:hover:not(:disabled) { background: #e2e8f0; border-color: #cbd5e1; }
    .qty-btn:active:not(:disabled) { transform: translateY(1px); }
    .qty-btn:disabled { opacity: 0.5; cursor: not-allowed; }
    .qty-val { font-weight: 700; width: 30px; text-align: center; color: #1e293b; }
    .stock-info { font-size: 0.8rem; color: #94a3b8; font-style: italic; margin-left: 5px; }

    .cart-item-actions { display: flex; flex-direction: column; align-items: flex-end; gap: 10px; min-width: 120px; }
    .item-total { font-size: 1.1rem; font-weight: 800; color: #1f2d3d; }
    
    .btn-remove { padding: 6px 12px; background: transparent; border: 1px solid #ef4444; color: #ef4444; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.85rem; transition: all 0.2s; }
    .btn-remove:hover { background: #fee2e2; }

    .cart-actions-footer { display: flex; align-items: center; justify-content: space-between; margin-top: 16px; gap: 16px; border-top: 2px dashed #f1f5f9; padding-top: 24px; }
    .cart-total { font-size: 1.25rem; color: #111827; }
    .cart-buttons { display:flex; gap:12px; align-items: center; }
    .btn-clear { padding: 10px 16px; background: #fff; border: 1px solid #e5e7eb; color: #ef4444; border-radius: 8px; cursor: pointer; font-weight: 700; }
    .btn-clear:hover { background: rgba(239,68,68,0.06); }
    .loading-state { padding: 40px; text-align: center; color: #64748b; }

    @media (max-width:1024px) { .container { padding:18px; } }
    @media (max-width:640px) { .header-section h1{font-size:20px;} }
    @media (max-width: 768px) {
        .breadcrumb-wrapper { width: 100%; margin: 16px auto; }
        .container { padding: 0 16px; }
        .header-section h1 { font-size: 2rem; }
        .breadcrumb-pill { padding: 8px 16px; font-size: 0.85rem; }
        .empty-state { padding: 60px 20px; }
        .empty-icon { font-size: 3rem; }
        .empty-state h2 { font-size: 1.4rem; }
        
        /* Layout Mobile Cart Item */
        .cart-item { flex-direction: row; flex-wrap: wrap; }
        .cart-item-img { width: 70px; height: 70px; }
        .cart-item-info { min-width: 60%; }
        .cart-item-actions { width: 100%; flex-direction: row; justify-content: space-between; align-items: center; margin-top: 10px; border-top: 1px solid #f1f5f9; padding-top: 10px; }
        
        .cart-actions-footer { flex-direction: column; align-items: flex-start; }
        .cart-buttons { width: 100%; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
        .btn-checkout { flex-grow: 1; justify-content: center; width: 100%; }
        .btn-continue, .btn-clear { flex: 1; }
    }
</style>