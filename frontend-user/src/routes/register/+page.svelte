<script lang="ts">
    import { goto } from '$app/navigation';
    import { onMount, onDestroy } from 'svelte';
    import { PUBLIC_API_URL } from '$env/static/public';
    // Variabel input
    let name = "";
    let email = "";
    let password = "";
    let confirmPassword = "";
    let isLoading = false;
    const API_URL = `${PUBLIC_API_URL}/register`;
    // Fungsi Register Sederhana
    async function handleRegister() {
        if (!name || !email || !password || !confirmPassword) {
            alert("Harap isi semua kolom!");
            return;
        }
        

        if (password !== confirmPassword) {
            alert("Password dan Konfirmasi Password tidak cocok!");
            return;
        }
        isLoading = true;
        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    name: name,
                    email: email,
                    password: password,
                    password_confirmation: confirmPassword // Wajib dikirim untuk validasi 'confirmed' Laravel
                })
            });

            const data = await response.json();

            if (!response.ok) {
                // Handle Error Validasi dari Laravel
                if (data.errors) {
                    // Gabungkan semua pesan error jadi satu string
                    const errorMessages = Object.values(data.errors).flat().join('\n');
                    throw new Error(errorMessages);
                }
                throw new Error(data.message || 'Gagal mendaftar');
            }

            // Sukses
            alert('Registrasi Berhasil! Silakan Login.');
            
            // Redirect ke halaman Login (sesuaikan path login Anda, sepertinya ada di /web/login)
            goto('/login');

        } catch (error) {
            if (error instanceof Error) {
                alert(error.message);
            } else {
                // Jika error bukan object Error (misal string atau object lain)
                alert('Terjadi kesalahan yang tidak diketahui.');
            }        
        } finally {
            isLoading = false;
        }
    }

    // --- Prevent background scroll while modal is open ---
    onMount(() => {
        const prevOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        onDestroy(() => {
            document.body.style.overflow = prevOverflow || '';
        });
    });
</script>

<!-- Overlay Modal Fullscreen -->
<div class="overlay">
    <div class="register-container">
        <div class="register-card" role="dialog" aria-modal="true" aria-label="Daftar">
            <!-- Tombol Close (X) -->
            <button class="close-btn" on:click={() => goto('/') } aria-label="Tutup">&times;</button>

            <h1>Daftar Akun</h1>
            
            <form on:submit|preventDefault={handleRegister}>
                <div class="form-group">
                    <label for="name">Nama Lengkap</label>
                    <input type="text" id="name" bind:value={name} placeholder="John Doe" required />
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" bind:value={email} placeholder="your@email.com" required />
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" bind:value={password} placeholder="••••••••" required />
                </div>
                <div class="form-group">
                    <label for="confirm">Konfirmasi Password</label>
                    <input type="password" id="confirm" bind:value={confirmPassword} placeholder="••••••••" required />
                </div>
                
                <button type="submit" class="btn-submit">Daftar Sekarang</button>
            </form>

            <p class="login-link">Sudah punya akun? <a href="/login" on:click|preventDefault={() => goto('/login')}>Login di sini</a></p>
            <a class="back-link" on:click={() => goto('/') }>Kembali ke Menu Utama</a>
        </div>
    </div>
</div>

<style>
    /* Reset body margin jika perlu */
    :global(body) { margin: 0; font-family: 'Segoe UI', sans-serif; }

    /* --- Style Overlay (Sama seperti Login) --- */
    .overlay {
        position: fixed;
        inset: 0;
        background: rgba(15,15,20,0.45);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        padding: 20px;
        box-sizing: border-box;
        overflow-y: auto; /* Agar bisa discroll jika form terlalu panjang di HP */
    }

    .register-container { 
        width: 100%; 
        max-width: 500px; 
        margin: auto; /* Center vertikal jika overflow */
    }

    /* --- Style Card (Diadaptasi dari kode Register kamu) --- */
    .register-card {
        background: #fff;
        padding: 40px;
        border-radius: 16px;
        box-shadow: 0 10px 35px rgba(0, 0, 0, 0.15);
        position: relative;
        text-align: left; /* Form register biasanya rata kiri */
    }

    /* Tombol Close Pojok Kanan Atas */
    .close-btn {
        position: absolute;
        right: 12px;
        top: 12px;
        background: transparent;
        border: none;
        font-size: 1.6rem;
        color: #666;
        cursor: pointer;
        padding: 6px;
        border-radius: 6px;
    }
    .close-btn:hover { background: rgba(0,0,0,0.04); }

    h1 {
        font-size: 2rem;
        font-weight: 700;
        color: #1f2d3d;
        margin: 0 0 25px;
        text-align: center;
    }

    .form-group { margin-bottom: 15px; }

    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #1f2d3d;
        font-size: 0.9rem;
    }

    input {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 1rem;
        box-sizing: border-box;
    }

    input:focus {
        outline: none;
        border-color: #8E42E1;
        box-shadow: 0 0 0 3px rgba(142, 66, 225, 0.1);
    }

    .btn-submit {
        width: 100%;
        padding: 14px;
        background: #8E42E1; /* Warna ungu original kamu */
        color: #fff;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        margin-top: 20px;
        transition: background 0.2s;
    }

    .btn-submit:hover { background: #7b3bcc; }

    .login-link {
        text-align: center;
        margin-top: 20px;
        color: #666;
        font-size: 0.9rem;
    }

    .login-link a {
        color: #8E42E1;
        text-decoration: none;
        font-weight: 600;
    }

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
</style>