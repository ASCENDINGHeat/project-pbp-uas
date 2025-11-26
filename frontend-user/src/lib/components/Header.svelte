<script lang="ts">
	import { page } from '$app/stores';
	import BrandButton from './BrandButton.svelte';
	import { goto } from '$app/navigation';

	// profile menu
	let menuOpen: boolean = false;
	let menuEl: HTMLElement | null = null;
	let btnEl: HTMLElement | null = null;

	// cart menu
	let cartOpen: boolean = false;
	let cartEl: HTMLElement | null = null;
	let cartBtnEl: HTMLElement | null = null;

	// --- ADDED: wishlist state & handlers ---
	let wishlistOpen: boolean = false;
	let wishlistEl: HTMLElement | null = null;
	let wishlistBtnEl: HTMLElement | null = null;
	// --- end added ---

	function toggleMenu(): void {
		menuOpen = !menuOpen;
		if (menuOpen) { cartOpen = false; wishlistOpen = false; }
	}

	function closeMenu(): void {
		menuOpen = false;
	}

	function toggleCart(): void {
		cartOpen = !cartOpen;
		if (cartOpen) { menuOpen = false; wishlistOpen = false; }
	}

	function closeCart(): void {
		cartOpen = false;
	}

	// --- ADDED: wishlist toggles ---
	function toggleWishlist(): void {
		wishlistOpen = !wishlistOpen;
		if (wishlistOpen) { menuOpen = false; cartOpen = false; }
	}
	function closeWishlist(): void {
		wishlistOpen = false;
	}
	// --- end added ---

	function onWindowClick(e: Event): void {
		const target = (e as MouseEvent).target as Node | null;
		if (
			menuOpen &&
			target &&
			menuEl &&
			btnEl &&
			!menuEl.contains(target) &&
			!btnEl.contains(target)
		) {
			menuOpen = false;
		}
		if (
			cartOpen &&
			target &&
			cartEl &&
			cartBtnEl &&
			!cartEl.contains(target) &&
			!cartBtnEl.contains(target)
		) {
			cartOpen = false;
		}
		// handle wishlist outside click
		if (
			wishlistOpen &&
			target &&
			wishlistEl &&
			wishlistBtnEl &&
			!wishlistEl.contains(target) &&
			!wishlistBtnEl.contains(target)
		) {
			wishlistOpen = false;
		}
	}

	function onWindowKeydown(e: KeyboardEvent): void {
		if (e.key === 'Escape') {
			if (menuOpen) closeMenu();
			if (cartOpen) closeCart();
			if (wishlistOpen) closeWishlist();
		}
	}

	// focus programmatically when opened (include wishlist)
	$: if (menuOpen) setTimeout(() => menuEl?.focus(), 0);
	$: if (cartOpen) setTimeout(() => cartEl?.focus(), 0);
	$: if (wishlistOpen) setTimeout(() => wishlistEl?.focus(), 0);

	function handleMenuClick(path: string) {
		closeMenu();
		goto(path);
	}

	function handleCategoryClick(path: string) {
		goto(path);
	}

	function handleWishlistClick(path: string) {
		closeWishlist();
		goto(path);
	}
</script>

