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

<div class="overlay">
    <div class="checkout-container">
        <div class="checkout-card">
            
            <div class="card-header">
                <h1>Konfirmasi Pesanan</h1>
                <button class="close-btn" on:click={() => goto('/web/cart')}>&times;</button>
            </div>

            <div class="card-body">
                {#if errorMessage}
                    <div class="alert-error">{errorMessage}</div>
                {/if}

                <div class="section-box">
                    <h3>Informasi Pembeli</h3>
                    <p class="buyer-name">{buyerInfo.name} <span class="badge">User</span></p>
                    <p class="buyer-phone">{buyerInfo.email}</p>
                    <p class="buyer-address" style="color: #666; font-style: italic; font-size: 0.85rem; margin-top:5px;">
                        (Alamat diambil dari profil pengguna)
                    </p>
                </div>

                <div class="section-box">
                    <h3>Daftar Produk ({cartItems.length} Item)</h3>
                    <div class="item-list">
                        {#each cartItems as item}
                            <div class="item-row">
                                <img src={item.image || '/images/placeholder.png'} alt={item.name} class="item-img" />
                                <div class="item-info">
                                    <span class="item-name">{item.name}</span>
                                    <span class="item-meta">{item.quantity} x {formatRupiah(Number(item.price))}</span>
                                </div>
                                <div class="item-total">
                                    {formatRupiah(Number(item.price) * item.quantity)}
                                </div>
                            </div>
                        {/each}
                    </div>
                </div>

                <div class="summary-section">
                    <div class="summary-row">
                        <span>Subtotal Produk</span>
                        <span>{formatRupiah(subTotal)}</span>
                    </div>
                    <div class="summary-row">
                        <span>Biaya Layanan (Admin)</span>
                        <span>{formatRupiah(adminFee)}</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total Tagihan</span>
                        <span class="total-price">{formatRupiah(grandTotal)}</span>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                {#if paymentUrl}
                    <div class="payment-ready">
                        <p>✅ Pesanan Dibuat!</p>
                        <a href={paymentUrl} target="_blank" class="btn-midtrans">
                            Lanjut ke Pembayaran &rarr;
                        </a>
                        <button class="btn-cancel" on:click={() => paymentUrl = ""}>Kembali</button>
                    </div>
                {:else}
                    <button class="btn-pay" on:click={handleCheckout} disabled={isLoading}>
                        {#if isLoading}
                            <span class="loader"></span> Memproses...
                        {:else}
                            Bayar Sekarang ({formatRupiah(grandTotal)})
                        {/if}
                    </button>
                {/if}
            </div>

        </div>
    </div>
</div>

<style>
    /* Global & Layout */
    :global(body) { margin: 0; font-family: 'Segoe UI', sans-serif; }
    .overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); display: flex; justify-content: center; align-items: center; z-index: 9999; padding: 20px; }
    
    .checkout-container { width: 100%; max-width: 500px; max-height: 85vh; display: flex; }
    .checkout-card { background: white; width: 100%; border-radius: 16px; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 -5px 30px rgba(0,0,0,0.2); }

    /* Header */
    .card-header { padding: 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; background: #fff; }
    .card-header h1 { margin: 0; font-size: 1.1rem; color: #333; font-weight: 700; }
    .close-btn { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #888; }

    /* Body */
    .card-body { flex: 1; overflow-y: auto; padding: 20px; background: #f8f9fa; }
    .alert-error { background-color: #fee2e2; color: #ef4444; padding: 12px; border-radius: 8px; margin-bottom: 15px; font-size: 0.9rem; border: 1px solid #fca5a5; }
    .section-box { background: #fff; padding: 15px; border-radius: 12px; margin-bottom: 15px; border: 1px solid #eaeaea; }
    h3 { margin: 0 0 12px 0; font-size: 0.85rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }

    /* User Info */
    .buyer-name { font-weight: 600; color: #333; margin: 0 0 4px; }
    .buyer-phone { font-size: 0.9rem; color: #666; margin: 0 0 4px; }
    .badge { background: #dff0d8; color: #3c763d; font-size: 0.7rem; padding: 2px 6px; border-radius: 4px; margin-left: 5px; }

    /* Items */
    .item-row { display: flex; gap: 12px; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #f0f0f0; }
    .item-row:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
    .item-img { width: 50px; height: 50px; border-radius: 8px; object-fit: cover; background: #eee; border: 1px solid #eee; }
    .item-info { flex: 1; display: flex; flex-direction: column; justify-content: center; }
    .item-name { font-size: 0.9rem; font-weight: 600; color: #333; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 200px; }
    .item-meta { font-size: 0.8rem; color: #888; margin-top: 4px; }
    .item-total { font-weight: 600; color: #333; font-size: 0.9rem; align-self: center; }

    /* Summary */
    .summary-section { margin-top: 10px; padding: 0 5px; }
    .summary-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.9rem; color: #666; }
    .summary-row.total { border-top: 1px dashed #ccc; padding-top: 12px; margin-top: 12px; font-weight: 700; color: #333; font-size: 1.1rem; }
    .total-price { color: #8E42E1; }

    /* Footer */
    .card-footer { padding: 20px; background: #fff; border-top: 1px solid #eee; box-shadow: 0 -4px 20px rgba(0,0,0,0.05); }
    .btn-pay { width: 100%; padding: 16px; background: #8E42E1; color: white; border: none; border-radius: 10px; font-size: 1rem; font-weight: 700; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 10px; transition: background 0.2s; }
    .btn-pay:hover { background: #7b3bcc; }
    .btn-pay:disabled { background: #ccc; cursor: not-allowed; }

    /* Midtrans State */
    .payment-ready p { text-align: center; margin: 0 0 15px; color: #28a745; font-weight: 700; font-size: 1.1rem; }
    .btn-midtrans { display: block; width: 100%; padding: 16px; background: #28a745; color: white; text-align: center; text-decoration: none; border-radius: 10px; font-weight: 700; margin-bottom: 10px; box-sizing: border-box; }
    .btn-midtrans:hover { background: #218838; }
    .btn-cancel { width: 100%; padding: 10px; background: transparent; border: 1px solid #ddd; color: #666; border-radius: 10px; cursor: pointer; font-weight: 600; }

    /* Loader */
    .loader { border: 3px solid rgba(255,255,255,0.3); border-radius: 50%; border-top: 3px solid white; width: 18px; height: 18px; animation: spin 1s linear infinite; }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>