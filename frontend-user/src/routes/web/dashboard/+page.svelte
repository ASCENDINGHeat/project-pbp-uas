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
            // Construct 'specs' string for the simulator checker (e.g. "AM5, DDR5")
            let specsArr: string[] = [];
            if (prodSocket) specsArr.push(prodSocket);
            if (prodMemory) specsArr.push(prodMemory);
            
            if (specsArr.length > 0) {
                formData.append('details[specs]', specsArr.join(', '));
            }
            // Store structured data as well
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
                if (vendorProfile?.id) fetchProducts(vendorProfile.id); // Refresh list
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

<main class="dashboard-page">
    <div class="main-container">
        
        <div class="page-header">
            <h1>Dashboard Vendor</h1>
            {#if vendorProfile}
                <p>Halo, <strong>{vendorProfile.store_name}</strong>! Berikut ringkasan tokomu.</p>
            {:else}
                <p>Ringkasan aktivitas toko Anda hari ini</p>
            {/if}
        </div>
        
        <div class="layout-grid">
            <aside class="sidebar">
                <nav class="nav-menu">
                    <button class="nav-item {activePage === 'dashboard' ? 'active' : ''}" on:click={() => setPage('dashboard')}>
                        <span>Dashboard</span>
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    </button>
                    <button class="nav-item {activePage === 'produk' || activePage === 'tambah-produk' ? 'active' : ''}" on:click={() => setPage('produk')}>
                        <span>Produk Saya</span>
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                    </button>
                    <button class="nav-item {activePage === 'pesanan' ? 'active' : ''}" on:click={() => setPage('pesanan')}>
                        <span>Pesanan</span>
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                    </button>
                    <button class="nav-item {activePage === 'tarik-tunai' ? 'active' : ''}" on:click={() => setPage('tarik-tunai')}>
                        <span>Tarik Tunai</span>
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"></path><path d="M4 6v12a2 2 0 0 0 2 2h14v-4"></path><path d="M18 12a2 2 0 0 0 0 4h4v-4h-4z"></path></svg>
                    </button>
                    <button class="nav-item logout" on:click={() => { localStorage.removeItem('auth_token'); goto('/login'); }}>
                        <span>Keluar</span>
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    </button>
                </nav>
            </aside>

            <div class="content-area">
                
                {#if activePage === 'dashboard'}
                    <div class="stats-grid">
                        {#each dashboardStats as stat}
                            <div class="stat-card">
                                <div class="stat-header">
                                    <span class="stat-label">{stat.label}</span>
                                    <span class="stat-change {stat.increase ? 'positive' : 'negative'}">
                                        {stat.change}
                                    </span>
                                </div>
                                <div class="stat-value">{stat.value}</div>
                            </div>
                        {/each}
                    </div>

                    <div class="orders-section">
                        <div class="section-header">
                            <h2>Pesanan Terbaru</h2>
                        </div>
                        <div class="table-container">
                            {#if isLoading}
                                <div style="padding:20px; text-align:center;">Memuat data...</div>
                            {:else if recentOrders.length === 0}
                                <div style="padding:20px; text-align:center; color:#888;">Belum ada pesanan masuk.</div>
                            {:else}
                                <table>
                                    <thead>
                                        <tr>
                                            <th>ID Pesanan</th>
                                            <th>Status</th>
                                            <th>Tanggal</th>
                                            <th class="text-right">Total</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {#each recentOrders as order}
                                            <tr>
                                                <td class="order-id">#{order.id}</td>
                                                <td><span class="status-badge {order.shipping_status?.toLowerCase()}">{order.shipping_status}</span></td>
                                                <td>{new Date(order.created_at).toLocaleDateString('id-ID')}</td>
                                                <td class="text-right font-bold">{formatCurrency(Number(order.total_price || 0))}</td>
                                                <td class="text-center">
                                                    {#if order.shipping_status === 'pending'}
                                                        <button class="btn-action process" disabled={processingOrderId === order.id} on:click={() => updateOrderStatus(order.id, 'processing')}>
                                                            {processingOrderId === order.id ? '...' : 'Proses'}
                                                        </button>
                                                    {:else if order.shipping_status === 'processing'}
                                                        <button class="btn-action ship" disabled={processingOrderId === order.id} on:click={() => updateOrderStatus(order.id, 'shipped')}>
                                                            {processingOrderId === order.id ? '...' : 'Kirim'}
                                                        </button>
                                                    {:else if order.shipping_status === 'shipped'}
                                                        <button class="btn-action done" disabled={processingOrderId === order.id} on:click={() => updateOrderStatus(order.id, 'delivered')}>
                                                            {processingOrderId === order.id ? '...' : 'Selesai'}
                                                        </button>
                                                    {:else}
                                                        <span class="text-muted">-</span>
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
                    <div class="products-container">
                        <div class="section-header">
                            <h2>Daftar Produk</h2>
                        </div>
                        
                        <div class="products-grid">
                            <div class="product-card add-new-card" on:click={() => setPage('tambah-produk')}>
                                <div class="add-icon-wrapper">
                                    <svg viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                </div>
                                <span>Tambah Produk</span>
                            </div>

                            {#each myProducts as product}
                                <div class="product-card">
                                    <div class="product-image-placeholder">
                                        {#if product.image_url}
                                            <img src={product.image_url} alt={product.title} />
                                        {:else}
                                            <span>IMG</span>
                                        {/if}
                                    </div>
                                    <div class="product-info">
                                        <h3>{product.title}</h3>
                                        <div class="product-meta">
                                            <span class="price">{formatCurrency(Number(product.price))}</span>
                                            <span class="stock {product.stock_quantity < 5 ? 'text-danger' : ''}">Stok: {product.stock_quantity}</span>
                                        </div>
                                    </div>
                                </div>
                            {/each}
                        </div>
                    </div>
                {/if}

                {#if activePage === 'tambah-produk'}
                    <div class="add-product-container">
                        <div class="back-button-wrapper">
                            <button class="btn-back" on:click={() => setPage('produk')}>
                                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                                Kembali ke Produk
                            </button>
                        </div>

                        <div class="product-form-card">
                            <h2>Tambah Produk Baru</h2>
                            <p class="form-subtitle">Lengkapi informasi produk yang ingin Anda jual</p>

                            <form class="product-form" on:submit|preventDefault={handleCreateProduct}>
                                <div class="form-group">
                                    <label>Foto Produk</label>
                                    <div class="image-upload-area">
                                        {#if prodImagePreview}
                                            <img src={prodImagePreview} alt="Preview" style="max-height: 150px; margin-bottom: 10px; border-radius: 4px;" />
                                        {:else}
                                            <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="#ccc" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                            <p>Klik atau seret foto ke sini</p>
                                        {/if}
                                        <input type="file" accept="image/*" on:change={handleFileSelect} />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="prod-name">Nama Produk</label>
                                    <input type="text" id="prod-name" bind:value={prodName} placeholder="Contoh: ASUS ROG Strix G15" required />
                                </div>

                                <div class="form-row">
                                    <div class="form-group half">
                                        <label for="prod-category">Kategori</label>
                                        <select id="prod-category" bind:value={prodCategory}>
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
                                    <div class="form-group half">
                                        <label for="prod-stock">Stok Awal</label>
                                        <input type="number" id="prod-stock" bind:value={prodStock} placeholder="0" min="0" required />
                                    </div>
                                </div>

                                {#if ['processor', 'motherboard', 'cooler', 'ram'].includes(prodCategory)}
                                    <div class="form-row" style="background: #f0f9ff; padding: 15px; border-radius: 8px; border: 1px dashed #bae6fd;">
                                        
                                        {#if ['processor', 'motherboard', 'cooler'].includes(prodCategory)}
                                            <div class="form-group half">
                                                <label for="prod-socket">Socket (Kompatibilitas)</label>
                                                <select id="prod-socket" bind:value={prodSocket}>
                                                    <option value="">- Pilih Socket -</option>
                                                    <option value="AM4">AM4</option>
                                                    <option value="AM5">AM5</option>
                                                    <option value="LGA1700">LGA 1700</option>
                                                    <option value="LGA1200">LGA 1200</option>
                                                </select>
                                            </div>
                                        {/if}

                                        {#if ['motherboard', 'ram'].includes(prodCategory)}
                                            <div class="form-group half">
                                                <label for="prod-memory">Tipe Memori</label>
                                                <select id="prod-memory" bind:value={prodMemory}>
                                                    <option value="">- Pilih Tipe -</option>
                                                    <option value="DDR4">DDR4</option>
                                                    <option value="DDR5">DDR5</option>
                                                </select>
                                            </div>
                                        {/if}

                                    </div>
                                {/if}

                                <div class="form-group">
                                    <label for="prod-price">Harga (Rp)</label>
                                    <input type="number" id="prod-price" bind:value={prodPrice} placeholder="Masukkan harga satuan" min="0" required />
                                </div>

                                <div class="form-group">
                                    <label for="prod-desc">Deskripsi Produk</label>
                                    <textarea id="prod-desc" bind:value={prodDesc} rows="5" placeholder="Jelaskan spesifikasi..."></textarea>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn-submit" disabled={isSubmitting}>
                                        {isSubmitting ? 'Menyimpan...' : 'Simpan Produk'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                {/if}

                {#if activePage === 'tarik-tunai'}
                    <div class="withdraw-layout">
                        <div class="withdraw-form-card">
                            <h2>Ajukan Penarikan</h2>
                            <div class="balance-display">
                                <span class="label">Saldo Dapat Ditarik</span>
                                <span class="value">
                                    {#if vendorProfile}
                                        {formatCurrency(Number(vendorProfile.balance))}
                                    {:else}
                                        Rp 0
                                    {/if}
                                </span>
                            </div>
                            <form class="wd-form" on:submit|preventDefault={() => alert("Fitur penarikan belum tersedia saat ini.")}>
                                <div class="form-group">
                                    <label for="amount">Nominal Penarikan</label>
                                    <input type="number" id="amount" placeholder="Min. Rp 50.000" />
                                </div>
                                <div class="form-group">
                                    <label for="bank">Rekening Tujuan</label>
                                    <select id="bank">
                                        <option>BCA</option>
                                        <option>Mandiri</option>
                                        <option>BRI</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn-submit">Tarik Saldo</button>
                            </form>
                        </div>
                    </div>
                {/if}

                {#if activePage === 'pesanan'}
                    <div class="orders-section">
                        <div class="section-header">
                            <h2>Semua Pesanan</h2>
                        </div>
                        <div class="table-container">
                            {#if myOrders.length === 0}
                                <p style="padding: 20px; text-align: center; color: #666;">Belum ada pesanan.</p>
                            {:else}
                                <table>
                                    <thead>
                                        <tr>
                                            <th>ID Pesanan</th>
                                            <th>Tanggal</th>
                                            <th>Customer</th>
                                            <th>Status</th>
                                            <th class="text-right">Total</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {#each myOrders as order}
                                            <tr>
                                                <td class="order-id">#{order.id}</td>
                                                <td>{new Date(order.created_at).toLocaleDateString('id-ID')}</td>
                                                <td>{order.parent_order?.user?.name || 'Customer'}</td>
                                                <td><span class="status-badge {order.shipping_status?.toLowerCase()}">{order.shipping_status}</span></td>
                                                <td class="text-right font-bold">{formatCurrency(Number(order.total_price))}</td>
                                                <td class="text-center">
                                                    {#if order.shipping_status === 'pending'}
                                                        <button class="btn-action process" disabled={processingOrderId === order.id} on:click={() => updateOrderStatus(order.id, 'processing')}>
                                                            {processingOrderId === order.id ? '...' : 'Proses'}
                                                        </button>
                                                    {:else if order.shipping_status === 'processing'}
                                                        <button class="btn-action ship" disabled={processingOrderId === order.id} on:click={() => updateOrderStatus(order.id, 'shipped')}>
                                                            {processingOrderId === order.id ? '...' : 'Kirim'}
                                                        </button>
                                                    {:else if order.shipping_status === 'shipped'}
                                                        <button class="btn-action done" disabled={processingOrderId === order.id} on:click={() => updateOrderStatus(order.id, 'delivered')}>
                                                            {processingOrderId === order.id ? '...' : 'Selesai'}
                                                        </button>
                                                    {:else}
                                                        <span class="text-muted">-</span>
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

<style>
    /* Global & Layout */
    .dashboard-page { min-height: 100vh; background: #f8f9fa; padding: 40px 20px; }
    .main-container { max-width: 1200px; margin: 0 auto; }
    
    /* Header Styles */
    .page-header { margin-bottom: 30px; }
    .page-header h1 { font-size: 2rem; font-weight: 700; color: #1f2d3d; margin: 0 0 5px; }
    .page-header p { color: #666; margin: 0; }

    .layout-grid { display: grid; grid-template-columns: 280px 1fr; gap: 30px; }
    
    /* Sidebar */
    .sidebar { height: fit-content; }
    .nav-menu { display: flex; flex-direction: column; gap: 10px; }
    .nav-item { display: flex; align-items: center; justify-content: space-between; padding: 15px 20px; background: #EAEAEA; border: none; border-radius: 8px; color: #333; font-size: 1rem; font-weight: 500; cursor: pointer; transition: all 0.2s; text-align: left; width: 100%; }
    .nav-item:hover { background: #d4d4d4; }
    .nav-item.active { background: #dcdcdc; font-weight: 600; }
    .nav-item.logout { background: #FF4444; color: white; margin-top: 20px; }
    .nav-item.logout:hover { background: #e03333; }

    /* Content Generic */
    .content-area { display: flex; flex-direction: column; gap: 30px; }
    .section-header { margin-bottom: 20px; }
    .section-header h2 { font-size: 1.25rem; font-weight: 700; color: #1f2d3d; margin: 0; }
    
    /* Stats */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
    .stat-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .stat-header { display: flex; justify-content: space-between; margin-bottom: 10px; }
    .stat-label { font-size: 0.9rem; color: #666; }
    .stat-value { font-size: 1.5rem; font-weight: 700; color: #1f2d3d; }
    .stat-change { font-size: 0.8rem; font-weight: 600; padding: 2px 8px; border-radius: 12px; }
    .stat-change.positive { background: #dcfce7; color: #166534; }
    .stat-change.negative { background: #fee2e2; color: #991b1b; }

    /* Tables */
    .orders-section, .withdraw-history-card { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .orders-section { background: #D9D9D9; }
    .table-container { background: white; border-radius: 8px; overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; min-width: 600px; }
    th { text-align: left; padding: 15px 20px; background: #f1f1f1; font-size: 0.85rem; color: #666; font-weight: 600; text-transform: uppercase; }
    td { padding: 15px 20px; border-bottom: 1px solid #eee; font-size: 0.95rem; color: #333; }
    tr:last-child td { border-bottom: none; }
    .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; text-transform: capitalize; }
    .status-badge.delivered, .status-badge.success { background: #4ADE80; color: white; }
    .status-badge.pending { background: #fbbf24; color: #fff; }
    .status-badge.processing { background: #3b82f6; color: #fff; }
    .status-badge.shipped { background: #8b5cf6; color: #fff; }
    .status-badge.cancelled { background: #f87171; color: white; }

    /* Buttons */
    .btn-action { padding: 6px 12px; border: none; border-radius: 6px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: 0.2s; color: white; }
    .btn-action.process { background-color: #3b82f6; } /* Blue */
    .btn-action.process:hover { background-color: #2563eb; }
    .btn-action.ship { background-color: #8b5cf6; } /* Purple */
    .btn-action.ship:hover { background-color: #7c3aed; }
    .btn-action.done { background-color: #10b981; } /* Green */
    .btn-action.done:hover { background-color: #059669; }
    .btn-action:disabled { background-color: #ccc; cursor: not-allowed; }
    .text-muted { color: #999; font-style: italic; }

    /* --- STYLE UNTUK GRID PRODUK & KOTAK TAMBAH --- */
    .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; }

    .product-card { background: white; border-radius: 12px; padding: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); display: flex; flex-direction: column; gap: 15px; transition: transform 0.2s; }

    /* Style Khusus Kotak Tambah Produk */
    .product-card.add-new-card { border: 2px dashed #ccc; background: transparent; box-shadow: none; align-items: center; justify-content: center; cursor: pointer; min-height: 250px; color: #666; }
    .product-card.add-new-card:hover { border-color: #4ADE80; color: #4ADE80; background: #f0fdf4; }

    .add-icon-wrapper { width: 60px; height: 60px; border-radius: 50%; background: #eee; display: flex; align-items: center; justify-content: center; margin-bottom: 10px; transition: background 0.2s; }
    .product-card.add-new-card:hover .add-icon-wrapper { background: #4ADE80; color: white; }

    /* Style Kartu Produk Biasa */
    .product-image-placeholder { width: 100%; height: 140px; background: #f1f1f1; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #999; font-weight: 700; font-size: 1.2rem; overflow: hidden; }
    .product-image-placeholder img { width: 100%; height: 100%; object-fit: cover; }

    .product-info h3 { margin: 0 0 5px 0; font-size: 1rem; color: #333; }
    .product-meta { display: flex; justify-content: space-between; font-size: 0.9rem; }
    .product-meta .price { font-weight: 700; color: #4ADE80; }
    .product-meta .stock { color: #888; }
    .text-danger { color: #e11d48 !important; font-weight: bold; }

    /* --- FORM TAMBAH PRODUK --- */
    .add-product-container { display: flex; flex-direction: column; gap: 20px; }
    .back-button-wrapper { margin-bottom: 10px; }
    .btn-back { display: flex; align-items: center; gap: 8px; background: none; border: none; font-weight: 600; color: #666; cursor: pointer; font-size: 1rem; }
    .btn-back:hover { color: #333; }
    
    .product-form-card, .withdraw-form-card { background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .product-form, .wd-form { display: flex; flex-direction: column; gap: 20px; }
    .form-group { display: flex; flex-direction: column; gap: 8px; }
    .form-group input, .form-group select, .form-group textarea { padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 1rem; }
    .form-row { display: flex; gap: 20px; }
    .form-group.half { flex: 1; }
    
    .image-upload-area { border: 2px dashed #ccc; border-radius: 8px; padding: 30px; text-align: center; cursor: pointer; position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; }
    .image-upload-area:hover { border-color: #4ADE80; background: #f0fdf4; }
    .image-upload-area input { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }

    .btn-submit { background: #4ADE80; color: white; border: none; padding: 12px 25px; border-radius: 6px; font-weight: 600; cursor: pointer; width: 100%; }
    .btn-submit:hover { background: #3ec770; }
    .btn-submit:disabled { background: #ccc; cursor: not-allowed; }

    /* Withdraw Styles */
    .balance-display { background: #f0fdf4; border: 1px solid #dcfce7; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
    .balance-display .label { font-size: 0.9rem; color: #166534; font-weight: 500; display: block; }
    .balance-display .value { font-size: 1.8rem; font-weight: 700; color: #166534; }

    @media (max-width: 900px) {
        .layout-grid { grid-template-columns: 1fr; }
        .nav-menu { flex-direction: row; overflow-x: auto; }
        .form-row { flex-direction: column; gap: 20px; }
    }
</style>