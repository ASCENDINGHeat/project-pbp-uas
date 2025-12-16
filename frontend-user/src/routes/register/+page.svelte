<script lang="ts">
    import { goto } from '$app/navigation';
    import { onMount, onDestroy } from 'svelte';
    import { PUBLIC_API_URL } from '$env/static/public';
    import { user, isLoggedIn } from '$lib/stores/auth';

    let name = "";
    let email = "";
    let password = "";
    let confirmPassword = "";
    let phoneNumber = "";
    let address = "";
    let isLoading = false;

    const API_URL = `${PUBLIC_API_URL}/register`;

    async function handleRegister() {
        if (!name || !email || !password || !confirmPassword || !phoneNumber || !address) {
            alert("Harap isi semua kolom!");
            return;
        }

        if (password !== confirmPassword) {
            alert("Password dan Konfirmasi Password tidak cocok!");
            return;
        }

        isLoading = true;
        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    name: name,
                    email: email,
                    password: password,
                    password_confirmation: confirmPassword,
                    phone_number: phoneNumber,
                    address: address
                })
            });
            const data = await response.json();

            if (!response.ok) {
                if (data.errors) {
                    const errorMessages = Object.values(data.errors).flat().join('\n');
                    throw new Error(errorMessages);
                }
                throw new Error(data.message || 'Gagal mendaftar');
            } else {
                localStorage.setItem('auth_token', data.access_token);
                localStorage.setItem('user_data', JSON.stringify(data.user));
                user.set(data.user);
                isLoggedIn.set(true);
                alert("Registrasi berhasil!");
                goto('/web');
            }
        } catch (error) {
            if (error instanceof Error) {
                alert(error.message);
            } else {
                alert('Terjadi kesalahan yang tidak diketahui.');
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

<div class="fixed inset-0 bg-slate-900/45 backdrop-blur-sm flex items-center justify-center z-[9999] p-5 box-border overflow-y-auto">
    <div class="w-full max-w-[500px] my-auto">
        <div class="bg-white p-10 rounded-2xl shadow-2xl relative text-left" role="dialog" aria-modal="true" aria-label="Daftar">
            <button class="absolute right-3 top-3 bg-transparent border-none text-2xl text-slate-500 cursor-pointer p-1.5 rounded-md hover:bg-slate-100 transition-colors" on:click={() => goto('/') } aria-label="Tutup">&times;</button>

            <h1 class="text-3xl font-bold text-slate-800 m-0 mb-6 text-center">Daftar Akun</h1>
            
            <form on:submit|preventDefault={handleRegister} class="flex flex-col gap-4">
                <div>
                    <label for="name" class="block mb-2 font-semibold text-sm text-slate-700">Nama Lengkap</label>
                    <input type="text" id="name" bind:value={name} placeholder="John Doe" required class="w-full p-3 border border-slate-200 rounded-lg text-base box-border focus:outline-none focus:border-purple-600 focus:ring-1 focus:ring-purple-600" />
                </div>
                <div>
                    <label for="email" class="block mb-2 font-semibold text-sm text-slate-700">Email</label>
                    <input type="email" id="email" bind:value={email} placeholder="your@email.com" required class="w-full p-3 border border-slate-200 rounded-lg text-base box-border focus:outline-none focus:border-purple-600 focus:ring-1 focus:ring-purple-600" />
                </div>
                <div>
                    <label for="phone" class="block mb-2 font-semibold text-sm text-slate-700">Nomor HP</label>
                    <input type="text" id="phone" bind:value={phoneNumber} placeholder="Contoh: 081234567890" required class="w-full p-3 border border-slate-200 rounded-lg text-base box-border focus:outline-none focus:border-purple-600 focus:ring-1 focus:ring-purple-600" />
                </div>

                <div>
                    <label for="address" class="block mb-2 font-semibold text-sm text-slate-700">Alamat Lengkap</label>
                    <input type="text" id="address" bind:value={address} placeholder="Masukkan alamat lengkap" required class="w-full p-3 border border-slate-200 rounded-lg text-base box-border focus:outline-none focus:border-purple-600 focus:ring-1 focus:ring-purple-600" />
                </div>
                <div>
                    <label for="password" class="block mb-2 font-semibold text-sm text-slate-700">Password</label>
                    <input type="password" id="password" bind:value={password} placeholder="••••••••" required class="w-full p-3 border border-slate-200 rounded-lg text-base box-border focus:outline-none focus:border-purple-600 focus:ring-1 focus:ring-purple-600" />
                </div>
                <div>
                    <label for="confirm" class="block mb-2 font-semibold text-sm text-slate-700">Konfirmasi Password</label>
                    <input type="password" id="confirm" bind:value={confirmPassword} placeholder="••••••••" required class="w-full p-3 border border-slate-200 rounded-lg text-base box-border focus:outline-none focus:border-purple-600 focus:ring-1 focus:ring-purple-600" />
                </div>
                
                <button type="submit" class="w-full p-3.5 bg-purple-600 text-white border-none rounded-lg font-bold text-base cursor-pointer mt-2 transition-colors hover:bg-purple-700 disabled:bg-gray-300 disabled:cursor-not-allowed" disabled={isLoading}>
                    {isLoading ? 'Memproses...' : 'Daftar Sekarang'}
                </button>
            </form>

            <p class="text-center mt-5 text-slate-500 text-sm">Sudah punya akun? <a href="/login" class="text-purple-600 no-underline font-semibold hover:underline" on:click|preventDefault={() => goto('/login')}>Login di sini</a></p>
            <a class="block mt-4 text-slate-400 no-underline text-sm cursor-pointer text-center hover:text-slate-600" on:click={() => goto('/') }>Kembali ke Menu Utama</a>
        </div>
    </div>
</div>