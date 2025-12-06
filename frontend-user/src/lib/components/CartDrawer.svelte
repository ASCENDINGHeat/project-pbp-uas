<script lang="ts">
	import { fly, fade } from 'svelte/transition';
	import { goto } from '$app/navigation';
	import { cart, removeFromCart } from '$lib/stores/cart';
	import { createEventDispatcher } from 'svelte';

	export let isOpen = false;

	const dispatch = createEventDispatcher();

	function close() {
		dispatch('close');
	}

	$: subtotal = $cart.reduce((sum, item) => sum + (Number(item.price ?? 0) * (item.quantity ?? 1)), 0);

	const formatIDR = (num: number) =>
		new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num);

	function handleViewCart() {
		close();
		goto('/web/cart');
	}

	function handleCheckout() {
		close();
		goto('/web/checkout');
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
		<div class="drawer-content">
			{#if $cart.length === 0}
				<div class="empty-state">
					<div class="icon-circle">
						<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
					</div>
					<p>Keranjang belanja Anda masih kosong</p>
					<button class="btn-continue" on:click={close}>Lanjutkan Belanja</button>
				</div>
			{:else}
				<div class="cart-items">
					{#each $cart as item}
						<div class="cart-item">
							<img src={item.image || '/images/placeholder.png'} alt={item.name} />
							<div class="item-info">
								<h4>{item.name}</h4>
								<div class="item-meta">
									<span>{item.quantity} x {formatIDR(item.price)}</span>
								</div>
							</div>
							<button class="btn-remove-item" on:click={() => removeFromCart(item.id)}>×</button>
						</div>
					{/each}
				</div>
			{/if}
		</div>
		<footer class="drawer-footer">
			<div class="subtotal-row">
				<span>Subtotal:</span>
				<span class="subtotal-val">{formatIDR(subtotal)}</span>
			</div>
			<div class="footer-actions">
				<button class="btn-action btn-red" on:click={handleViewCart}>Lihat Keranjang</button>
				<button class="btn-action btn-red-outline" on:click={handleCheckout} disabled={$cart.length === 0}>Checkout</button>
			</div>
		</footer>
	</aside>
{/if}

<style>
	.backdrop {
		position: fixed; top: 0; left: 0; width: 100%; height: 100%;
		background: rgba(0, 0, 0, 0.6);
		z-index: 9998;
		backdrop-filter: blur(2px);
	}
	.cart-drawer {
		position: fixed; top: 0; right: 0; height: 100%;
		width: 100%; max-width: 400px;
		background-color: #1e293b;
		color: #fff;
		z-index: 9999;
		display: flex; flex-direction: column;
		box-shadow: -5px 0 25px rgba(0,0,0,0.5);
	}
	.drawer-header {
		display: flex; justify-content: space-between; align-items: center;
		padding: 20px;
		border-bottom: 1px solid #334155;
	}
	.drawer-header h2 { margin: 0; font-size: 1.25rem; font-weight: 600; }
	.btn-close { background: none; border: none; color: #fff; cursor: pointer; }
	.drawer-content { flex: 1; overflow-y: auto; position: relative; }
	.empty-state {
		display: flex; flex-direction: column; align-items: center; justify-content: center;
		height: 100%; padding: 20px; text-align: center;
	}
	.icon-circle {
		width: 80px; height: 80px; background: #f1f5f9;
		border-radius: 50%; display: flex; align-items: center; justify-content: center;
		margin-bottom: 20px;
	}
	.empty-state p { margin-bottom: 30px; color: #cbd5e1; font-size: 1rem; }
	.btn-continue {
		background: #334155; color: white; border: 1px solid #475569;
		padding: 12px 30px; border-radius: 6px; cursor: pointer; font-weight: 500;
		width: 100%; transition: 0.2s;
	}
	.btn-continue:hover { background: #475569; }
	.cart-items { padding: 20px; display: flex; flex-direction: column; gap: 15px; }
	.cart-item {
		display: flex; gap: 15px; align-items: center;
		background: #0f172a; padding: 10px; border-radius: 8px; border: 1px solid #334155;
	}
	.cart-item img { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; background: #fff; }
	.item-info { flex: 1; }
	.item-info h4 { margin: 0 0 5px; font-size: 0.9rem; color: #f1f5f9; }
	.item-meta { font-size: 0.85rem; color: #94a3b8; }
	.btn-remove-item { background: transparent; border: none; color: #ef4444; font-size: 1.5rem; cursor: pointer; padding: 0 10px; }
	.drawer-footer {
		padding: 20px; border-top: 1px solid #334155;
		background-color: #1e293b;
	}
	.subtotal-row {
		display: flex; justify-content: space-between; align-items: center;
		margin-bottom: 15px; font-size: 1.1rem;
	}
	.subtotal-val { font-weight: 700; font-size: 1.2rem; }
	.footer-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
	.btn-action {
		padding: 12px; border-radius: 6px; font-weight: 600; cursor: pointer; border: none; font-size: 0.95rem;
	}
	.btn-red {
		background-color: #dc2626; color: white;
	}
	.btn-red:hover { background-color: #b91c1c; }
	.btn-red-outline {
		background-color: transparent; border: 2px solid #dc2626; color: #dc2626;
	}
	.btn-red-outline:hover:not(:disabled) { background-color: #dc2626; color: white; }
	.btn-red-outline:disabled { opacity: 0.5; cursor: not-allowed; border-color: #64748b; color: #64748b; }
</style>
