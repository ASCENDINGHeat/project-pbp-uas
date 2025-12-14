<script lang="ts">
    import { goto } from '$app/navigation';
    import { onMount, onDestroy } from 'svelte';
    import { cart } from '$lib/stores/cart';

    // --- Gunakan data dari store cart ---
    $: cartItems = $cart;

    // Info Pengiriman (Simulasi)
    let buyerInfo = {
        name: "John Doe",
        address: "Jl. Mawar Melati No. 12, Surabaya",
        phone: "081234567890"
    };

    // --- State Logic ---
    let isLoading = false;
    let paymentUrl = ""; // Nanti diisi link dari Midtrans

    // Hitung Total berdasarkan $cart
    $: subTotal = cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    $: tax = subTotal * 0.11; // PPN 11%
    $: grandTotal = subTotal + tax;

    // Fungsi Format Rupiah
    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
    }

    // --- Handle Checkout (Simulasi Request ke Midtrans) ---
    async function handleCheckout() {
        isLoading = true;
        
        // Simulasi delay API call ke Backend kamu -> Backend ke Midtrans
        setTimeout(() => {
            isLoading = false;
            // Anggap ini link yang didapat dari API response Midtrans (Snap Redirect)
            paymentUrl = "https://app.sandbox.midtrans.com/snap/v2/vtweb/your-token-here"; 
            
            // Opsional: Bisa langsung redirect otomatis kalau mau
            // window.location.href = paymentUrl;
        }, 2000);
    }

    // Prevent scroll background
    onMount(() => {
        document.body.style.overflow = 'hidden';
        onDestroy(() => document.body.style.overflow = '');
    });
</script>

