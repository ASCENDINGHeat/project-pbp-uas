<script lang="ts">
	import { page } from '$app/stores';
	import ProductGrid from '$lib/components/ProductGrid.svelte';
	import Breadcrumb from '$lib/components/Breadcrumb.svelte';
	import { getProductsByCategory, products } from '$lib/data/products';
	import { onMount } from 'svelte';

	let category = '';
	let categoryProducts: any[] = [];
	let breadcrumbItems: any[] = [];

	$: {
		category = $page.params.category;
		const categoryLabel = category === 'all' ? 'Semua Kategori' : category?.toUpperCase();
		breadcrumbItems = [
			{ label: 'Home', href: '/web' },
			{ label: 'All Categories', href: '/web/categories/all' },
			{ label: categoryLabel, active: true }
		];
	}

	onMount(() => {
		if (category === 'all') {
			categoryProducts = Array.isArray(products) ? products.slice(0, 20) : [];
		} else {
			const prods = getProductsByCategory(category);
			categoryProducts = prods ?? [];
		}
	});
</script>

<Breadcrumb items={breadcrumbItems} />

<main class="category-page">
	<div class="container">
		<header class="header-section">
			<h1>{category === 'all' ? 'Semua Kategori' : 'Kategori: ' + category?.toUpperCase()}</h1>
			<p class="subtitle">{category === 'all' ? 'Lihat semua produk kami' : 'Menampilkan produk untuk kategori ' + category}</p>
		</header>

		<ProductGrid products={categoryProducts} title="{category === 'all' ? 'Semua Produk' : category?.charAt(0).toUpperCase() + category?.slice(1) + ' Pilihan'}" />
	</div>
</main>

<style>
	/* ...existing code... */
	.category-page { background: #f7f7f7; min-height: 100vh; }
	.container { max-width: 95%; margin: 0 auto; padding: 40px 20px; }

	.header-section { margin-bottom: 40px; text-align: center; }
	.header-section h1 {
		font-size: 2.5rem;
		font-weight: 800;
		color: #1f2d3d;
		margin: 0 0 12px;
	}
	.header-section p {
		font-size: 1.1rem;
		color: #64748b;
		margin: 0;
	}

	@media (max-width: 768px) {
		.container { padding: 0 16px; }
		.header-section h1 { font-size: 2rem; }
	}
</style>
