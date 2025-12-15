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

    // Fungsi untuk menghapus dari wishlist (Toggle logic di backend)
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
                // Refresh list setelah menghapus
                await fetchWishlist();
            } else {
                alert("Gagal menghapus item.");
            }
        } catch (error) {
            console.error(error);
        }
    }

    // Fungsi helper format rupiah
    const formatIDR = (num: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num);
</script>

<svelte:head>
	<title>Wishlist - PC Store</title>
</svelte:head>

<Breadcrumb items={[
	{ label: 'Home', href: '/web' },
	{ label: 'Wishlist', active: true }
]} />

<main class="category-page">
    <div class="container">
        <header class="header-section">
            <h1>Wishlist</h1>
            <p class="subtitle">Produk yang ingin Anda beli di kemudian hari</p>
        </header>

        {#if isLoading}
            <div class="loading-state">Memuat Wishlist...</div>
        {:else if wishlistItems.length === 0}
            <section class="empty-state">
                <div class="empty-icon">❤️</div>
                <h2>Wishlist Anda Kosong</h2>
                <p>Belum ada produk yang ditambahkan ke wishlist.</p>
                <button class="btn-continue" on:click={() => goto('/web')}>Lihat Produk</button>
            </section>
        {:else}
            <section class="wishlist-grid">
                {#each wishlistItems as item}
                    {#if item.product}
                    <div class="wishlist-card">
                        <img src={item.product.image_url || '/images/placeholder.jpg'} alt={item.product.title} class="card-img" />
                        <div class="card-info">
                            <h3>{item.product.title}</h3>
                            <div class="price">{formatIDR(item.product.price)}</div>
                            <div class="stock-status">
                                {item.product.stock_quantity > 0 ? 'Stok Tersedia' : 'Stok Habis'}
                            </div>
                        </div>
                        <div class="card-actions">
                            <button class="btn-cart" on:click={() => goto(`/web/product/${item.product.id}`)}>Lihat Detail</button>
                            <button class="btn-remove" on:click={() => removeFromWishlist(item.product.id)}>Hapus</button>
                        </div>
                    </div>
                    {/if}
                {/each}
            </section>
        {/if}
    </div>
</main>

<style>
    .category-page { background: #f7f7f7; min-height: 100vh; }
    .container { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }

    .header-section { margin-bottom: 40px; text-align: center; }
    .header-section h1 { font-size: 2.5rem; font-weight: 800; color: #1f2d3d; margin: 0 0 12px; }
    .header-section p { font-size: 1.1rem; color: #64748b; margin: 0; }

    .empty-state {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        padding: 80px 20px; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        text-align: center;
    }
    .empty-icon { font-size: 4rem; margin-bottom: 20px; }
    .empty-state h2 { font-size: 1.8rem; font-weight: 800; color: #1f2d3d; margin: 0 0 12px; }
    .empty-state p { font-size: 1rem; color: #64748b; margin: 0 0 28px; max-width: 400px; }

    .btn-continue {
        padding: 12px 28px; background: linear-gradient(90deg, #7c3aed, #4f46e5); color: #fff;
        border: none; border-radius: 8px; font-weight: 700; cursor: pointer; transition: all 0.2s;
    }
    .btn-continue:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3); }

    /* Grid Layout untuk Wishlist Items */
    .wishlist-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
    
    .wishlist-card {
        background: #fff; border-radius: 12px; overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08); display: flex; flex-direction: column;
    }
    .card-img { width: 100%; height: 200px; object-fit: cover; }
    .card-info { padding: 16px; flex-grow: 1; }
    .card-info h3 { font-size: 1.1rem; margin: 0 0 8px; color: #1f2d3d; }
    .price { font-size: 1.2rem; font-weight: 700; color: #111; margin-bottom: 8px; }
    .stock-status { font-size: 0.9rem; color: #10b981; font-weight: 500; }
    
    .card-actions { padding: 16px; border-top: 1px solid #f1f5f9; display: flex; gap: 10px; }
    .btn-cart { flex: 1; background: #0f172a; color: #fff; border: none; padding: 10px; border-radius: 6px; cursor: pointer; font-weight: 600; }
    .btn-remove { background: #fee2e2; color: #ef4444; border: none; padding: 10px; border-radius: 6px; cursor: pointer; font-weight: 600; }

    .loading-state { text-align: center; font-size: 1.2rem; color: #64748b; padding: 50px; }
</style>