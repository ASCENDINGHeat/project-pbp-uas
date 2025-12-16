<script lang="ts">
    import { onMount } from 'svelte';
    import { goto } from '$app/navigation';
    import { PUBLIC_API_URL } from '$env/static/public';

    // --- State ---
    let activeTab = 'profile';
    let isLoading = true;
    let isEditing = false;
    let isSaving = false;
    let isRefreshing = false; // New state for refresh button
    
    // Data User
    let userProfile = {
        name: '',
        email: '',
        phone_number: '-',
        address: '-'
    };
    let editData = {
        name: '',
        email: '',
        phone_number: '',
        address: ''
    };

    // Data Order
    let orders: any[] = [];
    let orderPagination: any = {};

    function formatRupiah(number: number) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
    }

    function formatDate(dateString: string) {
        const options: Intl.DateTimeFormatOptions = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
        return new Date(dateString).toLocaleDateString('id-ID', options);
    }

    // Mapping Status
    const getStatusInfo = (status: string | number) => {
        const s = String(status);
        if (s === '1') return { label: 'Menunggu Pembayaran', class: 'bg-yellow-100 text-yellow-700' };
        if (s === '2') return { label: 'Lunas', class: 'bg-green-100 text-green-700' };
        if (s === '3') return { label: 'Dibatalkan / Kadaluarsa', class: 'bg-red-100 text-red-700' };
        return { label: 'Unknown', class: 'bg-gray-100 text-gray-600' };
    };

    // --- Fetch Data Functions ---
    async function fetchUserData(token: string) {
        const userRes = await fetch(`${PUBLIC_API_URL}/user`, {
            headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
        });
        if (userRes.ok) {
            userProfile = await userRes.json();
        } else {
            localStorage.removeItem('auth_token');
            goto('/web/login');
        }
    }

    async function fetchOrdersData(token: string) {
        const orderRes = await fetch(`${PUBLIC_API_URL}/orders`, {
            headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
        });
        if (orderRes.ok) {
            const data = await orderRes.json();
            orders = data.data || [];
            orderPagination = data; 
        }
    }

    async function handleRefreshOrders() {
        const token = localStorage.getItem('auth_token');
        if(!token) return;
        
        isRefreshing = true;
        await fetchOrdersData(token);
        isRefreshing = false;
    }

    onMount(async () => {
        const token = localStorage.getItem('auth_token');
        if (!token) {
            alert("Silakan login terlebih dahulu.");
            goto('/web/login');
            return;
        }

        try {
            await Promise.all([
                fetchUserData(token),
                fetchOrdersData(token)
            ]);
        } catch (error) {
            console.error("Gagal memuat data:", error);
        } finally {
            isLoading = false;
        }
    });

    function payOrder(snapToken: string) {
        if(snapToken) {
            window.open(`https://app.sandbox.midtrans.com/snap/v2/vtweb/${snapToken}`, '_blank');
        } else {
            alert("Token pembayaran tidak ditemukan.");
        }
    }

    function startEdit() {
        editData = { ...userProfile };
        isEditing = true;
    }

    function cancelEdit() {
        isEditing = false;
    }

    async function saveProfile() {
        isSaving = true;
        const token = localStorage.getItem('auth_token');

        try {
            const res = await fetch(`${PUBLIC_API_URL}/user`, {
                method: 'PUT',
                headers: { 
                    'Authorization': `Bearer ${token}`, 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json' 
                },
                body: JSON.stringify(editData)
            });
            const data = await res.json();

            if (res.ok) {
                userProfile = data.user;
                isEditing = false;
                alert('Profil berhasil diperbarui!');
            } else {
                alert(data.message || 'Gagal memperbarui profil.');
            }
        } catch (error) {
            console.error("Error updating profile:", error);
            alert("Terjadi kesalahan koneksi.");
        } finally {
            isSaving = false;
        }
    }
</script>

<svelte:head>
    <title>Profil Saya - PC Store</title>
</svelte:head>

<div class="w-[95%] mx-auto my-5 flex">
    <div class="inline-flex items-center gap-2 bg-slate-900 rounded-full px-4 py-2 text-sm text-slate-400">
        <a class="text-slate-400 hover:text-white no-underline" href="/web">Home</a>
        <span class="text-xs">›</span>
        <span class="text-purple-500 font-bold">Profil Pengguna</span>
    </div>
