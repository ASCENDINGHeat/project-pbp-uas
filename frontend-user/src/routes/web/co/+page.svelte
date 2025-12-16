<script lang="ts">
    import { goto } from '$app/navigation';
    import { onMount, onDestroy } from 'svelte';
    import { cart, selectedItemIds } from '$lib/stores/cart'; 
    // Import store user
    import { user } from '$lib/stores/auth';
    import { PUBLIC_API_URL } from '$env/static/public';

    // --- 1. Filter Item: Hanya ambil yang ID-nya ada di selectedItemIds ---
    $: cartItems = $cart.filter(item => $selectedItemIds.includes(item.id));
    
    // --- 2. Data Pembeli: Integrasi dengan Store User ---
    // Gunakan reactive statement ($:) agar otomatis berubah saat data user berhasil di-fetch
    $: buyerInfo = {
        name: $user?.name || "Memuat data user...",
        email: $user?.email || "Memuat email...", 
        address: "Alamat pengiriman belum disetting", 
        phone: "-" 
    };

    let isLoading = false;
    let paymentUrl = "";
    let errorMessage = "";

    // --- 3. Perhitungan Biaya ---
    $: subTotal = cartItems.reduce((sum, item) => sum + (Number(item.price) * item.quantity), 0);
    const adminFee = 4000; 
    $: grandTotal = subTotal + adminFee;

    function formatRupiah(number: number) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
    }
    
    // --- 4. Fetch Data User (JIKA STORE KOSONG) ---
    async function fetchUserProfile() {
        const token = localStorage.getItem('auth_token');
        if (!token) return; // Jika tidak ada token, biarkan handler lain yang mengurus (misal redirect login)

        try {
            // Asumsi endpoint bawaan Laravel Sanctum: GET /api/user
            const res = await fetch(`${PUBLIC_API_URL}/user`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });

            if (res.ok) {
                const userData = await res.json();
                // Update Global Store User
                user.set(userData); 
            }
        } catch (e) {
            console.error("Gagal mengambil profil user:", e);
        }
    }

    // --- 5. Handle Checkout ---
    async function handleCheckout() { 
        if (cartItems.length === 0) {
            alert("Error: Tidak ada item yang dipilih.");
            goto('/web/cart');
            return;
        }

        isLoading = true; 
        errorMessage = "";

        try {
            const token = localStorage.getItem('auth_token');
            if (!token) {
                alert("Sesi habis, silakan login kembali.");
                goto('/web/login');
                return;
            }

            const selected_ids_payload = cartItems.map(item => item.id);

            const response = await fetch(`${PUBLIC_API_URL}/checkout`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    selected_cart_ids: selected_ids_payload
                })
            });

            const data = await response.json();

            if (response.ok) {
                paymentUrl = data.redirect_url;
            } else {
                errorMessage = data.message || "Checkout gagal.";
                if (data.errors) errorMessage += " " + JSON.stringify(data.errors);
            }

        } catch (error) {
            console.error("Checkout Error:", error);
            errorMessage = "Gagal menghubungi server.";
        } finally {
            isLoading = false;
        }
    }

    // --- Lifecycle ---
    onMount(async () => {
        document.body.style.overflow = 'hidden';
        
        // Cek 1: Validasi Item Keranjang
        if ($selectedItemIds.length === 0) {
            alert("Silakan pilih barang di keranjang terlebih dahulu.");
            goto('/web/cart');
            return;
        }

        // Cek 2: Validasi User Data
        // Jika store user kosong tapi ada token, fetch ulang datanya
        const token = localStorage.getItem('auth_token');
        if (!$user && token) {
            await fetchUserProfile();
        } else if (!token) {
            alert("Anda belum login.");
            goto('/web/login');
        }
    });

    onDestroy(() => {
        if (typeof document !== 'undefined') document.body.style.overflow = '';
    });
</script>

