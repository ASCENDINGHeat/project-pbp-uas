<script lang="ts">
	import ProductGrid from '$lib/components/ProductGrid.svelte';
	import { getProductsByCategory } from '$lib/data/products';

	const categoryProducts = getProductsByCategory('psu');
</script>

<main class="container">
	<div class="header-section">
		<h1>Power Supply (PSU)</h1>
		<p>PSU berkualitas untuk stabilitas sistem</p>
	</div>

	<ProductGrid products={categoryProducts} title="Power Supply Pilihan" />
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