</div>

<main class="min-h-screen bg-slate-50 py-10 px-5">
    <div class="max-w-6xl mx-auto">
        
        <div class="flex flex-col md:flex-row gap-8 items-start">
            <aside class="w-full md:w-[280px] bg-white rounded-xl shadow-sm overflow-hidden flex-shrink-0">
                <div class="p-8 text-center bg-gradient-to-br from-slate-800 to-slate-900 text-white">
                    <div class="w-20 h-20 bg-purple-600 text-white rounded-full flex items-center justify-center text-4xl font-bold mx-auto mb-4 border-4 border-white/20">
                        {userProfile.name.charAt(0).toUpperCase()}
                    </div>
                    <h3 class="m-0 text-lg font-bold">{userProfile.name}</h3>
                    <p class="m-0 mt-1 text-sm opacity-80">{userProfile.email}</p>
                </div>
                <nav class="p-4 flex flex-col gap-1">
                    <button 
                        class="w-full text-left p-3 rounded-lg font-semibold text-sm transition-colors {activeTab === 'profile' ? 'bg-purple-50 text-purple-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-800'}" 
                        on:click={() => activeTab = 'profile'}>
                        👤 Biodata Diri
                    </button>
                    <button 
                        class="w-full text-left p-3 rounded-lg font-semibold text-sm transition-colors {activeTab === 'orders' ? 'bg-purple-50 text-purple-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-800'}" 
                        on:click={() => activeTab = 'orders'}>
                        📦 Riwayat Pesanan
                    </button>
                    <div class="h-px bg-slate-100 my-2"></div>
                    <button class="w-full text-left p-3 rounded-lg font-semibold text-sm text-red-500 hover:bg-red-50 transition-colors" 
                        on:click={() => {
                            localStorage.removeItem('auth_token');
                            goto('/web/login');
                        }}>
                        🚪 Logout
                    </button>
                </nav>
            </aside>

            <section class="flex-1 bg-white rounded-xl shadow-sm p-8 w-full min-h-[400px]">
                {#if isLoading}
                    <div class="text-center py-10 text-slate-500">Memuat data...</div>
                {:else}
                    
                    {#if activeTab === 'profile'}
                        <header class="flex justify-between items-center mb-8 border-b border-slate-100 pb-4">
                            <div>
                                <h2 class="m-0 text-2xl font-bold text-slate-800">Biodata Diri</h2>
                                <p class="m-0 mt-1 text-slate-500 text-sm">Informasi pribadi dan alamat pengiriman Anda.</p>
                            </div>
                            {#if !isEditing}
                                <button class="bg-white border border-slate-200 px-4 py-2 rounded-lg font-semibold text-slate-600 text-sm hover:bg-slate-50 hover:text-slate-900 transition-colors" on:click={startEdit}>
                                    ✎ Edit Profil
                                </button>
                            {/if}
                        </header>
                        
                        {#if isEditing}
                            <form class="flex flex-col gap-4 max-w-xl" on:submit|preventDefault={saveProfile}>
                                <div class="flex flex-col gap-1.5">
                                    <label for="name" class="text-sm font-semibold text-slate-600">Nama Lengkap</label>
                                    <input type="text" id="name" bind:value={editData.name} required class="p-2.5 border border-slate-300 rounded-lg text-base focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500" />
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label for="email" class="text-sm font-semibold text-slate-600">Email</label>
                                    <input type="email" id="email" bind:value={editData.email} required class="p-2.5 border border-slate-300 rounded-lg text-base focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500" />
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label for="phone" class="text-sm font-semibold text-slate-600">Nomor Telepon</label>
                                    <input type="text" id="phone" bind:value={editData.phone_number} class="p-2.5 border border-slate-300 rounded-lg text-base focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500" placeholder="08..." />
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label for="address" class="text-sm font-semibold text-slate-600">Alamat Lengkap</label>
                                    <textarea id="address" bind:value={editData.address} rows="3" class="p-2.5 border border-slate-300 rounded-lg text-base focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500" placeholder="Jalan..."></textarea>
                                </div>

                                <div class="flex gap-3 mt-2">
                                    <button type="button" class="px-5 py-2.5 bg-white border border-slate-300 text-slate-600 font-semibold rounded-lg hover:bg-slate-50 transition-colors" on:click={cancelEdit} disabled={isSaving}>Batal</button>
                                    <button type="submit" class="px-5 py-2.5 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition-colors disabled:bg-purple-300" disabled={isSaving}>
                                        {isSaving ? 'Menyimpan...' : 'Simpan Perubahan'}
                                    </button>
                                </div>
                            </form>
                        {:else}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Nama Lengkap</label>
                                    <div class="text-base text-slate-800 font-medium py-2 border-b border-dashed border-slate-200">{userProfile.name}</div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Email</label>
                                    <div class="text-base text-slate-800 font-medium py-2 border-b border-dashed border-slate-200">{userProfile.email}</div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Nomor Telepon</label>
                                    <div class="text-base text-slate-800 font-medium py-2 border-b border-dashed border-slate-200">{userProfile.phone_number || '-'}</div>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">Alamat Lengkap</label>
                                    <div class="bg-slate-50 p-4 rounded-lg text-slate-800 border border-slate-100">
                                        {userProfile.address || 'Alamat belum diatur.'}
                                    </div>
                                </div>
                            </div>
                        {/if}

                    {:else if activeTab === 'orders'}
                        <header class="flex justify-between items-center mb-6">
                            <div>
                                <h2 class="m-0 text-2xl font-bold text-slate-800">Riwayat Pesanan</h2>
                                <p class="m-0 mt-1 text-slate-500 text-sm">Daftar transaksi yang pernah Anda lakukan.</p>
                            </div>
                            <button class="bg-white border border-slate-200 p-2 rounded-lg text-slate-600 hover:bg-slate-50 hover:text-purple-600 transition-colors" on:click={handleRefreshOrders} title="Refresh Status">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class:animate-spin={isRefreshing}>
                                    <path d="M23 4v6h-6"></path><path d="M1 20v-6h6"></path><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                                </svg>
                            </button>
                        </header>

                        {#if orders.length === 0}
                            <div class="text-center py-12">
                                <p class="text-slate-500 mb-4">Belum ada pesanan.</p>
                                <button class="bg-slate-900 text-white px-6 py-2.5 rounded-lg font-bold hover:bg-slate-800 transition-colors" on:click={() => goto('/web')}>Mulai Belanja</button>
                            </div>
                        {:else}
                            <div class="flex flex-col gap-5">
                                {#each orders as order}
                                    {@const status = getStatusInfo(order.payment_status)}
                                    <div class="border border-slate-200 rounded-xl overflow-hidden transition-all hover:border-slate-300 hover:shadow-sm">
                                        <div class="bg-slate-50 px-5 py-3 flex justify-between items-center border-b border-slate-200 text-sm text-slate-500">
                                            <div class="font-bold text-slate-700">ID Pesanan: #{order.id}</div>
                                            <div>{formatDate(order.created_at)}</div>
                                        </div>
                                        
                                        <div class="p-5 flex flex-wrap gap-4 justify-between items-center">
                                            <div class="text-lg text-slate-800">
                                                Total Belanja: <strong>{formatRupiah(order.total_amount)}</strong>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm text-slate-500">Status:</span>
                                                <span class="px-3 py-1 rounded-full text-xs font-bold {status.class}">{status.label}</span>
                                            </div>
                                        </div>

                                        <div class="px-5 py-3 bg-white border-t border-dashed border-slate-200 flex justify-between items-center">
                                            <div>
                                                {#if String(order.payment_status) === '1'}
                                                    <button class="bg-purple-600 text-white border-none px-4 py-2 rounded-lg cursor-pointer font-semibold text-sm hover:bg-purple-700 transition-colors" on:click={() => payOrder(order.snap_token)}>
                                                        Bayar Sekarang
                                                    </button>
                                                {/if}
                                            </div>
                                            <span class="text-xs text-slate-400 italic">*Detail produk dikirim ke email vendor</span>
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