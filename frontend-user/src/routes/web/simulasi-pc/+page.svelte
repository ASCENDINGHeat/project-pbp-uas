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

<div class="breadcrumb-wrapper">
    <div class="breadcrumb-pill">
        <a class="breadcrumb-link" href="/web">Home</a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">Simulasi PC</span>
    </div>
</div>

<main class="category-page">
    <div class="container">
        <header class="header-section">
            <h1>Simulasi PC</h1>
            <p class="subtitle">Kustomisasi PC sesuai kebutuhan Anda dengan komponen pilihan</p>
        </header>

        <section class="simulation-wrapper">
            <aside class="sidebar">
                <div class="sidebar-section-title">Komponen Utama</div>
                <div class="nav-list">
                    {#each hardwareData.filter(c => c.group === 'Utama') as cat}
                        <button 
                            class="nav-item" 
                            class:active={activeCategory === cat.id}
                            class:filled={selectionMap[cat.id] !== null}
                            on:click={() => changeCategory(cat.id)}
                        >
                            <div class="nav-icon-box">
                                {#if selectionMap[cat.id]}
                                    <span class="check-icon">✓</span>
                                {:else}
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                        <path d={cat.iconPath} />
                                    </svg>
                                {/if}
                            </div>
                            <div class="nav-text">
                                <div class="nav-label">{cat.label}</div>
                                <div class="nav-sublabel">
                                    {selectionMap[cat.id] ? (selectionMap[cat.id]?.name.substring(0, 30) + '...') : cat.subLabel}
                                </div>
                            </div>
                        </button>
                    {/each}
                </div>

                <div class="sidebar-section-title mt-4">Komponen Tambahan</div>
                <div class="nav-list">
                    {#each hardwareData.filter(c => c.group === 'Tambahan') as cat}
                        <button 
                            class="nav-item" 
                            class:active={activeCategory === cat.id}
                            class:filled={selectionMap[cat.id] !== null}
                            on:click={() => changeCategory(cat.id)}
                        >
                            <div class="nav-icon-box">
                                {#if selectionMap[cat.id]}
                                    <span class="check-icon">✓</span>
                                {:else}
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                        <path d={cat.iconPath} />
                                    </svg>
                                {/if}
                            </div>
                            <div class="nav-text">
                                <div class="nav-label">{cat.label}</div>
                                <div class="nav-sublabel">
                                    {selectionMap[cat.id] ? (selectionMap[cat.id]?.name.substring(0, 30) + '...') : cat.subLabel}
                                </div>
                            </div>
                        </button>
                    {/each}
                </div>
            </aside>

            <main class="content-panel">
                <div class="content-header">
                    <h2>{currentCategoryData?.label}</h2>
                    <div class="filter-controls">
                        <div class="search-wrapper">
                            <input 
                                type="text" 
                                placeholder="Cari komponen..." 
                                bind:value={searchQuery} 
                                class="search-input" 
                            />
                        </div>
                        
                        <div class="select-group">
                            <select bind:value={brandFilter} class="light-select" aria-label="Filter Brand">
                                <option value="ALL">Semua Brand</option>
                                {#each availableBrands as b}
                                    <option value={b.toUpperCase()}>{b}</option>
                                {/each}
                            </select>

                            <select bind:value={sortOrder} class="light-select">
                                <option value="price-asc">Harga Terendah</option>
                                <option value="price-desc">Harga Tertinggi</option>
                            </select>
                        </div>
                    </div>
                </div>

                {#if activeCategory === 'motherboard' && selectionMap.processor}
                    <div class="compatibility-alert">
                        ℹ️ Menampilkan motherboard yang kompatibel dengan <strong>{selectionMap.processor.name}</strong> ({selectionMap.processor.specs})
                    </div>
                {/if}
                {#if activeCategory === 'ram' && selectionMap.motherboard}
                    <div class="compatibility-alert">
                        ℹ️ Menampilkan RAM yang kompatibel dengan <strong>{selectionMap.motherboard.name}</strong> ({selectionMap.motherboard.specs})
                    </div>
                {/if}

                <div class="product-list">
                    {#if isLoading}
                        <div class="empty-state">Memuat data produk...</div>
                    {:else if filteredProducts.length === 0}
                        <div class="empty-state">
                            <p>Tidak ada produk ditemukan.</p>
                            {#if activeCategory === 'motherboard' && selectionMap.processor}
                                <small>Coba ganti Processor lain untuk melihat motherboard tipe berbeda.</small>
                            {/if}
                        </div>
                    {:else}
                        {#each filteredProducts as product}
                            <div
                                class="product-card"
                                class:selected={selectionMap[activeCategory]?.id === product.id}
                                role="button"
                                tabindex="0"
                                on:click={() => openProduct(product)}
                            >
                                <div class="prod-img">
                                    {#if product.imagePlaceholder && product.imagePlaceholder !== '/images/placeholder.png'}
                                        <img src={product.imagePlaceholder} alt="" style="width:50px; height:50px; object-fit:contain;">
                                    {:else}
                                        📦
                                    {/if}
                                </div>
                                <div class="prod-info">
                                    <div class="prod-name">{product.name}</div>
                                    <div class="prod-specs">
                                        {#if product.brand !== 'Generic'}
                                            <span class="brand-badge">{product.brand}</span>
                                        {/if}
                                        <span>{product.specs}</span>
                                    </div>
                                </div>
                                <div class="prod-action">
                                    {#if selectionMap[activeCategory]?.id === product.id}
                                        <button class="btn-select remove" on:click|stopPropagation={() => selectProduct(product)}>Remove</button>
                                    {:else}
                                        <button class="btn-select" on:click|stopPropagation={() => selectProduct(product)}>Select</button>
                                    {/if}
                                    <div class="prod-price">{formatRupiah(product.price)}</div>
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
    <div class="floating-dock-container">
        {#if showSummary}
            <div class="dock-backdrop" on:click={() => showSummary = false} transition:fly={{ duration: 200 }}></div>
        {/if}

        {#if showSummary}
            <div class="summary-popup" transition:fly={{ y: 50, duration: 300 }}>
                <div class="summary-header">
                    <h3>Rincian Rakitan Anda</h3>
                    <button class="btn-close-summary" on:click={() => showSummary = false}>✕</button>
                </div>
                <div class="summary-content">
                    {#each selectedItems as item}
                        <div class="summary-item">
                            <div class="si-info">
                                <span class="si-cat">{item.key.toUpperCase()}</span>
                                <span class="si-name">{item.name}</span>
                            </div>
                            <div class="si-right">
                                <span class="si-price">{formatRupiah(item.price)}</span>
                                <button class="btn-si-remove" on:click={() => removeSpecificItem(item.key)}>🗑️</button>
                            </div>
                        </div>
                    {/each}
                </div>
                <div class="summary-footer-info">
                    Total: <strong>{formatRupiah(totalPrice)}</strong>
                </div>
            </div>
        {/if}

        <div class="dock-bar">
            <div class="dock-total">
                <div class="total-label">Total</div>
                <div class="total-price">{formatRupiah(totalPrice)}</div>
            </div>

            <div class="dock-divider"></div>

            <div class="dock-actions">
                <button class="btn-dock-secondary" on:click={toggleSummary} class:active={showSummary}>
                    {showSummary ? 'v Tutup' : '^ Summary'}
                    {#if totalItems > 0}
                        <span class="badge-count">{totalItems}</span>
                    {/if}
                </button>
                
                <button class="btn-dock-secondary" on:click={resetSelection}>
                    ↻ Reset
                </button>

                <div class="spacer"></div>

                <button class="btn-dock-primary" on:click={handleAddToCart} disabled={isAddingToCart}>
                    {isAddingToCart ? 'Menyimpan...' : '🛒 Tambah'}
                </button>
                
                <button class="btn-dock-primary checkout" on:click={handleDirectCheckout} disabled={isAddingToCart}>
                    {isAddingToCart ? '...' : '🧾 Checkout'}
                </button>
            </div>
        </div>
    </div>
{/if}

<style>
    /* Global & Layout - Font Size Basis 16px */
    :global(body) { margin: 0; font-family: 'Segoe UI', system-ui, sans-serif; background-color: #f7f7f7; color: #1f2937; font-size: 16px; }
    
    .container { max-width: 95%; margin: 0 auto; padding: 40px 20px 100px 20px; }
    
    /* breadcrumb wrapper */
    .breadcrumb-wrapper { width: 95%; margin: 20px auto; padding: 0; display: flex; justify-content: flex-start; position: relative; z-index: 1; }
    .breadcrumb-pill { display: inline-flex; align-items: center; gap: 10px; background-color: #0f172a; border-radius: 50px; padding: 10px 24px; font-size: 0.9rem; font-weight: 500; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid rgba(255,255,255,0.05); }
    .breadcrumb-link { color: #94a3b8; text-decoration: none; cursor: pointer; transition: color 0.2s; font-family: inherit; }
    .breadcrumb-link:hover { color: #fff; }
    .breadcrumb-sep { color: #475569; font-size: 0.8rem; }
    .breadcrumb-current { color: #ff0055; font-weight: 700; }

    .category-page { background: #f7f7f7; min-height: 100vh; font-family: inherit; }

    .header-section { margin-bottom: 40px; text-align: center; font-family: inherit; }
    .header-section h1 { font-size: 2.5rem; font-weight: 800; color: #1f2d3d; margin: 0 0 12px; font-family: inherit; }
    .header-section p { font-size: 1.1rem; color: #64748b; margin: 0; font-family: inherit; }

    .simulation-wrapper { display: grid; grid-template-columns: 320px 1fr; gap: 28px; } 
    
    /* SIDEBAR */
    .sidebar { background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #e5e7eb; height: fit-content; position: sticky; top: 20px; z-index: 10; }
    .sidebar-section-title { font-size: 1rem; font-weight: 700; color: #64748b; margin: 0 0 12px 4px; letter-spacing: 0.5px; }
    .mt-4 { margin-top: 24px; border-top: 1px solid #f1f5f9; padding-top: 16px; }

    .nav-list { display: flex; flex-direction: column; gap: 8px; }

    .nav-item { display: flex; align-items: center; gap: 14px; background: #fff; border: 1px solid #e2e8f0; padding: 14px 16px; border-radius: 8px; cursor: pointer; transition: all 0.2s; text-align: left; position: relative; overflow: hidden; }
    .nav-item:hover { border-color: #cbd5e1; background: #f8fafc; }
    .nav-item.active { background: #eff6ff; border-color: #bfdbfe; border-left: 5px solid #dc2626; padding-left: 11px; }

    .nav-icon-box { width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; background: #f1f5f9; border-radius: 8px; color: #475569; }
    .nav-item.active .nav-icon-box { background: #dbeafe; color: #2563eb; }
    .nav-item.filled .nav-icon-box { background: #2563eb; color: #fff; }

    .nav-text { flex: 1; overflow: hidden; }
    .nav-label { font-weight: 700; font-size: 1.1rem; color: #334155; }
    .nav-sublabel { font-size: 0.9rem; color: #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    /* Content Panel */
    .content-panel { background: #fff; padding: 28px; border-radius: 12px; border: 1px solid #e5e7eb; }
    .content-header { margin-bottom: 24px; }
    .content-header h2 { margin: 0 0 16px; font-size: 1.8rem; font-weight: 800; color: #0f172a; }
    
    .filter-controls { display: flex; gap: 16px; width: 100%; align-items: center; flex-wrap: wrap; }
    .search-wrapper { flex: 1; min-width: 250px; }
    
    .search-input { width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; background: #f8fafc; font-size: 1.05rem; box-sizing: border-box; }
    .search-input:focus { outline: none; border-color: #3b82f6; background: #fff; }

    .select-group { display: flex; gap: 12px; }
    .light-select { padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; font-size: 1.05rem; cursor: pointer; min-width: 160px; }

    .compatibility-alert { background: #ecfdf5; color: #065f46; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #a7f3d0; font-size: 1rem; }
    
    .product-list { display: flex; flex-direction: column; gap: 16px; }
    
    .product-card { display: flex; align-items: center; gap: 20px; background: #fff; border: 1px solid #e2e8f0; padding: 20px; border-radius: 10px; transition: all 0.2s; }
    .product-card:hover { border-color: #cbd5e1; box-shadow: 0 4px 12px -2px rgba(0,0,0,0.08); }
    .product-card.selected { border-color: #ef4444; background: #fef2f2; }
    
    .prod-img { font-size: 2.5rem; display: flex; align-items: center; justify-content: center; width: 60px; }
    .prod-info { flex: 1; }
    
    .prod-name { font-weight: 700; font-size: 1.2rem; color: #1e293b; margin-bottom: 6px; }
    .prod-specs { font-size: 1rem; color: #64748b; display: flex; gap: 12px; }
    .brand-badge { font-weight: 600; color: #475569; background: #f1f5f9; padding: 4px 8px; border-radius: 6px; font-size: 0.9rem; }
    
    .prod-action { display: flex; flex-direction: column; align-items: flex-end; gap: 10px; min-width: 120px; }
    .prod-price { font-weight: 700; color: #2563eb; font-size: 1.3rem; }
    
    .btn-select { padding: 10px 24px; border: 1px solid #cbd5e1; background: #fff; border-radius: 8px; font-weight: 600; font-size: 1rem; cursor: pointer; transition: all 0.2s; }
    .btn-select:hover { background: #f1f5f9; border-color: #94a3b8; }
    .btn-select.remove { background: #ef4444; color: #fff; border-color: #ef4444; }
    .btn-select.remove:hover { background: #dc2626; }

    .empty-state { text-align: center; padding: 50px 20px; color: #94a3b8; font-size: 1.1rem; }

    /* --- FLOATING DOCK STYLE --- */
    .floating-dock-container { position: fixed; bottom: 30px; left: 0; width: 100%; display: flex; justify-content: center; align-items: flex-end; z-index: 100; pointer-events: none; }

    .dock-bar { background-color: #ffffff; color: #1e293b; padding: 12px 24px; border-radius: 16px; display: flex; align-items: center; gap: 24px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); pointer-events: auto; border: 1px solid #cbd5e1; max-width: 90%; overflow-x: auto; position: relative; z-index: 102; }

    .dock-backdrop { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.2); pointer-events: auto; z-index: 101; }

    .summary-popup { position: absolute; bottom: 85px; background: #fff; width: 400px; max-width: 90vw; border-radius: 12px; box-shadow: 0 -5px 25px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; pointer-events: auto; z-index: 102; display: flex; flex-direction: column; overflow: hidden; }

    .summary-header { padding: 16px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
    .summary-header h3 { margin: 0; font-size: 1rem; color: #334155; }
    .btn-close-summary { background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #94a3b8; }

    .summary-content { max-height: 300px; overflow-y: auto; padding: 10px; }

    .summary-item { display: flex; justify-content: space-between; align-items: center; padding: 10px; border-bottom: 1px dashed #f1f5f9; }
    .summary-item:last-child { border-bottom: none; }

    .si-info { display: flex; flex-direction: column; gap: 2px; }
    .si-cat { font-size: 0.7rem; color: #64748b; font-weight: 700; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; width: fit-content; }
    .si-name { font-size: 0.9rem; font-weight: 600; color: #1e293b; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .si-right { display: flex; align-items: center; gap: 10px; }
    .si-price { font-size: 0.9rem; color: #2563eb; font-weight: 700; }
    .btn-si-remove { background: none; border: none; cursor: pointer; opacity: 0.6; transition: 0.2s; }
    .btn-si-remove:hover { opacity: 1; transform: scale(1.1); }

    .summary-footer-info { padding: 12px 16px; background: #f0fdf4; border-top: 1px solid #dcfce7; text-align: right; font-size: 1rem; color: #166534; }

    .dock-total { display: flex; flex-direction: column; align-items: flex-end; min-width: 120px; }
    .total-label { font-size: 0.75rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
    .total-price { font-size: 1.25rem; font-weight: 800; color: #0f172a; line-height: 1.2; }

    .dock-divider { width: 1px; height: 30px; background-color: #e2e8f0; }

    .dock-actions { display: flex; gap: 8px; align-items: center; }

    .spacer { width: 10px; }

    .btn-dock-secondary { background: #fff; border: 1px solid #cbd5e1; color: #475569; padding: 8px 16px; border-radius: 8px; font-size: 0.9rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s; }
    .btn-dock-secondary:hover, .btn-dock-secondary.active { background: #f1f5f9; border-color: #94a3b8; color: #1e293b; }
    .btn-dock-secondary.active { background: #e2e8f0; border-color: #64748b; }

    .badge-count { background-color: #ef4444; color: white; font-size: 0.75rem; font-weight: 700; padding: 2px 6px; border-radius: 99px; min-width: 16px; text-align: center; }

    .btn-dock-primary { background-color: #10b981; border: 1px solid #059669; color: #fff; padding: 9px 18px; border-radius: 6px; font-size: 0.95rem; font-weight: 600; cursor: pointer; white-space: nowrap; transition: transform 0.1s, background-color 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .btn-dock-primary:hover { background-color: #059669; }
    .btn-dock-primary:active { transform: scale(0.95); }
    .btn-dock-primary:disabled { background-color: #9ca3af; border-color: #9ca3af; cursor: not-allowed; transform: none; }

    .btn-dock-primary.checkout { background-color: #16a34a; font-weight: 700; padding: 9px 24px; box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.3); }

    @media (max-width: 1024px) {
        .simulation-wrapper { grid-template-columns: 1fr; }
        .sidebar { position: static; margin-bottom: 20px; }
        .container { padding: 18px; } 
    }
    @media (max-width: 768px) {
        .floating-dock-container { bottom: 10px; }
        .dock-bar { flex-direction: column; gap: 12px; width: 95%; padding: 16px; border: 1px solid #e2e8f0; }
        .dock-divider { display: none; }
        .dock-total { align-items: center; width: 100%; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; }
        .dock-actions { display: grid; grid-template-columns: 1fr 1fr; width: 100%; }
        .spacer { display: none; }
        .btn-dock-secondary, .btn-dock-primary { justify-content: center; }
        .btn-dock-primary.checkout { grid-column: span 2; }
        .summary-popup { bottom: 20px; width: 95%; }
        .container, .breadcrumb-container { padding: 0 16px; }
        .header-section h1 { font-size: 2rem; }
        .breadcrumb-pill { padding: 8px 16px; font-size: 0.85rem; }
    }
    @media (max-width: 640px) {
        .filter-controls { flex-direction: column; align-items: stretch; }
        .select-group { width: 100%; }
        .light-select { flex: 1; }
        .header-section h1 { font-size: 2.2rem; }
    }
</style>