<div class="overlay">
    <div class="checkout-container">
        <div class="checkout-card">
            
            <div class="card-header">
                <h1>Detail Pesanan</h1>
                <button class="close-btn" on:click={() => goto('/web/cart')}>&times;</button>
            </div>

            <div class="card-body">
                <div class="section-box">
                    <h3>Alamat Pengiriman</h3>
                    <p class="buyer-name">{buyerInfo.name} <span class="badge">Utama</span></p>
                    <p class="buyer-phone">{buyerInfo.phone}</p>
                    <p class="buyer-address">{buyerInfo.address}</p>
                </div>

                <div class="section-box">
                    <h3>Daftar Produk</h3>
                    <div class="item-list">
                        {#each cartItems as item}
                            <div class="item-row">
                                <img src={item.image} alt={item.name} class="item-img" />
                                <div class="item-info">
                                    <span class="item-name">{item.name}</span>
                                    <span class="item-meta">{item.qty} x {formatRupiah(item.price)}</span>
                                </div>
                                <div class="item-total">
                                    {formatRupiah(item.price * item.qty)}
                                </div>
                            </div>
                        {/each}
                    </div>
                </div>

                <div class="summary-section">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>{formatRupiah(subTotal)}</span>
                    </div>
                    <div class="summary-row">
                        <span>Biaya Layanan & Pajak (11%)</span>
                        <span>{formatRupiah(tax)}</span>
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
                        <p>Link pembayaran siap!</p>
                        <a href={paymentUrl} target="_blank" class="btn-midtrans">
                            Lanjut ke Pembayaran (Midtrans) &rarr;
                        </a>
                        <button class="btn-cancel" on:click={() => paymentUrl = ""}>Batal</button>
                    </div>
                {:else}
                    <button class="btn-pay" on:click={handleCheckout} disabled={isLoading}>
                        {#if isLoading}
                            <span class="loader"></span> Memproses...
                        {:else}
                            Bayar Sekarang
                        {/if}
                    </button>
                {/if}
            </div>

        </div>
    </div>
</div>

<style>
    /* Reset & Base */
    :global(body) { margin: 0; font-family: 'Segoe UI', sans-serif; }

    /* Layout Overlay */
    .overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        backdrop-filter: blur(4px);
        display: flex;
        justify-content: center;
        align-items: flex-end; /* Mobile: sheet dari bawah, Desktop: tengah */
        z-index: 9999;
    }

    @media(min-width: 600px) {
        .overlay { align-items: center; padding: 20px; }
    }

    .checkout-container {
        width: 100%;
        max-width: 480px;
        height: 90vh; /* Mobile: Hampir full */
        display: flex;
    }
    
    @media(min-width: 600px) {
        .checkout-container { height: auto; max-height: 85vh; }
    }

    .checkout-card {
        background: white;
        width: 100%;
        border-radius: 16px 16px 0 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 0 -5px 30px rgba(0,0,0,0.2);
    }

    @media(min-width: 600px) {
        .checkout-card { border-radius: 16px; }
    }

    /* --- Header --- */
    .card-header {
        padding: 20px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fff;
    }
    .card-header h1 { margin: 0; font-size: 1.2rem; color: #333; }
    .close-btn { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #888; }

    /* --- Body (Scrollable) --- */
    .card-body {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        background: #f8f9fa;
    }

    .section-box {
        background: #fff;
        padding: 15px;
        border-radius: 12px;
        margin-bottom: 15px;
        border: 1px solid #eaeaea;
    }

    h3 { margin: 0 0 12px 0; font-size: 0.95rem; color: #555; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }

    /* Alamat Styles */
    .buyer-name { font-weight: 600; color: #333; margin: 0 0 4px; }
    .buyer-phone { font-size: 0.9rem; color: #666; margin: 0 0 4px; }
    .buyer-address { font-size: 0.9rem; color: #666; margin: 0; line-height: 1.4; }
    .badge { background: #dff0d8; color: #3c763d; font-size: 0.7rem; padding: 2px 6px; border-radius: 4px; margin-left: 5px; }

    /* Item List Styles */
    .item-row { display: flex; gap: 12px; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #f0f0f0; }
    .item-row:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
    
    .item-img { width: 50px; height: 50px; border-radius: 8px; object-fit: cover; background: #eee; }
    .item-info { flex: 1; display: flex; flex-direction: column; justify-content: center; }
    .item-name { font-size: 0.9rem; font-weight: 600; color: #333; }
    .item-meta { font-size: 0.8rem; color: #888; margin-top: 4px; }
    .item-total { font-weight: 600; color: #333; font-size: 0.9rem; align-self: center; }

    /* Summary Styles */
    .summary-section { margin-top: 10px; padding: 0 5px; }
    .summary-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.9rem; color: #666; }
    .summary-row.total { border-top: 1px dashed #ccc; padding-top: 12px; margin-top: 12px; font-weight: 700; color: #333; font-size: 1.1rem; }
    .total-price { color: #8E42E1; }

    /* --- Footer (Fixed) --- */
    .card-footer {
        padding: 20px;
        background: #fff;
        border-top: 1px solid #eee;
        box-shadow: 0 -4px 20px rgba(0,0,0,0.05);
    }

    .btn-pay {
        width: 100%;
        padding: 16px;
        background: #8E42E1;
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 1rem;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        transition: background 0.2s;
    }
    .btn-pay:hover { background: #7b3bcc; }
    .btn-pay:disabled { background: #ccc; cursor: not-allowed; }

    /* State: Payment Ready */
    .payment-ready p { text-align: center; margin: 0 0 10px; color: #28a745; font-weight: 600; }
    .btn-midtrans {
        display: block;
        width: 100%;
        padding: 16px;
        background: #28a745; /* Warna hijau success */
        color: white;
        text-align: center;
        text-decoration: none;
        border-radius: 10px;
        font-weight: 700;
        margin-bottom: 10px;
    }
    .btn-cancel {
        width: 100%;
        padding: 10px;
        background: transparent;
        border: 1px solid #ddd;
        color: #666;
        border-radius: 10px;
        cursor: pointer;
    }

    /* Loader */
    .loader {
        border: 3px solid rgba(255,255,255,0.3);
        border-radius: 50%;
        border-top: 3px solid white;
        width: 18px;
        height: 18px;
        animation: spin 1s linear infinite;
    }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>