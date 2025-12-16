<script lang="ts">
    import { onMount } from 'svelte';
    import { fly, slide } from 'svelte/transition';
    import { goto } from '$app/navigation';
    import { cart } from '$lib/stores/cart';
    import { PUBLIC_API_URL } from '$env/static/public';

    // --- Types ---
    type CategoryKey = 'processor' | 'motherboard' | 'cooler' | 'ram' | 'storage' |
        'vga' | 'psu' | 'casing' | 'monitor';

    interface Product {
        id: string;
        category: string;
        brand: string;
        name: string;
        price: number;
        specs: string; 
        imagePlaceholder: string;
    }

    interface CategoryDef {
        id: CategoryKey;
        group: 'Utama' | 'Tambahan';
        label: string;
        subLabel: string;
        iconPath: string; 
        options: Product[];
    }

    // --- State Management ---
    let products: Product[] = [];
    let isLoading = true;
    let isAddingToCart = false; // New State for API loading
    
    let activeCategory: CategoryKey = 'processor';
    let brandFilter: string = 'ALL'; 
    let sortOrder: 'price-asc' | 'price-desc' = 'price-asc'; 
    let searchQuery = '';
    let showSummary = false;
    let selectionMap: Record<CategoryKey, Product | null> = {
        processor: null, motherboard: null, cooler: null, ram: null, storage: null, vga: null, psu: null, casing: null, monitor: null
    };

    // --- API INTEGRATION ---
    onMount(async () => {
        await fetchAllProducts();
    });

    async function fetchAllProducts() {
        try {
            const res = await fetch(`${PUBLIC_API_URL}/product?per_page=100`);
            if (res.ok) {
                const data = await res.json();
                const rawProducts = data.data || [];

                products = rawProducts.map((p: any) => {
                    const details = p.details || {};
                    let brand = 'Generic';
                    const titleUpper = p.title.toUpperCase();
                    if (titleUpper.includes('AMD')) brand = 'AMD';
                    else if (titleUpper.includes('INTEL')) brand = 'INTEL';
                    else if (titleUpper.includes('NVIDIA')) brand = 'NVIDIA';
                    else if (titleUpper.includes('ASUS')) brand = 'ASUS';
                    else if (titleUpper.includes('MSI')) brand = 'MSI';
                    else if (titleUpper.includes('GIGABYTE')) brand = 'GIGABYTE';

                    return {
                        id: String(p.id),
                        name: p.title,
                        price: Number(p.price),
                        category: details.category || 'unknown',
                        specs: details.specs || '',
                        brand: brand,
                        imagePlaceholder: p.image_url || '/images/placeholder.png'
                    };
                });
            }
        } catch (e) {
            console.error("Error loading simulation data:", e);
        } finally {
            isLoading = false;
        }
    }

    // --- Hardware Definitions ---
    $: hardwareData = [
        {
            id: 'processor', group: 'Utama', label: 'Processor', subLabel: 'CPU untuk sistem', 
            iconPath: 'M6 4h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2zm3 5a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm0 4a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm6-4a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm0 4a1 1 0 1 0 0 2 1 1 0 0 0 0-2z',
            options: products.filter(p => p.category === 'processor')
        },
        { 
            id: 'motherboard', group: 'Utama', label: 'Mainboard', subLabel: 'Motherboard', 
            iconPath: 'M20 6h-2.18c.11-.31.18-.65.18-1a3 3 0 0 0-3-3c-.35 0-.69.07-1 .18V2a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v.18c-.31-.11-.65-.18-1-.18a3 3 0 0 0-3 3c0 .35.07.69.18 1H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h2.18c-.11.31-.18.65-.18 1a3 3 0 0 0 3 3c.35 0 .69-.07 1-.18V22a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2v-.18c.31.11.65.18 1 .18a3 3 0 0 0 3-3c0-.35-.07-.69-.18-1H22a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2z',
            options: products.filter(p => p.category === 'motherboard')
        },
        { id: 'cooler', group: 'Utama', label: 'CPU Cooler', subLabel: 'Pendingin processor', 
          iconPath: 'M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8z M12 6a1 1 0 0 0-1 1v4H7a1 1 0 0 0 0 2h4v4a1 1 0 0 0 2 0v-4h4a1 1 0 0 0 0-2h-4V7a1 1 0 0 0-1-1z', 
          options: products.filter(p => p.category === 'cooler') 
        },
        { id: 'ram', group: 'Utama', label: 'RAM', subLabel: 'Memory komputer', 
          iconPath: 'M2 6h20v4H2z M4 6v4 M8 6v4 M12 6v4 M16 6v4 M20 6v4 M2 14h20v4H2z', 
          options: products.filter(p => p.category === 'ram') 
        },
        { id: 'storage', group: 'Utama', label: 'Storage', subLabel: 'SSD/HDD', 
          iconPath: 'M4 4h16v16H4z M10 16h4 M12 8v4', 
          options: products.filter(p => p.category === 'storage') 
        },
        { id: 'vga', group: 'Utama', label: 'VGA', subLabel: 'Graphics card', 
          iconPath: 'M2 5h20v14H2z M6 10a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm12 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4z', 
          options: products.filter(p => p.category === 'vga') 
        },
        { id: 'psu', group: 'Utama', label: 'PSU', subLabel: 'Power Supply', 
          iconPath: 'M4 4h16v16H4z M8 15l3-6h2l3 6 M12 11v-2', 
          options: products.filter(p => p.category === 'psu') 
        },
        { id: 'casing', group: 'Utama', label: 'Casing', subLabel: 'PC Case', 
          iconPath: 'M6 2h12v20H6z M14 6h2 M14 10h2', 
          options: products.filter(p => p.category === 'casing') 
        },
        { id: 'monitor', group: 'Tambahan', label: 'Monitor', subLabel: 'Display Screen', 
          iconPath: 'M2 4h20v12H2z M8 20h8 M12 16v4', 
          options: products.filter(p => p.category === 'monitor') 
        }
    ] as CategoryDef[];

    // --- Logic & Helper Functions ---
    const formatRupiah = (num: number) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num);
    };

    $: currentCategoryData = hardwareData.find(cat => cat.id === activeCategory);

    $: availableBrands = (() => {
        const extracted = (currentCategoryData?.options || [])
            .map(p => (p.brand || '').trim())
            .filter(b => b.length > 0 && b !== 'Generic');
        return [...new Set(extracted)].sort();
    })();

    // --- LOGIC FILTERING & SORTING ---
    $: filteredProducts = (currentCategoryData?.options || [])
        .filter(p => {
            const matchSearch = p.name.toLowerCase().includes(searchQuery.toLowerCase());
            let matchBrand = true;
            if (brandFilter !== 'ALL') {
                const targetBrand = brandFilter.toUpperCase().trim();
                const productBrand = (p.brand || '').toUpperCase().trim();
                const productName = (p.name || '').toUpperCase();
                matchBrand = productBrand === targetBrand || productName.includes(targetBrand);
            }

            let matchCompatibility = true;
            if (activeCategory === 'motherboard' && selectionMap.processor) {
                const cpuSpecs = (selectionMap.processor.specs || '').toUpperCase();
                const moboSpecs = (p.specs || '').toUpperCase();
                if (cpuSpecs.includes('AM5') && !moboSpecs.includes('AM5')) matchCompatibility = false;
                if (cpuSpecs.includes('LGA1700') && !moboSpecs.includes('LGA1700')) matchCompatibility = false;
                if (cpuSpecs.includes('AM4') && !moboSpecs.includes('AM4')) matchCompatibility = false;
            }
            if (activeCategory === 'ram' && selectionMap.motherboard) {
                const moboSpecs = (selectionMap.motherboard.specs || '').toUpperCase();
                const ramSpecs = (p.specs || '').toUpperCase();
                if (moboSpecs.includes('DDR5') && !ramSpecs.includes('DDR5')) matchCompatibility = false;
                if (moboSpecs.includes('DDR4') && !ramSpecs.includes('DDR4')) matchCompatibility = false;
            }
            return matchSearch && matchBrand && matchCompatibility;
        })
        .sort((a, b) => {
            if (sortOrder === 'price-asc') return a.price - b.price;
            else if (sortOrder === 'price-desc') return b.price - a.price;
            return 0;
        });

    // --- REACTIVE FOOTER LOGIC ---
    $: selectedItems = Object.entries(selectionMap)
        .filter(([_, item]) => item !== null)
        .map(([key, item]) => ({ key: key as CategoryKey, ...item! }));
    $: totalItems = selectedItems.length;
    $: totalPrice = selectedItems.reduce((acc, item) => acc + item.price, 0);

    function changeCategory(catId: CategoryKey) {
        activeCategory = catId;
        brandFilter = 'ALL'; 
        sortOrder = 'price-asc';
        searchQuery = '';    
    }

    function selectProduct(item: Product) {
        if (selectionMap[activeCategory]?.id === item.id) {
            selectionMap[activeCategory] = null;
        } else {
            selectionMap[activeCategory] = item;
        }
        selectionMap = selectionMap;
    }

    function removeSpecificItem(key: CategoryKey) {
        selectionMap[key] = null;
        selectionMap = selectionMap;
    }

    function resetSelection() {
        if(confirm("Reset semua pilihan?")) {
            selectionMap = { processor: null, motherboard: null, cooler: null, ram: null, storage: null, vga: null, psu: null, casing: null, monitor: null };
            showSummary = false;
        }
    }

    function openProduct(item: Product) {
        goto(`/web/product/${item.id}`);
    }

    function toggleSummary() {
        showSummary = !showSummary;
    }

    // --- FIX: ADD TO CART VIA API ---
    async function handleAddToCart() {
        if (totalItems === 0) {
            alert('Belum ada komponen yang dipilih!');
            return;
        }

        const token = localStorage.getItem('auth_token');
        if (!token) {
            alert('Anda harus login untuk menyimpan keranjang.');
            goto('/web/login');
            return;
        }

        isAddingToCart = true;
        let successCount = 0;
        let failCount = 0;

        // Loop through items and POST to backend
        for (const item of selectedItems) {
            try {
                const res = await fetch(`${PUBLIC_API_URL}/cart/${item.id}`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ quantity: 1 })
                });

                if (res.ok) {
                    successCount++;
                } else {
                    const data = await res.json();
                    console.error(`Gagal add ${item.name}:`, data);
                    failCount++;
                }
            } catch (e) {
                console.error(`Network error ${item.name}:`, e);
                failCount++;
            }
        }

        isAddingToCart = false;

        if (successCount > 0) {
            alert(`Berhasil menambahkan ${successCount} item ke keranjang!`);
        }
        if (failCount > 0) {
            alert(`Gagal menambahkan ${failCount} item. Cek console atau stok barang.`);
        }
    }

    async function handleDirectCheckout() {
        if (totalItems === 0) {
            alert('Silakan pilih komponen terlebih dahulu.');
            return;
        }
        
        // Wait for API Add process
        await handleAddToCart();
        
        // Redirect to Cart page to fetch the real Cart IDs from DB
        goto('/web/cart');
    }

