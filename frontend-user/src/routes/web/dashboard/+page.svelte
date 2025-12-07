<script lang="ts">
    // State untuk navigasi halaman
    let activePage = 'dashboard';

    // Mock Data untuk Statistik
    const stats = [
        { label: 'Total Pendapatan', value: 'Rp 15.450.000', increase: true, change: '+12%' },
        { label: 'Pesanan Baru', value: '24', increase: true, change: '+4' },
        { label: 'Produk Terjual', value: '156', increase: true, change: '+8%' },
        { label: 'Stok Menipis', value: '3', increase: false, change: 'Perlu Cek' }
    ];

    // Mock Data Produk Saya
    const myProducts = [
        { id: 1, name: 'RTX 3060 12GB', price: 'Rp 5.200.000', stock: 12, image: 'GPU' },
        { id: 2, name: 'Monitor 24" 144Hz', price: 'Rp 2.100.000', stock: 5, image: 'MON' },
        { id: 3, name: 'Mechanical Keyboard', price: 'Rp 850.000', stock: 20, image: 'KEY' },
        { id: 4, name: 'Mouse Gaming Wireless', price: 'Rp 450.000', stock: 8, image: 'MOU' },
    ];

    // Mock Data Pesanan
    const recentOrders = [
        { id: '#ORD-001', customer: 'Budi Santoso', product: 'RTX 3060 12GB', date: '07 Des 2025', status: 'Selesai', amount: 'Rp 5.200.000' },
        { id: '#ORD-002', customer: 'Siti Aminah', product: 'Monitor 24" 144Hz', date: '07 Des 2025', status: 'Proses', amount: 'Rp 2.100.000' },
    ];

    // Mock Data Penarikan
    const withdrawalHistory = [
        { id: '#WD-001', bank: 'BCA - 123xxxx', date: '05 Des 2025', status: 'Berhasil', amount: 'Rp 2.500.000' },
    ];

    function setPage(page: string) {
        activePage = page;
    }
</script>

<svelte:head>
    <title>Dashboard Vendor - PC Store</title>
</svelte:head>

