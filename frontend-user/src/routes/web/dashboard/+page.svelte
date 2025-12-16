<script lang="ts">
    import { onMount } from 'svelte';
    import { goto } from '$app/navigation';
    import { PUBLIC_API_URL } from '$env/static/public';

    // --- STATE MANAGEMENT ---
    let activePage = 'dashboard';
    let isLoading = true;
    let token = '';

    // Data Models (State)
    let vendorProfile: any = null;
    let myProducts: any[] = [];
    let myOrders: any[] = [];
    let dashboardStats = [
        { label: 'Total Pendapatan', value: 'Rp 0', increase: true, change: '-' },
        { label: 'Pesanan Baru', value: '0', increase: true, change: 'Pending' },
        { label: 'Produk Terjual', value: '0', increase: true, change: 'Selesai' },
        { label: 'Stok Menipis', value: '0', increase: false, change: 'Perlu Cek' }
    ];

    // Form State (Create Product)
    let prodName = '';
    let prodCategory = '';
    let prodStock: number | null = null;
    let prodPrice: number | null = null;
    let prodDesc = '';
    // --- COMPATIBILITY FEATURES ---
    let prodSocket = '';
    let prodMemory = '';
    
    let prodImageFile: File | null = null;
    let prodImagePreview: string | null = null;
    let isSubmitting = false;

    // Loading state for specific order buttons
    let processingOrderId: number | null = null;

    // --- FORMATTERS ---
    const formatCurrency = (val: number) => 
        new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);

    // --- INITIALIZATION ---
    onMount(async () => {
        token = localStorage.getItem('auth_token') || '';
        if (!token) {
            alert('Anda belum login. Mengalihkan ke halaman login...');
            goto('/login');
            return;
        }

        await fetchDashboardData();
        isLoading = false;
    });

    async function fetchDashboardData() {
        try {
            // 1. Fetch Profile (for ID and Balance)
            const resProfile = await fetch(`${PUBLIC_API_URL}/vendor/profile`, {
                headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
            });

            if (resProfile.ok) {
                const data = await resProfile.json();
                vendorProfile = data.vendor;
                
                // Update Balance Stat
                dashboardStats[0].value = formatCurrency(Number(vendorProfile.balance || 0));

                // 2. Fetch Products (Using Vendor ID)
                if (vendorProfile && vendorProfile.id) {
                    await fetchProducts(vendorProfile.id);
                }
            }

            // 3. Fetch Orders
            await fetchOrders();

        } catch (error) {
            console.error("Error fetching dashboard data:", error);
        }
    }

    async function fetchProducts(vendorId: number) {
        try {
            const res = await fetch(`${PUBLIC_API_URL}/vendor/${vendorId}/products`, {
                 headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
            });

            if (res.ok) {
                const data = await res.json();
                myProducts = data.data.products || [];

                // Calculate "Stok Menipis"
                const lowStockCount = myProducts.filter((p: any) => p.stock_quantity < 5).length;
                dashboardStats[3].value = lowStockCount.toString();
            }
        } catch (e) { console.error(e); }
    }

    async function fetchOrders() {
        try {
            const res = await fetch(`${PUBLIC_API_URL}/vendor/orders`, {
                headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
            });

            if (res.ok) {
                const data = await res.json();
                myOrders = Array.isArray(data) ? data : (data.data || []);

                // Calculate Stats
                const pending = myOrders.filter((o: any) => o.shipping_status === 'pending').length;
                const completed = myOrders.filter((o: any) => o.shipping_status === 'delivered').length;

                dashboardStats[1].value = pending.toString();
                dashboardStats[2].value = completed.toString();
            }
        } catch (e) { console.error(e); }
    }

    // --- ORDER ACTIONS ---
    async function updateOrderStatus(orderId: number, newStatus: string) {
        if (!confirm(`Ubah status pesanan #${orderId} menjadi "${newStatus}"?`)) return;

        processingOrderId = orderId;
        try {
            const res = await fetch(`${PUBLIC_API_URL}/vendor/orders/${orderId}/status`, {
                method: 'PATCH',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ shipping_status: newStatus })
            });

            if (res.ok) {
                // Update local state directly to reflect changes UI
                myOrders = myOrders.map(o => 
                    o.id === orderId ? { ...o, shipping_status: newStatus } : o
                );
                alert(`Status berhasil diubah menjadi ${newStatus}`);
                // Re-calculate stats
                const pending = myOrders.filter((o: any) => o.shipping_status === 'pending').length;
                const completed = myOrders.filter((o: any) => o.shipping_status === 'delivered').length;
                dashboardStats[1].value = pending.toString();
                dashboardStats[2].value = completed.toString();
            } else {
                const err = await res.json();
                alert(err.message || 'Gagal mengubah status');
            }
        } catch (e) {
            console.error(e);
            alert('Gagal menghubungi server');
        } finally {
            processingOrderId = null;
        }
    }

    // --- ACTIONS ---

    function setPage(page: string) {
        activePage = page;
    }

    function handleFileSelect(e: Event) {
        const target = e.target as HTMLInputElement;
        if (target.files && target.files.length > 0) {
            prodImageFile = target.files[0];
            const reader = new FileReader();
            reader.onload = (e) => { prodImagePreview = e.target?.result as string; };
            reader.readAsDataURL(prodImageFile);
        }
    }

    async function handleCreateProduct() {
        if (!prodName || !prodPrice || !prodStock) return alert("Mohon lengkapi data wajib.");

        isSubmitting = true;
        try {
            const formData = new FormData();
            formData.append('title', prodName);
            formData.append('price', prodPrice.toString());
            formData.append('stock_quantity', prodStock.toString());
            if (prodCategory) formData.append('category', prodCategory);
            if (prodDesc) formData.append('description', prodDesc);
            if (prodImageFile) formData.append('image', prodImageFile);

            // --- COMPATIBILITY LOGIC ---
            let specsArr: string[] = [];
            if (prodSocket) specsArr.push(prodSocket);
            if (prodMemory) specsArr.push(prodMemory);
            
            if (specsArr.length > 0) {
                formData.append('details[specs]', specsArr.join(', '));
            }
            if (prodSocket) formData.append('details[socket]', prodSocket);
            if (prodMemory) formData.append('details[memory]', prodMemory);

            const res = await fetch(`${PUBLIC_API_URL}/product`, {
                method: 'POST',
                headers: { 'Authorization': `Bearer ${token}` }, // Content-Type handled automatically
                body: formData
            });

            if (res.ok) {
                alert('Produk berhasil dibuat!');
                resetForm();
                setPage('produk');
                if (vendorProfile?.id) fetchProducts(vendorProfile.id);
            } else {
                const err = await res.json();
                alert('Gagal: ' + (err.message || 'Validasi error'));
            }
        } catch (e) { alert('Error koneksi'); } 
        finally { isSubmitting = false; }
    }

    function resetForm() {
        prodName = '';
        prodCategory = ''; prodStock = null; prodPrice = null; 
        prodDesc = ''; prodImageFile = null; prodImagePreview = null;
        prodSocket = ''; prodMemory = '';
    }

    // --- DERIVED VIEW HELPERS ---
    $: recentOrders = myOrders.slice(0, 5);
