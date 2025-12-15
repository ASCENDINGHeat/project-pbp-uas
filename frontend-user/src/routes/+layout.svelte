<script lang="ts">
	import { page } from '$app/stores';
	import Header from '$lib/components/Header.svelte';
	import Footer from '$lib/components/Footer.svelte';
	import { onMount } from 'svelte';
    import { isLoggedIn } from '$lib/stores/auth';
    import { user } from '$lib/stores/user';
    import { cart } from '$lib/stores/cart';
    import { get } from 'svelte/store'; // [PENTING] Tambahkan import ini
    import { PUBLIC_API_URL, PUBLIC_STORAGE_URL } from '$env/static/public';

	$: showHeaderFooter = $page?.url?.pathname && $page.url.pathname !== '/';

    async function initGlobalData() {
        const token = localStorage.getItem('auth_token');
        if (!token) return;

        await Promise.allSettled([
            fetchUser(token),
            fetchCart(token)
        ]);
    }

	async function fetchUser(token: string) {
        // [SOLUSI UTAMA] Cek apakah data user sudah ada di store
        // Jika data user sudah ada (misal dari localStorage), return saja.
        // Ini mencegah fetch berulang-ulang setiap pindah halaman/refresh.
        if (get(user)) return; 

        try {
            const res = await fetch(`${PUBLIC_API_URL}/user`, {
                headers: { 
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json' 
                }
            });

            if (res.ok) {
                const result = await res.json();
                const userData = result.data || result;
                
                // Handle URL Avatar
                if (userData.avatar && !userData.avatar.startsWith('http')) {
                    const baseUrl = PUBLIC_STORAGE_URL.endsWith('/') ?
                        PUBLIC_STORAGE_URL : `${PUBLIC_STORAGE_URL}/`;
                    userData.avatar = `${baseUrl}storage/${userData.avatar}`;
                }

                // SIMPAN KE STORE 
                // (Ini akan otomatis men-trigger subscription di user.ts untuk update localStorage)
                user.set({
                    id: userData.id,
                    name: userData.name,
                    email: userData.email,
                    avatar: userData.avatar,
                    phone: userData.phone,
                    is_vendor: Boolean(userData.is_vendor) 
                });
            }
        } catch (e) {
            console.error("Gagal load user:", e);
        }
    }

    onMount(async () => {
        // Cek jika user login, langsung tarik data keranjang untuk inisialisasi
        if ($isLoggedIn) {
            initGlobalData();
        }
    });

    // ... sisa kode fetchCart dan lainnya tetap sama ...
    async function fetchCart(token: string) {
        try {
            // Note: Cart sebaiknya tetap di-fetch agar stok/harga selalu update
            const res = await fetch(`${PUBLIC_API_URL}/cart`, {
                headers: { 'Authorization': `Bearer ${token}` }
            });

            if (res.ok) {
                const data = await res.json();
                const mappedData = data.data.map((item: any) => ({
                    id: item.product_id,
                    name: item.product.name,
                    price: item.product.price,
                    image: item.product.image,
                    quantity: item.quantity,
                    stock: item.product.stock
                }));
                cart.set(mappedData);
            }
        } catch (e) {
            console.error("Gagal init keranjang:", e);
        }
    }
</script>

{#if showHeaderFooter}
    <Header />
{/if}

<main class="main-content">
	<slot />
</main>

{#if showHeaderFooter}
    <Footer />
{/if}

<style>
    /* Style tetap sama persis seperti sebelumnya */
	:global(*) {
		font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;
	}

	:global(html), :global(body) {
		margin: 0;
		padding: 0;
		width: 100%;
		min-height: 100vh;
		overflow-y: auto !important;
		overflow-x: hidden;
		font-family: 'Segoe UI', sans-serif;
		background-color: #ffffff;
		scroll-behavior: smooth;
		-webkit-font-smoothing: antialiased;
		-moz-osx-font-smoothing: grayscale;
	}

	.main-content {
		flex: 1;
		min-height: 60vh;
		width: 100%;
	}
</style>