<main class="dashboard-page">
    <div class="main-container">
        
        <div class="page-header">
            <h1>Dashboard Vendor</h1>
            <p>Ringkasan aktivitas toko Anda hari ini</p>
        </div>
        
        <div class="layout-grid">
            <aside class="sidebar">
                <nav class="nav-menu">
                    <button 
                        class="nav-item {activePage === 'dashboard' ? 'active' : ''}"
                        on:click={() => setPage('dashboard')}
                    >
                        <span>Dashboard</span>
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    </button>

                    <button 
                        class="nav-item {activePage === 'produk' || activePage === 'tambah-produk' ? 'active' : ''}"
                        on:click={() => setPage('produk')}
                    >
                        <span>Produk Saya</span>
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                    </button>

                    <button 
                        class="nav-item {activePage === 'pesanan' ? 'active' : ''}"
                        on:click={() => setPage('pesanan')}
                    >
                        <span>Pesanan</span>
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                    </button>

                    <button 
                        class="nav-item {activePage === 'tarik-tunai' ? 'active' : ''}"
                        on:click={() => setPage('tarik-tunai')}
                    >
                        <span>Tarik Tunai</span>
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"></path><path d="M4 6v12a2 2 0 0 0 2 2h14v-4"></path><path d="M18 12a2 2 0 0 0 0 4h4v-4h-4z"></path></svg>
                    </button>

                    <button class="nav-item logout">
                        <span>Keluar</span>
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    </button>
                </nav>
            </aside>

            <div class="content-area">
                
                {#if activePage === 'dashboard'}
                    <div class="stats-grid">
                        {#each stats as stat}
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
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID Pesanan</th>
                                        <th>Pelanggan</th>
                                        <th>Produk</th>
                                        <th>Status</th>
                                        <th class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {#each recentOrders as order}
                                        <tr>
                                            <td class="order-id">{order.id}</td>
                                            <td>{order.customer}</td>
                                            <td class="product-col">{order.product}</td>
                                            <td><span class="status-badge {order.status.toLowerCase()}">{order.status}</span></td>
                                            <td class="text-right font-bold">{order.amount}</td>
                                        </tr>
                                    {/each}
                                </tbody>
                            </table>
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
                                    <svg viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                    </svg>
                                </div>
                                <span>Tambah Produk</span>
                            </div>

                            {#each myProducts as product}
                                <div class="product-card">
                                    <div class="product-image-placeholder">
                                        <span>{product.image}</span>
                                    </div>
                                    <div class="product-info">
                                        <h3>{product.name}</h3>
                                        <div class="product-meta">
                                            <span class="price">{product.price}</span>
                                            <span class="stock">Stok: {product.stock}</span>
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

                            <form class="product-form">
                                <div class="form-group">
                                    <label>Foto Produk</label>
                                    <div class="image-upload-area">
                                        <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="#ccc" stroke-width="1.5">
                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                            <polyline points="21 15 16 10 5 21"></polyline>
                                        </svg>
                                        <p>Klik atau seret foto ke sini</p>
                                        <input type="file" accept="image/*" />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="prod-name">Nama Produk</label>
                                    <input type="text" id="prod-name" placeholder="Contoh: ASUS ROG Strix G15" />
                                </div>

                                <div class="form-row">
                                    <div class="form-group half">
                                        <label for="prod-category">Kategori</label>
                                        <select id="prod-category">
                                            <option value="">Pilih Kategori</option>
                                            <option value="processor">Processor</option>
                                            <option value="vga">VGA Card</option>
                                            <option value="monitor">Monitor</option>
                                        </select>
                                    </div>
                                    <div class="form-group half">
                                        <label for="prod-stock">Stok Awal</label>
                                        <input type="number" id="prod-stock" placeholder="0" />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="prod-price">Harga (Rp)</label>
                                    <input type="number" id="prod-price" placeholder="Masukkan harga satuan" />
                                </div>

                                <div class="form-group">
                                    <label for="prod-desc">Deskripsi Produk</label>
                                    <textarea id="prod-desc" rows="5" placeholder="Jelaskan spesifikasi..."></textarea>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn-submit">Simpan Produk</button>
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
                                <span class="value">Rp 15.450.000</span>
                            </div>
                            <form class="wd-form">
                                <div class="form-group">
                                    <label for="amount">Nominal Penarikan</label>
                                    <input type="number" id="amount" placeholder="Min. Rp 50.000" />
                                </div>
                                <div class="form-group">
                                    <label for="bank">Rekening Tujuan</label>
                                    <select id="bank">
                                        <option>BCA - 1234567890 (a.n Budi)</option>
                                        <option>Mandiri - 0987654321 (a.n Budi)</option>
                                    </select>
                                </div>
                                <button type="button" class="btn-submit">Tarik Saldo</button>
                            </form>
                        </div>

                        <div class="withdraw-history-card">
                            <h2>Riwayat Penarikan</h2>
                            <div class="table-container">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Bank</th>
                                            <th>Status</th>
                                            <th class="text-right">Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {#each withdrawalHistory as history}
                                            <tr>
                                                <td class="order-id">{history.id}</td>
                                                <td>{history.bank}</td>
                                                <td><span class="status-badge {history.status.toLowerCase()}">{history.status}</span></td>
                                                <td class="text-right font-bold">{history.amount}</td>
                                            </tr>
                                        {/each}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                {/if}

                {#if activePage === 'pesanan'}
                    <div class="placeholder-content">
                        <h2>Halaman Pesanan</h2>
                        <p>Daftar lengkap pesanan akan muncul di sini.</p>
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
    
    /* Header Styles (DITAMBAHKAN) */
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
    .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
    .status-badge.selesai, .status-badge.berhasil { background: #4ADE80; color: white; }
    .status-badge.proses { background: #fbbf24; color: #fff; }

    /* --- STYLE UNTUK GRID PRODUK & KOTAK TAMBAH --- */
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 20px;
    }

    .product-card {
        background: white;
        border-radius: 12px;
        padding: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        display: flex;
        flex-direction: column;
        gap: 15px;
        transition: transform 0.2s;
    }

    /* Style Khusus Kotak Tambah Produk */
    .product-card.add-new-card {
        border: 2px dashed #ccc; /* Kotak putus-putus */
        background: transparent;
        box-shadow: none;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        min-height: 250px; /* Samakan tinggi dengan kartu produk lain */
        color: #666;
    }

    .product-card.add-new-card:hover {
        border-color: #4ADE80;
        color: #4ADE80;
        background: #f0fdf4;
    }

    .add-icon-wrapper {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: #eee;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
        transition: background 0.2s;
    }
    
    .product-card.add-new-card:hover .add-icon-wrapper {
        background: #4ADE80;
        color: white;
    }

    /* Style Kartu Produk Biasa */
    .product-image-placeholder {
        width: 100%;
        height: 140px;
        background: #f1f1f1;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #999;
        font-weight: 700;
        font-size: 1.2rem;
    }
    .product-info h3 { margin: 0 0 5px 0; font-size: 1rem; color: #333; }
    .product-meta { display: flex; justify-content: space-between; font-size: 0.9rem; }
    .product-meta .price { font-weight: 700; color: #4ADE80; }
    .product-meta .stock { color: #888; }

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
    
    .image-upload-area { border: 2px dashed #ccc; border-radius: 8px; padding: 30px; text-align: center; cursor: pointer; position: relative; }
    .image-upload-area:hover { border-color: #4ADE80; background: #f0fdf4; }
    .image-upload-area input { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }

    .btn-submit { background: #4ADE80; color: white; border: none; padding: 12px 25px; border-radius: 6px; font-weight: 600; cursor: pointer; width: 100%; }
    .btn-submit:hover { background: #3ec770; }

    /* Withdraw Styles */
    .balance-display { background: #f0fdf4; border: 1px solid #dcfce7; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
    .balance-display .label { font-size: 0.9rem; color: #166534; font-weight: 500; display: block; }
    .balance-display .value { font-size: 1.8rem; font-weight: 700; color: #166534; }
    
    .placeholder-content { padding: 40px; text-align: center; background: white; border-radius: 12px; color: #666; }

    @media (max-width: 900px) {
        .layout-grid { grid-template-columns: 1fr; }
        .nav-menu { flex-direction: row; overflow-x: auto; }
        .form-row { flex-direction: column; gap: 20px; }
    }
</style>