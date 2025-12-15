<script lang="ts">
	import { fly, fade } from 'svelte/transition';
	import { goto } from '$app/navigation';
	// Import store cart dan selectedItemIds
	import { cart, removeFromCart, selectedItemIds } from '$lib/stores/cart';
	import { createEventDispatcher, onMount } from 'svelte';
	import { PUBLIC_API_URL, PUBLIC_STORAGE_URL } from '$env/static/public'; // Import env URL
    import { isLoggedIn } from '$lib/stores/auth';

	export let isOpen = false;

	const dispatch = createEventDispatcher();
    let isFetching = false;

	function close() {
		dispatch('close');
	}

	// Hitung Subtotal
	$: subtotal = $cart.reduce((sum, item) => sum + (Number(item.price ?? 0) * (item.quantity ?? 1)), 0);

	const formatIDR = (num: number) =>
		new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num);

    // --- LOGIC: FETCH DATA DARI DATABASE SAAT DRAWER DIBUKA ---
    // Menggunakan reactive statement agar setiap kali 'isOpen' bernilai true, data ditarik ulang (fresh)
    $: if (isOpen && $isLoggedIn) {
        loadCartFromDatabase();
    }

    async function loadCartFromDatabase() {
        if (isFetching) return;
        isFetching = true;

        try {
            const token = localStorage.getItem('auth_token');
            if (!token) return;

            const res = await fetch(`${PUBLIC_API_URL}/cart`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });

            if (res.ok) {
                const responseData = await res.json();
                // Asumsi: Backend mengembalikan { data: [ { id, quantity, product: { ... } } ] }
                // Kita perlu mapping agar sesuai struktur store lokal
                const serverItems = responseData.data.map((item: any) => {
                    // Handle URL Gambar (Absolute vs Relative)
                    let img = item.product.image;
                    if (img && !img.startsWith('http')) {
                         const baseUrl = PUBLIC_STORAGE_URL.endsWith('/') ? PUBLIC_STORAGE_URL : `${PUBLIC_STORAGE_URL}/`;
                         img = `${baseUrl}storage/${img}`;
                    }

                    return {
                        id: item.product_id,     // ID Produk (bukan ID record cart) untuk konsistensi store
                        cart_id: item.id,        // ID record cart (opsional, jika butuh untuk hapus spesifik)
                        name: item.product.name,
                        price: item.product.price,
                        image: img,
                        quantity: item.quantity,
                        stock: item.product.stock // Simpan info stok untuk validasi
                    };
                });

                // Update Store Svelte
                cart.set(serverItems);
            }
        } catch (error) {
            console.error("Gagal memuat keranjang:", error);
        } finally {
            isFetching = false;
        }
    }

	// --- LOGIC UPDATE QUANTITY ---
	async function updateQuantity(item: any, change: number) {
		const newQty = item.quantity + change;

		// 1. Validasi Batas Bawah
		if (newQty < 1) return;

		// 2. Validasi Stok
		if (item.stock !== undefined && newQty > item.stock) {
			alert(`Maaf, stok hanya tersedia ${item.stock} item.`);
			return;
		}

		// 3. Optimistic Update
		const oldQty = item.quantity;
		cart.update(items => items.map(i => i.id === item.id ? { ...i, quantity: newQty } : i));

		try {
			const token = localStorage.getItem('auth_token');
			if (!token) return;

			// 4. Request ke Backend
			const res = await fetch(`${PUBLIC_API_URL}/cart/update/${item.id}`, { // Pastikan backend terima Product ID atau Cart ID
				method: 'PUT',
				headers: {
					'Content-Type': 'application/json',
					'Authorization': `Bearer ${token}`,
					'Accept': 'application/json'
				},
				body: JSON.stringify({ quantity: newQty })
			});

			if (!res.ok) {
				// Revert jika gagal
				cart.update(items => items.map(i => i.id === item.id ? { ...i, quantity: oldQty } : i));
				const err = await res.json();
				alert(err.message || 'Gagal mengubah jumlah');
			}
		} catch (e) {
			// Revert jika error koneksi
			cart.update(items => items.map(i => i.id === item.id ? { ...i, quantity: oldQty } : i));
			console.error(e);
		}
	}

	// --- LOGIC HAPUS ITEM ---
	async function handleRemoveItem(id: string) {
		if(!confirm("Hapus item ini dari keranjang?")) return;

		// Hapus dari store lokal dulu (Optimistic)
		const oldCart = [...$cart]; 
		removeFromCart(id);

		try {
			const token = localStorage.getItem('auth_token');
			if (!token) return;

			const res = await fetch(`${PUBLIC_API_URL}/cart/delete/${id}`, {
				method: 'DELETE',
				headers: {
					'Authorization': `Bearer ${token}`,
					'Accept': 'application/json'
				}
			});

			if (!res.ok) {
				cart.set(oldCart); // Rollback
				alert("Gagal menghapus item dari server");
			}
		} catch (e) {
			cart.set(oldCart);
			alert("Gagal menghapus item (koneksi error)");
		}
	}

	function handleViewCart() {
		close();
		goto('/web/cart');
	}

	function handleCheckout() {
		if ($cart.length === 0) {
			alert("Keranjang kosong!");
			return;
		}
		// Pilih SEMUA item saat checkout dari drawer
		const allIds = $cart.map(item => item.id);
		selectedItemIds.set(allIds);

		close();
		goto('/web/co');
	}
