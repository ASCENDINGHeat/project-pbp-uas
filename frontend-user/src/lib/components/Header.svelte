<script lang="ts">
	import { page } from '$app/stores';
	import BrandButton from './BrandButton.svelte';
	import UserMenu from './UserMenu.svelte';
	// 1. Pastikan Component CartDrawer diimport
	import CartDrawer from './CartDrawer.svelte'; 
	import { goto } from '$app/navigation';
	import { cart, removeFromCart, clearCart } from '$lib/stores/cart';
	import { wishlist } from '$lib/stores/wishlist';
	import { createEventDispatcher } from 'svelte';

	const dispatch = createEventDispatcher();

	// --- Search State ---
	let searchTerm = '';
	function handleSearch() {
		if (searchTerm.trim()) {
			goto(`/web/categories/all?search=${encodeURIComponent(searchTerm)}`);
		}
	}

	function handleSearchKeydown(e: KeyboardEvent) {
		if (e.key === 'Enter') handleSearch();
	}

	// --- Menu States ---
	let cartOpen: boolean = false;
	let wishlistOpen: boolean = false;
	let showUserDropdown: boolean = false;

	// Elements for click-outside detection
	let cartBtnEl: HTMLElement | null = null;
	let wishlistEl: HTMLElement | null = null;
	let wishlistBtnEl: HTMLElement | null = null;

	// --- Handlers ---
	function toggleCart(): void {
		cartOpen = !cartOpen;
		if (cartOpen) { 
			wishlistOpen = false;
			showUserDropdown = false; 
		}
		dispatch('cartClick');
	}

	function closeCart(): void { 
		cartOpen = false; 
	}
	
	function toggleWishlist(): void {
		wishlistOpen = !wishlistOpen;
		if (wishlistOpen) { 
			cartOpen = false; 
			showUserDropdown = false; 
		}
	}
	
	function closeWishlist(): void { 
		wishlistOpen = false;
	}

	function toggleUserMenu(): void {
		showUserDropdown = !showUserDropdown;
		if (showUserDropdown) { 
			cartOpen = false; 
			wishlistOpen = false;
		}
	}

	// Click Outside Handler
	function onWindowClick(e: Event): void {
		const target = (e as MouseEvent).target as Node | null;
		
		// Note: CartDrawer sudah punya logic tutup sendiri (backdrop), 
		// jadi kita hanya cek wishlist dan user menu di sini.
		
		if (wishlistOpen && target && wishlistEl && wishlistBtnEl && !wishlistEl.contains(target) && !wishlistBtnEl.contains(target)) {
			wishlistOpen = false;
		}
		
		const userMenuArea = document.querySelector('.user-menu-area');
		if (showUserDropdown && target && userMenuArea && !userMenuArea.contains(target)) {
			showUserDropdown = false;
		}
	}

	$: if (wishlistOpen) setTimeout(() => wishlistEl?.focus(), 0);

	function handleCategoryClick(path: string) {
		goto(path.startsWith('/web') ? path : `/web${path}`);
	}

	function handleWishlistClick(path: string) {
		closeWishlist();
		goto(path.startsWith('/web') ? path : `/web${path}`);
	}

	function onKeyDown(e: KeyboardEvent): void {
		if (e.key === 'Escape') {
			showUserDropdown = false;
			cartOpen = false;
			wishlistOpen = false;
		}
	}
</script>

<CartDrawer isOpen={cartOpen} on:close={closeCart} />

