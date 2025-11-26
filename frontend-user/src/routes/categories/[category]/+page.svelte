<script lang="ts">
	import { page } from '$app/stores';
	import ProductGrid from '$lib/components/ProductGrid.svelte';
	import { getProductsByCategory } from '$lib/data/products';

	$: category = $page.params.category;
	$: categoryProducts = getProductsByCategory(category);

	const categoryNames: Record<string, string> = {
		all: 'Semua Kategori',
		processor: 'Processor',
		motherboard: 'Motherboard',
		vga: 'Kartu Grafis',
		ram: 'RAM',
		storage: 'Storage',
		casing: 'Casing',
		psu: 'Power Supply',
		monitor: 'Monitor'
	};

	$: categoryName = categoryNames[category] || 'Kategori';
</script>

<main class="container">
	<div class="header-section">
		<h1>{categoryName}</h1>
		<p>Produk berkualitas untuk kebutuhan Anda</p>
	</div>

	<ProductGrid products={categoryProducts} title="{categoryName}" />
</main>

<style>
	.container {
		max-width: 1400px;
		margin: 0 auto;
		padding: 40px 20px;
	}

	.header-section {
		margin-bottom: 40px;
		text-align: center;
	}

	.header-section h1 {
		font-size: 2.5rem;
		font-weight: 700;
		color: #1f2d3d;
		margin: 0 0 12px;
	}

	.header-section p {
		font-size: 1.1rem;
		color: #666;
		margin: 0;
	}

	@media (max-width: 768px) {
		.container {
			padding: 24px 16px;
		}

		.header-section h1 {
			font-size: 2rem;
		}
	}
</style>
