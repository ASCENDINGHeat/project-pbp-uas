<script lang="ts">
	import { goto } from '$app/navigation';
	import Breadcrumb from '$lib/components/Breadcrumb.svelte';
</script>

<svelte:head>
	<title>Wishlist - PC Store</title>
</svelte:head>

<Breadcrumb items={[
	{ label: 'Home', href: '/web' },
	{ label: 'Wishlist', active: true }
]} />

<main class="category-page">
	<div class="container">
		<header class="header-section">
			<h1>Wishlist</h1>
			<p class="subtitle">Produk yang ingin Anda beli di kemudian hari</p>
		</header>

		<section class="empty-state">
			<div class="empty-icon">❤️</div>
			<h2>Wishlist Anda Kosong</h2>
			<p>Belum ada produk yang ditambahkan ke wishlist.</p>
			<button class="btn-continue" on:click={() => goto('/web')}>Lihat Produk</button>
		</section>
	</div>
</main>

<style>
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

	.empty-state {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		padding: 80px 20px;
		background: #fff;
		border-radius: 12px;
		box-shadow: 0 2px 8px rgba(0,0,0,0.1);
		text-align: center;
	}

	.empty-icon {
		font-size: 4rem;
		margin-bottom: 20px;
	}

	.empty-state h2 {
		font-size: 1.8rem;
		font-weight: 800;
		color: #1f2d3d;
		margin: 0 0 12px;
	}

	.empty-state p {
		font-size: 1rem;
		color: #64748b;
		margin: 0 0 28px;
		max-width: 400px;
	}

	.btn-continue {
		padding: 12px 28px;
		background: linear-gradient(90deg, #7c3aed, #4f46e5);
		color: #fff;
		border: none;
		border-radius: 8px;
		font-weight: 700;
		cursor: pointer;
		transition: all 0.2s;
	}

	.btn-continue:hover {
		transform: translateY(-2px);
		box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
	}

	@media (max-width: 768px) {
		.container { padding: 0 16px; }
		.header-section h1 { font-size: 2rem; }
		.empty-state { padding: 60px 20px; }
	}

	   /* --- Responsive --- */
    @media (max-width: 1024px) { 
        .container { padding: 18px; } 
    }

    @media (max-width: 768px) {
        .container, .breadcrumb-container {
            padding: 0 16px;
        }
        
        .header-section h1 { font-size: 2rem; }
        
        .breadcrumb-pill {
            padding: 8px 16px;
            font-size: 0.85rem;
        }
    }
</style>
