<script lang="ts">
	import { goto } from '$app/navigation';
	import { createEventDispatcher } from 'svelte';

	const dispatch = createEventDispatcher();

	function handleSettingsClick() {
		const isLoggedIn = localStorage.getItem("userLoggedIn");
		if (isLoggedIn) {
			goto('/web/pengaturan');
		} else {
			goto('/web/login');
		}
		dispatch('close');
	}

	function handleMenuItemClick(path: string) {
		goto(path);
		dispatch('close');
	}

	// NEW: Check login status untuk display
	let isLoggedIn = false;
	$: if (typeof window !== 'undefined') {
		isLoggedIn = !!localStorage.getItem("userLoggedIn");
	}
</script>

<div class="user-menu-dropdown" role="menu" aria-label="User menu">
	<!-- NEW: Status badge -->
	<div class="menu-header">
		<span class="status-badge {isLoggedIn ? 'logged-in' : 'logged-out'}">
			{isLoggedIn ? '✓ Terkoneksi' : '○ Belum Login'}
		</span>
	</div>

	<a href="/web/login" class="menu-item" role="menuitem" on:click|preventDefault={() => handleMenuItemClick('/web/login')}>
		<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.5">
			<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
			<path d="M10 17l5-5-5-5"></path>
		</svg>
		<span>Login</span>
	</a>

	<a href="/web/register" class="menu-item" role="menuitem" on:click|preventDefault={() => handleMenuItemClick('/web/register')}>
		<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.5">
			<path d="M12 5v14"></path>
			<path d="M5 12h14"></path>
		</svg>
		<span>Register</span>
	</a>

	<a href="/web/pengaturan" class="menu-item muted" role="menuitem" on:click|preventDefault={handleSettingsClick}>
		<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.5">
			<path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"></path>
			<path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06A2 2 0 0 1 2.27 16.9l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09c.68 0 1.26-.41 1.51-1a1.65 1.65 0 0 0-.33-1.82l-.06-.06A2 2 0 0 1 6.1 2.27l.06.06c.49.49 1.14.71 1.82.33.49-.28 1.12-.28 1.61 0 .68.38 1.33.16 1.82-.33l.06-.06A2 2 0 0 1 14 2.27l.06.06c.49.49 1.14.71 1.82.33.49-.28 1.12-.28 1.61 0 .68.38 1.33.16 1.82-.33l.06-.06A2 2 0 0 1 21.73 7.1l-.06.06c-.28.49-.28 1.12 0 1.61.38.68.16 1.33-.33 1.82l-.06.06c-.49.49-.71 1.14-.33 1.82.28.49.28 1.12 0 1.61-.38.68-.16 1.33.33 1.82l.06.06A2 2 0 0 1 19.4 15z"></path>
		</svg>
		<span>Pengaturan</span>
	</a>
</div>

<style>
	.user-menu-dropdown {
		background: #0f0d28;
		border-radius: 10px;
		box-shadow: 0 10px 30px rgba(0, 0, 0, 0.45);
		padding: 12px;
		min-width: 200px;
		overflow: hidden;
	}

	/* NEW: Menu header dengan status badge */
	.menu-header {
		padding: 8px 4px;
		margin-bottom: 8px;
		border-bottom: 1px solid rgba(255, 255, 255, 0.1);
	}

	.status-badge {
		display: inline-block;
		padding: 6px 10px;
		border-radius: 20px;
		font-size: 0.8rem;
		font-weight: 600;
		color: #fff;
	}

	.status-badge.logged-in {
		background: rgba(34, 197, 94, 0.2);
		color: #86efac;
		border: 1px solid rgba(34, 197, 94, 0.3);
	}

	.status-badge.logged-out {
		background: rgba(239, 68, 68, 0.15);
		color: #fca5a5;
		border: 1px solid rgba(239, 68, 68, 0.3);
	}

	.menu-item {
		display: flex;
		align-items: center;
		gap: 10px;
		padding: 10px 12px;
		color: #ede7ff;
		text-decoration: none;
		border-radius: 8px;
		transition: background 0.12s ease;
		cursor: pointer;
	}

	.menu-item:hover {
		background: rgba(255, 255, 255, 0.03);
	}

	.menu-item svg {
		opacity: 0.9;
	}

	.menu-item.muted {
		opacity: 0.8;
		font-size: 0.95rem;
	}
</style>
