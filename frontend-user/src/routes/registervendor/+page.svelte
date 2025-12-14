<script lang="ts">
    import { goto } from '$app/navigation';
    import { onMount, onDestroy } from 'svelte';

    // Variabel input khusus Vendor
    let businessName = "";
    let ownerName = "";
    let phone = "";
    let address = "";
    let email = "";
    let password = "";
    let confirmPassword = "";

    // Opsi Kategori
    const categories = [
        "Makanan & Minuman",
        "Fashion & Pakaian",
        "Elektronik & Gadget",
        "Jasa & Layanan",
        "Lainnya"
    ];

    // Fungsi Register Vendor
    function handleVendorRegister() {
        // Validasi kelengkapan data
        if (!businessName || !ownerName  || !phone || !address || !email || !password) {
            alert("Harap lengkapi semua data pendaftaran vendor!");
            return;
        }

        if (password !== confirmPassword) {
            alert("Password dan Konfirmasi Password tidak cocok!");
            return;
        }

        // Validasi sederhana panjang nomor telepon
        if (phone.length < 10) {
            alert("Nomor telepon tidak valid.");
            return;
        }

        // Simulasi sukses daftar
        console.log("Register Vendor:", { businessName, category, email }); // Debugging
        alert("Pendaftaran Vendor Berhasil! Silakan Login untuk mengelola toko Anda.");
        goto('/login'); 
    }

    // --- Prevent background scroll ---
    onMount(() => {
        const prevOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        onDestroy(() => {
            document.body.style.overflow = prevOverflow || '';
        });
    });
</script>

<div class="overlay">
    <div class="register-container">
        <div class="register-card" role="dialog" aria-modal="true" aria-label="Daftar Vendor">
            <button class="close-btn" on:click={() => goto('/') } aria-label="Tutup">&times;</button>

            <h1>Gabung Jadi Vendor</h1>
            <p class="subtitle">Mulai jualan dan kembangkan bisnismu sekarang.</p>
            
            <form on:submit|preventDefault={handleVendorRegister}>
                
                <div class="section-label">Informasi Usaha</div>
                
                <div class="form-row">
                    <div class="form-group half">
                        <label for="businessName">Nama Toko/Usaha</label>
                        <input type="text" id="businessName" bind:value={businessName} placeholder="Contoh: BOSPC" required />
                    </div>
                </div>

                <div class="form-group">
                    <label for="ownerName">Nama Pemilik</label>
                    <input type="text" id="ownerName" bind:value={ownerName} placeholder="Nama Lengkap Anda" required />
                </div>

                <div class="form-group">
                    <label for="phone">No. WhatsApp / Telepon</label>
                    <input type="tel" id="phone" bind:value={phone} placeholder="0812xxxx" required />
                </div>

                <div class="form-group">
                    <label for="address">Alamat Lengkap Toko</label>
                    <textarea id="address" bind:value={address} rows="2" placeholder="Jalan, No, RT/RW, Kota..." required></textarea>
                </div>

                <div class="section-label" style="margin-top: 20px;">Akun Login</div>

                <div class="form-group">
                    <label for="email">Email Bisnis</label>
                    <input type="email" id="email" bind:value={email} placeholder="bisnis@email.com" required />
                </div>

                <div class="form-row">
                    <div class="form-group half">
                        <label for="password">Password</label>
                        <input type="password" id="password" bind:value={password} placeholder="••••••••" required />
                    </div>
                    <div class="form-group half">
                        <label for="confirm">Konfirmasi</label>
                        <input type="password" id="confirm" bind:value={confirmPassword} placeholder="••••••••" required />
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">Daftar Vendor</button>
            </form>

            <p class="login-link">Sudah punya akun? <a href="/login" on:click|preventDefault={() => goto('/login')}>Login Akun</a></p>
            <a class="back-link" on:click={() => goto('/') }>Kembali ke Menu Utama</a>
        </div>
    </div>
</div>

<style>
    :global(body) { margin: 0; font-family: 'Segoe UI', sans-serif; }

    /* --- Overlay & Container --- */
    .overlay {
        position: fixed;
        inset: 0;
        background: rgba(15,15,20,0.5); /* Sedikit lebih gelap agar fokus */
        backdrop-filter: blur(5px);
        display: flex;
        align-items: flex-start; /* Ubah ke flex-start agar scroll enak */
        justify-content: center;
        z-index: 9999;
        padding: 40px 20px; /* Padding atas bawah agar tidak mentok */
        overflow-y: auto; /* Wajib auto untuk form panjang */
    }

    .register-container { 
        width: 100%; 
        max-width: 550px; /* Sedikit lebih lebar untuk vendor */
        margin: auto 0; /* Center vertical safe */
    }

    .register-card {
        background: #fff;
        padding: 40px;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        position: relative;
        text-align: left;
    }

    /* --- Typography --- */
    h1 {
        font-size: 1.8rem;
        font-weight: 700;
        color: #1f2d3d;
        margin: 0 0 5px;
        text-align: center;
    }
    
    .subtitle {
        text-align: center;
        color: #666;
        font-size: 0.95rem;
        margin-bottom: 30px;
    }

    .section-label {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #999;
        border-bottom: 1px solid #eee;
        padding-bottom: 5px;
        margin-bottom: 15px;
        font-weight: 700;
    }

    /* --- Form Elements --- */
    .form-group { margin-bottom: 15px; }

    /* Layout untuk 2 kolom (half) */
    .form-row {
        display: flex;
        gap: 15px;
    }
    .form-group.half {
        flex: 1;
    }

    label {
        display: block;
        margin-bottom: 6px;
        font-weight: 600;
        color: #333;
        font-size: 0.9rem;
    }

    input, select, textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 0.95rem;
        box-sizing: border-box;
        background-color: #fcfcfc;
        transition: border 0.2s, box-shadow 0.2s;
        font-family: inherit;
    }

    textarea {
        resize: vertical;
        min-height: 80px;
    }

    input:focus, select:focus, textarea:focus {
        outline: none;
        border-color: #8E42E1;
        box-shadow: 0 0 0 3px rgba(142, 66, 225, 0.1);
        background-color: #fff;
    }

    /* --- Buttons --- */
    .close-btn {
        position: absolute;
        right: 15px;
        top: 15px;
        background: transparent;
        border: none;
        font-size: 1.8rem;
        color: #888;
        cursor: pointer;
        line-height: 1;
    }
    .close-btn:hover { color: #333; }

    .btn-submit {
        width: 100%;
        padding: 14px;
        background: #8E42E1;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        margin-top: 25px;
        transition: background 0.2s, transform 0.1s;
    }

    .btn-submit:hover { background: #7b3bcc; }
    .btn-submit:active { transform: scale(0.98); }

    /* --- Links --- */
    .login-link {
        text-align: center;
        margin-top: 20px;
        color: #666;
        font-size: 0.9rem;
    }
    .login-link a { color: #8E42E1; text-decoration: none; font-weight: 600; }
    .login-link a:hover { text-decoration: underline; }

    .back-link {
        display: block;
        margin-top: 15px;
        color: #999;
        text-decoration: none;
        font-size: 0.85rem;
        cursor: pointer;
        text-align: center;
    }
    .back-link:hover { color: #666; }

    /* Responsif untuk HP kecil */
    @media (max-width: 480px) {
        .form-row { flex-direction: column; gap: 0; }
        .overlay { padding: 10px; align-items: flex-start; }
        .register-card { padding: 25px 20px; }
    }
</style>