</script>

<svelte:head>
    <title>Simulasi PC - PC Store</title>
</svelte:head>

<div class="w-[95%] mx-auto my-5 flex justify-start relative z-10">
    <div class="inline-flex items-center gap-2.5 bg-slate-900 rounded-full px-6 py-2.5 text-sm font-medium shadow-sm border border-white/5 text-slate-400">
        <a class="text-slate-400 no-underline cursor-pointer transition-colors duration-200 hover:text-white" href="/web">Home</a>
        <span class="text-slate-600 text-xs">›</span>
        <span class="text-[#ff0055] font-bold">Simulasi PC</span>
    </div>
</div>

<main class="bg-[#f7f7f7] min-h-screen font-sans text-base text-gray-800">
    <div class="max-w-[95%] mx-auto py-10 px-5 pb-24">
        <header class="text-center mb-10">
            <h1 class="text-4xl font-extrabold text-slate-800 mb-3">Simulasi PC</h1>
            <p class="text-lg text-slate-500 m-0">Kustomisasi PC sesuai kebutuhan Anda dengan komponen pilihan</p>
        </header>

        <section class="grid grid-cols-1 lg:grid-cols-[320px_1fr] gap-7">
            <aside class="bg-white p-5 rounded-xl border border-gray-200 h-fit sticky top-5 z-10 lg:static mb-5 lg:mb-0">
                <div class="text-base font-bold text-slate-500 mb-3 ml-1 tracking-wide">Komponen Utama</div>
                <div class="flex flex-col gap-2">
                    {#each hardwareData.filter(c => c.group === 'Utama') as cat}
                        <button 
                            class="flex items-center gap-3.5 bg-white border border-slate-200 p-3.5 rounded-lg cursor-pointer transition-all text-left relative overflow-hidden hover:bg-slate-50 hover:border-slate-300
                            {activeCategory === cat.id ? '!bg-blue-50 !border-blue-200 border-l-[5px] !border-l-red-600 pl-[11px]' : ''}"
                            on:click={() => changeCategory(cat.id)}
                        >
                            <div class="w-[42px] h-[42px] flex items-center justify-center rounded-lg 
                                {selectionMap[cat.id] ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600'}
                                {activeCategory === cat.id && !selectionMap[cat.id] ? '!bg-blue-100 !text-blue-600' : ''}">
                                {#if selectionMap[cat.id]}
                                    <span class="font-bold">✓</span>
                                {:else}
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                        <path d={cat.iconPath} />
                                    </svg>
                                {/if}
                            </div>
                            <div class="flex-1 overflow-hidden">
                                <div class="font-bold text-lg text-slate-700">{cat.label}</div>
                                <div class="text-sm text-slate-400 whitespace-nowrap overflow-hidden text-ellipsis">
                                    {selectionMap[cat.id] ? (selectionMap[cat.id]?.name.substring(0, 30) + '...') : cat.subLabel}
                                </div>
                            </div>
                        </button>
                    {/each}
                </div>

                <div class="text-base font-bold text-slate-500 mb-3 ml-1 tracking-wide mt-6 pt-4 border-t border-slate-100">Komponen Tambahan</div>
                <div class="flex flex-col gap-2">
                    {#each hardwareData.filter(c => c.group === 'Tambahan') as cat}
                        <button 
                            class="flex items-center gap-3.5 bg-white border border-slate-200 p-3.5 rounded-lg cursor-pointer transition-all text-left relative overflow-hidden hover:bg-slate-50 hover:border-slate-300
                            {activeCategory === cat.id ? '!bg-blue-50 !border-blue-200 border-l-[5px] !border-l-red-600 pl-[11px]' : ''}"
                            on:click={() => changeCategory(cat.id)}
                        >
                            <div class="w-[42px] h-[42px] flex items-center justify-center rounded-lg 
                                {selectionMap[cat.id] ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600'}
                                {activeCategory === cat.id && !selectionMap[cat.id] ? '!bg-blue-100 !text-blue-600' : ''}">
                                {#if selectionMap[cat.id]}
                                    <span class="font-bold">✓</span>
                                {:else}
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                        <path d={cat.iconPath} />
                                    </svg>
                                {/if}
                            </div>
                            <div class="flex-1 overflow-hidden">
                                <div class="font-bold text-lg text-slate-700">{cat.label}</div>
                                <div class="text-sm text-slate-400 whitespace-nowrap overflow-hidden text-ellipsis">
                                    {selectionMap[cat.id] ? (selectionMap[cat.id]?.name.substring(0, 30) + '...') : cat.subLabel}
                                </div>
                            </div>
                        </button>
                    {/each}
                </div>
            </aside>

            <main class="bg-white p-7 rounded-xl border border-gray-200">
                <div class="mb-6">
                    <h2 class="m-0 mb-4 text-3xl font-extrabold text-slate-900">{currentCategoryData?.label}</h2>
                    <div class="flex gap-4 w-full items-center flex-wrap max-sm:flex-col max-sm:items-stretch">
                        <div class="flex-1 min-w-[250px]">
                            <input 
                                type="text" 
                                placeholder="Cari komponen..." 
                                bind:value={searchQuery} 
                                class="w-full p-3 border border-slate-300 rounded-lg bg-slate-50 text-base focus:outline-none focus:border-blue-500 focus:bg-white" 
                            />
                        </div>
                        
                        <div class="flex gap-3 max-sm:w-full">
                            <select bind:value={brandFilter} class="p-3 border border-slate-300 rounded-lg bg-white text-base cursor-pointer min-w-[160px] flex-1">
                                <option value="ALL">Semua Brand</option>
                                {#each availableBrands as b}
                                    <option value={b.toUpperCase()}>{b}</option>
                                {/each}
                            </select>

                            <select bind:value={sortOrder} class="p-3 border border-slate-300 rounded-lg bg-white text-base cursor-pointer flex-1">
                                <option value="price-asc">Harga Terendah</option>
                                <option value="price-desc">Harga Tertinggi</option>
                            </select>
                        </div>
                    </div>
                </div>

                {#if activeCategory === 'motherboard' && selectionMap.processor}
                    <div class="bg-emerald-50 text-emerald-800 p-3.5 rounded-lg mb-5 border border-emerald-200 text-base">
                        ℹ️ Menampilkan motherboard yang kompatibel dengan <strong>{selectionMap.processor.name}</strong> ({selectionMap.processor.specs})
                    </div>
                {/if}
                {#if activeCategory === 'ram' && selectionMap.motherboard}
                    <div class="bg-emerald-50 text-emerald-800 p-3.5 rounded-lg mb-5 border border-emerald-200 text-base">
                        ℹ️ Menampilkan RAM yang kompatibel dengan <strong>{selectionMap.motherboard.name}</strong> ({selectionMap.motherboard.specs})
                    </div>
                {/if}

                <div class="flex flex-col gap-4">
                    {#if isLoading}
                        <div class="text-center py-12 text-slate-400 text-lg">Memuat data produk...</div>
                    {:else if filteredProducts.length === 0}
                        <div class="text-center py-12 text-slate-400 text-lg">
                            <p>Tidak ada produk ditemukan.</p>
                            {#if activeCategory === 'motherboard' && selectionMap.processor}
                                <small>Coba ganti Processor lain untuk melihat motherboard tipe berbeda.</small>
                            {/if}
                        </div>
                    {:else}
                        {#each filteredProducts as product}
                            <div
                                class="flex items-center gap-5 bg-white border border-slate-200 p-5 rounded-lg transition-all hover:border-slate-300 hover:shadow-sm cursor-pointer
                                {selectionMap[activeCategory]?.id === product.id ? '!border-red-500 !bg-red-50' : ''}"
                                role="button"
                                tabindex="0"
                                on:click={() => openProduct(product)}
                            >
                                <div class="text-4xl flex items-center justify-center w-[60px]">
                                    {#if product.imagePlaceholder && product.imagePlaceholder !== '/images/placeholder.png'}
                                        <img src={product.imagePlaceholder} alt="" class="w-[50px] h-[50px] object-contain">
                                    {:else}
                                        📦
                                    {/if}
                                </div>
                                <div class="flex-1">
                                    <div class="font-bold text-xl text-slate-800 mb-1.5">{product.name}</div>
                                    <div class="text-base text-slate-500 flex gap-3">
                                        {#if product.brand !== 'Generic'}
                                            <span class="font-semibold text-slate-600 bg-slate-100 px-2 py-1 rounded text-sm">{product.brand}</span>
                                        {/if}
                                        <span>{product.specs}</span>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-2.5 min-w-[120px]">
                                    {#if selectionMap[activeCategory]?.id === product.id}
                                        <button class="px-6 py-2.5 border border-red-500 bg-red-500 text-white rounded-lg font-semibold text-base cursor-pointer transition-all hover:bg-red-600" on:click|stopPropagation={() => selectProduct(product)}>Remove</button>
                                    {:else}
                                        <button class="px-6 py-2.5 border border-slate-300 bg-white rounded-lg font-semibold text-base cursor-pointer transition-all hover:bg-slate-100 hover:border-slate-400" on:click|stopPropagation={() => selectProduct(product)}>Select</button>
                                    {/if}
                                    <div class="font-bold text-blue-600 text-xl">{formatRupiah(product.price)}</div>
                                </div>
                            </div>
                        {/each}
                    {/if}
                </div>
            </main>
        </section>
    </div>
</main>

{#if totalItems > 0}
    <div class="fixed bottom-8 left-0 w-full flex justify-center items-end z-[100] pointer-events-none max-md:bottom-2">
        {#if showSummary}
            <div class="fixed top-0 left-0 w-full h-full bg-black/20 pointer-events-auto z-[101]" on:click={() => showSummary = false} transition:fly={{ duration: 200 }}></div>
            <div class="absolute bottom-[85px] bg-white w-[400px] max-w-[90vw] rounded-xl shadow-xl border border-slate-200 pointer-events-auto z-[102] flex flex-col overflow-hidden max-md:bottom-5 max-md:w-[95%]">
                <div class="p-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                    <h3 class="m-0 text-base text-slate-700">Rincian Rakitan Anda</h3>
                    <button class="bg-none border-none text-xl cursor-pointer text-slate-400" on:click={() => showSummary = false}>✕</button>
                </div>
                <div class="max-h-[300px] overflow-y-auto p-2.5">
                    {#each selectedItems as item}
                        <div class="flex justify-between items-center p-2.5 border-b border-dashed border-slate-100 last:border-none">
                            <div class="flex flex-col gap-0.5">
                                <span class="text-xs text-slate-500 font-bold bg-slate-100 px-1.5 py-0.5 rounded w-fit">{item.key.toUpperCase()}</span>
                                <span class="text-sm font-semibold text-slate-800 max-w-[200px] whitespace-nowrap overflow-hidden text-ellipsis">{item.name}</span>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <span class="text-sm text-blue-600 font-bold">{formatRupiah(item.price)}</span>
                                <button class="bg-none border-none cursor-pointer opacity-60 transition-transform hover:opacity-100 hover:scale-110" on:click={() => removeSpecificItem(item.key)}>🗑️</button>
                            </div>
                        </div>
                    {/each}
                </div>
                <div class="p-3 bg-emerald-50 border-t border-emerald-100 text-right text-base text-emerald-800">
                    Total: <strong>{formatRupiah(totalPrice)}</strong>
                </div>
            </div>
        {/if}

        <div class="bg-white text-slate-800 p-3 rounded-2xl flex items-center gap-6 shadow-xl border border-slate-300 max-w-[90%] overflow-x-auto relative z-[102] pointer-events-auto max-md:flex-col max-md:gap-3 max-md:w-[95%] max-md:p-4">
            <div class="flex flex-col items-end min-w-[120px] max-md:items-center max-md:w-full max-md:border-b max-md:border-slate-100 max-md:pb-2">
                <div class="text-xs text-slate-500 uppercase tracking-wide font-semibold">Total</div>
                <div class="text-xl font-extrabold text-slate-900 leading-tight">{formatRupiah(totalPrice)}</div>
            </div>

            <div class="w-px h-[30px] bg-slate-200 max-md:hidden"></div>

            <div class="flex gap-2 items-center max-md:grid max-md:grid-cols-2 max-md:w-full">
                <button 
                    class="bg-white border border-slate-300 text-slate-600 px-4 py-2 rounded-lg text-sm font-semibold cursor-pointer flex items-center gap-2 transition-all hover:bg-slate-50 hover:border-slate-400 hover:text-slate-800 max-md:justify-center {showSummary ? 'bg-slate-200 border-slate-500' : ''}"
                    on:click={toggleSummary}
                >
                    {showSummary ? 'v Tutup' : '^ Summary'}
                    {#if totalItems > 0}
                        <span class="bg-red-500 text-white text-xs font-bold px-1.5 rounded-full min-w-[16px] text-center">{totalItems}</span>
                    {/if}
                </button>
                
                <button class="bg-white border border-slate-300 text-slate-600 px-4 py-2 rounded-lg text-sm font-semibold cursor-pointer flex items-center gap-2 transition-all hover:bg-slate-50 hover:border-slate-400 hover:text-slate-800 max-md:justify-center" on:click={resetSelection}>
                    ↻ Reset
                </button>

                <div class="w-2.5 max-md:hidden"></div>

                <button class="bg-emerald-500 border border-emerald-600 text-white px-4.5 py-2.5 rounded-md text-sm font-semibold cursor-pointer whitespace-nowrap shadow-sm transition-all hover:bg-emerald-600 active:scale-95 disabled:bg-gray-400 disabled:border-gray-400 disabled:cursor-not-allowed max-md:justify-center" on:click={handleAddToCart} disabled={isAddingToCart}>
                    {isAddingToCart ? 'Menyimpan...' : '🛒 Tambah'}
                </button>
                
                <button class="bg-green-600 border border-green-700 text-white px-6 py-2.5 rounded-md text-sm font-bold cursor-pointer shadow-md transition-all hover:bg-green-700 active:scale-95 disabled:bg-gray-400 disabled:border-gray-400 disabled:cursor-not-allowed max-md:col-span-2 max-md:justify-center" on:click={handleDirectCheckout} disabled={isAddingToCart}>
                    {isAddingToCart ? '...' : '🧾 Checkout'}
                </button>
            </div>
        </div>
    </div>
{/if}