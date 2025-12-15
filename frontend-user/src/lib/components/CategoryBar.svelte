<script lang="ts">
	import { goto } from '$app/navigation';

	const categories = [
		{ label: 'ALL CATEGORIES', path: '/web/categories/all', large: true },
		{ label: 'PROCESSOR', path: '/web/categories/processor' },
		{ label: 'MOTHERBOARD', path: '/web/categories/motherboard' },
		{ label: 'VGA', path: '/web/categories/vga' },
		{ label: 'STORAGE', path: '/web/categories/storage' },
		{ label: 'RAM', path: '/web/categories/ram' },
		{ label: 'CASING', path: '/web/categories/casing' },
		{ label: 'PSU', path: '/web/categories/psu' },
		{ label: 'MONITOR', path: '/web/categories/monitor' }
	];

	function handleClick(path: string) {
		goto(path);
	}
</script>

<div class="category-container">
	<div class="gradient-nav">
		<div class="nav-links">
			{#each categories as cat}
				<a 
					href={cat.path} 
					class="cat-link {cat.large ? 'large' : ''}" 
					on:click|preventDefault={() => handleClick(cat.path)}
				>
					{cat.label}
				</a>
			{/each}
		</div>
	</div>
</div>

<style>
	.category-container {
		width: 100%;
		max-width: 95%;
		margin: 0 auto;
		margin-top: 0; /* CHANGED: stick to navbar without gap */
		padding: 0; /* CHANGED: removed 20px 0 padding */
		font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
	}

	.gradient-nav {
		background: linear-gradient(90deg, #ff0055 0%, #8000ff 100%);
		height: 60px;
		/* CHANGED: only round bottom corners to stick to navbar */
		border-top-left-radius: 0;
		border-top-right-radius: 0;
		border-bottom-left-radius: 12px;
		border-bottom-right-radius: 12px;
		display: flex;
		align-items: center;
		justify-content: flex-start;
		padding: 0 24px;
		color: #fff;
		box-shadow: 0 4px 15px rgba(255, 0, 85, 0.3);
		overflow: hidden;
	}

	.nav-links {
		display: flex;
		align-items: center;
		gap: 20px;
		overflow-x: auto;
		white-space: nowrap;
		scrollbar-width: none;
		height: 100%;
		flex: 1;
	}

	.nav-links::-webkit-scrollbar {
		display: none;
	}

	.cat-link {
		text-decoration: none;
		color: #fff;
		font-size: 0.95rem;
		font-weight: 700;
		letter-spacing: 0.5px;
		transition: opacity 0.2s;
		background: none;
		border: none;
		cursor: pointer;
		padding: 0;
		display: inline-block;
	}

	.cat-link:hover {
		opacity: 0.8;
	}

	@media (max-width: 768px) {
		.gradient-nav {
			border-top-left-radius: 0;
			border-top-right-radius: 0;
			border-bottom-left-radius: 8px;
			border-bottom-right-radius: 8px;
			padding: 0 12px;
		}

		.nav-links {
			gap: 12px;
		}

		.cat-link {
			font-size: 0.85rem;
		}
	}

	@media (max-width: 480px) {
		.gradient-nav {
			height: 45px;
			border-top-left-radius: 0;
			border-top-right-radius: 0;
			border-bottom-left-radius: 6px;
			border-bottom-right-radius: 6px;
			padding: 0 8px 0 16px;
		}

		.cat-link {
			font-size: 0.75rem;
		}

		.nav-links {
			gap: 8px;
		}
	}
</style>
