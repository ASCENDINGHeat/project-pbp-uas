
<script lang="ts">
	import { cart } from '$lib/stores/cartStore';
	import { browser } from '$app/environment';

	export let id: string;
	export let name: string;
	export let price: number;
	export let image: string;
	export let rating: number = 4.5;
	export let reviews: number = 0;
	export let inStock: boolean = true;

	function handleAddToCart() {
		cart.addItem({
			id,
			name,
			price,
			quantity: 1,
			image
		});
		alert(`${name} ditambahkan ke keranjang!`);
	}

	// WATCHLIST (fix SSR)
	let watchlist = new Set<string>();

	if (browser) {
		watchlist = new Set<string>(
			JSON.parse(localStorage.getItem("watchlist") || "[]")
		);
	}

	// reactive
	$: isWatchlisted = watchlist.has(id);

	function toggleWatchlist() {
		if (watchlist.has(id)) {
			watchlist.delete(id);
		} else {
			watchlist.add(id);
		}

		if (browser) {
			localStorage.setItem("watchlist", JSON.stringify([...watchlist]));
		}
	}
</script>


<div class="product-card">
	<div class="product-image">
		<img src={image} alt={name} />
		<button class="watchlist-btn" on:click|stopPropagation={toggleWatchlist}>
			{#if isWatchlisted}
				❤️
			{:else}
				🤍
			{/if}
		</button>
		{#if !inStock}
			<div class="out-of-stock">Habis Stok</div>
		{/if}
	</div>

	<div class="product-info">
		<h3 class="product-name">{name}</h3>

		<div class="product-rating">
			<span class="stars">★</span>
			<span class="rating-value">{rating}</span>
		</div>

		<div class="product-price">
			<span class="price">Rp {price.toLocaleString('id-ID')}</span>
		</div>

		<button class="btn-add-cart" on:click={handleAddToCart} disabled={!inStock}>
			{inStock ? 'Tambah ke Keranjang' : 'Habis Stok'}
		</button>
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
}

.watchlist-btn:hover {
	transform: scale(1.15);
	background: white;
}

</style>
