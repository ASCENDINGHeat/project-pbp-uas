<script lang="ts">
	import { goto } from '$app/navigation';
	import Breadcrumb from '$lib/components/Breadcrumb.svelte';
	import { onMount } from 'svelte';
	import { PUBLIC_API_URL } from '$env/static/public';
    import { isLoggedIn } from '$lib/stores/auth';

    let wishlistItems: any[] = [];
    let isLoading = true;

	onMount(async () => {
        if (!$isLoggedIn) {
            goto('/web/login');
            return;
        }
        await fetchWishlist();
    });

    async function fetchWishlist() {
        try {
            const token = localStorage.getItem('auth_token');
            const res = await fetch(`${PUBLIC_API_URL}/wishlist`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            if (res.ok) {
                const result = await res.json();
                wishlistItems = result.data || [];
            }
        } catch (error) {
            console.error('Error fetching wishlist:', error);
        } finally {
            isLoading = false;
        }
    }

    async function removeFromWishlist(productId: string) {
        if (!confirm("Hapus barang ini dari wishlist?")) return;
        try {
            const token = localStorage.getItem('auth_token');
            const res = await fetch(`${PUBLIC_API_URL}/wishlist/${productId}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            if (res.ok) {
                await fetchWishlist();
            } else {
                alert("Gagal menghapus item.");
            }
        } catch (error) {
            console.error(error);
        }
    }

    const formatIDR = (num: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num);
</script>

<svelte:head>
	<title>Wishlist - PC Store</title>
</svelte:head>

<Breadcrumb items={[
	{ label: 'Home', href: '/web' },
	{ label: 'Wishlist', active: true }
]} />

<main class="bg-[#f7f7f7] min-h-screen">
    <div class="max-w-7xl mx-auto py-10 px-5">
        <header class="mb-10 text-center">
            <h1 class="text-4xl font-extrabold text-slate-800 mb-3">Wishlist</h1>
            <p class="text-lg text-slate-500 m-0">Produk yang ingin Anda beli di kemudian hari</p>
        </header>

        {#if isLoading}
            <div class="text-center text-xl text-slate-500 py-12">Memuat Wishlist...</div>
        {:else if wishlistItems.length === 0}
            <section class="flex flex-col items-center justify-center py-20 bg-white rounded-xl shadow-sm text-center">
                <div class="text-6xl mb-5">❤️</div>
                <h2 class="text-3xl font-extrabold text-slate-800 mb-3">Wishlist Anda Kosong</h2>
                <p class="text-base text-slate-500 m-0 mb-7 max-w-[400px]">Belum ada produk yang ditambahkan ke wishlist.</p>
                <button class="px-7 py-3 bg-gradient-to-r from-violet-600 to-indigo-600 text-white border-none rounded-lg font-bold cursor-pointer transition-all hover:-translate-y-0.5 hover:shadow-lg hover:shadow-violet-600/30" on:click={() => goto('/web')}>Lihat Produk</button>
            </section>
        {:else}
            <section class="grid grid-cols-[repeat(auto-fill,minmax(280px,1fr))] gap-5">
                {#each wishlistItems as item}
                    {#if item.product}
                    <div class="bg-white rounded-xl overflow-hidden shadow-sm flex flex-col border border-slate-200 transition-hover duration-200 hover:shadow-md">
                        <img src={item.product.image_url || '/images/placeholder.jpg'} alt={item.product.title} class="w-full h-[200px] object-cover bg-slate-50" />
                        <div class="p-4 flex-grow flex flex-col gap-2">
                            <h3 class="text-lg font-semibold text-slate-800 m-0 leading-snug line-clamp-2">{item.product.title}</h3>
                            <div class="text-xl font-bold text-slate-900">{formatIDR(item.product.price)}</div>
                            <div class="text-sm font-medium {item.product.stock_quantity > 0 ? 'text-emerald-500' : 'text-red-500'}">
                                {item.product.stock_quantity > 0 ? 'Stok Tersedia' : 'Stok Habis'}
                            </div>
                        </div>
                        <div class="p-4 border-t border-slate-100 flex gap-2.5">
                            <button class="flex-1 bg-slate-900 text-white border-none p-2.5 rounded-md cursor-pointer font-semibold transition-colors hover:bg-slate-800" on:click={() => goto(`/web/product/${item.product.id}`)}>Lihat Detail</button>
                            <button class="bg-red-50 text-red-500 border border-red-100 p-2.5 rounded-md cursor-pointer font-semibold transition-colors hover:bg-red-100" on:click={() => removeFromWishlist(item.product.id)}>Hapus</button>
                        </div>
                    </div>
                    {/if}
                {/each}
            </section>
        {/if}
    </div>
</main>