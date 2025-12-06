<script lang="ts">
	let activeTab: string = 'profil';

	function switchTab(tab: string) {
		activeTab = tab;
	}
</script>

<svelte:head>
	<title>Pengaturan - PC Store</title>
</svelte:head>

<main class="settings-page">
	<div class="settings-container">
		<div class="settings-header">
			<h1>Pengaturan Akun</h1>
			<p>Kelola informasi dan preferensi akun Anda</p>
		</div>

		<div class="settings-layout">
			<!-- Sidebar Navigation -->
			<aside class="settings-sidebar">
				<nav class="settings-nav">
					<button
						class="nav-item {activeTab === 'profil' ? 'active' : ''}"
						on:click={() => switchTab('profil')}
					>
						<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5">
							<path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"></path>
							<path d="M4 20a8 8 0 0 1 16 0"></path>
						</svg>
						<span>Profil</span>
					</button>

					<button
						class="nav-item {activeTab === 'keamanan' ? 'active' : ''}"
						on:click={() => switchTab('keamanan')}
					>
						<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5">
							<path d="M12 1v22m11-9c0 5.5-5 10-11 10S1 19.5 1 14c0-3.5 2-6.5 5-8"></path>
						</svg>
						<span>Keamanan</span>
					</button>

					<button
						class="nav-item {activeTab === 'notifikasi' ? 'active' : ''}"
						on:click={() => switchTab('notifikasi')}
					>
						<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5">
							<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
							<path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
						</svg>
						<span>Notifikasi</span>
					</button>

					<button
						class="nav-item {activeTab === 'privasi' ? 'active' : ''}"
						on:click={() => switchTab('privasi')}
					>
						<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5">
							<path d="M12 1l9 5v7c0 5.55-3.84 10.74-9 12-5.16-1.26-9-6.45-9-12V6l9-5z"></path>
						</svg>
						<span>Privasi</span>
					</button>
				</nav>
			</aside>

			<!-- Content Area -->
			<div class="settings-content">
				{#if activeTab === 'profil'}
					<section class="content-section">
						<h2>Informasi Profil</h2>
						<form class="settings-form">
							<div class="form-group">
								<label for="nama">Nama Lengkap</label>
								<input type="text" id="nama" placeholder="Masukkan nama lengkap Anda" />
							</div>

							<div class="form-group">
								<label for="email">Email</label>
								<input type="email" id="email" placeholder="email@example.com" />
							</div>

							<div class="form-group">
								<label for="telepon">Nomor Telepon</label>
								<input type="tel" id="telepon" placeholder="Masukkan nomor telepon" />
							</div>

							<div class="form-group">
								<label for="alamat">Alamat</label>
								<textarea id="alamat" placeholder="Masukkan alamat lengkap Anda" rows="4"></textarea>
							</div>

							<div class="form-actions">
								<button type="submit" class="btn btn-primary">Simpan Perubahan</button>
								<button type="reset" class="btn btn-outline">Batal</button>
							</div>
						</form>
					</section>
				{:else if activeTab === 'keamanan'}
					<section class="content-section">
						<h2>Keamanan Akun</h2>
						<form class="settings-form">
							<div class="form-group">
								<label for="password-lama">Password Saat Ini</label>
								<input type="password" id="password-lama" placeholder="Masukkan password saat ini" />
							</div>

							<div class="form-group">
								<label for="password-baru">Password Baru</label>
								<input type="password" id="password-baru" placeholder="Masukkan password baru" />
							</div>

							<div class="form-group">
								<label for="konfirmasi-password">Konfirmasi Password</label>
								<input type="password" id="konfirmasi-password" placeholder="Konfirmasi password baru" />
							</div>

							<div class="form-actions">
								<button type="submit" class="btn btn-primary">Ubah Password</button>
								<button type="reset" class="btn btn-outline">Batal</button>
							</div>
						</form>

						<hr class="divider" />

						<div class="security-info">
							<h3>Autentikasi Dua Faktor</h3>
							<p>Tingkatkan keamanan akun Anda dengan autentikasi dua faktor</p>
							<button class="btn btn-secondary">Aktifkan 2FA</button>
						</div>
					</section>
				{:else if activeTab === 'notifikasi'}
					<section class="content-section">
						<h2>Preferensi Notifikasi</h2>
						<div class="notification-settings">
							<div class="notification-item">
								<div class="notification-info">
									<h3>Notifikasi Email</h3>
									<p>Terima notifikasi melalui email tentang pesanan dan promosi</p>
								</div>
								<input type="checkbox" id="email-notif" checked />
								<label for="email-notif" class="toggle-label"></label>
							</div>

							<div class="notification-item">
								<div class="notification-info">
									<h3>Notifikasi SMS</h3>
									<p>Terima notifikasi melalui SMS tentang status pesanan</p>
								</div>
								<input type="checkbox" id="sms-notif" checked />
								<label for="sms-notif" class="toggle-label"></label>
							</div>

							<div class="notification-item">
								<div class="notification-info">
									<h3>Penawaran & Promosi</h3>
									<p>Dapatkan penawaran khusus dan promosi terbaru</p>
								</div>
								<input type="checkbox" id="promo-notif" />
								<label for="promo-notif" class="toggle-label"></label>
							</div>

							<div class="notification-item">
								<div class="notification-info">
									<h3>Update Produk</h3>
									<p>Dapatkan notifikasi tentang produk baru dan update stok</p>
								</div>
								<input type="checkbox" id="product-notif" checked />
								<label for="product-notif" class="toggle-label"></label>
							</div>
						</div>
					</section>
				{:else if activeTab === 'privasi'}
					<section class="content-section">
						<h2>Pengaturan Privasi</h2>
						<div class="privacy-settings">
							<div class="privacy-item">
								<div class="privacy-info">
									<h3>Visibilitas Profil</h3>
									<p>Kontrol siapa yang dapat melihat profil Anda</p>
								</div>
								<select>
									<option>Publik</option>
									<option>Hanya Teman</option>
									<option>Pribadi</option>
								</select>
							</div>

							<div class="privacy-item">
								<div class="privacy-info">
									<h3>Riwayat Pembelian</h3>
									<p>Tentukan akses ke riwayat pembelian Anda</p>
								</div>
								<select>
									<option>Hanya Saya</option>
									<option>Admin</option>
								</select>
							</div>

							<div class="privacy-item">
								<div class="privacy-info">
									<h3>Cookies</h3>
									<p>Kelola penggunaan cookies di website kami</p>
								</div>
								<button class="btn btn-secondary">Kelola Cookies</button>
							</div>

							<hr class="divider" />

							<div class="danger-zone">
								<h3>Zona Berbahaya</h3>
								<p>Tindakan di bawah tidak dapat dibatalkan</p>
								<button class="btn btn-danger">Hapus Akun</button>
							</div>
						</div>
					</section>
				{/if}
			</div>
		</div>
	</div>
</main>

<style>
	.settings-page {
		min-height: calc(100vh - 200px);
		background: #f8f9fa;
		padding: 40px 20px;
	}

	.settings-container {
		max-width: 1200px;
		margin: 0 auto;
	}

	.settings-header {
		margin-bottom: 40px;
	}

	.settings-header h1 {
		font-size: 2rem;
		font-weight: 700;
		color: #1f2d3d;
		margin: 0 0 10px;
	}

	.settings-header p {
		color: #666;
		font-size: 1rem;
		margin: 0;
	}

	.settings-layout {
		display: grid;
		grid-template-columns: 250px 1fr;
		gap: 30px;
	}

	/* Sidebar */
	.settings-sidebar {
		background: white;
		border-radius: 12px;
		padding: 20px;
		height: fit-content;
		box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
	}

	.settings-nav {
		display: flex;
		flex-direction: column;
		gap: 8px;
	}

	.nav-item {
		display: flex;
		align-items: center;
		gap: 12px;
		padding: 12px 16px;
		background: transparent;
		border: none;
		border-radius: 8px;
		color: #666;
		font-size: 0.95rem;
		font-weight: 500;
		cursor: pointer;
		transition: all 0.2s ease;
		text-align: left;
	}

	.nav-item:hover {
		background: #f0f0f0;
		color: #8E42E1;
	}

	.nav-item.active {
		background: #8E42E1;
		color: white;
	}

	.nav-item svg {
		width: 18px;
		height: 18px;
		stroke-width: 1.5;
	}

	/* Content Area */
	.settings-content {
		background: white;
		border-radius: 12px;
		padding: 30px;
		box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
	}

	.content-section h2 {
		font-size: 1.5rem;
		font-weight: 700;
		color: #1f2d3d;
		margin: 0 0 25px;
		padding-bottom: 15px;
		border-bottom: 2px solid #eee;
	}

	/* Forms */
	.settings-form {
		display: flex;
		flex-direction: column;
		gap: 20px;
	}

	.form-group {
		display: flex;
		flex-direction: column;
		gap: 8px;
	}

	.form-group label {
		font-weight: 600;
		color: #1f2d3d;
		font-size: 0.95rem;
	}

	.form-group input,
	.form-group textarea {
		padding: 10px 12px;
		border: 1px solid #ddd;
		border-radius: 8px;
		font-size: 0.95rem;
		font-family: inherit;
		transition: border-color 0.2s;
	}

	.form-group input:focus,
	.form-group textarea:focus {
		outline: none;
		border-color: #8E42E1;
		box-shadow: 0 0 0 3px rgba(142, 66, 225, 0.1);
	}

	.form-actions {
		display: flex;
		gap: 12px;
		margin-top: 15px;
	}

	.btn {
		padding: 10px 20px;
		border-radius: 8px;
		font-weight: 600;
		border: none;
		cursor: pointer;
		transition: all 0.2s;
		font-size: 0.95rem;
	}

	.btn-primary {
		background: #8E42E1;
		color: white;
	}

	.btn-primary:hover {
		background: #7a36c4;
		box-shadow: 0 4px 12px rgba(142, 66, 225, 0.3);
	}

	.btn-outline {
		background: transparent;
		border: 1px solid #ddd;
		color: #666;
	}

	.btn-outline:hover {
		background: #f8f9fa;
		border-color: #999;
	}

	.btn-secondary {
		background: #f0f0f0;
		color: #1f2d3d;
	}

	.btn-secondary:hover {
		background: #e0e0e0;
	}

	.btn-danger {
		background: #d32f2f;
		color: white;
	}

	.btn-danger:hover {
		background: #b71c1c;
	}

	/* Notification Settings */
	.notification-settings {
		display: flex;
		flex-direction: column;
		gap: 20px;
	}

	.notification-item {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 16px;
		background: #f8f9fa;
		border-radius: 8px;
		gap: 20px;
	}

	.notification-info h3 {
		font-size: 1rem;
		font-weight: 600;
		color: #1f2d3d;
		margin: 0 0 5px;
	}

	.notification-info p {
		font-size: 0.85rem;
		color: #666;
		margin: 0;
	}

	input[type="checkbox"] {
		display: none;
	}

	.toggle-label {
		width: 50px;
		height: 28px;
		background: #ccc;
		border-radius: 999px;
		display: inline-block;
		position: relative;
		cursor: pointer;
		transition: background 0.3s;
	}

	.toggle-label::after {
		content: '';
		position: absolute;
		width: 24px;
		height: 24px;
		background: white;
		border-radius: 50%;
		top: 2px;
		left: 2px;
		transition: left 0.3s;
	}

	input[type="checkbox"]:checked + .toggle-label {
		background: #8E42E1;
	}

	input[type="checkbox"]:checked + .toggle-label::after {
		left: 24px;
	}

	/* Privacy Settings */
	.privacy-settings {
		display: flex;
		flex-direction: column;
		gap: 20px;
	}

	.privacy-item {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 16px;
		background: #f8f9fa;
		border-radius: 8px;
		gap: 20px;
	}

	.privacy-info h3 {
		font-size: 1rem;
		font-weight: 600;
		color: #1f2d3d;
		margin: 0 0 5px;
	}

	.privacy-info p {
		font-size: 0.85rem;
		color: #666;
		margin: 0;
	}

	.privacy-item select {
		padding: 8px 12px;
		border: 1px solid #ddd;
		border-radius: 6px;
		font-size: 0.9rem;
		cursor: pointer;
		min-width: 150px;
	}

	.divider {
		border: none;
		border-top: 1px solid #eee;
		margin: 20px 0;
	}

	.security-info,
	.danger-zone {
		padding: 20px;
		background: #f8f9fa;
		border-radius: 8px;
	}

	.security-info h3,
	.danger-zone h3 {
		font-size: 1rem;
		font-weight: 600;
		color: #1f2d3d;
		margin: 0 0 8px;
	}

	.security-info p,
	.danger-zone p {
		font-size: 0.9rem;
		color: #666;
		margin: 0 0 15px;
	}

	@media (max-width: 768px) {
		.settings-layout {
			grid-template-columns: 1fr;
		}

		.settings-sidebar {
			order: 2;
		}

		.settings-content {
			order: 1;
		}

		.settings-nav {
			flex-direction: row;
			flex-wrap: wrap;
		}

		.nav-item {
			flex: 1;
			min-width: 120px;
		}

		.notification-item,
		.privacy-item {
			flex-direction: column;
			align-items: flex-start;
		}

		.form-actions {
			flex-direction: column;
		}

		.btn {
			width: 100%;
		}
	}
</style>
