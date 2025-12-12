<script lang="ts">
	import { page } from '$app/stores';
	import Header from '$lib/components/Header.svelte';
	import Footer from '$lib/components/Footer.svelte';

	$: showHeaderFooter = $page?.url?.pathname && $page.url.pathname !== '/';
</script>

{#if showHeaderFooter}
    <!-- keep Header; it has its own white drawer -->
    <Header />
{/if}

<main class="main-content">
	<slot />
</main>

{#if showHeaderFooter}
    <Footer />
{/if}

<style>
	:global(*) {
		font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;
	}

	:global(html), :global(body) {
		margin: 0;
		padding: 0;
		width: 100%;

		min-height: 100vh;

		overflow-y: auto !important;
		overflow-x: hidden;

		font-family: 'Segoe UI', sans-serif;
		background-color: #ffffff;

		scroll-behavior: smooth;

		-webkit-font-smoothing: antialiased;
		-moz-osx-font-smoothing: grayscale;
	}

	.main-content {
		flex: 1;
		min-height: 60vh;
		width: 100%;
	}
</style>