<header class="site-header">
	<div class="site-top">
		<div class="brand">
			<div class="brand-top-row">
				<div class="logo">PC Store</div>
				<div class="brand-action-row">
					<BrandButton as="button" ariaLabel="PC Ready" on:click={() => handleCategoryClick('/pc-ready')}>PC Ready</BrandButton>
					<BrandButton as="button" ariaLabel="Jual PC" on:click={() => handleCategoryClick('/jual-pc')}>Jual PC</BrandButton>
				</div>
			</div>
		</div>

		<div class="top-right desktop-only">
			<div class="search-wrap" role="search">
				<input class="search-input" type="search" placeholder="Cari produk ..." aria-label="Cari produk" />
				<button class="search-btn" aria-hidden="true" title="Cari">
					<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="white" stroke-width="2" aria-hidden="true">
						<circle cx="11" cy="11" r="6"></circle>
						<path d="M21 21l-4.35-4.35"></path>
					</svg>
				</button>
			</div>

			<!-- cart button (existing logic kept, bind variables are in script) -->
			<div class="cart-wrapper">
				<button
					class="cart-btn"
					on:click={toggleCart}
					bind:this={cartBtnEl}
					aria-haspopup="true"
					aria-expanded={cartOpen}
					aria-controls="cart-menu"
					aria-label="Keranjang"
				>
					<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
						<path d="M6 6h15l-1.68 9.39A2 2 0 0 1 17.36 17H8.64a2 2 0 0 1-1.96-1.61L5 4H3"></path>
						<circle cx="10" cy="20" r="1"></circle>
						<circle cx="18" cy="20" r="1"></circle>
					</svg>
					<span class="cart-count">0</span>
				</button>

				{#if cartOpen}
					<aside
						class="cart-menu menu cart-drawer"
						bind:this={cartEl}
						id="cart-menu"
						role="dialog"
						aria-labelledby="cart-title"
						aria-label="Cart drawer"
						tabindex="-1"
						on:click|stopPropagation
					>
						<header class="cart-header">
							<h2 id="cart-title">Keranjang Belanja</h2>
							<button class="cart-close" on:click={closeCart} aria-label="Tutup keranjang">✕</button>
						</header>

						<section class="cart-body">
							<div class="cart-empty-illustration" aria-hidden="true">
								<svg viewBox="0 0 64 64" width="88" height="88" fill="none" stroke="#9aa0a6" stroke-width="1.5">
									<circle cx="32" cy="32" r="28" fill="#f4f5f6"></circle>
									<path d="M22 28h20l-3.5 12H25.5L22 28z" fill="none" stroke="#9aa0a6"></path>
								</svg>
							</div>

							<p class="cart-empty-title">Keranjang belanja Anda masih kosong</p>
							<a href="/" class="btn btn-primary btn-wide" on:click={closeCart}>Lanjutkan Belanja</a>
						</section>

						<footer class="cart-footer">
							<div class="cart-subtotal">
								<span>Subtotal:</span>
								<strong>0</strong>
							</div>

							<div class="cart-actions">
								<a href="/cart" class="btn btn-outline" on:click={closeCart}>Lihat Keranjang</a>
								<a href="/checkout" class="btn btn-dark" on:click={closeCart}>Checkout</a>
							</div>
						</footer>
					</aside>
				{/if}
			</div>

			<!-- MOVED: wishlist should be here (between cart and profile) -->
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
						<a href="/wishlist" class="menu-item" role="menuitem" on:click|preventDefault={() => handleWishlistClick('/wishlist')}>
							<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l7.78-7.78a5.5 5.5 0 0 0 0-7.78z"/></svg>
							<span>Lihat Wishlist</span>
						</a>
						<a href="/profile/wishlist" class="menu-item" role="menuitem" on:click|preventDefault={() => handleWishlistClick('/profile/wishlist')}>
							<span>Wishlist Saya</span>
						</a>
					</div>
				{/if}
			</div>
			<!-- end moved -->

			<div class="profile">
				<button
					class="profile-btn"
					on:click={toggleMenu}
					bind:this={btnEl}
					aria-haspopup="true"
					aria-expanded={menuOpen}
					aria-controls="profile-menu"
				>
					<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.5">
						<path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"></path>
						<path d="M4 20a8 8 0 0 1 16 0"></path>
					</svg>
				</button>

				{#if menuOpen}
					<div class="profile-menu" bind:this={menuEl} id="profile-menu" role="menu" aria-label="User menu" tabindex="-1" on:click|stopPropagation>
						<a href="/login" class="menu-item" role="menuitem" on:click|preventDefault={() => handleMenuClick('/login')}>
							<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.5">
								<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
								<path d="M10 17l5-5-5-5"></path>
							</svg>
							<span>Login</span>
						</a>
						<a href="/register" class="menu-item" role="menuitem" on:click|preventDefault={() => handleMenuClick('/register')}>
							<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.5">
								<path d="M12 5v14"></path>
								<path d="M5 12h14"></path>
							</svg>
							<span>Register</span>
						</a>
						<a href="/settings" class="menu-item muted" role="menuitem" on:click|preventDefault={() => handleMenuClick('/settings')}>
							<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.5">
								<path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"></path>
								<path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06A2 2 0 0 1 2.27 16.9l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09c.68 0 1.26-.41 1.51-1a1.65 1.65 0 0 0-.33-1.82l-.06-.06A2 2 0 0 1 6.1 2.27l.06.06c.49.49 1.14.71 1.82.33.49-.28 1.12-.28 1.61 0 .68.38 1.33.16 1.82-.33l.06-.06A2 2 0 0 1 14 2.27l.06.06c.49.49 1.14.71 1.82.33.49-.28 1.12-.28 1.61 0 .68.38 1.33.16 1.82-.33l.06-.06A2 2 0 0 1 21.73 7.1l-.06.06c-.28.49-.28 1.12 0 1.61.38.68.16 1.33-.33 1.82l-.06.06c-.49.49-.71 1.14-.33 1.82.28.49.28 1.12 0 1.61-.38.68-.16 1.33.33 1.82l.06.06A2 2 0 0 1 19.4 15z"></path>
							</svg>
							<span>Pengaturan</span>
						</a>
					</div>
				{/if}
			</div>
		</div>

					<!-- ADDED: wishlist placed between cart and profile -->
			
		<button class="mobile-menu-btn" aria-label="Menu">
			<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="white" stroke-width="2">
				<line x1="3" y1="6" x2="21" y2="6"></line>
				<line x1="3" y1="12" x2="21" y2="12"></line>
				<line x1="3" y1="18" x2="21" y2="18"></line>
			</svg>
		</button>
	</div>
</header>

<!-- KATEGORI: dipindahkan keluar header supaya berada di bawah (full-width) -->
<nav class="brand-category-bar bottom desktop-only" aria-label="Kategori - full width">
	<div class="categories" role="navigation" aria-label="Kategori produk">
		<a class="cat-pill large" href="/categories/all" on:click|preventDefault={() => handleCategoryClick('/categories/all')}>ALL CATEGORIES</a>
		<a class="cat-pill" href="/categories/processor" on:click|preventDefault={() => handleCategoryClick('/categories/processor')}>PROCESSOR</a>
		<a class="cat-pill" href="/categories/motherboard" on:click|preventDefault={() => handleCategoryClick('/categories/motherboard')}>MOTHERBOARD</a>
		<a class="cat-pill" href="/categories/vga" on:click|preventDefault={() => handleCategoryClick('/categories/vga')}>VGA</a>
		<a class="cat-pill" href="/categories/storage" on:click|preventDefault={() => handleCategoryClick('/categories/storage')}>STORAGE</a>
		<a class="cat-pill" href="/categories/ram" on:click|preventDefault={() => handleCategoryClick('/categories/ram')}>RAM</a>
		<a class="cat-pill" href="/categories/casing" on:click|preventDefault={() => handleCategoryClick('/categories/casing')}>CASING</a>
		<a class="cat-pill" href="/categories/psu" on:click|preventDefault={() => handleCategoryClick('/categories/psu')}>PSU</a>
		<a class="cat-pill" href="/categories/monitor" on:click|preventDefault={() => handleCategoryClick('/categories/monitor')}>MONITOR</a>
	</div>
</nav>

<svelte:window on:click={onWindowClick} on:keydown={onWindowKeydown} />

<style>
	:root {
		/* tinggi acuan untuk bar kategori dan elemen header */
		--category-bar-height: 56px;
	}

	/* pastikan category bar menggunakan tinggi acuan */
	.brand-category-bar {
		min-height: var(--category-bar-height);
		padding: 0 16px; /* horizontal spacing tetap */
		display: flex;
		align-items: center;
		box-sizing: border-box;
	}

	/* header top (kotak yang berisi logo, tombol, search, icon) samakan tinggi */
	.site-top {
		min-height: var(--category-bar-height);
		align-items: center;
	}

	/* buat elemen internal mengisi tinggi header dan ter-center vertikal */
	.brand,
	.brand-top-row,
	.brand-action-row,
	.top-right,
	.search-wrap {
		height: 100%;
		display: flex;
		align-items: center;
	}

	/* logo agar vertikal terpusat tanpa mengubah ukuran font yang sudah ada */
	.logo {
		/* biarkan ukuran font ada, cukup pastikan center secara vertikal */
		display: inline-flex;
		align-items: center;
		height: 100%;
	}

	/* tombol PC Ready / Jual PC mengikuti tinggi header */
	.brand-action-row :global(button),
	.brand-action-row .brand-action {
		height: calc(var(--category-bar-height) - 14px); /* beri sedikit padding vertikal */
		padding: 0 18px;
		border-radius: 10px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
	}

	/* search pill mengikuti tinggi yang sama */
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

	/* cart / profile tombol ukuran sama dengan category bar (sedikit dikurangi agar tidak melebihi) */
	.cart-btn,
	.profile-btn,
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

	/* sesuaikan badge posisi bila diperlukan */
	.cart-count {
		top: -6px;
		right: -6px;
	}

	/* Responsive: tetap jaga proporsi di layar kecil */
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
	}

	.brand-action-row {
		display: flex;
		align-items: center;
		gap: 10px;
		margin-left: 8px;
	}

	.brand-action {
		background: linear-gradient(90deg,#ff5f8a,#8a4bff, #ff5f8a 80%);
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

	.brand-tag {
		background: linear-gradient(90deg,#ff5f8a,#8a4bff, #ff5f8a 80%);
		background-size: 200% 200%;
		background-position: 0% 50%;
		color: #fff;
		font-weight: 900;
		padding: 10px 20px;
		border-radius: 12px;
		font-size: 1.05rem;
		border: 2px solid rgba(255,255,255,0.12);
		box-shadow: 0 10px 24px rgba(11,9,38,0.22);
		margin-left: 0;
		animation: blink-border 1.6s linear infinite, blink-bg 2.2s linear infinite;
	}

	@keyframes blink-border {
		0%   { box-shadow: 0 0 0 0 rgba(138,75,255,0); border-color: rgba(255,255,255,0.12); }
		30%  { box-shadow: 0 0 12px 6px rgba(138,75,255,0.18); border-color: rgba(255,255,255,0.28); }
		60%  { box-shadow: 0 0 6px 3px rgba(138,75,255,0.10); border-color: rgba(255,255,255,0.12); }
		100% { box-shadow: 0 0 0 0 rgba(138,75,255,0); border-color: rgba(255,255,255,0.12); }
	}

	@keyframes blink-bg {
		0%   { background-position: 0% 50%; }
		50%  { background-position: 100% 50%; }
		100% { background-position: 0% 50%; }
	}

	.brand-category-bar {
		display: flex;
		align-items: center;
		overflow: center;
		background: linear-gradient(90deg,#d33ad3,#6b3bff);
		padding: 11px 10px;
		width: 95%;
		margin: 9px auto 0 auto;
		border-radius: 0 0 14px 14px;
		box-shadow: 0 12px 36px rgba(107,59,255,0.16);
		box-sizing: border-box;
	}
 
	.brand-category-bar.bottom { padding-left: 24px; padding-right: 24px; }
	.categories { display: flex; gap: 10px; width: 100%; }
	.cat-pill { color: #fff; font-weight: 900; padding: 12px 18px; border-radius: 999px; background: rgba(255,255,255,0.10); font-size: 1.12rem; white-space: nowrap; }

	.top-right { display: flex; align-items: center; gap: 12px; flex: 0 0 auto; margin-left: 0; }
	.search-input { background: transparent; border: 2px solid #fff; color: #fff; width: 420px; outline: none; font-size: 1.18rem; padding: 6px 12px; border-radius: 15px; transition: border-color 0.2s; }
	.search-input:focus { border-color: #ff5f8a; }
	.search-input::placeholder { color: rgba(255,255,255,0.65); font-size: 1.08rem; }

	.cart-wrapper { position: relative; margin-right: 12px; }
	.cart-btn { width: 64px; height: 64px; border-radius: 100%; background: linear-gradient(135deg,#7B4BFF,#4BB1FF); border: 2px solid rgba(255,255,255,0.12); color: white; display: inline-flex; align-items: center; justify-content: center; position: relative; cursor: pointer; }
	.cart-count { position: absolute; right: -6px; top: -6px; background: #ff3b30; color: white; font-weight: 700; font-size: 0.7rem; padding: 2px 6px; border-radius: 999px; border: 2px solid #0f0d28; }

	.profile { position: relative; margin-left: 16px; }
	.profile-btn { width: 64px; height: 64px; border-radius: 50%; background: linear-gradient(135deg,#FF5A5A,#D32F2F); display: inline-flex; align-items: center; justify-content: center; border: 2px solid rgba(255,255,255,0.12); color: white; cursor: pointer; box-shadow: 0 4px 10px rgba(11,9,38,0.45); }
	.profile-menu { position: absolute; right: 0; top: calc(100% + 8px); min-width: 180px; background: #0f0d28; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.45); padding: 8px; overflow: hidden; z-index: 30; }

	.cart-drawer { position: fixed; right: 0; top: 0; height: 100vh; width: min(420px, 94%); background: #ffffff; color: #222; box-shadow: -20px 0 40px rgba(11,9,38,0.25); z-index: 1000; display: flex; flex-direction: column; padding: 0; }
	.cart-header { display: flex; align-items: center; justify-content: space-between; padding: 22px 20px; border-bottom: 1px solid rgba(0,0,0,0.06); }
	.cart-header h2 { margin: 0; font-size: 1.25rem; color: #1f2d3d; font-weight: 700; }
	.cart-close { background: transparent; border: none; font-size: 1.15rem; color: #7b8794; cursor: pointer; padding: 6px; }
	.cart-body { padding: 36px 24px; text-align: center; flex: 1 1 auto; }
	.cart-empty-illustration { margin-bottom: 18px; display: flex; justify-content: center; }
	.cart-empty-title { color: #334155; margin: 10px 0 18px; }
	.btn { display: inline-block; text-decoration: none; padding: 10px 18px; border-radius: 10px; font-weight: 700; }
	.btn-wide { width: 80%; max-width: 320px; }
	.btn-primary { background: #d32f2f; color: #fff; }
	.btn-outline { background: transparent; border: 1px solid #e0e6ea; color: #1f2d3d; padding: 10px 16px; border-radius: 8px; }
	.btn-dark { background: #2f3a46; color: #fff; padding: 10px 16px; border-radius: 8px; margin-left: 8px; }
	.cart-footer { padding: 14px 18px; border-top: 1px solid rgba(0,0,0,0.06); background: #fff; }
	.cart-subtotal { display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; color: #475569; }
	.cart-actions { display: flex; justify-content: space-between; gap: 8px; }
	.cart-drawer:focus { outline: none; }

	.menu-item { display: flex; align-items: center; gap: 10px; padding: 10px 12px; color: #EDE7FF; text-decoration: none; border-radius: 8px; transition: background 0.12s ease; }
	.menu-item svg { opacity: 0.9; }
	.menu-item:hover { background: rgba(255,255,255,0.03); }
	.menu-item.muted { opacity: 0.8; font-size: 0.95rem; }

	.search-btn { background: transparent; border: none; padding: 6px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; }
	.mobile-menu-btn { display: none; background: transparent; border: none; color: white; cursor: pointer; padding: 8px; }

	/* REMOVED: .bottom-nav and .nav-item CSS rules */
	/* REMOVED: any media-query lines that force .bottom-nav to display (e.g. .bottom-nav { display: flex !important; }) */

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
		.nav-item span { font-size: 0.65rem; }
		.nav-item svg { width: 22px; height: 22px; }
	}

	.cart-items {
		max-height: 400px;
		overflow-y: auto;
		padding: 12px;
	}

	.cart-item {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 12px;
		border-bottom: 1px solid rgba(0, 0, 0, 0.06);
	}

	.item-info {
		flex: 1;
	}

	.item-name {
		margin: 0;
		font-weight: 600;
		color: #1f2d3d;
		font-size: 0.95rem;
	}

	.item-price {
		margin: 4px 0 0;
		color: #666;
		font-size: 0.85rem;
	}

	.item-quantity {
		color: #8E42E1;
		font-weight: 600;
	}

	/* ensure search icon sits inside input and input has room */
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

	/* wishlist styles */
	.wishlist-wrapper { position: relative; margin: 0 8px; display: inline-flex; align-items: center; }
	.wishlist-btn {
		width: 56px;
		height: 56px;
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

</style>
