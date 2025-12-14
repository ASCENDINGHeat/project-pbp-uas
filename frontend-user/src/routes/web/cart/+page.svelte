<script lang="ts">
    import { goto } from '$app/navigation';
    import Breadcrumb from '$lib/components/Breadcrumb.svelte';
    import { cart, removeFromCart, clearCart } from '$lib/stores/cart'; 
    
    // Gunakan auto-subscribe Svelte
    $: cartItems = $cart;
    
    // total harga reaktif
    $: total = cartItems.reduce((sum, it) => sum + (Number(it.price ?? 0) * (it.quantity ?? 1)), 0);

    const formatCurrency = (val: number) =>
        new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);

    function removeItem(id: string) {
        removeFromCart(id);
    }

    function clearAll() {
        if (confirm('Kosongkan seluruh keranjang?')) {
            clearCart();
        }
    }

    // --- FUNGSI BARU: Handle Checkout ---
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

        {#if cartItems.length === 0}
            <section class="empty-state">
                <div class="empty-icon">🛒</div>
                <h2>Keranjang Anda Kosong</h2>
                <p>Belum ada produk yang ditambahkan ke keranjang.</p>
                <button class="btn-continue" on:click={() => goto('/web')}>Lanjut Belanja</button>
            </section>
        {:else}
            <section class="cart-list">
                {#each cartItems as item}
                    <div class="cart-item">
                        <img src={item.image} alt={item.name} class="cart-item-img" />
                        <div class="cart-item-info">
                            <h3>{item.name}</h3>
                            <p>Jumlah: {item.quantity}</p>
                            <p>Harga: Rp {item.price}</p>
                        </div>
                        <div class="cart-item-actions">
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
    /* breadcrumb wrapper - simple container */
    .breadcrumb-wrapper {
        width: 95%;
        margin: 20px auto;
        padding: 0;
        display: flex;
        justify-content: flex-start;
        position: relative;
        z-index: 1;
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

    .category-page { background: #f7f7f7; min-height: 100vh; }
    .container { max-width: 95%; margin: 0 auto; padding: 40px 20px; }

    .header-section { margin-bottom: 40px; text-align: center; }
    .header-section h1 {
        font-size: 2.5rem;
        font-weight: 800;
        color: #1f2d3d;
        margin: 0 0 12px;
    }
    .header-section p {
        font-size: 1.1rem;
        color: #64748b;
        margin: 0;
    }

    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 80px 20px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        text-align: center;
    }

    .empty-icon {
        font-size: 4rem;
        margin-bottom: 20px;
    }

    .empty-state h2 {
        font-size: 1.8rem;
        font-weight: 800;
        color: #1f2d3d;
        margin: 0 0 12px;
    }

    .empty-state p {
        font-size: 1rem;
        color: #64748b;
        margin: 0 0 28px;
        max-width: 400px;
    }

    .btn-continue {
        padding: 12px 28px;
        background: linear-gradient(90deg, #7c3aed, #4f46e5);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-continue:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
    }

    /* CSS BARU UNTUK TOMBOL CHECKOUT */
    .btn-checkout {
        padding: 12px 28px;
        background: #0f172a; /* Warna gelap agar kontras */
        color: #fff;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
    }

    .btn-checkout:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.3);
        background: #1e293b;
    }

    .cart-list {
        display: flex;
        flex-direction: column;
        gap: 24px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 40px 20px;
        margin-bottom: 32px;
    }
    .cart-item {
        display: flex;
        align-items: center;
        gap: 24px;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 16px;
        position: relative;
    }
    .cart-item-img {
        width: 80px; 
        height: 80px; 
        object-fit: cover; 
        border-radius: 8px;
    }
    .cart-item-actions { margin-left: auto; }
    .btn-remove {
        padding: 8px 12px;
        background: transparent;
        border: 1px solid #ef4444;
        color: #ef4444;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 700;
    }
    .btn-remove:hover { background: rgba(239,68,68,0.08); }
    .cart-actions-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 16px;
        gap: 16px;
    }
    .cart-total { font-size: 1.1rem; color: #111827; }
    .cart-buttons { display:flex; gap:12px; align-items: center; }
    
    .btn-clear {
        padding: 10px 16px;
        background: #fff;
        border: 1px solid #e5e7eb;
        color: #ef4444;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 700;
    }
    .btn-clear:hover { background: rgba(239,68,68,0.06); }

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
        
        /* Penyesuaian responsif untuk tombol */
        .cart-actions-footer { flex-direction: column; align-items: flex-start; }
        .cart-buttons { width: 100%; justify-content: space-between; flex-wrap: wrap; }
        .btn-checkout { flex-grow: 1; justify-content: center; }
    }
</style>