</script>

{#if isOpen}
	<div class="backdrop" on:click={close} transition:fade={{ duration: 200 }}></div>
	
	<aside class="cart-drawer" transition:fly={{ x: 300, duration: 300 }}>
		<header class="drawer-header">
			<h2>Keranjang Belanja</h2>
			<button class="btn-close" on:click={close}>
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
			</button>
		</header>

		<div class="drawer-body">
            {#if isFetching && $cart.length === 0}
                <div class="empty-state">
                    <p>Memuat keranjang...</p>
                </div>
			{:else if $cart.length === 0}
				<div class="empty-state">
					<p>Keranjang Anda kosong.</p>
					<button class="btn-shop" on:click={() => { close(); goto('/web'); }}>Belanja Sekarang</button>
				</div>
			{:else}
				{#each $cart as item (item.id)}
					<div class="cart-item">
						<img src={item.image} alt={item.name} on:error={(e) => e.currentTarget.src = '/images/placeholder.jpg'} />
						<div class="item-info">
							<h4>{item.name}</h4>
							<div class="item-meta">
								{formatIDR(item.price)}
							</div>
							<div class="quantity-control-sm">
								<button on:click={() => updateQuantity(item, -1)} disabled={item.quantity <= 1}>-</button>
								<span>{item.quantity}</span>
								<button on:click={() => updateQuantity(item, 1)} disabled={item.stock !== undefined && item.quantity >= item.stock}>+</button>
							</div>
						</div>
						<button class="btn-remove-item" on:click={() => handleRemoveItem(item.id)} title="Hapus">
							&times;
						</button>
					</div>
				{/each}
			{/if}
		</div>

		{#if $cart.length > 0}
			<div class="drawer-footer">
				<div class="subtotal-row">
					<span>Subtotal:</span>
					<strong>{formatIDR(subtotal)}</strong>
				</div>
				<div class="action-buttons">
					<button class="btn-view-cart" on:click={handleViewCart}>Lihat Keranjang</button>
					<button class="btn-checkout" on:click={handleCheckout}>Checkout</button>
				</div>
			</div>
		{/if}
	</aside>
{/if}

<style>
	.backdrop {
		position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000;
	}
	.cart-drawer {
		position: fixed; top: 0; right: 0; bottom: 0; width: 350px;
		background: #1e293b; color: white; z-index: 1001;
		display: flex;
		flex-direction: column;
		box-shadow: -4px 0 15px rgba(0,0,0,0.3);
	}
	.drawer-header {
		padding: 20px; border-bottom: 1px solid #334155;
		display: flex; justify-content: space-between; align-items: center;
	}
	.drawer-header h2 { margin: 0; font-size: 1.25rem; font-weight: 700; }
	.btn-close { background: none; border: none; color: #cbd5e1; cursor: pointer;
	}
	
	.drawer-body { flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 15px; }
	
	.cart-item {
		display: flex; gap: 15px;
		align-items: center;
		background: #0f172a; padding: 10px; border-radius: 8px; border: 1px solid #334155;
	}
	.cart-item img { width: 60px; height: 60px;
		object-fit: cover; border-radius: 4px; background: #fff; }
	.item-info { flex: 1; display: flex; flex-direction: column; gap: 4px;
	}
	.item-info h4 { margin: 0; font-size: 0.9rem; color: #f1f5f9; line-height: 1.2; }
	.item-meta { font-size: 0.85rem; color: #94a3b8; font-weight: 600;
	}
	
	/* Style Quantity Control Kecil */
	.quantity-control-sm {
		display: flex; align-items: center; gap: 8px; margin-top: 5px;
		background: #1e293b; width: fit-content; border-radius: 4px;
		padding: 2px;
	}
	.quantity-control-sm button {
		width: 20px; height: 20px; background: #334155; border: none; color: white;
		border-radius: 3px; cursor: pointer; display: flex;
		align-items: center; justify-content: center;
	}
	.quantity-control-sm button:disabled { opacity: 0.5; cursor: not-allowed; }
	.quantity-control-sm span { font-size: 0.85rem; padding: 0 5px;
		min-width: 20px; text-align: center; }

	.btn-remove-item { background: transparent; border: none; color: #ef4444; font-size: 1.5rem; cursor: pointer; padding: 0 8px;
	}
	.btn-remove-item:hover { color: #ff0000; }

	.drawer-footer {
		padding: 20px; border-top: 1px solid #334155;
		background-color: #1e293b;
	}
	.subtotal-row {
		display: flex; justify-content: space-between; align-items: center;
		margin-bottom: 15px; font-size: 1.1rem;
	}
	.action-buttons { display: flex; gap: 10px; }
	.btn-view-cart, .btn-checkout {
		flex: 1; padding: 12px; border: none; border-radius: 6px;
		font-weight: 600; cursor: pointer; font-size: 0.95rem;
	}
	.btn-view-cart { background: #334155; color: white; }
	.btn-checkout { background: #8E42E1; color: white;
	}
	.btn-checkout:hover { background: #7c3aed; }
	
	.empty-state { text-align: center; padding-top: 40px; color: #94a3b8; }
	.btn-shop { margin-top: 15px; padding: 10px 20px;
		background: #8E42E1; color: white; border: none; border-radius: 6px; cursor: pointer; }
</style>