</script>

<svelte:head>
    <title>Dashboard Vendor - PC Store</title>
</svelte:head>

<main class="min-h-screen bg-gray-50 py-10 px-5 font-sans text-gray-800">
    <div class="max-w-7xl mx-auto">
        
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900 mb-1.5">Dashboard Vendor</h1>
            {#if vendorProfile}
                <p class="text-slate-500 m-0 text-base">Halo, <strong>{vendorProfile.store_name}</strong>! Berikut ringkasan tokomu.</p>
            {:else}
                <p class="text-slate-500 m-0 text-base">Ringkasan aktivitas toko Anda hari ini</p>
            {/if}
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-8">
            <aside class="h-fit">
                <nav class="flex flex-col gap-2.5 max-lg:flex-row max-lg:overflow-x-auto max-lg:pb-2">
                    <button 
                        class="flex items-center justify-between p-4 bg-gray-200 border-none rounded-lg text-slate-800 text-base font-medium cursor-pointer transition-colors w-full text-left hover:bg-gray-300 max-lg:min-w-[160px]
                        {activePage === 'dashboard' ? 'bg-gray-300 font-semibold' : ''}" 
                        on:click={() => setPage('dashboard')}
                    >
                        <span>Dashboard</span>
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    </button>
                    <button 
                        class="flex items-center justify-between p-4 bg-gray-200 border-none rounded-lg text-slate-800 text-base font-medium cursor-pointer transition-colors w-full text-left hover:bg-gray-300 max-lg:min-w-[160px]
                        {activePage === 'produk' || activePage === 'tambah-produk' ? 'bg-gray-300 font-semibold' : ''}" 
                        on:click={() => setPage('produk')}
                    >
                        <span>Produk Saya</span>
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                    </button>
                    <button 
                        class="flex items-center justify-between p-4 bg-gray-200 border-none rounded-lg text-slate-800 text-base font-medium cursor-pointer transition-colors w-full text-left hover:bg-gray-300 max-lg:min-w-[160px]
                        {activePage === 'pesanan' ? 'bg-gray-300 font-semibold' : ''}" 
                        on:click={() => setPage('pesanan')}
                    >
                        <span>Pesanan</span>
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                    </button>
                    <button 
                        class="flex items-center justify-between p-4 bg-gray-200 border-none rounded-lg text-slate-800 text-base font-medium cursor-pointer transition-colors w-full text-left hover:bg-gray-300 max-lg:min-w-[160px]
                        {activePage === 'tarik-tunai' ? 'bg-gray-300 font-semibold' : ''}" 
                        on:click={() => setPage('tarik-tunai')}
                    >
                        <span>Tarik Tunai</span>
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"></path><path d="M4 6v12a2 2 0 0 0 2 2h14v-4"></path><path d="M18 12a2 2 0 0 0 0 4h4v-4h-4z"></path></svg>
                    </button>
                    <button 
                        class="flex items-center justify-between p-4 bg-red-500 border-none rounded-lg text-white text-base font-medium cursor-pointer transition-colors w-full text-left hover:bg-red-600 mt-5 max-lg:mt-0 max-lg:min-w-[160px]" 
                        on:click={() => { localStorage.removeItem('auth_token'); goto('/login'); }}
                    >
                        <span>Keluar</span>
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    </button>
                </nav>
            </aside>

            <div class="flex flex-col gap-8">
                
                {#if activePage === 'dashboard'}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                        {#each dashboardStats as stat}
                            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                                <div class="flex justify-between mb-2.5">
                                    <span class="text-sm text-slate-500">{stat.label}</span>
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full {stat.increase ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'}">
                                        {stat.change}
                                    </span>
                                </div>
                                <div class="text-2xl font-bold text-slate-800">{stat.value}</div>
                            </div>
                        {/each}
                    </div>

                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                        <div class="mb-5">
                            <h2 class="text-xl font-bold text-slate-800 m-0">Pesanan Terbaru</h2>
                        </div>
                        <div class="bg-white rounded-lg overflow-x-auto">
                            {#if isLoading}
                                <div class="p-5 text-center text-slate-500">Memuat data...</div>
                            {:else if recentOrders.length === 0}
                                <div class="p-5 text-center text-slate-400">Belum ada pesanan masuk.</div>
                            {:else}
                                <table class="w-full border-collapse min-w-[600px]">
                                    <thead>
                                        <tr>
                                            <th class="text-left p-4 bg-gray-50 text-xs text-slate-500 font-bold uppercase tracking-wider">ID Pesanan</th>
                                            <th class="text-left p-4 bg-gray-50 text-xs text-slate-500 font-bold uppercase tracking-wider">Status</th>
                                            <th class="text-left p-4 bg-gray-50 text-xs text-slate-500 font-bold uppercase tracking-wider">Tanggal</th>
                                            <th class="text-right p-4 bg-gray-50 text-xs text-slate-500 font-bold uppercase tracking-wider">Total</th>
                                            <th class="text-center p-4 bg-gray-50 text-xs text-slate-500 font-bold uppercase tracking-wider">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {#each recentOrders as order}
                                            <tr class="border-b border-gray-100 last:border-none">
                                                <td class="p-4 text-sm text-slate-800 font-mono font-medium">#{order.id}</td>
                                                <td class="p-4">
                                                    <span class="px-3 py-1 rounded-full text-xs font-bold capitalize 
                                                        {order.shipping_status === 'pending' ? 'bg-yellow-400 text-white' : ''}
                                                        {order.shipping_status === 'processing' ? 'bg-blue-500 text-white' : ''}
                                                        {order.shipping_status === 'shipped' ? 'bg-purple-500 text-white' : ''}
                                                        {order.shipping_status === 'delivered' ? 'bg-green-500 text-white' : ''}
                                                        {order.shipping_status === 'cancelled' ? 'bg-red-400 text-white' : ''}
                                                    ">{order.shipping_status}</span>
                                                </td>
                                                <td class="p-4 text-sm text-slate-700">{new Date(order.created_at).toLocaleDateString('id-ID')}</td>
                                                <td class="p-4 text-sm text-slate-900 font-bold text-right">{formatCurrency(Number(order.total_price || 0))}</td>
                                                <td class="p-4 text-center">
                                                    {#if order.shipping_status === 'pending'}
                                                        <button class="px-3 py-1.5 rounded bg-blue-500 text-white text-xs font-bold hover:bg-blue-600 transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed" disabled={processingOrderId === order.id} on:click={() => updateOrderStatus(order.id, 'processing')}>
                                                            {processingOrderId === order.id ? '...' : 'Proses'}
                                                        </button>
                                                    {:else if order.shipping_status === 'processing'}
                                                        <button class="px-3 py-1.5 rounded bg-purple-500 text-white text-xs font-bold hover:bg-purple-600 transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed" disabled={processingOrderId === order.id} on:click={() => updateOrderStatus(order.id, 'shipped')}>
                                                            {processingOrderId === order.id ? '...' : 'Kirim'}
                                                        </button>
                                                    {:else if order.shipping_status === 'shipped'}
                                                        <button class="px-3 py-1.5 rounded bg-green-500 text-white text-xs font-bold hover:bg-green-600 transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed" disabled={processingOrderId === order.id} on:click={() => updateOrderStatus(order.id, 'delivered')}>
                                                            {processingOrderId === order.id ? '...' : 'Selesai'}
                                                        </button>
                                                    {:else}
                                                        <span class="text-slate-400 italic text-sm">-</span>
                                                    {/if}
                                                </td>
                                            </tr>
                                        {/each}
                                    </tbody>
                                </table>
                            {/if}
                        </div>
                    </div>
                {/if}

                {#if activePage === 'produk'}
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                        <div class="mb-6">
                            <h2 class="text-xl font-bold text-slate-800 m-0">Daftar Produk</h2>
                        </div>
                        
                        <div class="grid grid-cols-[repeat(auto-fill,minmax(220px,1fr))] gap-5">
                            <div class="border-2 border-dashed border-gray-300 rounded-xl bg-transparent shadow-none flex flex-col items-center justify-center cursor-pointer min-h-[250px] text-gray-400 transition-all hover:border-emerald-400 hover:text-emerald-500 hover:bg-emerald-50" on:click={() => setPage('tambah-produk')}>
                                <div class="w-[60px] h-[60px] rounded-full bg-gray-100 flex items-center justify-center mb-2.5 transition-colors group-hover:bg-emerald-400 group-hover:text-white">
                                    <svg viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                </div>
                                <span class="font-medium">Tambah Produk</span>
                            </div>

                            {#each myProducts as product}
                                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex flex-col gap-4 transition-transform hover:-translate-y-1 hover:shadow-md">
                                    <div class="w-full h-[140px] bg-gray-50 rounded-lg flex items-center justify-center text-gray-300 font-bold text-lg overflow-hidden">
                                        {#if product.image_url}
                                            <img src={product.image_url} alt={product.title} class="w-full h-full object-cover" />
                                        {:else}
                                            <span>IMG</span>
                                        {/if}
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <h3 class="m-0 text-base text-slate-800 font-semibold line-clamp-2">{product.title}</h3>
                                        <div class="flex justify-between items-center text-sm mt-1">
                                            <span class="font-bold text-emerald-600">{formatCurrency(Number(product.price))}</span>
                                            <span class="text-slate-500 {product.stock_quantity < 5 ? '!text-red-500 font-bold' : ''}">Stok: {product.stock_quantity}</span>
                                        </div>
                                    </div>
                                </div>
                            {/each}
                        </div>
                    </div>
                {/if}

                {#if activePage === 'tambah-produk'}
                    <div class="flex flex-col gap-5">
                        <div class="mb-2">
                            <button class="flex items-center gap-2 bg-transparent border-none font-semibold text-slate-500 cursor-pointer text-base hover:text-slate-800" on:click={() => setPage('produk')}>
                                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                                Kembali ke Produk
                            </button>
                        </div>

                        <div class="bg-white rounded-xl p-8 shadow-sm border border-gray-100">
                            <h2 class="text-xl font-bold text-slate-800 m-0 mb-2">Tambah Produk Baru</h2>
                            <p class="text-slate-500 m-0 mb-6">Lengkapi informasi produk yang ingin Anda jual</p>

                            <form class="flex flex-col gap-5" on:submit|preventDefault={handleCreateProduct}>
                                <div class="flex flex-col gap-2">
                                    <label class="font-medium text-slate-700">Foto Produk</label>
                                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer relative flex flex-col items-center justify-center hover:border-emerald-400 hover:bg-emerald-50 transition-colors">
                                        {#if prodImagePreview}
                                            <img src={prodImagePreview} alt="Preview" class="max-h-[150px] mb-2.5 rounded shadow-sm" />
                                        {:else}
                                            <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="#ccc" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                            <p class="text-gray-400 mt-2">Klik atau seret foto ke sini</p>
                                        {/if}
                                        <input type="file" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" on:change={handleFileSelect} />
                                    </div>
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label for="prod-name" class="font-medium text-slate-700">Nama Produk</label>
                                    <input type="text" id="prod-name" bind:value={prodName} placeholder="Contoh: ASUS ROG Strix G15" required class="p-3 border border-gray-300 rounded-lg text-base focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none" />
                                </div>

                                <div class="flex gap-5 max-sm:flex-col">
                                    <div class="flex flex-col gap-2 flex-1">
                                        <label for="prod-category" class="font-medium text-slate-700">Kategori</label>
                                        <select id="prod-category" bind:value={prodCategory} class="p-3 border border-gray-300 rounded-lg text-base bg-white focus:border-emerald-500 outline-none">
                                            <option value="">Pilih Kategori</option>
                                            <option value="processor">Processor</option>
                                            <option value="motherboard">Motherboard</option>
                                            <option value="cooler">CPU Cooler (HSF)</option>
                                            <option value="vga">VGA Card</option>
                                            <option value="monitor">Monitor</option>
                                            <option value="storage">Storage</option>
                                            <option value="ram">RAM</option>
                                            <option value="casing">Casing</option>
                                            <option value="psu">PSU</option>
                                            <option value="pc-ready">PC Ready</option>
                                        </select>
                                    </div>
                                    <div class="flex flex-col gap-2 flex-1">
                                        <label for="prod-stock" class="font-medium text-slate-700">Stok Awal</label>
                                        <input type="number" id="prod-stock" bind:value={prodStock} placeholder="0" min="0" required class="p-3 border border-gray-300 rounded-lg text-base focus:border-emerald-500 outline-none" />
                                    </div>
                                </div>

                                {#if ['processor', 'motherboard', 'cooler', 'ram'].includes(prodCategory)}
                                    <div class="flex gap-5 max-sm:flex-col bg-sky-50 p-4 rounded-lg border border-dashed border-sky-200">
                                        
                                        {#if ['processor', 'motherboard', 'cooler'].includes(prodCategory)}
                                            <div class="flex flex-col gap-2 flex-1">
                                                <label for="prod-socket" class="font-medium text-sky-800">Socket (Kompatibilitas)</label>
                                                <select id="prod-socket" bind:value={prodSocket} class="p-3 border border-sky-200 rounded-lg text-base bg-white focus:border-sky-500 outline-none">
                                                    <option value="">- Pilih Socket -</option>
                                                    <option value="AM4">AM4</option>
                                                    <option value="AM5">AM5</option>
                                                    <option value="LGA1700">LGA 1700</option>
                                                    <option value="LGA1200">LGA 1200</option>
                                                </select>
                                            </div>
                                        {/if}

                                        {#if ['motherboard', 'ram'].includes(prodCategory)}
                                            <div class="flex flex-col gap-2 flex-1">
                                                <label for="prod-memory" class="font-medium text-sky-800">Tipe Memori</label>
                                                <select id="prod-memory" bind:value={prodMemory} class="p-3 border border-sky-200 rounded-lg text-base bg-white focus:border-sky-500 outline-none">
                                                    <option value="">- Pilih Tipe -</option>
                                                    <option value="DDR4">DDR4</option>
                                                    <option value="DDR5">DDR5</option>
                                                </select>
                                            </div>
                                        {/if}

                                    </div>
                                {/if}

                                <div class="flex flex-col gap-2">
                                    <label for="prod-price" class="font-medium text-slate-700">Harga (Rp)</label>
                                    <input type="number" id="prod-price" bind:value={prodPrice} placeholder="Masukkan harga satuan" min="0" required class="p-3 border border-gray-300 rounded-lg text-base focus:border-emerald-500 outline-none" />
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label for="prod-desc" class="font-medium text-slate-700">Deskripsi Produk</label>
                                    <textarea id="prod-desc" bind:value={prodDesc} rows="5" placeholder="Jelaskan spesifikasi..." class="p-3 border border-gray-300 rounded-lg text-base focus:border-emerald-500 outline-none"></textarea>
                                </div>

                                <div class="mt-2">
                                    <button type="submit" class="bg-emerald-500 text-white border-none py-3 px-6 rounded-lg font-bold cursor-pointer w-full hover:bg-emerald-600 transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed" disabled={isSubmitting}>
                                        {isSubmitting ? 'Menyimpan...' : 'Simpan Produk'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                {/if}

                {#if activePage === 'tarik-tunai'}
                    <div class="flex justify-center">
                        <div class="bg-white rounded-xl p-8 shadow-sm border border-gray-100 max-w-lg w-full">
                            <h2 class="text-xl font-bold text-slate-800 m-0 mb-6">Ajukan Penarikan</h2>
                            <div class="bg-emerald-50 border border-emerald-200 p-5 rounded-lg mb-6">
                                <span class="text-sm font-medium text-emerald-800 block mb-1">Saldo Dapat Ditarik</span>
                                <span class="text-3xl font-bold text-emerald-700">
                                    {#if vendorProfile}
                                        {formatCurrency(Number(vendorProfile.balance))}
                                    {:else}
                                        Rp 0
                                    {/if}
                                </span>
                            </div>
                            <form class="flex flex-col gap-5" on:submit|preventDefault={() => alert("Fitur penarikan belum tersedia saat ini.")}>
                                <div class="flex flex-col gap-2">
                                    <label for="amount" class="font-medium text-slate-700">Nominal Penarikan</label>
                                    <input type="number" id="amount" placeholder="Min. Rp 50.000" class="p-3 border border-gray-300 rounded-lg text-base focus:border-emerald-500 outline-none" />
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label for="bank" class="font-medium text-slate-700">Rekening Tujuan</label>
                                    <select id="bank" class="p-3 border border-gray-300 rounded-lg text-base bg-white focus:border-emerald-500 outline-none">
                                        <option>BCA</option>
                                        <option>Mandiri</option>
                                        <option>BRI</option>
                                    </select>
                                </div>
                                <button type="submit" class="bg-emerald-500 text-white border-none py-3 px-6 rounded-lg font-bold cursor-pointer w-full hover:bg-emerald-600 transition-colors mt-2">Tarik Saldo</button>
                            </form>
                        </div>
                    </div>
                {/if}

                {#if activePage === 'pesanan'}
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                        <div class="mb-6">
                            <h2 class="text-xl font-bold text-slate-800 m-0">Semua Pesanan</h2>
                        </div>
                        <div class="bg-white rounded-lg overflow-x-auto">
                            {#if myOrders.length === 0}
                                <p class="p-5 text-center text-slate-500">Belum ada pesanan.</p>
                            {:else}
                                <table class="w-full border-collapse min-w-[700px]">
                                    <thead>
                                        <tr>
                                            <th class="text-left p-4 bg-gray-50 text-xs text-slate-500 font-bold uppercase tracking-wider">ID Pesanan</th>
                                            <th class="text-left p-4 bg-gray-50 text-xs text-slate-500 font-bold uppercase tracking-wider">Tanggal</th>
                                            <th class="text-left p-4 bg-gray-50 text-xs text-slate-500 font-bold uppercase tracking-wider">Customer</th>
                                            <th class="text-left p-4 bg-gray-50 text-xs text-slate-500 font-bold uppercase tracking-wider">Status</th>
                                            <th class="text-right p-4 bg-gray-50 text-xs text-slate-500 font-bold uppercase tracking-wider">Total</th>
                                            <th class="text-center p-4 bg-gray-50 text-xs text-slate-500 font-bold uppercase tracking-wider">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {#each myOrders as order}
                                            <tr class="border-b border-gray-100 last:border-none hover:bg-gray-50 transition-colors">
                                                <td class="p-4 text-sm text-slate-800 font-mono font-medium">#{order.id}</td>
                                                <td class="p-4 text-sm text-slate-700">{new Date(order.created_at).toLocaleDateString('id-ID')}</td>
                                                <td class="p-4 text-sm text-slate-700 font-medium">{order.parent_order?.user?.name || 'Customer'}</td>
                                                <td class="p-4">
                                                    <span class="px-3 py-1 rounded-full text-xs font-bold capitalize 
                                                        {order.shipping_status === 'pending' ? 'bg-yellow-400 text-white' : ''}
                                                        {order.shipping_status === 'processing' ? 'bg-blue-500 text-white' : ''}
                                                        {order.shipping_status === 'shipped' ? 'bg-purple-500 text-white' : ''}
                                                        {order.shipping_status === 'delivered' ? 'bg-green-500 text-white' : ''}
                                                        {order.shipping_status === 'cancelled' ? 'bg-red-400 text-white' : ''}
                                                    ">{order.shipping_status}</span>
                                                </td>
                                                <td class="p-4 text-sm text-slate-900 font-bold text-right">{formatCurrency(Number(order.total_price))}</td>
                                                <td class="p-4 text-center">
                                                    {#if order.shipping_status === 'pending'}
                                                        <button class="px-3 py-1.5 rounded bg-blue-500 text-white text-xs font-bold hover:bg-blue-600 transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed" disabled={processingOrderId === order.id} on:click={() => updateOrderStatus(order.id, 'processing')}>
                                                            {processingOrderId === order.id ? '...' : 'Proses'}
                                                        </button>
                                                    {:else if order.shipping_status === 'processing'}
                                                        <button class="px-3 py-1.5 rounded bg-purple-500 text-white text-xs font-bold hover:bg-purple-600 transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed" disabled={processingOrderId === order.id} on:click={() => updateOrderStatus(order.id, 'shipped')}>
                                                            {processingOrderId === order.id ? '...' : 'Kirim'}
                                                        </button>
                                                    {:else if order.shipping_status === 'shipped'}
                                                        <button class="px-3 py-1.5 rounded bg-green-500 text-white text-xs font-bold hover:bg-green-600 transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed" disabled={processingOrderId === order.id} on:click={() => updateOrderStatus(order.id, 'delivered')}>
                                                            {processingOrderId === order.id ? '...' : 'Selesai'}
                                                        </button>
                                                    {:else}
                                                        <span class="text-slate-400 italic text-sm">-</span>
                                                    {/if}
                                                </td>
                                            </tr>
                                        {/each}
                                    </tbody>
                                </table>
                            {/if}
                        </div>
                    </div>
                {/if}

            </div>
        </div>
    </div>
</main>