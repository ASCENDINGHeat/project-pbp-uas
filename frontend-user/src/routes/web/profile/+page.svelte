<script lang="ts">
    import { onMount } from 'svelte';
    import { goto } from '$app/navigation';
    import { PUBLIC_API_URL } from '$env/static/public';

    // --- State ---
    let activeTab = 'profile'; // 'profile' | 'orders'
    let isLoading = true;
    
    // Data User
    let userProfile = {
        name: '',
        email: '',
        phone_number: '-',
        address: '-'
    };

    // Data Order
    let orders: any[] = [];
    let orderPagination: any = {};

    // --- Helpers ---
    function formatRupiah(number: number) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
    }

    function formatDate(dateString: string) {
        const options: Intl.DateTimeFormatOptions = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
        return new Date(dateString).toLocaleDateString('id-ID', options);
    }

    // Mapping Status dari Backend (1, 2, 3) ke Teks & Warna
    const getStatusInfo = (status: string | number) => {
        // Konversi ke string untuk aman
        const s = String(status);
        if (s === '1') return { label: 'Menunggu Pembayaran', class: 'status-pending' };
        if (s === '2') return { label: 'Lunas', class: 'status-success' };
        if (s === '3') return { label: 'Dibatalkan / Kadaluarsa', class: 'status-failed' };
        return { label: 'Unknown', class: 'status-default' };
    };

    // --- Fetch Data ---
    onMount(async () => {
        const token = localStorage.getItem('auth_token');
        
        if (!token) {
            alert("Silakan login terlebih dahulu.");
            goto('/web/login');
            return;
        }

        try {
            // 1. Fetch Data User
            const userRes = await fetch(`${PUBLIC_API_URL}/user`, {
                headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
            });

            if (userRes.ok) {
                userProfile = await userRes.json();
            } else {
                // Token expired
                localStorage.removeItem('auth_token');
                goto('/web/login');
                return;
            }

            // 2. Fetch Riwayat Pesanan
            const orderRes = await fetch(`${PUBLIC_API_URL}/orders`, {
                headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
            });

            if (orderRes.ok) {
                const data = await orderRes.json();
                // Backend mengembalikan pagination, data pesanan ada di properti 'data'
                orders = data.data || [];
                orderPagination = data; 
            }

        } catch (error) {
            console.error("Gagal memuat data:", error);
        } finally {
            isLoading = false;
        }
    });

    // Fungsi Lanjut Bayar (Jika status masih pending dan ada snap_token)
    function payOrder(snapToken: string) {
        if(snapToken) {
            window.open(`https://app.sandbox.midtrans.com/snap/v2/vtweb/${snapToken}`, '_blank');
        } else {
            alert("Token pembayaran tidak ditemukan.");
        }
    }
</script>

<svelte:head>
    <title>Profil Saya - PC Store</title>
</svelte:head>

<div class="breadcrumb-wrapper">
    <div class="breadcrumb-pill">
        <a class="breadcrumb-link" href="/web">Home</a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">Profil Pengguna</span>
    </div>
</div>

