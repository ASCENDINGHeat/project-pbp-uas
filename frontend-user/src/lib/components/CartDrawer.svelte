<script lang="ts">
	import { fly, fade } from 'svelte/transition';
	import { goto } from '$app/navigation';
	import { cart, removeFromCart, selectedItemIds } from '$lib/stores/cart';
	import { createEventDispatcher } from 'svelte';
	import { PUBLIC_API_URL } from '$env/static/public';

	export let isOpen = false;
	const dispatch = createEventDispatcher();

	function close() { dispatch('close'); }

	$: subtotal = $cart.reduce((sum, item) => sum + (Number(item.price ?? 0) * (item.quantity ?? 1)), 0);

	const formatIDR = (num: number) =>
		new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num);

	// --- LOGIC HAPUS ITEM (FIXED) ---
	async function handleRemoveItem(id: string) {
		if(!confirm("Hapus item ini?")) return;

		// 1. Simpan state lama untuk rollback (Optimistic UI)
		const oldCart = [...$cart];
		
		// 2. Hapus dari UI duluan
		removeFromCart(id);

		try {
			const token = localStorage.getItem('auth_token');
			if (!token) throw new Error("Token tidak ditemukan");

			// 3. Panggil API (Endpoint: /cart/delete/{id})
			const res = await fetch(`${PUBLIC_API_URL}/cart/delete/${id}`, {
				method: 'DELETE',
				headers: {
					'Authorization': `Bearer ${token}`,
					'Accept': 'application/json'
				}
			});

			// 4. Cek Response
			if (!res.ok) {
				const data = await res.json();
				throw new Error(data.message || "Gagal menghapus");
			}
		} catch (e) {
			console.error(e);
			// 5. Rollback jika gagal
			cart.set(oldCart);
			alert("Gagal menghapus item. Silakan coba lagi.");
		}
	}

	async function updateQuantity(item: any, change: number) {
		const newQty = item.quantity + change;
		if (newQty < 1) return;
		if (item.stock !== undefined && newQty > item.stock) {
			alert(`Stok hanya tersedia ${item.stock}`);
			return;
		}

		// Optimistic update
		const oldQty = item.quantity;
		cart.update(items => items.map(i => i.id === item.id ? { ...i, quantity: newQty } : i));

		try {
			const token = localStorage.getItem('auth_token');
			await fetch(`${PUBLIC_API_URL}/cart/update/${item.id}`, {
				method: 'PUT',
				headers: {
					'Content-Type': 'application/json',
					'Authorization': `Bearer ${token}`,
					'Accept': 'application/json'
				},
				body: JSON.stringify({ quantity: newQty })
			});
		} catch (e) {
			cart.update(items => items.map(i => i.id === item.id ? { ...i, quantity: oldQty } : i));
		}
	}

	function handleCheckout() {
		if ($cart.length === 0) return alert("Keranjang kosong!");
		selectedItemIds.set($cart.map(i => String(i.id)));
		close();
		goto('/web/co');
	}
</script>

{#if isOpen}
	<div class="backdrop" on:click={close} transition:fade={{ duration: 200 }}></div>
	
	<aside class="cart-drawer" transition:fly={{ x: 300, duration: 300 }}>
		<header class="drawer-header">
			<h2>Keranjang ({$cart.length})</h2>
			<button class="btn-close" on:click={close}>&times;</button>
		</header>

		<div class="drawer-body">
			{#if $cart.length === 0}
				<div class="empty-state">
					<p>Keranjang kosong.</p>
					<button class="btn-shop" on:click={() => { close(); goto('/web'); }}>Belanja</button>
				</div>
			{:else}
				{#each $cart as item (item.id)}
					<div class="cart-item">
						<img src={item.image} alt={item.name} />
						<div class="item-info">
							<h4>{item.name}</h4>
							<div class="item-meta">{formatIDR(item.price)}</div>
							<div class="quantity-control-sm">
								<button on:click={() => updateQuantity(item, -1)}>-</button>
								<span>{item.quantity}</span>
								<button on:click={() => updateQuantity(item, 1)}>+</button>
							</div>
						</div>
						<button class="btn-remove-item" on:click={() => handleRemoveItem(item.id)}>&times;</button>
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
					<button class="btn-view-cart" on:click={() => { close(); goto('/web/cart'); }}>Lihat Detail</button>
					<button class="btn-checkout" on:click={handleCheckout}>Checkout</button>
				</div>
			</div>
		{/if}
	</aside>
{/if}

<style>
	/* ... Style sama seperti sebelumnya ... */
	.backdrop { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; }
	.cart-drawer { position: fixed; top: 0; right: 0; bottom: 0; width: 350px; background: #1e293b; color: white; z-index: 1001; display: flex; flex-direction: column; box-shadow: -4px 0 15px rgba(0,0,0,0.3); }
	.drawer-header { padding: 20px; border-bottom: 1px solid #334155; display: flex; justify-content: space-between; align-items: center; }
	.drawer-header h2 { margin: 0; font-size: 1.25rem; font-weight: 700; }
	.btn-close { background: none; border: none; color: #cbd5e1; cursor: pointer; font-size: 1.5rem; }
	.drawer-body { flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 15px; }
	.cart-item { display: flex; gap: 15px; align-items: center; background: #0f172a; padding: 10px; border-radius: 8px; border: 1px solid #334155; }
	.cart-item img { width: 60px; height: 60px; object-fit: cover; border-radius: 4px; background: #fff; }
	.item-info { flex: 1; display: flex; flex-direction: column; gap: 4px; }
	.item-info h4 { margin: 0; font-size: 0.9rem; color: #f1f5f9; line-height: 1.2; }
	.item-meta { font-size: 0.85rem; color: #94a3b8; font-weight: 600; }
	.quantity-control-sm { display: flex; align-items: center; gap: 8px; margin-top: 5px; background: #1e293b; width: fit-content; border-radius: 4px; padding: 2px; }
	.quantity-control-sm button { width: 20px; height: 20px; background: #334155; border: none; color: white; border-radius: 3px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
	.quantity-control-sm span { font-size: 0.85rem; padding: 0 5px; min-width: 20px; text-align: center; }
	.btn-remove-item { background: transparent; border: none; color: #ef4444; font-size: 1.5rem; cursor: pointer; padding: 0 8px; }
	.btn-remove-item:hover { color: #ff0000; }
	.drawer-footer { padding: 20px; border-top: 1px solid #334155; background-color: #1e293b; }
	.subtotal-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; font-size: 1.1rem; }
	.action-buttons { display: flex; gap: 10px; }
	.btn-view-cart, .btn-checkout { flex: 1; padding: 12px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.95rem; }
	.btn-view-cart { background: #334155; color: white; }
	.btn-checkout { background: #8E42E1; color: white; }
	.empty-state { text-align: center; padding-top: 40px; color: #94a3b8; }
	.btn-shop { margin-top: 15px; padding: 10px 20px; background: #8E42E1; color: white; border: none; border-radius: 6px; cursor: pointer; }
</style>