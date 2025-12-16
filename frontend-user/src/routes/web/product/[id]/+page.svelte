<script lang="ts">
    import type { PageData } from './$types';
    import { goto } from '$app/navigation';
    import { addToCart as addToLocalCart } from '$lib/stores/cart';
    import { PUBLIC_API_URL } from '$env/static/public';
    import { onMount } from 'svelte';
    import { isLoggedIn } from '$lib/stores/auth';

    export let data: PageData;
    $: ({ product } = data);
    
    let quantity = 1;
    let isWishlisted = false;
    let isLoadingCart = false;
    let isLoadingWishlist = false;

    $: categorySlug = product ? 
        product.category.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9\-]/g, '') : '';

    onMount(async () => {
        if ($isLoggedIn) {
            await checkWishlistStatus();
        }
    });

    async function checkWishlistStatus() {
        try {
            const token = localStorage.getItem('auth_token');
            if (!token) return;

            const res = await fetch(`${PUBLIC_API_URL}/wishlist`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            if (res.ok) {
                const responseData = await res.json();
                const wishItems = responseData.data || [];
                isWishlisted = wishItems.some((item: any) => String(item.product_id) === String(product.id));
            }
        } catch (error) {
            console.error("Gagal memuat status wishlist:", error);
        }
    }

    async function toggleWishlist() {
        if (!$isLoggedIn) {
            alert("Silakan login terlebih dahulu untuk menyimpan ke wishlist.");
            goto('/login');
            return;
        }

        if (isLoadingWishlist) return;
        isLoadingWishlist = true;
        try {
            const token = localStorage.getItem('auth_token');
            const res = await fetch(`${PUBLIC_API_URL}/wishlist/${product.id}`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });

            const result = await res.json();
            if (res.ok) {
                if (result.status === 'added') {
                    isWishlisted = true;
                } else if (result.status === 'removed') {
                    isWishlisted = false;
                } else {
                    isWishlisted = !isWishlisted;
                }
                goto('/web/wishlist')
            } else {
                alert(result.message || "Gagal mengubah wishlist.");
            }
        } catch (error) {
            console.error("Error wishlist:", error);
            alert("Terjadi kesalahan koneksi.");
        } finally {
            isLoadingWishlist = false;
        }
    }

    async function addToCartBackend() {
        if (!$isLoggedIn) {
            if (confirm("Anda perlu login untuk berbelanja. Ke halaman login?")) {
               goto('/login');
            }
            return;
        }

        if (isLoadingCart) return;
        isLoadingCart = true;
        try {
            const token = localStorage.getItem('auth_token');
            const res = await fetch(`${PUBLIC_API_URL}/cart/${product.id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    quantity: quantity
                })
            });
            const result = await res.json();

            if (res.ok) {
                addToLocalCart({
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    image: product.image,
                    quantity
                });
                goto('/web/cart');
            } else {
                alert(result.message || "Gagal menambahkan ke keranjang.");
            }
        } catch (error) {
            console.error("Error add to cart:", error);
            alert("Terjadi kesalahan saat menghubungi server.");
        } finally {
            isLoadingCart = false;
        }
    }

    function goToCategory() {
        goto(`/web/categories/${categorySlug}`);
    }

    const formatIDR = (num: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num);
    function increaseQty() { 
        if (quantity < product.stock) quantity++;
    }
    
    function decreaseQty() { 
        if (quantity > 1) quantity--;
    }

    function goBack() {
        const currentPath = location.pathname + location.search;
        let refPath: string | null = null;
        if (document.referrer) {
            try {
                const u = new URL(document.referrer);
                refPath = u.pathname + u.search;
            } catch { refPath = null; }
        }

        if (history.length > 1) {
            history.back();
            setTimeout(() => {
                const now = location.pathname + location.search;
                if (now === currentPath) {
                    if (refPath) goto(refPath);
                    else goto('/web');
                }
            }, 220);
            return;
        }

        if (refPath) goto(refPath);
        else goto('/web');
    }

    function capitalizeFirstLetter(str: string) {
        if (!str) return str;
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
</script>

<svelte:head>
    <title>{product?.name || 'Loading...'} | Store</title>
</svelte:head>

{#if product}
<div class="bg-slate-50 min-h-screen px-5 py-5 pb-10 box-border">
    
    <div class="max-w-7xl mx-auto flex justify-start pb-4">
        <button class="inline-flex items-center gap-2.5 m-0 px-4 py-2 bg-[#111] text-red-500 border-none rounded-full font-bold text-sm cursor-pointer transition-all shadow-md hover:bg-black hover:-translate-y-0.5 hover:shadow-lg" on:click={goBack} aria-label="Kembali">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span>Kembali</span>
        </button>
    </div>

    <main class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-[1.2fr_1fr] gap-10 items-start">
        
        <section class="flex flex-col">
            <div class="bg-white rounded-3xl overflow-hidden border border-slate-200 aspect-square flex items-center justify-center">
                <img src={product.image} alt={product.name} class="w-full h-full object-cover" />
            </div>
        </section>

        <div class="flex flex-col gap-6 sticky top-5 max-lg:static">
            <section class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <div class="inline-flex items-center gap-2 bg-blue-50 text-blue-800 border border-blue-100 text-sm font-bold px-3 py-1.5 rounded-full mb-4 w-fit">
                    <span class="text-base">🏪</span>
                    <span>{product.shop_name}</span>
                </div>

                <h1 class="m-0 mb-3 text-3xl leading-snug font-bold text-slate-900 tracking-tight">{product.name}</h1>
                
                <div class="flex items-center gap-2 text-slate-500 mb-5">
                    <span class="text-amber-400">★★★★★</span>
                    <span class="font-semibold text-slate-700">{product.rating}</span>
                    <span class="text-slate-300">|</span>
                    <span class="font-medium">Terjual {product.sold}+</span>
                </div>

                <div class="text-4xl font-extrabold text-slate-900">
                    {formatIDR(product.price)}
                </div>

                <div class="bg-gradient-to-b from-[#7B4BFF] to-[#8E42E1] text-white rounded-xl p-5 mt-5 border border-purple-500/20 shadow-xl shadow-purple-500/10">
                    <h3 class="m-0 mb-4 text-lg font-bold text-white">Informasi Produk</h3>
                    <div class="flex justify-between mb-3 text-sm last:mb-0">
                        <span class="text-white/80 font-medium">Kategori:</span>
                        <a href={`/web/categories/${categorySlug}`} class="text-white font-bold no-underline cursor-pointer hover:text-white hover:opacity-100" on:click|preventDefault={goToCategory}>
                            {capitalizeFirstLetter(product.category)}
                        </a>
                    </div>
                    <div class="flex justify-between mb-0 text-sm last:mb-0">
                        <span class="text-white/80 font-medium">Status:</span>
                        <span class="text-white font-bold">{product.status}</span>
                    </div>
                </div>
            </section>

            <section class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                <div class="flex items-center gap-4 mb-5">
                    <span class="font-semibold text-slate-700">Atur Jumlah:</span>
                    <div class="flex border border-slate-300 rounded-lg overflow-hidden">
                        <button class="w-9 h-9 bg-white border-none cursor-pointer text-lg hover:bg-slate-50" on:click={decreaseQty} disabled={quantity <= 1}>−</button>
                        <input type="text" readonly value={quantity} class="w-10 text-center border-l border-r border-slate-300 outline-none font-bold text-slate-900" />
                        <button class="w-9 h-9 bg-white border-none cursor-pointer text-lg hover:bg-slate-50" on:click={increaseQty} disabled={quantity >= product.stock}>+</button>
                    </div>
                    <span class="text-sm text-emerald-500 font-medium">Sisa {product.stock} pcs</span>
                </div>

                <div class="flex justify-between mb-5 pt-4 border-t border-dashed border-slate-200 font-bold text-slate-800">
                    <span>Subtotal:</span>
                    <strong>{formatIDR(product.price * quantity)}</strong>
                </div>

                <div class="flex gap-3">
                    <button 
                        class="flex-1 p-3.5 rounded-xl font-semibold cursor-pointer flex justify-center gap-2 border-none {isWishlisted ? 'border-2 border-red-500 bg-white text-red-500' : 'border-2 border-purple-500/10 bg-white text-slate-700 hover:bg-slate-50'}" 
                        on:click={toggleWishlist}
                        disabled={isLoadingWishlist}
                    >
                        <span>{isWishlisted ? '♥' : '♡'}</span> 
                        {isWishlisted ? 'Disimpan' : 'Wishlist'}
                    </button>

                    <button 
                        class="flex-1 p-3.5 rounded-xl font-semibold cursor-pointer flex justify-center gap-2 border-none bg-gradient-to-br from-[#8E42E1] to-[#6B3BFF] text-white shadow-lg shadow-purple-600/20 hover:to-[#5a30e0] disabled:bg-slate-300 disabled:cursor-not-allowed disabled:shadow-none disabled:from-slate-300 disabled:to-slate-300" 
                        on:click={addToCartBackend}
                        disabled={isLoadingCart || product.stock <= 0}
                    >
                        {isLoadingCart ? 'Memproses...' : '+ Keranjang'}
                    </button>
                </div>

            </section>
        </div> 
        
        <section class="col-span-full bg-white rounded-3xl p-10 border border-slate-200 mt-5 max-md:p-6">
            <div class="flex gap-8 border-b border-slate-200 mb-6">
                <button class="bg-none border-none pb-4 cursor-pointer text-blue-500 font-bold border-b-[3px] border-blue-500">Deskripsi Produk</button>
                <button class="bg-none border-none pb-4 cursor-pointer text-slate-500 font-medium hover:text-slate-700">Spesifikasi</button>
            </div>
            <div class="text-slate-700 leading-relaxed whitespace-pre-line">
                <p>{product.description}</p>
            </div>
        </section>

    </main>
</div>
{:else}
    <div class="text-center p-12 text-slate-500 text-xl font-medium">Loading Product...</div>
{/if}