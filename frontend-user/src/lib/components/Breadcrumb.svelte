<script lang="ts">
	import { goto } from '$app/navigation';

	export let items: Array<{ label: string; href?: string; active?: boolean }> = [];

	function handleClick(href: string | undefined, e: MouseEvent) {
		if (href) {
			e.preventDefault();
			goto(href);
		}
	}
</script>

<div class="breadcrumb-wrapper">
	<div class="breadcrumb-pill">
		{#each items as item, i}
			{#if item.href && !item.active}
				<a 
					class="breadcrumb-link" 
					href={item.href}
					on:click={(e) => handleClick(item.href, e)}
				>
					{item.label}
				</a>
			{:else}
				<span class="breadcrumb-current">{item.label}</span>
			{/if}
			{#if i < items.length - 1}
				<span class="breadcrumb-sep">›</span>
			{/if}
		{/each}
	</div>
</div>

<style>
	.breadcrumb-wrapper {
		width: 95%;
		margin: 20px auto;
		padding: 0;
		display: flex;
		justify-content: flex-start;
		position: relative;
		z-index: 1;
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
		font-family: inherit;
	}

	.breadcrumb-link:hover {
		color: #fff;
	}

	.breadcrumb-sep {
		color: #475569;
		font-size: 0.8rem;
	}

	.breadcrumb-current {
		color: #ff0055;
		font-weight: 700;
	}

	@media (max-width: 768px) {
		.breadcrumb-wrapper {
			width: 100%;
			margin: 16px auto;
		}
		.breadcrumb-pill {
			padding: 8px 16px;
			font-size: 0.85rem;
		}
	}
</style>
