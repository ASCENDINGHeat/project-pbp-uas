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
                 headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
            });
            if (res.ok) {
                const data = await res.json();
                // Mapping ID Cart ke store
                cart.set(data.data.map((item: any) => ({
                    id: String(item.id), // Pastikan string
                    product_id: item.product_id,
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

<div class="w-[95%] mx-auto my-5 flex justify-start relative z-10">
    <div class="inline-flex items-center gap-2.5 bg-slate-900 rounded-full px-6 py-2.5 text-sm font-medium shadow-sm border border-white/5 text-slate-400">
        <a class="text-slate-400 no-underline cursor-pointer transition-colors duration-200 hover:text-white" href="/web">Home</a>
        <span class="text-slate-600 text-xs">›</span>
        <span class="text-[#ff0055] font-bold">Keranjang Belanja</span>
    </div>
</div>

<main class="bg-[#f7f7f7] min-h-screen">
    <div class="max-w-[95%] mx-auto py-10 px-5">
        <header class="mb-10 text-center">
            <h1 class="text-4xl font-extrabold text-slate-800 mb-3">Keranjang Belanja</h1>
        </header>

        {#if loading}
            <div class="py-10 text-center text-slate-500">Memuat keranjang...</div>
        {:else if cartItems.length === 0}
            <section class="flex flex-col items-center justify-center py-20 bg-white rounded-xl shadow-sm text-center">
                <div class="text-6xl mb-5">🛒</div>
                <h2 class="text-3xl font-extrabold text-slate-800 mb-3">Keranjang Anda Kosong</h2>
                <button class="px-7 py-3 bg-gradient-to-r from-violet-600 to-indigo-600 text-white border-none rounded-lg font-bold cursor-pointer transition-all hover:opacity-90" on:click={() => goto('/web')}>Lanjut Belanja</button>
            </section>
        {:else}
            <section class="flex flex-col gap-6 bg-white rounded-xl shadow-sm p-10 mb-8 max-md:p-5">
                <div class="flex justify-between items-center pb-4 border-b-2 border-slate-100 max-md:flex-col max-md:items-start max-md:gap-4">
                    <label class="flex items-center h-5 cursor-pointer text-base font-semibold text-slate-700 select-none">
                        <input type="checkbox" 
                            class="w-5 h-5 mr-3 accent-indigo-600 cursor-pointer rounded border-slate-300"
                            checked={cartItems.length > 0 && selectedIds.length === cartItems.length} 
                            on:change={toggleAll} 
                        />
                        Pilih Semua ({cartItems.length})
                    </label>
                    <button class="bg-none border-none text-red-500 font-semibold cursor-pointer hover:text-red-700" on:click={clearAll}>Hapus Semua</button>
                </div>

                {#each cartItems as item (item.id)}
                    <div class="flex items-center gap-4 border-b border-gray-200 pb-6 p-4 rounded-lg transition-colors max-md:flex-wrap max-md:justify-center {selectedIds.includes(item.id) ? 'bg-slate-50 border border-slate-200' : ''}">
                        
                        <div class="flex items-center justify-center">
                            <label class="block relative cursor-pointer select-none">
                                <input type="checkbox" 
                                    class="w-5 h-5 accent-indigo-600 cursor-pointer rounded border-slate-300"
                                    checked={selectedIds.includes(item.id)} 
                                    on:change={() => toggleItem(item.id)} 
                                />
                            </label>
                        </div>

                        <img src={item.image || '/images/placeholder.png'} alt={item.name} class="w-[100px] h-[100px] object-cover rounded-lg border border-gray-100 max-md:w-[70px] max-md:h-[70px]" />
                        
                        <div class="flex-1 flex flex-col gap-2 max-md:min-w-[50%]">
                            <h3 class="m-0 text-lg text-slate-800 leading-snug font-semibold">{item.name}</h3>
                            <div class="font-semibold text-indigo-600 text-base">{formatCurrency(Number(item.price))}</div>
                            
                            <div class="flex items-center gap-2.5 mt-1">
                                <button class="w-8 h-8 rounded-md border border-gray-300 bg-slate-50 text-slate-700 font-bold cursor-pointer flex items-center justify-center hover:bg-white disabled:opacity-50" on:click={() => updateQuantity(item, -1)} disabled={item.quantity <= 1}>−</button>
                                <span class="font-bold w-8 text-center text-slate-800">{item.quantity}</span>
                                <button class="w-8 h-8 rounded-md border border-gray-300 bg-slate-50 text-slate-700 font-bold cursor-pointer flex items-center justify-center hover:bg-white disabled:opacity-50" on:click={() => updateQuantity(item, 1)} disabled={item.stock !== undefined && item.quantity >= item.stock}>+</button>
                            </div>
                            {#if item.stock !== undefined}
                                <div class="text-xs text-slate-400 italic mt-1">Stok: {item.stock}</div>
                            {/if}
                        </div>

                        <div class="flex flex-col items-end gap-2.5 min-w-[120px] max-md:w-full max-md:flex-row max-md:justify-between max-md:items-center max-md:border-t max-md:border-gray-100 max-md:pt-3">
                            <div class="text-lg font-extrabold text-slate-800">
                                {formatCurrency(Number(item.price) * item.quantity)}
                            </div>
                            <button class="px-3 py-1.5 bg-transparent border border-red-500 text-red-500 rounded-md cursor-pointer font-semibold text-sm hover:bg-red-50" on:click={() => removeItem(item.id)}>Hapus</button>
                        </div>
                    </div>
                {/each}

                <div class="flex items-center justify-between mt-4 gap-4 border-t-2 border-dashed border-slate-100 pt-6 max-md:flex-col max-md:items-start">
                    <div class="text-xl text-gray-900">
                        Total ({selectedIds.length} Barang): <strong>{formatCurrency(total)}</strong>
                    </div>
                    <div class="flex gap-3 items-center max-md:w-full">
                        <button class="px-7 py-3 bg-gradient-to-r from-violet-600 to-indigo-600 text-white border-none rounded-lg font-bold cursor-pointer transition-all hover:opacity-90 max-md:flex-1" on:click={() => goto('/web')}>+ Produk</button>
                        <button class="px-7 py-3 bg-slate-900 text-white border-none rounded-lg font-bold cursor-pointer transition-all inline-flex items-center gap-2 hover:bg-slate-800 disabled:bg-gray-300 disabled:cursor-not-allowed max-md:flex-1 justify-center" on:click={handleCheckout} disabled={selectedIds.length === 0}>
                            Checkout
                        </button>
                    </div>
                </div>
            </section>
        {/if}
    </div>
</main>