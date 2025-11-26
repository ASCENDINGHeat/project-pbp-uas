<script lang="ts">
	import ProductCard from './ProductCard.svelte';

	export let products: Array<{
		id: string;
		name: string;
		price: number;
		image: string;
		rating?: number;
		reviews?: number;
		inStock?: boolean;
	}> = [];

	export let title: string = '';
	export let columns: number = 4;
</script>

<div class="product-section">
	{#if title}
		<h2 class="section-title">{title}</h2>
	{/if}

	{#if products.length === 0}
		<div class="empty-state">
			<p>Tidak ada produk</p>
		</div>
	{:else}
		<div class="products-grid" style="--columns: {columns}">
			{#each products as product (product.id)}
				<ProductCard
					id={product.id}
					name={product.name}
					price={product.price}
					image={product.image}
					rating={product.rating || 4.5}
					reviews={product.reviews || 0}
					inStock={product.inStock !== false}
				/>
			{/each}
		</div>
	{/if}
</div>

<style>
	.product-section {
		margin: 40px 0;
	}

	.section-title {
		font-size: 1.8rem;
		font-weight: 700;
		color: #1f2d3d;
		margin-bottom: 24px;
		padding-left: 20px;
	}

	.products-grid {
		display: grid;
		grid-template-columns: repeat(var(--columns), 1fr);
		gap: 20px;
		padding: 0 20px;
	}

	.empty-state {
		text-align: center;
		padding: 60px 20px;
		color: #999;
	}

	@media (max-width: 1200px) {
		.products-grid {
			--columns: 3;
		}
	}

	@media (max-width: 768px) {
		.products-grid {
			--columns: 2;
			gap: 16px;
			padding: 0 16px;
		}

		.section-title {
			padding-left: 16px;
			font-size: 1.5rem;
		}
	}

	@media (max-width: 480px) {
		.products-grid {
			--columns: 1;
			padding: 0 12px;
		}

		.section-title {
			padding-left: 12px;
		}
	}
</style>