<header class="site-header">
	<div class="site-top">
		<div class="brand">
			<div class="brand-top-row">
				<a href="/web" class="logo" aria-label="Beranda - PC Store">TechForge</a>
				
				<div class="brand-action-row">
					<BrandButton variant="pc" ariaLabel="PC Ready" on:click={() => goto('/web/pc-ready')}>PC Ready</BrandButton>
					<BrandButton variant="sell" ariaLabel="Simulasi PC" on:click={() => goto('/web/simulasi-pc')}>Simulasi PC</BrandButton>
				</div>
			</div>
		</div>

		<div class="top-right desktop-only">
			<div class="search-wrap" role="search">
				<input 
					class="search-input" 
					type="search" 
					placeholder="Cari produk ..." 
					aria-label="Cari produk"
					bind:value={searchTerm}
					on:keydown={handleSearchKeydown}
				/>
				<button class="search-btn" aria-hidden="true" title="Cari" on:click={handleSearch}>
					<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="white" stroke-width="2" aria-hidden="true">
						<circle cx="11" cy="11" r="6"></circle>
						<path d="M21 21l-4.35-4.35"></path>
					</svg>
				</button>
			</div>

			<div class="cart-wrapper">
				<button
					class="cart-btn"
					on:click={() => toggleCart()}
					bind:this={cartBtnEl}
					aria-haspopup="true"
					aria-expanded={cartOpen}
					aria-label="Keranjang"
				>
					<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
						<path d="M6 6h15l-1.68 9.39A2 2 0 0 1 17.36 17H8.64a2 2 0 0 1-1.96-1.61L5 4H3"></path>
						<circle cx="10" cy="20" r="1"></circle>
						<circle cx="18" cy="20" r="1"></circle>
					</svg>
					<span class="cart-count">{$cart.length}</span>
				</button>
				
				</div>

			<div class="wishlist-wrapper">
				<button
					class="wishlist-btn"
					on:click={toggleWishlist}
					bind:this={wishlistBtnEl}
					aria-haspopup="true"
					aria-expanded={wishlistOpen}
					aria-controls="wishlist-menu"
					aria-label="Wishlist"
				>
					<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
						<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
					</svg>
					<span class="wishlist-count">0</span>
				</button>

				{#if wishlistOpen}
					<div class="wishlist-menu menu" bind:this={wishlistEl} id="wishlist-menu" role="menu" tabindex="-1" on:click|stopPropagation>
						<a href="/web/wishlist" class="menu-item" role="menuitem" on:click|preventDefault={() => handleWishlistClick('/web/wishlist')}>
							<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l7.78-7.78a5.5 5.5 0 0 0 0-7.78z"/></svg>
							<span>Lihat Wishlist</span>
						</a>
					</div>
				{/if}
			</div>

			<div class="user-menu-area">
				<button
					class="btn-icon-user"
					on:click={toggleUserMenu}
					aria-haspopup="true"
					aria-expanded={showUserDropdown}
					aria-label="Menu Pengguna"
				>
					<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
						<circle cx="12" cy="7" r="4"></circle>
					</svg>
				</button>

				{#if showUserDropdown}
					<div class="dropdown-wrapper">
						<UserMenu on:close={() => showUserDropdown = false} />
					</div>
				{/if}
			</div>
		</div>

		<button class="mobile-menu-btn" aria-label="Menu" on:click={() => goto('/web/sidebar-mobile')}>
			<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="white" stroke-width="2">
				<line x1="3" y1="6" x2="21" y2="6"></line>
				<line x1="3" y1="12" x2="21" y2="12"></line>
				<line x1="3" y1="18" x2="21" y2="18"></line>
			</svg>
		</button>
	</div>
</header>

<nav class="brand-category-bar bottom desktop-only" aria-label="Kategori - full width">
	<div class="categories" role="navigation" aria-label="Kategori produk">
		<a class="cat-pill large" href="/web/categories/all" on:click|preventDefault={() => handleCategoryClick('/web/categories/all')}>ALL CATEGORIES</a>
		<a class="cat-pill" href="/web/categories/processor" on:click|preventDefault={() => handleCategoryClick('/web/categories/processor')}>PROCESSOR</a>
		<a class="cat-pill" href="/web/categories/motherboard" on:click|preventDefault={() => handleCategoryClick('/web/categories/motherboard')}>MOTHERBOARD</a>
		<a class="cat-pill" href="/web/categories/vga" on:click|preventDefault={() => handleCategoryClick('/web/categories/vga')}>VGA</a>
		<a class="cat-pill" href="/web/categories/storage" on:click|preventDefault={() => handleCategoryClick('/web/categories/storage')}>STORAGE</a>
		<a class="cat-pill" href="/web/categories/ram" on:click|preventDefault={() => handleCategoryClick('/web/categories/ram')}>RAM</a>
		<a class="cat-pill" href="/web/categories/casing" on:click|preventDefault={() => handleCategoryClick('/web/categories/casing')}>CASING</a>
		<a class="cat-pill" href="/web/categories/psu" on:click|preventDefault={() => handleCategoryClick('/web/categories/psu')}>PSU</a>
		<a class="cat-pill" href="/web/categories/monitor" on:click|preventDefault={() => handleCategoryClick('/web/categories/monitor')}>MONITOR</a>
	</div>
</nav>

<svelte:window on:click={onWindowClick} on:keydown={onKeyDown} />

<style>
	:root { --category-bar-height: 56px; }

	.brand-category-bar {
		min-height: var(--category-bar-height);
		padding: 0 16px;
		display: flex;
		align-items: center;
		box-sizing: border-box;
	}

	.site-top {
		min-height: var(--category-bar-height);
		align-items: center;
	}

	.brand,
	.brand-top-row,
	.brand-action-row,
	.top-right,
	.search-wrap {
		height: 100%;
		display: flex;
		align-items: center;
	}

	.logo {
		display: inline-flex;
		align-items: center;
		height: 100%;
	}

	.brand-action-row :global(button),
	.brand-action-row .brand-action {
		height: calc(var(--category-bar-height) - 14px);
		padding: 0 18px;
		border-radius: 10px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
	}

	.search-wrap {
		height: calc(var(--category-bar-height) - 14px);
		padding: 0 12px;
		border-radius: 999px;
	}

	.search-input {
		height: 100%;
		line-height: 1;
		padding: 0 8px;
	}

	.cart-btn,
	.wishlist-btn,
	.icon-btn {
		width: calc(var(--category-bar-height) - 12px);
		height: calc(var(--category-bar-height) - 12px);
		min-width: calc(var(--category-bar-height) - 12px);
		min-height: calc(var(--category-bar-height) - 12px);
		display: inline-flex;
		align-items: center;
		justify-content: center;
		border-radius: 999px;
	}

	.cart-count { top: -6px; right: -6px; }

	@media (max-width: 900px) {
		:root { --category-bar-height: 48px; }
		.search-input { width: 140px; }
	}

	@media (max-width: 480px) {
		:root { --category-bar-height: 44px; }
		.search-input { width: 100px; }
		.logo { font-size: 1.2rem; }
	}

	.site-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 14px 20px;
		background: #8E42E1;
		color: #fff;
		box-shadow: 0 2px 6px rgba(0,0,0,0.12);
		position: sticky;
		top: 0;
		z-index: 20;
	}

	.site-top {
		display: flex;
		align-items: center;
		gap: 18px;
		width: 100%;
		justify-content: space-between;
	}

	.brand {
		display: flex;
		flex-direction: column;
		gap: 10px;
		min-width: 220px;
	}

	.brand-top-row {
		display: flex;
		align-items: center;
		gap: 16px;
	}

	.logo {
		font-weight: 900;
		font-size: 3.2rem;
		color: #fff;
		line-height: 1;
		text-decoration: none;
		cursor: pointer;
		transition: opacity 0.2s;
	}

	.logo:hover { opacity: 0.8; }

	.brand-action-row {
		display: flex;
		align-items: center;
		gap: 10px;
		margin-left: 8px;
	}

	.brand-category-bar {
		display: flex;
		align-items: center;
		overflow: hidden;
		background: linear-gradient(90deg,#d33ad3,#6b3bff);
		padding: 11px 10px;
		width: 95%;
		margin: 0px auto 0 auto;
		border-radius: 0 0 14px 14px;
		box-shadow: none; 
		box-sizing: border-box;
	}

	.brand-category-bar.bottom { padding-left: 24px; padding-right: 24px; }
	.categories { display: flex; gap: 10px; width: 100%; }
	.cat-pill { color: #fff; font-weight: 900; padding: 12px 18px; border-radius: 999px; background: rgba(255,255,255,0.10); font-size: 1.12rem; white-space: nowrap; }

	.cat-pill, .cat-pill:link, .cat-pill:visited, .cat-pill:hover, .cat-pill:focus { text-decoration: none; }
	
	.top-right { display: flex; align-items: center; gap: 12px; flex: 0 0 auto; margin-left: 0; }
	.search-input { background: transparent; border: 2px solid #fff; color: #fff; width: 420px; outline: none; font-size: 1.18rem; padding: 6px 12px; border-radius: 15px; transition: border-color 0.2s; }
	.search-input:focus { border-color: #ff5f8a; }
	.search-input::placeholder { color: rgba(255,255,255,0.65); font-size: 1.08rem; }

	.cart-wrapper { position: relative; margin-right: 12px; }
	.cart-btn { width: 64px; height: 64px; border-radius: 100%; background: linear-gradient(135deg,#7B4BFF,#4BB1FF); border: 2px solid rgba(255,255,255,0.12); color: white; display: inline-flex; align-items: center; justify-content: center; position: relative; cursor: pointer; }
	.cart-count { position: absolute; right: -6px; top: -6px; background: #ff3b30; color: white; font-weight: 700; font-size: 0.7rem; padding: 2px 6px; border-radius: 999px; border: 2px solid #0f0d28; }

	.menu-item { display: flex; align-items: center; gap: 10px; padding: 10px 12px; color: #EDE7FF; text-decoration: none; border-radius: 8px; transition: background 0.12s ease; }
	.menu-item svg { opacity: 0.9; }
	.menu-item:hover { background: rgba(255,255,255,0.03); }

	.search-btn { background: transparent; border: none; padding: 6px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; }
	.mobile-menu-btn { display: none; background: transparent; border: none; color: white; cursor: pointer; padding: 8px; }

	@media (max-width: 768px) {
		.site-header { padding: 12px 16px; }
		.site-top { flex-direction: column; gap: 12px; align-items: flex-start; }
		.brand { min-width: 100%; width: 100%; }
		.brand-top-row { width: 100%; flex-direction: column; gap: 12px; align-items: flex-start; }
		.logo { font-size: 1.8rem; letter-spacing: 0.05em; }
		.brand-action-row { width: 100%; gap: 10px; flex-wrap: wrap; margin-left: 0; }
		.desktop-only { display: none !important; }
		.mobile-only { display: flex !important; }
		.mobile-menu-btn { display: block; position: absolute; top: 12px; right: 16px; }
	}

	@media (max-width: 640px) {
		.site-header { padding: 10px 12px; position: relative; }
		.site-top { flex-direction: column; gap: 8px; align-items: flex-start; }
		.brand { width: 100%; min-width: 100%; }
		.brand-top-row { width: 100%; flex-direction: column; gap: 10px; }
		.logo { font-size: 1.6rem; margin-bottom: 4px; }
		.brand-action-row { width: 100%; gap: 8px; flex-wrap: wrap; margin-left: 0; }
		.mobile-menu-btn { display: block; position: absolute; top: 10px; right: 12px; }
	}

	@media (max-width: 480px) {
		.site-header { padding: 8px 10px; }
		.logo { font-size: 1.4rem; }
		.brand-top-row { gap: 8px; }
		.brand-action-row { gap: 6px; }
	}

	.search-wrap { position: relative; }
	.search-input { padding: 8px 44px 8px 12px; }

	.search-btn {
		position: absolute;
		right: 8px;
		top: 50%;
		transform: translateY(-50%);
		width: 36px;
		height: 36px;
		border-radius: 999px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		background: rgba(255,255,255,0.08);
		border: none;
		color: #fff;
		cursor: pointer;
		padding: 0;
	}

	.wishlist-wrapper { position: relative; margin: 0 8px; display: inline-flex; align-items: center; }
	.wishlist-btn {
		width: 64px;
		height: 64px;
		border-radius: 999px;
		background: linear-gradient(135deg,#FF77A9,#FFB86A);
		border: 2px solid rgba(255,255,255,0.12);
		color: white;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		position: relative;
		cursor: pointer;
	}
	.wishlist-count {
		position: absolute;
		right: -6px;
		top: -6px;
		background: #ff3b30;
		color: white;
		font-weight: 700;
		font-size: 0.65rem;
		padding: 2px 6px;
		border-radius: 999px;
		border: 2px solid rgba(15,13,40,0.9);
	}

	.wishlist-menu {
		position: absolute;
		right: 0;
		top: calc(100% + 8px);
		min-width: 200px;
		background: #0f0d28;
		border-radius: 10px;
		box-shadow: 0 10px 30px rgba(0,0,0,0.45);
		padding: 8px;
		z-index: 30;
	}

	.user-menu-area {
		position: relative;
		display: inline-flex;
		align-items: center;
		margin-left: 8px;
	}

	.btn-icon-user {
		width: 64px;
		height: 64px;
		border-radius: 50%;
		background: linear-gradient(135deg, #FF6B9D, #C44569);
		border: 2px solid rgba(255, 255, 255, 0.12);
		color: white;
		cursor: pointer;
		padding: 0;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		position: relative;
		transition: transform 0.2s, box-shadow 0.2s;
	}

	.btn-icon-user:hover {
		transform: scale(1.05);
		box-shadow: 0 8px 20px rgba(255, 107, 157, 0.3);
	}

	.dropdown-wrapper {
		position: absolute;
		top: 100%;
		right: 0;
		margin-top: 8px;
		z-index: 9999;
	}
</style>