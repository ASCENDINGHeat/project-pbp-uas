
	<script lang="ts">
		import { cart } from '$lib/stores/cartStore';
		import { browser } from '$app/environment';
		import { PUBLIC_API_URL, PUBLIC_STORAGE_URL } from '$env/static/public';
		export let product: {
			id: number;
			name: string;
			price: number;
			stock_quantity: number; 
			image: string; 
			rating?: number;
			is_wishlisted?: boolean;
		};

		// State lokal untuk UI
		let isLoading = false;
		let isWishlisted = product.is_wishlisted || false; // Default status
		const STORAGE_BASE = `${PUBLIC_STORAGE_URL}/`;
		$: imageUrl = product.image && product.image.startsWith('http') 
			? product.image 
			: `${STORAGE_BASE}${product.image}`;

		// --- FUNGSI BACKEND ADD TO CART ---
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
					// Kirim quantity fix 1
					body: JSON.stringify({ quantity: 1 }) 
				});

				const data = await res.json();

				if (res.ok) {
					alert("Berhasil masuk keranjang!");
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

		// --- FUNGSI BACKEND TOGGLE WISHLIST ---
		async function handleToggleWishlist() {
			const token = localStorage.getItem('auth_token');
			if (!token) return alert('Silakan login terlebih dahulu');

			// Optimistic UI Update (Ubah warna dulu biar user seneng, baru request)
			isWishlisted = !isWishlisted;

			try {
				const res = await fetch(`${PUBLIC_API_URL}/wishlist/${product.id}`, {
					method: 'POST',
					headers: { 'Authorization': `Bearer ${token}` }
				});
				const data = await res.json();
				
				// Jika status dari backend beda dengan prediksi kita, balikin
				if (!res.ok) {
					isWishlisted = !isWishlisted; 
					alert(data.message);
				}
			} catch (err) {
				console.error(err);
				isWishlisted = !isWishlisted; // Rollback jika error
			}
		}
	</script>


	<div class="product-card">
		<div class="product-image">
			<img src={imageUrl} alt={product.name} />
			<button class="watchlist-btn" on:click|stopPropagation={handleToggleWishlist}>
				{#if isWishlisted}
					❤️
				{:else}
					🤍
				{/if}
			</button>
			{#if product.stock_quantity <= 0}
				<div class="out-of-stock">Habis Stok</div>
			{/if}
		</div>

		<div class="product-info">
			<h3 class="product-name">{product.name}</h3>

			<div class="product-rating">
				<span class="stars">★</span>
				<span class="rating-value">{product.rating || 4.5}</span>
			</div>

			<div class="product-price">
				<span class="price">Rp {product.price.toLocaleString('id-ID')}</span>
			</div>

			<button 	
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
