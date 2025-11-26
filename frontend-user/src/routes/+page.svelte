<script lang="ts">
	import ProductGrid from '$lib/components/ProductGrid.svelte';
	import { products } from '$lib/data/products';
	import { onMount } from 'svelte';

	let slides = [
		{ id: 's1', image: 'https://via.placeholder.com/860x360?text=GeForce+RTX+Latest', title: 'GeForce RTX', caption: 'GPU terbaru' },
		{ id: 's2', image: 'https://via.placeholder.com/860x360?text=Processor+Terbaru', title: 'Processor Terbaru', caption: 'Performa maksimal' },
		{ id: 's3', image: 'https://via.placeholder.com/860x360?text=PC+Ready', title: 'PC Ready', caption: 'Siap pakai' }
	];

	let idx = 0;
	let timer: ReturnType<typeof setInterval> | null = null;

	function next() { idx = (idx + 1) % slides.length; }
	function prev() { idx = (idx - 1 + slides.length) % slides.length; }
	function go(i: number) { idx = i; }

	function startAuto() {
		if (timer) clearInterval(timer);
		timer = setInterval(next, 4500);
	}

	onMount(() => {
		startAuto();
		return () => { if (timer) clearInterval(timer); };
	});

	const promos = [
		{ id: 'p1', image: 'https://via.placeholder.com/320x360?text=Promo+Besar', title: 'Promo Besar' },
		{ id: 'p2', image: 'https://via.placeholder.com/320x170?text=GTR+ZERO', title: 'GTR ZERO' },
		{ id: 'p3', image: 'https://via.placeholder.com/320x170?text=Why+Agres', title: 'Why Agres' }
	];

	const featured = products.slice(0, 8);
</script>

<div class="hero-section">
	<div class="hero-grid">
		<!-- carousel kiri -->
		<div class="carousel-wrap">
			<div class="slides" style="transform:translateX({-idx * 100}%);">
				{#each slides as s (s.id)}
					<div class="slide">
						<img src={s.image} alt={s.title} />
						<div class="slide-overlay">
							<h3>{s.title}</h3>
							<p>{s.caption}</p>
						</div>
					</div>
				{/each}
			</div>
			<button class="nav prev" on:click={prev}>‹</button>
			<button class="nav next" on:click={next}>›</button>
		</div>

		<!-- promo stack kanan -->
		<aside class="promo-stack">
			<img src={promos[0].image} alt={promos[0].title} class="promo-large" />
			<div class="promo-smalls">
				<img src={promos[1].image} alt={promos[1].title} />
				<img src={promos[2].image} alt={promos[2].title} />
			</div>
		</aside>
	</div>
</div>

<div class="container">
	<ProductGrid products={featured} title="Produk Unggulan" columns={4} />
</div>

<style>
	/* gunakan variabel dari Header */
	:root {
		--category-bar-height: 56px;
	}

	.hero-section {
		padding: 20px;
		background: #f5f7fa;
	}

	.hero-grid {
		max-width: 1260px;
		margin: 0 auto;
		display: grid;
		grid-template-columns: 1fr 340px;
		gap: 20px;
		align-items: start;
	}

	/* carousel height selaras dengan category bar + padding */
	.carousel-wrap {
		position: relative;
		overflow: hidden;
		border-radius: 12px;
		background: #0f1724;
		height: calc(var(--category-bar-height) * 6); /* 6x tinggi bar = ~336px untuk 56px */
	}

	.slides {
		display: flex;
		transition: transform 0.5s ease;
		height: 100%;
	}

	.slide {
		min-width: 100%;
		position: relative;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.slide img {
		width: 100%;
		height: 100%;
		object-fit: cover;
		display: block;
	}

	.slide-overlay {
		position: absolute;
		left: 20px;
		bottom: 20px;
		color: #fff;
		text-shadow: 0 4px 12px rgba(0, 0, 0, 0.6);
	}

	.slide-overlay h3 {
		margin: 0 0 6px;
		font-size: 1.5rem;
		font-weight: 900;
	}

	.slide-overlay p {
		margin: 0;
		font-size: 0.95rem;
	}

	.nav {
		position: absolute;
		top: 50%;
		transform: translateY(-50%);
		width: 44px;
		height: 44px;
		border-radius: 999px;
		background: rgba(0, 0, 0, 0.45);
		color: #fff;
		border: none;
		font-size: 1.4rem;
		display: flex;
		align-items: center;
		justify-content: center;
		cursor: pointer;
		z-index: 10;
	}

	.prev { left: 12px; }
	.next { right: 12px; }

	/* promo stack height sama carousel */
	.promo-stack {
		display: flex;
		flex-direction: column;
		gap: 12px;
		height: calc(var(--category-bar-height) * 6);
	}

	.promo-large {
		width: 100%;
		height: calc((var(--category-bar-height) * 6) * 0.67);
		object-fit: cover;
		border-radius: 12px;
		display: block;
	}

	.promo-smalls {
		display: flex;
		flex-direction: column;
		gap: 12px;
		flex: 1;
	}

	.promo-smalls img {
		width: 100%;
		height: 100%;
		object-fit: cover;
		border-radius: 12px;
		display: block;
	}

	.container {
		max-width: 1260px;
		margin: 40px auto;
		padding: 0 20px 70px 20px; /* Tambah 70px bottom padding untuk mobile nav */
	}

	/* responsive */
	@media (max-width: 1024px) {
		.hero-grid { grid-template-columns: 1fr 280px; gap: 16px; }
		.promo-stack { height: calc(var(--category-bar-height) * 5); }
		.carousel-wrap { height: calc(var(--category-bar-height) * 5); }
	}

	@media (max-width: 900px) {
		.hero-grid { grid-template-columns: 1fr; }
		.promo-stack { flex-direction: row; height: auto; }
		.promo-large { height: 180px; flex: 0 0 45%; }
		.promo-smalls { flex-direction: row; flex: 1; }
		.promo-smalls img { height: 100%; }
	}

	@media (max-width: 768px) {
		.container {
			padding: 0 20px 80px 20px; /* Lebih besar di mobile */
		}
	}

	@media (max-width: 480px) {
		.carousel-wrap { height: calc(var(--category-bar-height) * 4); }
		.promo-large { height: 140px; }
		.promo-smalls img { height: 100%; }
	}
</style>
