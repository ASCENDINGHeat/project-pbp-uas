<script lang="ts">
    import { goto } from '$app/navigation';
    import { onMount } from 'svelte';
    // Import selectedItemIds dari store
    import { cart, selectedItemIds } from '$lib/stores/cart'; 
    import { PUBLIC_API_URL } from '$env/static/public';

    let cartItems: any[] = [];
    let loading = false;
    
    // State lokal untuk checkbox (ID Cart)
    let selectedIds: string[] = [];

    // Subscribe ke store cart
    cart.subscribe(value => {
        cartItems = value;
    });

    // Subscribe ke store selectedItemIds (agar sinkron saat tombol back ditekan)
    selectedItemIds.subscribe(val => {
        selectedIds = val;
    });

    onMount(async () => {
        await fetchCart();
    });

    async function fetchCart() {
        loading = true;
        try {
            const token = localStorage.getItem('auth_token');
            const res = await fetch(`${PUBLIC_API_URL}/cart`, {
                 headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            if (res.ok) {
                const data = await res.json();
                // Mapping data Backend -> Frontend
                cart.set(data.data.map((item: any) => ({
                    id: item.id,                 // ID Cart (PENTING: Ini yang dikirim ke backend)
                    product_id: item.product_id, // ID Produk
                    name: item.product.title,
                    price: Number(item.product.price),
                    image: item.product.image_url,
                    quantity: item.quantity,
                    stock: item.product.stock_quantity
                })));
            }
        } catch (e) {
            console.error("Gagal fetch keranjang", e);
        } finally {
            loading = false;
        }
    }

    // --- Logic Checkbox ---

    function toggleItem(id: string) {
        if (selectedIds.includes(id)) {
            selectedIds = selectedIds.filter(itemId => itemId !== id);
        } else {
            selectedIds = [...selectedIds, id];
        }
    }

    function toggleAll() {
        if (selectedIds.length === cartItems.length) {
            selectedIds = []; // Uncheck semua
        } else {
            selectedIds = cartItems.map(item => item.id); // Check semua
        }
    }

    // Hitung Total (Hanya item yang dicentang)
    $: total = cartItems
        .filter(item => selectedIds.includes(item.id))
        .reduce((sum, it) => sum + (Number(it.price ?? 0) * (it.quantity ?? 1)), 0);

    const formatCurrency = (val: number) =>
        new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);

    async function updateQuantity(item: any, change: number) {
        const newQty = item.quantity + change;
        if (newQty < 1) return;
        
        if (item.stock !== undefined && newQty > item.stock) {
            alert(`Maaf, stok hanya tersedia ${item.stock} item.`);
            return;
        }

        const oldQty = item.quantity;
        item.quantity = newQty;
        cartItems = [...cartItems]; 

        try {
            const token = localStorage.getItem('auth_token');
            const res = await fetch(`${PUBLIC_API_URL}/cart/update/${item.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ quantity: newQty })
            });
            if (!res.ok) {
                item.quantity = oldQty; 
                cartItems = [...cartItems];
                const err = await res.json();
                alert(err.message || 'Gagal mengubah jumlah');
            } else {
                cart.set(cartItems); 
            }
        } catch (e) {
            item.quantity = oldQty;
            cartItems = [...cartItems];
        }
    }

    async function removeItem(id: string) {
        if(!confirm("Hapus item ini?")) return;
        
        if (selectedIds.includes(id)) {
            selectedIds = selectedIds.filter(i => i !== id);
        }

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
        }
    }

    async function clearAll() {
        if (!confirm('Kosongkan seluruh keranjang?')) return;
        for(let item of cartItems) {
            await removeItem(item.id);
        }
        selectedIds = [];
        selectedItemIds.set([]); 
    }

    // --- PERBAIKAN UTAMA DISINI ---
    function handleCheckout() {
        // Validasi: Harus ada yang dicentang di selectedIds (bukan cartItems)
        if (selectedIds.length === 0) {
            alert('Silakan pilih produk terlebih dahulu (centang kotak).');
            return;
        }
        
        // Simpan ID yang dipilih ke Store Global agar dibaca halaman checkout
        selectedItemIds.set(selectedIds);
        
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
        </header>

        {#if loading}
            <div class="loading-state">Memuat keranjang...</div>
        {:else if cartItems.length === 0}
            <section class="empty-state">
                <div class="empty-icon">🛒</div>
                <h2>Keranjang Anda Kosong</h2>
                <button class="btn-continue" on:click={() => goto('/web')}>Lanjut Belanja</button>
            </section>
        {:else}
            <section class="cart-list">
                <div class="cart-header-actions">
                    <label class="checkbox-container select-all">
                        <input type="checkbox" 
                            checked={cartItems.length > 0 && selectedIds.length === cartItems.length} 
                            on:change={toggleAll} 
                        />
                        <span class="checkmark"></span>
                        Pilih Semua ({cartItems.length})
                    </label>
                    <button class="btn-clear-text" on:click={clearAll}>Hapus Semua</button>
                </div>

                {#each cartItems as item (item.id)}
                    <div class="cart-item {selectedIds.includes(item.id) ? 'selected' : ''}">
                        
                        <div class="item-checkbox">
                            <label class="checkbox-container">
                                <input type="checkbox" 
                                    checked={selectedIds.includes(item.id)} 
                                    on:change={() => toggleItem(item.id)} 
                                />
                                <span class="checkmark"></span>
                            </label>
                        </div>

                        <img src={item.image || '/images/placeholder.png'} alt={item.name} class="cart-item-img" />
                        
                        <div class="cart-item-info">
                            <h3>{item.name}</h3>
                            <div class="price-tag">{formatCurrency(Number(item.price))}</div>
                            
                            <div class="quantity-control">
                                <button class="qty-btn" on:click={() => updateQuantity(item, -1)} disabled={item.quantity <= 1}>−</button>
                                <span class="qty-val">{item.quantity}</span>
                                <button class="qty-btn" on:click={() => updateQuantity(item, 1)} disabled={item.stock !== undefined && item.quantity >= item.stock}>+</button>
                            </div>
                            {#if item.stock !== undefined}
                                <div class="stock-info">Stok: {item.stock}</div>
                            {/if}
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
                    <div class="cart-total">
                        Total ({selectedIds.length} Barang): <strong>{formatCurrency(total)}</strong>
                    </div>
                    <div class="cart-buttons">
                        <button class="btn-continue" on:click={() => goto('/web')}>+ Produk</button>
                        <button class="btn-checkout" on:click={handleCheckout} disabled={selectedIds.length === 0}>
                            Checkout
                        </button>
                    </div>
                </div>
            </section>
        {/if}
    </div>
</main>

<style>
    .cart-header-actions { display: flex; justify-content: space-between; align-items: center; padding-bottom: 15px; border-bottom: 2px solid #f1f5f9; }
    .btn-clear-text { background: none; border: none; color: #ef4444; font-weight: 600; cursor: pointer; }
    .cart-item { display: flex; align-items: center; gap: 16px; border-bottom: 1px solid #e5e7eb; padding-bottom: 24px; transition: background 0.2s; padding: 15px; border-radius: 8px; }
    .cart-item.selected { background-color: #f8fafc; border: 1px solid #e2e8f0; }
    .item-checkbox { display: flex; align-items: center; justify-content: center; }
    .checkbox-container { display: block; position: relative; padding-left: 30px; margin-bottom: 0; cursor: pointer; font-size: 1rem; user-select: none; font-weight: 600; color: #334155; }
    .checkbox-container input { position: absolute; opacity: 0; cursor: pointer; height: 0; width: 0; }
    .checkmark { position: absolute; top: -2px; left: 0; height: 22px; width: 22px; background-color: #fff; border: 2px solid #cbd5e1; border-radius: 6px; }
    .checkbox-container:hover input ~ .checkmark { background-color: #f1f5f9; }
    .checkbox-container input:checked ~ .checkmark { background-color: #4f46e5; border-color: #4f46e5; }
    .checkmark:after { content: ""; position: absolute; display: none; }
    .checkbox-container input:checked ~ .checkmark:after { display: block; }
    .checkbox-container .checkmark:after { left: 7px; top: 3px; width: 5px; height: 10px; border: solid white; border-width: 0 2px 2px 0; transform: rotate(45deg); }
    .select-all { display: flex; align-items: center; height: 20px; } 
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
    .empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 80px 20px; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center; }
    .empty-icon { font-size: 4rem; margin-bottom: 20px; }
    .empty-state h2 { font-size: 1.8rem; font-weight: 800; color: #1f2d3d; margin: 0 0 12px; }
    .btn-continue { padding: 12px 28px; background: linear-gradient(90deg, #7c3aed, #4f46e5); color: #fff; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; transition: all 0.2s; }
    .btn-checkout { padding: 12px 28px; background: #0f172a; color: #fff; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; }
    .btn-checkout:disabled { background: #ccc; cursor: not-allowed; }
    .cart-list { display: flex; flex-direction: column; gap: 24px; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); padding: 40px 20px; margin-bottom: 32px; }
    .cart-item-img { width: 100px; height: 100px; object-fit: cover; border-radius: 10px; border: 1px solid #eee; }
    .cart-item-info { flex: 1; display: flex; flex-direction: column; gap: 8px; }
    .cart-item-info h3 { margin: 0; font-size: 1.1rem; color: #1f2d3d; line-height: 1.4; }
    .price-tag { font-weight: 600; color: #4f46e5; font-size: 1rem; }
    .quantity-control { display: flex; align-items: center; gap: 10px; margin-top: 5px; }
    .qty-btn { width: 32px; height: 32px; border-radius: 6px; border: 1px solid #d1d5db; background: #f8fafc; color: #334155; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .qty-val { font-weight: 700; width: 30px; text-align: center; color: #1e293b; }
    .stock-info { font-size: 0.8rem; color: #94a3b8; font-style: italic; margin-top: 5px; }
    .cart-item-actions { display: flex; flex-direction: column; align-items: flex-end; gap: 10px; min-width: 120px; }
    .item-total { font-size: 1.1rem; font-weight: 800; color: #1f2d3d; }
    .btn-remove { padding: 6px 12px; background: transparent; border: 1px solid #ef4444; color: #ef4444; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.85rem; }
    .cart-actions-footer { display: flex; align-items: center; justify-content: space-between; margin-top: 16px; gap: 16px; border-top: 2px dashed #f1f5f9; padding-top: 24px; }
    .cart-total { font-size: 1.25rem; color: #111827; }
    .cart-buttons { display:flex; gap:12px; align-items: center; }
    .loading-state { padding: 40px; text-align: center; color: #64748b; }
    @media (max-width: 768px) {
        .cart-item { flex-direction: row; flex-wrap: wrap; }
        .cart-item-img { width: 70px; height: 70px; }
        .cart-item-info { min-width: 50%; }
        .cart-item-actions { width: 100%; flex-direction: row; justify-content: space-between; align-items: center; margin-top: 10px; padding-top: 10px; border-top: 1px solid #f0f0f0; }
        .cart-actions-footer { flex-direction: column; align-items: flex-start; gap: 15px; }
        .cart-buttons { width: 100%; }
        .btn-checkout { width: 100%; justify-content: center; }
    }
</style>