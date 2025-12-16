<script lang="ts">
    import { page } from '$app/stores';
    import { goto } from '$app/navigation';
    import { PUBLIC_API_URL } from '$env/static/public';
    import ProductCard from '$lib/components/ProductCard.svelte';

    // --- LOGIC BARU: Mengambil Data dari API ---
    let categoryProducts: any[] = [];
    let loading = true;

    $: category = $page.params.category;
    // Get search term from URL query parameter
    $: search = $page.url.searchParams.get('search') || '';

    // Trigger fetch whenever category or search changes
    $: fetchProducts(category, search);

    async function fetchProducts(cat: string, searchTerm: string) {
        loading = true;
        try {
            // Build Query URL
            let url = `${PUBLIC_API_URL}/product?per_page=20`;
            // Filter by Category
            if (cat && cat !== 'all') {
                url += `&category=${cat}`;
            }

            // Filter by Search
            if (searchTerm) {
                url += `&search=${encodeURIComponent(searchTerm)}`;
            }

            const res = await fetch(url);
            if (res.ok) {
                const data = await res.json();
                // FIX: Map the API data to include the 'image' property expected by ProductCard
                const rawData = data.data || [];
                categoryProducts = rawData.map((item: any) => ({
                    ...item,
                    image: item.image_url || '/images/placeholder.png' // Map image_url to image
                }));
            } else {
                console.error('Failed to fetch products');
                categoryProducts = [];
            }
        } catch (error) {
            console.error('Error:', error);
            categoryProducts = [];
        } finally {
            loading = false;
        }
    }

    // Category names mapping
    const categoryNames: Record<string, string> = {
        all: 'Semua Kategori',
        processor: 'Processor',
        motherboard: 'Motherboard',
        vga: 'Kartu Grafis',
        ram: 'RAM',
        storage: 'Storage',
        casing: 'Casing',
        psu: 'Power Supply',
        monitor: 'Monitor',
        cooler: 'CPU Cooler (HSF)'
    };

    $: categoryName = categoryNames[category] || labelize(category);

    // Update title dynamic if searching
    $: displayTitle = search ? `Hasil Pencarian: "${search}"` : categoryName;

    function labelize(seg: string) {
        return decodeURIComponent(seg).replace(/[-_]/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
    }

    function buildBreadcrumbs(pathname: string) {
        const crumbs = [{ label: 'Home', path: '/web' }];
        crumbs.push({ label: 'All Categories', path: '/web/categories/all' });
        
        const match = pathname.match(/\/categories\/([^/]+)/);
        if (match) {
            const catParam = match[1];
            if (catParam !== 'all') {
                crumbs.push({ 
                    label: categoryNames[catParam] || labelize(catParam), 
                    path: `/web/categories/${catParam}` 
                });
            }
        }
        return crumbs;
    }

    let breadcrumbs: { label: string; path: string }[] = [];
    $: breadcrumbs = buildBreadcrumbs($page.url.pathname);

    // --- DATA KATEGORI (Static Menu Icons) ---
    const categoriesList = [
        { name: 'Processor', slug: 'processor', icon: '/images/icons/cpu.png' },
        { name: 'Motherboard', slug: 'motherboard', icon: '/images/icons/mobo.png' },
        { name: 'Kartu Grafis', slug: 'vga', icon: '/images/icons/gpu.png' },
        { name: 'RAM', slug: 'ram', icon: '/images/icons/ssd.png' },
        { name: 'Storage', slug: 'storage', icon: '/images/icons/ssd.png' },
        { name: 'Casing', slug: 'casing', icon: '/images/icons/case.png' },
        { name: 'Power Supply', slug: 'psu', icon: '/images/icons/psu.png' },
        { name: 'Monitor', slug: 'monitor', icon: '/images/icons/monitor.png' },
        { name: 'CPU Cooler (HSF)', slug: 'cooler', icon: '/images/icons/cooler.png' }
    ];

    function handleIconError(e: Event) {
        const target = e.target as HTMLImageElement;
        target.style.display = 'none';
    }

    function openProduct(id: string) {
        goto(`/web/product/${id}`);
    }
</script>

<div class="w-full pt-5 pb-2.5 relative z-10">
    <div class="max-w-[95%] mx-auto px-5">
        <div class="inline-flex items-center gap-2.5 bg-slate-900 rounded-full px-6 py-2.5 text-sm font-medium shadow-sm border border-white/5">
            {#each breadcrumbs as crumb, i}
                {#if i < breadcrumbs.length - 1}
                    <a href={crumb.path} class="text-slate-400 no-underline cursor-pointer transition-colors duration-200 hover:text-white">{crumb.label}</a>
                    <span class="text-slate-600 text-xs">›</span>
                {:else}
                    <span class="text-[#ff0055] font-bold">{crumb.label}</span>
                {/if}
            {/each}
        </div>
    </div>
</div>

<main class="bg-[#f7f7f7] min-h-screen font-sans">
    <div class="max-w-[95%] mx-auto py-10 px-5">
        <header class="mb-10 text-center">
            <h1 class="text-4xl font-extrabold text-slate-800 mb-3">{displayTitle}</h1>
            <p class="text-lg text-slate-500 m-0">
                {#if search}
                    Menampilkan hasil pencarian untuk "{search}"
                {:else if category === 'all'}
                    Jelajahi berbagai kategori produk terbaik kami
                {:else}
                    Produk berkualitas untuk kebutuhan Anda
                {/if}
            </p>
        </header>

        {#if category === 'all' && !search}
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 w-full">
                {#each categoriesList as cat}
                    <a href="/web/categories/{cat.slug}" class="group flex items-center px-5 py-[18px] rounded-xl no-underline transition-all duration-200 border border-white/10 overflow-hidden relative bg-gradient-to-br from-purple-500 to-fuchsia-500 hover:-translate-y-1 hover:shadow-lg hover:shadow-purple-500/40 hover:brightness-110">
                        <div class="w-[42px] h-[42px] rounded-full bg-white/20 flex items-center justify-center mr-4 shrink-0 backdrop-blur-sm">
                            <img src={cat.icon} alt={cat.name} class="w-6 h-6 object-contain invert brightness-0" on:error={handleIconError} />
                        </div>
                        <span class="text-white font-semibold text-base tracking-wide shadow-sm">{cat.name}</span>
                    </a>
                {/each}
            </div>
        {/if}

        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-5 mt-5 max-sm:gap-2.5">
            {#if loading}
                <div class="col-span-full text-center p-10 text-gray-500">
                    Memuat produk...
                </div>
            {:else if categoryProducts.length > 0}
                {#each categoryProducts as product}
                    <ProductCard {product} />
                {/each}
            {:else}
                <div class="col-span-full text-center p-10 text-gray-400">
                    Tidak ada produk ditemukan.
                </div>
            {/if}
        </div>
    </div>
</main>