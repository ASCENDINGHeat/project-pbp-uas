<script lang="ts">
	import { goto } from '$app/navigation';
</script>

<!-- breadcrumb pill standalone -->
<div class="breadcrumb-wrapper">
	<div class="breadcrumb-pill">
		<a class="breadcrumb-link" href="/">Home</a>
		<span class="breadcrumb-sep">›</span>
		<a class="breadcrumb-link" href="/categories/all">All Categories</a>
		<span class="breadcrumb-sep">›</span>
		<span class="breadcrumb-current">Jual PC</span>
	</div>
</div>

<main class="category-page">
	<div class="container">
		<header class="header-section">
			<h1>Jual PC</h1>
			<p class="subtitle">Jual komputer bekas atau custom PC Anda</p>
		</header>

		<section class="product-grid">
			{#each Array(10) as _, i}
				<article class="card">
					<div class="card-image">
						<button class="card-wishlist" aria-label="wishlist">❤</button>
					</div>

					<div class="card-footer">
						<h3 class="card-title">PC Bekas {i + 1}</h3>
						<div class="meta"><span class="rating">★ 4.{(i % 3) + 4}</span></div>
						<div class="price">Rp {Math.floor(3 + i) + '.000.000'}</div>
						<button class="btn-add" on:click={() => goto('/sell/'+(i+1))}>Tambah ke Keranjang</button>
					</div>
				</article>
			{/each}
		</section>
	</div>
</main>

<style>
	/* breadcrumb wrapper - simple container */
	.breadcrumb-wrapper {
		width: 95%;
		margin: 20px auto;
		padding: 0;
		display: flex;
		justify-content: flex-start;
		position: relative;
		z-index: 40;
	}
	
	.breadcrumb-pill {
		display: inline-flex;
		align-items: center;
		gap: 10px;
		background-color: #0f172a;
		border-radius: 50px;
		padding: 10px 24px;
		font-size: 0.9rem;
		font-weight: 500;
		box-shadow: 0 2px 8px rgba(0,0,0,0.1);
		border: 1px solid rgba(255,255,255,0.05);
	}
	
	.breadcrumb-link {
		color: #94a3b8;
		text-decoration: none;
		cursor: pointer;
		transition: color 0.2s;
	}
	.breadcrumb-link:hover { color: #fff; }
	.breadcrumb-sep { color: #475569; font-size: 0.8rem; }
	.breadcrumb-current { color: #ff0055; font-weight: 700; }

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

	.product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 22px; }
	.card { display:flex; flex-direction:column; min-height:420px; position:relative; }

	.card-image { flex:1 1 auto; background:#f3f4f6; border-radius:10px; height: calc(100% - 110px); position: relative; }
	.card-wishlist { position:absolute; right:12px; top:12px; width:34px; height:34px; border-radius:50%; background:#fff; border:none; color:#ff3b6a; }

	.card-footer { background:#fff; padding:12px; border-radius:8px; margin-top:10px; display:flex; flex-direction:column; gap:8px; }
	.card-title { font-size:0.95rem; margin:0; font-weight:700; color:#0f172a; }
	.meta { color:#f59e0b; }
	.price { color:#ff5f8a; font-weight:800; }

	.btn-add { margin-top:6px; background: linear-gradient(90deg,#ff5f8a,#ff9a56); border:none; color:#fff; padding:10px; border-radius:8px; width:100%; cursor:pointer; }

	@media (max-width:1024px) { .container { padding:18px; } .product-grid{gap:16px;} .card{min-height:380px;} }
	@media (max-width:640px) { .header-section h1{font-size:20px;} .card{min-height:320px;} .card-image{height:220px;} }
	@media (max-width: 768px) {
		.breadcrumb-wrapper { width: 100%; margin: 16px auto; }
		.container { padding: 0 16px; }
		.header-section h1 { font-size: 2rem; }
		.breadcrumb-pill { padding: 8px 16px; font-size: 0.85rem; }
	}
</style>
