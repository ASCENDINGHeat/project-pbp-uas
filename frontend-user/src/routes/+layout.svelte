<script lang="ts">
	import { page } from '$app/stores';
	import Header from '$lib/components/Header.svelte';
	import Footer from '$lib/components/Footer.svelte';

	$: showHeaderFooter = $page?.url?.pathname && $page.url.pathname !== '/';
	import { onMount } from 'svelte';
    import { isLoggedIn } from '$lib/stores/auth';
	import { user } from '$lib/stores/user';
    import { cart } from '$lib/stores/cart'; // Store global
    import { PUBLIC_API_URL,PUBLIC_STORAGE_URL } from '$env/static/public';

	async function initGlobalData() {
        const token = localStorage.getItem('auth_token');
        if (!token) return;

        // Kita bisa jalankan fetch User dan Cart secara paralel agar lebih cepat
        // Promise.allSettled memastikan jika satu gagal, yang lain tetap jalan
        await Promise.allSettled([
            fetchUser(token),
            fetchCart(token)
        ]);
    }
	async function fetchUser(token: string) {
        // Jika data user sudah ada, skip fetch (optional)
        // if ($user) return; 

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
                    const baseUrl = PUBLIC_STORAGE_URL.endsWith('/') ? PUBLIC_STORAGE_URL : `${PUBLIC_STORAGE_URL}/`;
                    userData.avatar = `${baseUrl}storage/${userData.avatar}`;
                }

                // SIMPAN KE STORE
                user.set({
                    id: userData.id,
                    name: userData.name,
                    email: userData.email,
                    avatar: userData.avatar,
                    phone: userData.phone,
                    // Pastikan dikonversi jadi boolean jika DB mengembalikan 0/1
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

    async function fetchCart(token: string) {
        try {
            const token = localStorage.getItem('auth_token');
            if(!token) return;

            const res = await fetch(`${PUBLIC_API_URL}/cart`, {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            
            if (res.ok) {
                const data = await res.json();
                // Mapping data sesuai struktur store
                const mappedData = data.data.map(item => ({
                    id: item.product_id,
                    name: item.product.name,
                    price: item.product.price,
                    image: item.product.image, // Ingat handle URL gambar disini
                    quantity: item.quantity,
                    stock: item.product.stock
                }));
                
                // Isi store cart di awal!
                cart.set(mappedData);
            }
        } catch (e) {
            console.error("Gagal init keranjang:", e);
        }
    }
</script>

{#if showHeaderFooter}
    <!-- keep Header; it has its own white drawer -->
    <Header />
{/if}

<main class="main-content">
	<slot />
</main>

{#if showHeaderFooter}
    <Footer />
{/if}

<style>
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
