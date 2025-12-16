<script lang="ts">
	import { goto } from '$app/navigation';
	import { onMount, onDestroy } from 'svelte';
	import { PUBLIC_API_URL } from '$env/static/public';
	import { user, isLoggedIn } from '$lib/stores/auth';

	let email = '';
	let password = '';
	let errorMessage = '';
	let isLoading = false;

	async function handleLogin() {
		if (!email || !password) {
			errorMessage = 'Harap isi email dan password.';
			return;
		}

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
				user.set(data.user);
				isLoggedIn.set(true);
				goto('/web');
			} else {
				errorMessage = data.message || 'Login gagal. Periksa kembali identitas anda';
			}
		} catch (error) {
			if (error instanceof Error) {
				errorMessage = error.message;
			} else {
				errorMessage = 'Terjadi kesalahan koneksi ke server.';
			}
		} finally {
			isLoading = false;
		}
	}

	onMount(() => {
		const prevOverflow = document.body.style.overflow;
		document.body.style.overflow = 'hidden';
		onDestroy(() => {
			document.body.style.overflow = prevOverflow || '';
		});
	});
</script>

<div class="fixed inset-0 bg-slate-900/45 backdrop-blur-sm flex items-center justify-center z-[9999] p-5 box-border">
	<div class="w-full max-w-[480px]">
		<div class="bg-white p-10 rounded-2xl w-full shadow-2xl relative text-center" role="dialog" aria-modal="true" aria-label="Login">
			<button class="absolute right-3 top-3 bg-transparent border-none text-2xl text-slate-500 cursor-pointer p-1.5 rounded-md hover:bg-slate-100 transition-colors" on:click={() => goto('/')} aria-label="Kembali">&times;</button>

			<h2 class="m-0 mb-2.5 text-slate-800 text-2xl font-bold">Selamat Datang Kembali! 👋</h2>
			<p class="mb-8 text-slate-500">Silakan masuk untuk mengakses area pengguna.</p>

			{#if errorMessage}
				<div class="bg-red-50 text-red-500 p-3 rounded-lg mb-5 text-sm border border-red-200">
					{errorMessage}
				</div>
			{/if}

			<form on:submit|preventDefault={handleLogin}>
				<div class="text-left mb-5">
					<label for="email" class="block mb-2 font-semibold text-sm text-slate-700">Email</label>
					<input type="email" id="email" bind:value={email} placeholder="Masukan email..." required class="w-full p-3 border border-slate-200 rounded-lg text-base box-border focus:outline-none focus:border-purple-600 focus:ring-1 focus:ring-purple-600 transition-all" />
				</div>

				<div class="text-left mb-5">
					<label for="pass" class="block mb-2 font-semibold text-sm text-slate-700">Password</label>
					<input type="password" id="pass" bind:value={password} placeholder="Masukan password..." required class="w-full p-3 border border-slate-200 rounded-lg text-base box-border focus:outline-none focus:border-purple-600 focus:ring-1 focus:ring-purple-600 transition-all" />
				</div>

				<button type="submit" class="w-full p-3.5 bg-gradient-to-r from-purple-600 to-indigo-600 text-white border-none rounded-lg text-base font-bold cursor-pointer mb-5 transition-transform hover:-translate-y-0.5 disabled:bg-gray-300 disabled:cursor-not-allowed disabled:transform-none" disabled={isLoading}>
					{isLoading ? 'Memproses...' : 'Masuk Sekarang'}
				</button>
			</form>

			<p class="text-sm mt-2.5 text-slate-500">
				Belum punya akun?
				<a href="/register" class="text-indigo-600 no-underline font-bold hover:underline" on:click|preventDefault={() => goto('/register')}>Daftar di sini</a>
			</p>
			<a class="block mt-5 text-slate-400 no-underline text-sm cursor-pointer hover:text-slate-600" on:click={() => goto('/')}>Kembali ke Menu Utama</a>
		</div>
	</div>
</div>