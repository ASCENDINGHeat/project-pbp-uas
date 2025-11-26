<script lang="ts">
	export let as: 'button' | 'span' = 'button';
	export let type: 'button' | 'submit' | 'reset' | undefined = 'button';
	export let ariaLabel: string = '';
	export let className: string = '';
	// No onClick prop, use event forwarding
</script>

{#if as === 'button'}
	<button
		type={type}
		class="brand-action blink-border blink-bg {className}"
		aria-label={ariaLabel}
		{...$$restProps}
	>
		<slot />
	</button>
{:else}
	<span
		class="brand-tag blink-border blink-bg {className}"
		aria-label={ariaLabel}
		{...$$restProps}
	>
		<slot />
	</span>
{/if}

<style>
	.brand-action,
	.brand-tag {
		background: linear-gradient(90deg,#ff5f8a,#8a4bff,#ff5f8a 80%);
		background-size: 200% 200%;
		background-position: 0% 50%;
		color: #fff;
		border: 2px solid rgba(255,255,255,0.12);
		padding: 10px 20px;
		border-radius: 12px;
		font-weight: 900;
		font-size: 1.05rem;
		box-shadow: 0 10px 24px rgba(11,9,38,0.22);
		cursor: pointer;
		animation: blink-border 1.6s linear infinite;
	}
	.blink-border {
		animation: blink-border 1.6s linear infinite;
	}
	.blink-bg {
		animation: blink-bg 2.2s linear infinite;
	}
	@keyframes blink-border {
		0%   { box-shadow: 0 0 0 0 rgba(138,75,255,0); border-color: rgba(255,255,255,0.12);}
		30%  { box-shadow: 0 0 12px 6px rgba(138,75,255,0.18); border-color: rgba(255,255,255,0.28);}
		60%  { box-shadow: 0 0 6px 3px rgba(138,75,255,0.10); border-color: rgba(255,255,255,0.12);}
		100% { box-shadow: 0 0 0 0 rgba(138,75,255,0); border-color: rgba(255,255,255,0.12);}
	}
	@keyframes blink-bg {
		0%   { background-position: 0% 50%; }
		50%  { background-position: 100% 50%; }
		100% { background-position: 0% 50%; }
	}
</style>
