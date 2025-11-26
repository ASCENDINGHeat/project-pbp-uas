<script lang="ts">
	export let slides: { id: string; image: string; title?: string; caption?: string }[] = [];
	let idx = 0;
	let timer: number | null = null;

	function next() { idx = (idx + 1) % slides.length; }
	function prev() { idx = (idx - 1 + slides.length) % slides.length; }
	function go(i: number) { idx = i; }

	$: {
		if (timer) clearInterval(timer);
		timer = setInterval(next, 4500);
	}
</script>

<div class="hero-carousel" role="region" aria-label="Hero carousel">
	{#if slides.length}
		<div class="slides">
			{#each slides as s, i}
				<div class="slide" aria-hidden={i !== idx} class:active={i === idx}>
					<!-- gunakan background-image atau <img> -->
					<img src={s.image} alt={s.title || `slide-${i}`} />
					{#if s.title}
						<div class="caption">
							<h3>{s.title}</h3>
							<p>{s.caption}</p>
						</div>
					{/if}
				</div>
			{/each}
		</div>

		<button class="prev" on:click={prev} aria-label="Previous">‹</button>
		<button class="next" on:click={next} aria-label="Next">›</button>

		<div class="dots">
			{#each slides as _, i}
				<button class:active={i === idx} on:click={() => go(i)} aria-label={`Go to slide ${i+1}`}></button>
			{/each}
		</div>
	{:else}
		<div class="empty">No slides</div>
	{/if}
</div>

<style>
	/* ...minimal styling, fokus ke layout: .hero-carousel .slide { display: none } .slide.active { display:block } ... */
	.hero-carousel { position: relative; width: 100%; height: 420px; overflow: hidden; border-radius: 12px; }
	.slide img { width: 100%; height: 100%; object-fit: cover; display:block; }
	.prev, .next { position: absolute; top:50%; transform:translateY(-50%); background:rgba(0,0,0,0.4); color:#fff; border:none; padding:10px; border-radius:50%; }
	.prev { left: 12px } .next { right: 12px }
	.dots { position:absolute; left:50%; transform:translateX(-50%); bottom:12px; display:flex; gap:8px }
	.dots button { width:10px; height:10px; border-radius:50%; background:rgba(255,255,255,0.5); border:none }
	.dots button.active { background:#fff }
</style>