<div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex justify-center items-center z-[9999] p-5">
    <div class="w-full max-w-[500px] max-h-[85vh] flex">
        <div class="bg-white w-full rounded-2xl flex flex-col overflow-hidden shadow-2xl">
            
            <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-white">
                <h1 class="m-0 text-lg text-gray-800 font-bold">Konfirmasi Pesanan</h1>
                <button class="bg-none border-none text-2xl cursor-pointer text-gray-400 hover:text-gray-600" on:click={() => goto('/web/cart')}>&times;</button>
            </div>

            <div class="flex-1 overflow-y-auto p-5 bg-gray-50">
                {#if errorMessage}
                    <div class="bg-red-100 text-red-500 p-3 rounded-lg mb-4 text-sm border border-red-200">{errorMessage}</div>
                {/if}

                <div class="bg-white p-4 rounded-xl mb-4 border border-gray-100 shadow-sm">
                    <h3 class="m-0 mb-3 text-sm text-slate-500 font-bold uppercase tracking-wide">Informasi Pembeli</h3>
                    <p class="font-semibold text-gray-800 m-0 mb-1">{buyerInfo.name} <span class="bg-green-100 text-green-700 text-xs px-1.5 py-0.5 rounded ml-1">User</span></p>
                    <p class="text-sm text-gray-500 m-0 mb-1">{buyerInfo.email}</p>
                    <p class="text-xs text-gray-400 italic m-0 mt-1">
                        (Alamat diambil dari profil pengguna)
                    </p>
                </div>

                <div class="bg-white p-4 rounded-xl mb-4 border border-gray-100 shadow-sm">
                    <h3 class="m-0 mb-3 text-sm text-slate-500 font-bold uppercase tracking-wide">Daftar Produk ({cartItems.length} Item)</h3>
                    <div class="flex flex-col">
                        {#each cartItems as item}
                            <div class="flex gap-3 mb-3 pb-3 border-b border-gray-50 last:mb-0 last:pb-0 last:border-none">
                                <img src={item.image || '/images/placeholder.png'} alt={item.name} class="w-[50px] h-[50px] rounded-lg object-cover bg-gray-100 border border-gray-100" />
                                <div class="flex-1 flex flex-col justify-center">
                                    <span class="text-sm font-semibold text-gray-800 overflow-hidden text-ellipsis whitespace-nowrap max-w-[200px]">{item.name}</span>
                                    <span class="text-xs text-gray-400 mt-1">{item.quantity} x {formatRupiah(Number(item.price))}</span>
                                </div>
                                <div class="font-semibold text-gray-800 text-sm self-center">
                                    {formatRupiah(Number(item.price) * item.quantity)}
                                </div>
                            </div>
                        {/each}
                    </div>
                </div>

                <div class="mt-2.5 px-1">
                    <div class="flex justify-between mb-2 text-sm text-gray-500">
                        <span>Subtotal Produk</span>
                        <span>{formatRupiah(subTotal)}</span>
                    </div>
                    <div class="flex justify-between mb-2 text-sm text-gray-500">
                        <span>Biaya Layanan (Admin)</span>
                        <span>{formatRupiah(adminFee)}</span>
                    </div>
                    <div class="flex justify-between mt-3 pt-3 border-t border-dashed border-gray-300 font-bold text-gray-800 text-lg">
                        <span>Total Tagihan</span>
                        <span class="text-violet-600">{formatRupiah(grandTotal)}</span>
                    </div>
                </div>
            </div>

            <div class="p-5 bg-white border-t border-gray-100 shadow-[0_-4px_20px_rgba(0,0,0,0.05)]">
                {#if paymentUrl}
                    <div class="text-center">
                        <p class="m-0 mb-4 text-green-600 font-bold text-lg">✅ Pesanan Dibuat!</p>
                        <a href={paymentUrl} target="_blank" class="block w-full p-4 bg-green-600 text-white text-center no-underline rounded-xl font-bold mb-2.5 hover:bg-green-700">
                            Lanjut ke Pembayaran &rarr;
                        </a>
                        <button class="w-full p-2.5 bg-transparent border border-gray-200 text-gray-500 rounded-xl cursor-pointer font-semibold hover:bg-gray-50" on:click={() => paymentUrl = ""}>Kembali</button>
                    </div>
                {:else}
                    <button class="w-full p-4 bg-violet-600 text-white border-none rounded-xl text-base font-bold cursor-pointer flex justify-center items-center gap-2.5 transition-colors hover:bg-violet-700 disabled:bg-gray-300 disabled:cursor-not-allowed" on:click={handleCheckout} disabled={isLoading}>
                        {#if isLoading}
                            <span class="w-[18px] h-[18px] border-[3px] border-white/30 border-t-white rounded-full animate-spin"></span> Memproses...
                        {:else}
                            Bayar Sekarang ({formatRupiah(grandTotal)})
                        {/if}
                    </button>
                {/if}
            </div>

        </div>
    </div>
</div>