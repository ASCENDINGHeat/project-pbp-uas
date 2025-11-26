<script lang="ts">
	export let promos: { id: string; image: string; url?: string }[] = [];
</script>

<aside class="promo-sidebar" aria-label="Promotions">
	{#each promos as p}
		<a class="promo-card" href={p.url || '#'} on:click|preventDefault={() => p.url && (location.href = p.url)}>
			<img src={p.image} alt="promo" />
		</a>
	{/each}
</aside>

<style>
	.promo-sidebar { display:flex; flex-direction:column; gap:16px; width:320px; }
	.promo-card { display:block; border-radius:10px; overflow:hidden; box-shadow:0 6px 18px rgba(0,0,0,0.12); }
	.promo-card img { width:100%; height:180px; object-fit:cover; display:block; }
	@media (max-width: 768px) { .promo-sidebar { width:100%; flex-direction:row; overflow:auto; } .promo-card img { height:120px } }
</style>
