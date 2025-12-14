<script lang="ts">
	import { goto } from '$app/navigation';
	import { onMount, onDestroy } from 'svelte';
	import { PUBLIC_API_URL } from '$env/static/public';

	// Variabel untuk input form
	let email = '';
	let password = '';
	let errorMessage = '';
	let isLoading = false;

	// Fungsi Login Sederhana (Simulasi)
	async function handleLogin() {
		isLoading = true;
		errorMessage = '';

		try {
			const response = await fetch(`${PUBLIC_API_URL}/login`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'Accept': 'application/json'
				},
				body: JSON.stringify({ email, password })
			});

			const data = await response.json();

			if (response.ok) {
				localStorage.setItem('auth_token', data.access_token);
				localStorage.setItem('user_data', JSON.stringify(data.user));

				goto('/web');
			} else {
				errorMessage = data.message || 'Login gagal. Periksa kembali identitas anda';
			}
		} catch (error) {
			if (error instanceof Error) {
				alert(error.message);
			} else {
				// Jika error bukan object Error (misal string atau object lain)
				alert('Terjadi kesalahan yang tidak diketahui.');
			}
		} finally {
			isLoading = false;
		}
	}

	// --- new: prevent background scroll while modal is open ---
	onMount(() => {
		const prevOverflow = document.body.style.overflow;
		document.body.style.overflow = 'hidden';
		onDestroy(() => {
			document.body.style.overflow = prevOverflow || '';
		});
	});
	// --- end new ---
</script>

<!-- tampilkan sebagai overlay modal fullscreen sehingga "menyembunyikan" komponen di bawah -->
<div class="overlay">
	<div class="login-container">
		<div class="login-card" role="dialog" aria-modal="true" aria-label="Login">
			<button class="close-btn" on:click={() => goto('/')} aria-label="Kembali">&times;</button>

			<h2>Selamat Datang Kembali! 👋</h2>
			<p>Silakan masuk untuk mengakses area pengguna.</p>

			<div class="input-group">
				<label for="email">Email</label>
				<input type="email" id="email" bind:value={email} placeholder="Masukan email..." />
			</div>

			<div class="input-group">
				<label for="pass">Password</label>
				<input type="password" id="pass" bind:value={password} placeholder="Masukan password..." />
			</div>

			<button on:click={handleLogin} class="btn-login">Masuk Sekarang</button>

			<!-- ubah link agar memakai goto (SPA) -->
			<p class="footer-text">
				Belum punya akun? <a href="/register" on:click|preventDefault={() => goto('/register')}
					>Daftar di sini</a
				>
			</p>
			<a class="back-link" on:click={() => goto('/')}>Kembali ke Menu Utama</a>
		</div>
	</div>
</div>

<style>
	/* filepath: d:\Kuliah\PBP\project-pbp-uas\frontend-user\src\routes\login\+page.svelte */
	:global(body) {
		margin: 0;
		font-family: 'Segoe UI', sans-serif;
	}

	/* overlay menutupi seluruh layar (menyembunyikan komponen lain) */
	.overlay {
		position: fixed;
		inset: 0;
		background: rgba(15, 15, 20, 0.45);
		backdrop-filter: blur(4px);
		display: flex;
		align-items: center;
		justify-content: center;
		z-index: 9999;
		padding: 20px;
		box-sizing: border-box;
	}

	.login-container {
		width: 100%;
		max-width: 480px;
	}

	.login-card {
		background: white;
		padding: 40px;
		border-radius: 16px;
		width: 100%;
		box-shadow: 0 10px 35px rgba(0, 0, 0, 0.15);
		position: relative;
		text-align: center;
	}

	.close-btn {
		position: absolute;
		right: 12px;
		top: 12px;
		background: transparent;
		border: none;
		font-size: 1.6rem;
		color: #666;
		cursor: pointer;
		padding: 6px;
		border-radius: 6px;
	}
	.close-btn:hover {
		background: rgba(0, 0, 0, 0.04);
	}

	h2 {
		margin: 0 0 10px;
		color: #333;
	}
	p {
		margin-bottom: 30px;
		color: #666;
	}

	.input-group {
		text-align: left;
		margin-bottom: 20px;
	}
	label {
		display: block;
		margin-bottom: 8px;
		font-weight: 600;
		font-size: 0.9rem;
		color: #444;
	}
	input {
		width: 100%;
		padding: 12px;
		border: 1px solid #ddd;
		border-radius: 8px;
		box-sizing: border-box;
		font-size: 1rem;
	}
	input:focus {
		outline: none;
		border-color: #8e2de2;
	}

	.btn-login {
		width: 100%;
		padding: 14px;
		background: linear-gradient(to right, #8e2de2, #4a00e0);
		color: white;
		border: none;
		border-radius: 8px;
		font-size: 1rem;
		font-weight: bold;
		cursor: pointer;
		margin-bottom: 20px;
		transition: 0.2s;
	}
	.btn-login:hover {
		transform: translateY(-2px);
	}

	.footer-text {
		font-size: 0.9rem;
		margin-top: 10px;
	}
	.footer-text a {
		color: #4a00e0;
		text-decoration: none;
		font-weight: bold;
	}

	.back-link {
		display: block;
		margin-top: 20px;
		color: #999;
		text-decoration: none;
		font-size: 0.85rem;
		cursor: pointer;
	}
</style>