<main class="profile-page">
    <div class="container">
        
        <div class="profile-layout">
            <aside class="sidebar-card">
                <div class="user-brief">
                    <div class="avatar-circle">
                        {userProfile.name.charAt(0).toUpperCase()}
                    </div>
                    <h3>{userProfile.name}</h3>
                    <p>{userProfile.email}</p>
                </div>
                <nav class="nav-menu">
                    <button 
                        class="nav-item {activeTab === 'profile' ? 'active' : ''}" 
                        on:click={() => activeTab = 'profile'}>
                        👤 Biodata Diri
                    </button>
                    <button 
                        class="nav-item {activeTab === 'orders' ? 'active' : ''}" 
                        on:click={() => activeTab = 'orders'}>
                        📦 Riwayat Pesanan
                    </button>
                    <button class="nav-item logout" on:click={() => {
                        localStorage.removeItem('auth_token');
                        goto('/web/login');
                    }}>
                        🚪 Logout
                    </button>
                </nav>
            </aside>

            <section class="content-card">
                {#if isLoading}
                    <div class="loading-state">Memuat data...</div>
                {:else}
                    
                    {#if activeTab === 'profile'}
                        <header class="content-header">
                            <h2>Biodata Diri</h2>
                            <p class="subtitle">Informasi pribadi dan alamat pengiriman Anda.</p>
                        </header>
                        
                        <div class="info-grid">
                            <div class="info-group">
                                <label>Nama Lengkap</label>
                                <div class="info-value">{userProfile.name}</div>
                            </div>
                            <div class="info-group">
                                <label>Email</label>
                                <div class="info-value">{userProfile.email}</div>
                            </div>
                            <div class="info-group">
                                <label>Nomor Telepon</label>
                                <div class="info-value">{userProfile.phone_number || '-'}</div>
                            </div>
                            <div class="info-group full-width">
                                <label>Alamat Lengkap</label>
                                <div class="info-value address-box">
                                    {userProfile.address || 'Alamat belum diatur.'}
                                </div>
                            </div>
                        </div>

                    {:else if activeTab === 'orders'}
                        <header class="content-header">
                            <h2>Riwayat Pesanan</h2>
                            <p class="subtitle">Daftar transaksi yang pernah Anda lakukan.</p>
                        </header>

                        {#if orders.length === 0}
                            <div class="empty-orders">
                                <p>Belum ada pesanan.</p>
                                <button class="btn-shop" on:click={() => goto('/web')}>Mulai Belanja</button>
                            </div>
                        {:else}
                            <div class="order-list">
                                {#each orders as order}
                                    {@const status = getStatusInfo(order.payment_status)}
                                    <div class="order-card">
                                        <div class="order-header">
                                            <div class="order-id">ID Pesanan: #{order.id}</div>
                                            <div class="order-date">{formatDate(order.created_at)}</div>
                                        </div>
                                        
                                        <div class="order-body">
                                            <div class="order-total">
                                                Total Belanja: <strong>{formatRupiah(order.total_amount)}</strong>
                                            </div>
                                            <div class="order-status">
                                                Status: <span class="badge {status.class}">{status.label}</span>
                                            </div>
                                        </div>

                                        <div class="order-footer">
                                            {#if String(order.payment_status) === '1'}
                                                <button class="btn-pay" on:click={() => payOrder(order.snap_token)}>
                                                    Bayar Sekarang
                                                </button>
                                            {/if}
                                            <span class="note">*Detail produk dikirim ke email vendor</span>
                                        </div>
                                    </div>
                                {/each}
                            </div>
                        {/if}
                    {/if}

                {/if}
            </section>
        </div>

    </div>
</main>

<style>
    /* --- Layout & Reset --- */
    :global(body) { background-color: #f8fafc; font-family: 'Segoe UI', sans-serif; }
    
    .breadcrumb-wrapper { width: 95%; margin: 20px auto; display: flex; }
    .breadcrumb-pill { background: #0f172a; padding: 8px 16px; border-radius: 50px; display: inline-flex; align-items: center; gap: 8px; color: #94a3b8; font-size: 0.9rem; }
    .breadcrumb-link { color: #94a3b8; text-decoration: none; }
    .breadcrumb-current { color: #8E42E1; font-weight: 700; }

    .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
    
    .profile-layout {
        display: flex; gap: 30px;
        align-items: flex-start;
    }

    /* --- Sidebar --- */
    .sidebar-card {
        flex: 0 0 280px;
        background: white; border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    .user-brief {
        padding: 30px 20px; text-align: center;
        background: linear-gradient(135deg, #1e293b, #0f172a); color: white;
    }
    .avatar-circle {
        width: 80px; height: 80px; background: #8E42E1; color: white;
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
        font-size: 2.5rem; font-weight: 700; margin: 0 auto 15px;
        border: 4px solid rgba(255,255,255,0.2);
    }
    .user-brief h3 { margin: 0; font-size: 1.1rem; font-weight: 700; }
    .user-brief p { margin: 5px 0 0; font-size: 0.9rem; opacity: 0.8; }

    .nav-menu { padding: 15px; display: flex; flex-direction: column; gap: 5px; }
    .nav-item {
        text-align: left; background: none; border: none; padding: 12px 15px;
        border-radius: 8px; cursor: pointer; color: #64748b; font-weight: 600;
        transition: all 0.2s; font-size: 0.95rem; width: 100%;
    }
    .nav-item:hover { background: #f1f5f9; color: #334155; }
    .nav-item.active { background: #f3e8ff; color: #8E42E1; }
    .nav-item.logout { color: #ef4444; margin-top: 10px; border-top: 1px solid #f1f5f9; border-radius: 0; }
    .nav-item.logout:hover { background: #fef2f2; }

    /* --- Content --- */
    .content-card {
        flex: 1; background: white; border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05); padding: 30px;
        min-height: 400px;
    }
    .content-header { margin-bottom: 30px; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; }
    .content-header h2 { margin: 0; color: #1e293b; font-size: 1.5rem; font-weight: 800; }
    .content-header .subtitle { margin: 5px 0 0; color: #64748b; font-size: 0.95rem; }

    /* --- Profile Info --- */
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .info-group label { display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 6px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
    .info-value { font-size: 1.05rem; color: #334155; font-weight: 500; padding: 10px 0; border-bottom: 1px dashed #e2e8f0; }
    .address-box { line-height: 1.6; border-bottom: none; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #f1f5f9; }
    .full-width { grid-column: 1 / -1; }

    /* --- Order List --- */
    .order-list { display: flex; flex-direction: column; gap: 20px; }
    .order-card { border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; transition: all 0.2s; }
    .order-card:hover { border-color: #cbd5e1; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    
    .order-header { background: #f8fafc; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; font-size: 0.9rem; color: #64748b; }
    .order-id { font-weight: 700; color: #334155; }
    
    .order-body { padding: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
    .order-total { font-size: 1.1rem; color: #1e293b; }
    
    .badge { padding: 6px 12px; border-radius: 50px; font-size: 0.85rem; font-weight: 700; }
    .status-pending { background: #fef9c3; color: #854d0e; }
    .status-success { background: #dcfce7; color: #166534; }
    .status-failed { background: #fee2e2; color: #991b1b; }
    .status-default { background: #f1f5f9; color: #475569; }

    .order-footer { padding: 12px 20px; background: #fff; border-top: 1px dashed #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
    .btn-pay { background: #8E42E1; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.9rem; }
    .btn-pay:hover { background: #7c3aed; }
    .note { font-size: 0.8rem; color: #94a3b8; font-style: italic; }

    .empty-orders { text-align: center; padding: 40px; color: #64748b; }
    .btn-shop { margin-top: 15px; padding: 10px 20px; background: #0f172a; color: white; border: none; border-radius: 8px; cursor: pointer; }

    /* Responsive */
    @media (max-width: 768px) {
        .profile-layout { flex-direction: column; }
        .sidebar-card { width: 100%; }
        .info-grid { grid-template-columns: 1fr; }
        .order-body { flex-direction: column; align-items: flex-start; }
    }
</style>