<script lang="ts">
	import './layout.css';
	import Header from '$lib/components/Header.svelte';
	import Footer from '$lib/components/Footer.svelte';
</script>

<Header />
<main class="main-content">
	<slot />
</main>
<Footer />

<style>
	:global(body) {
		margin: 0;
		padding: 0;
		background: #f5f5f5;
	}

	.main-content {
		flex: 1;
		min-height: 60vh;
	}
</style>
