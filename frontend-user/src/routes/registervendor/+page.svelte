<script lang="ts">
    import { goto } from '$app/navigation';
    import { onMount, onDestroy } from 'svelte';
    import { PUBLIC_API_URL } from '$env/static/public';

    // State
    let businessName = "";
    let ownerName = "";
    let phone = "";
    let address = "";
    let isLoading = false;
    let token = "";

    onMount(async () => {
        // 1. Check Auth
        token = localStorage.getItem('auth_token') || "";
        if (!token) {
            alert("Anda harus login terlebih dahulu untuk mendaftar sebagai vendor.");
            goto('/login');
            return;
        }

        // 2. Prevent Scroll
        const prevOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';

        // 3. Pre-fill User Name (Optional)
        try {
            const userData = localStorage.getItem('user_data');
            if (userData) {
                const user = JSON.parse(userData);
                ownerName = user.name || "";
            }
        } catch (e) {}

        return () => { document.body.style.overflow = prevOverflow || ''; };
    });

    async function handleVendorRegister() {
        if (!businessName || !ownerName || !phone || !address) {
            alert("Harap lengkapi semua data!");
            return;
        }

        isLoading = true;

        try {
            const response = await fetch(`${PUBLIC_API_URL}/vendor/register`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({
                    store_name: businessName,
                    owner_name: ownerName,
                    phone_number: phone,
                    address: address,
                    store_description: `Alamat: ${address}` // Optional default desc
                })
            });

            const data = await response.json();

            if (response.ok) {
                alert("Selamat! Toko Anda berhasil dibuat.");
                // Update local storage user data if needed
                if (data.user) {
                    localStorage.setItem('user_data', JSON.stringify(data.user));
                }
                goto('/web/dashboard'); // Redirect to Vendor Dashboard
            } else {
                throw new Error(data.message || 'Gagal mendaftar vendor.');
            }

        } catch (error: any) {
            alert(error.message);
        } finally {
            isLoading = false;
        }
    }
</script>

<div class="overlay">
    <div class="register-container">
        <div class="register-card" role="dialog" aria-modal="true" aria-label="Daftar Vendor">
            <button class="close-btn" on:click={() => goto('/') } aria-label="Tutup">&times;</button>

            <h1>Gabung Jadi Vendor</h1>
            <p class="subtitle">Lengkapi profil usaha Anda untuk mulai berjualan.</p>
            
            <form on:submit|preventDefault={handleVendorRegister}>
                
                <div class="section-label">Informasi Toko</div>
                
                <div class="form-group">
                    <label for="businessName">Nama Toko</label>
                    <input type="text" id="businessName" bind:value={businessName} placeholder="Contoh: BOSPC Surabaya" required />
                </div>

                <div class="form-group">
                    <label for="address">Alamat Toko</label>
                    <textarea id="address" bind:value={address} rows="2" placeholder="Alamat lengkap untuk pengiriman..." required></textarea>
                </div>

                <div class="section-label" style="margin-top: 20px;">Kontak Pemilik</div>

                <div class="form-group">
                    <label for="ownerName">Nama Pemilik</label>
                    <input type="text" id="ownerName" bind:value={ownerName} placeholder="Nama Lengkap Sesuai KTP" required />
                </div>

                <div class="form-group">
                    <label for="phone">Nomor WhatsApp / Telepon</label>
                    <input type="tel" id="phone" bind:value={phone} placeholder="0812xxxx" required />
                </div>
                
                <button type="submit" class="btn-submit" disabled={isLoading}>
                    {isLoading ? 'Memproses...' : 'Buka Toko Sekarang'}
                </button>
            </form>

            <a class="back-link" on:click={() => goto('/') }>Batal & Kembali</a>
        </div>
    </div>
</div>

<style>
    :global(body) { margin: 0; font-family: 'Segoe UI', sans-serif; }

    .overlay {
        position: fixed; inset: 0; background: rgba(15,15,20,0.5);
        backdrop-filter: blur(5px); display: flex; align-items: flex-start;
        justify-content: center; z-index: 9999; padding: 40px 20px; overflow-y: auto;
    }

    .register-container { width: 100%; max-width: 500px; margin: auto 0; }

    .register-card {
        background: #fff; padding: 40px; border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2); position: relative; text-align: left;
    }

    h1 { font-size: 1.8rem; font-weight: 700; color: #1f2d3d; margin: 0 0 5px; text-align: center; }
    .subtitle { text-align: center; color: #666; font-size: 0.95rem; margin-bottom: 30px; }

    .section-label {
        font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px;
        color: #999; border-bottom: 1px solid #eee; padding-bottom: 5px;
        margin-bottom: 15px; font-weight: 700;
    }

    .form-group { margin-bottom: 15px; }
    
    label { display: block; margin-bottom: 6px; font-weight: 600; color: #333; font-size: 0.9rem; }

    input, textarea {
        width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;
        font-size: 0.95rem; box-sizing: border-box; background-color: #fcfcfc;
        transition: border 0.2s; font-family: inherit;
    }

    input:focus, textarea:focus { outline: none; border-color: #8E42E1; background-color: #fff; }

    .close-btn {
        position: absolute; right: 15px; top: 15px; background: transparent;
        border: none; font-size: 1.8rem; color: #888; cursor: pointer;
    }

    .btn-submit {
        width: 100%; padding: 14px; background: #8E42E1; color: #fff;
        border: none; border-radius: 8px; font-weight: 700; font-size: 1rem;
        cursor: pointer; margin-top: 25px; transition: background 0.2s;
    }
    .btn-submit:hover { background: #7b3bcc; }
    .btn-submit:disabled { background: #ccc; cursor: not-allowed; }

    .back-link {
        display: block; margin-top: 15px; color: #999; text-decoration: none;
        font-size: 0.85rem; cursor: pointer; text-align: center;
    }
    .back-link:hover { color: #666; }
</style>