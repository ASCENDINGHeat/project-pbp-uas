<script lang="ts">
    let activeTab: string = 'profil';

    function switchTab(tab: string) {
        activeTab = tab;
    }
</script>

<svelte:head>
    <title>Pengaturan - PC Store</title>
</svelte:head>

<main class="settings-page">
    <div class="settings-container">
        <div class="settings-header">
            <h1>Pengaturan Akun</h1>
            <p>Kelola informasi dan preferensi akun Anda</p>
        </div>

        <div class="settings-layout">
            <aside class="settings-sidebar">
                <nav class="settings-nav">
                    <button 
                        class="nav-item {activeTab === 'profil' ? 'active' : ''}" 
                        on:click={() => switchTab('profil')}
                    >
                        <span>Profile</span>
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="13 17 18 12 13 7"></polyline>
                            <polyline points="6 17 11 12 6 7"></polyline>
                        </svg>
                    </button>

                    <button 
                        class="nav-item {activeTab === 'keamanan' ? 'active' : ''}" 
                        on:click={() => switchTab('keamanan')}
                    >
                        <span>Keamanan</span>
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="13 17 18 12 13 7"></polyline>
                            <polyline points="6 17 11 12 6 7"></polyline>
                        </svg>
                    </button>

                    <button class="nav-item logout">
                        <span>Keluar</span>
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                    </button>
                </nav>
            </aside>

            <div class="settings-content">
                {#if activeTab === 'profil'}
                    <section class="content-section">
                        <div class="profile-photo-section">
                            <div class="profile-photo-placeholder"></div>
                            <div class="profile-photo-controls">
                                <h3>Ganti foto profil</h3>
                                <div class="file-input-wrapper">
                                    <input type="file" id="foto-profil" accept="image/*" />
                                    <label for="foto-profil" class="file-input-label">
                                        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                                        </svg>
                                    </label>
                                </div>
                                <button class="btn btn-photo-save">Simpan</button>
                            </div>
                        </div>

                        <h2>Informasi Profil</h2>
                        <form class="settings-form">
                            <div class="form-group">
                                <label for="nama">Nama Lengkap</label>
                                <input type="text" id="nama" placeholder="Masukkan nama lengkap Anda" />
                            </div>

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" placeholder="email@example.com" />
                            </div>

                            <div class="form-group">
                                <label for="telepon">Nomor Telepon</label>
                                <input type="tel" id="telepon" placeholder="Masukkan nomor telepon" />
                            </div>

                            <div class="form-group">
                                <label for="alamat">Alamat</label>
                                <textarea id="alamat" placeholder="Masukkan alamat lengkap Anda" rows="4"></textarea>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                <button type="reset" class="btn btn-outline">Batal</button>
                            </div>
                        </form>
                    </section>
                {:else if activeTab === 'keamanan'}
                    <section class="content-section">
                        <h2>Keamanan Akun</h2>
                        <form class="settings-form">
                            <div class="form-group">
                                <label for="password-lama">Password Saat Ini</label>
                                <input type="password" id="password-lama" placeholder="Masukkan password saat ini" />
                            </div>
                            <div class="form-group">
                                <label for="password-baru">Password Baru</label>
                                <input type="password" id="password-baru" placeholder="Masukkan password baru" />
                            </div>
                            <div class="form-group">
                                <label for="konfirmasi-password">Konfirmasi Password</label>
                                <input type="password" id="konfirmasi-password" placeholder="Konfirmasi password baru" />
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Ubah Password</button>
                                <button type="reset" class="btn btn-outline">Batal</button>
                            </div>
                        </form>
                    </section>
                {/if}
            </div>
        </div>
    </div>
</main>

<style>
    /* Layout Utama & Sidebar (TIDAK DIUBAH) */
    .settings-page { min-height: calc(100vh - 200px); background: #f8f9fa; padding: 40px 20px; }
    .settings-container { max-width: 1200px; margin: 0 auto; }
    .settings-header { margin-bottom: 40px; }
    .settings-header h1 { font-size: 2rem; font-weight: 700; color: #1f2d3d; margin: 0 0 10px; }
    .settings-header p { color: #666; font-size: 1rem; margin: 0; }
    .settings-layout { display: grid; grid-template-columns: 300px 1fr; gap: 30px; }
    .settings-sidebar { background: transparent; padding: 0; height: fit-content; }
    .settings-nav { display: flex; flex-direction: column; gap: 10px; }
    .nav-item { display: flex; align-items: center; justify-content: space-between; padding: 15px 20px; background: #EAEAEA; border: none; border-radius: 8px; color: #333; font-size: 1rem; font-weight: 500; cursor: pointer; transition: all 0.2s ease; text-align: left; width: 100%; }
    .nav-item span { flex-grow: 1; }
    .nav-item:hover { background: #d4d4d4; }
    .nav-item.active { background: #dcdcdc; font-weight: 600; }
    .nav-item.logout { background: #FF4444; color: white; margin-top: 5px; }
    .nav-item.logout:hover { background: #e03333; }
    .nav-item svg { width: 20px; height: 20px; stroke-width: 2; }
    .settings-content { background: #D9D9D9; border-radius: 12px; padding: 30px; }
    .content-section h2 { font-size: 1.5rem; font-weight: 700; color: #1f2d3d; margin: 0 0 25px; padding-bottom: 15px; border-bottom: 1px solid #ccc; }
    .settings-form { display: flex; flex-direction: column; gap: 20px; }
    .form-group { display: flex; flex-direction: column; gap: 8px; }
    .form-group label { font-weight: 600; color: #333; font-size: 0.9rem; }
    .form-group input, .form-group textarea { padding: 12px; border: none; border-radius: 6px; font-size: 0.95rem; font-family: inherit; background: white; }
    .form-group input:focus, .form-group textarea:focus { outline: none; box-shadow: 0 0 0 2px rgba(142, 66, 225, 0.2); }
    .form-actions { display: flex; gap: 12px; margin-top: 20px; }
    .btn { padding: 10px 25px; border-radius: 6px; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s; font-size: 0.95rem; }
    .btn-primary { background: #4ADE80; color: white; }
    .btn-primary:hover { background: #3ec770; }
    .btn-outline { background: #FF4444; color: white; }
    .btn-outline:hover { background: #e03333; }

    /* --- STYLE UNTUK BAGIAN FOTO PROFIL BARU --- */
    .profile-photo-section {
        display: flex;
        align-items: flex-start;
        gap: 30px;
        margin-bottom: 40px; /* Jarak dengan form di bawahnya */
    }

    .profile-photo-placeholder {
        width: 120px;
        height: 120px;
        background-color: #FF4444; /* Warna merah seperti di gambar */
        border-radius: 50%;
        flex-shrink: 0;
    }

    .profile-photo-controls {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .profile-photo-controls h3 {
        font-size: 1.2rem;
        font-weight: 600;
        color: #1f2d3d;
        margin: 0;
    }

    .file-input-wrapper {
        position: relative;
        width: 100%;
        max-width: 300px;
        height: 45px;
        background: #777; /* Warna abu-abu tua seperti di gambar */
        border-radius: 6px;
        overflow: hidden;
    }

    .file-input-wrapper input[type="file"] {
        position: absolute;
        left: 0;
        top: 0;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }

    .file-input-label {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding: 0 15px;
        height: 100%;
        color: #333;
        cursor: pointer;
    }
    
    .file-input-label svg {
        color: #333; /* Warna ikon folder */
    }

    .btn-photo-save {
        background: #4ADE80; /* Hijau tombol simpan */
        color: white;
        padding: 10px 25px;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        font-size: 0.95rem;
        width: 100%;
        max-width: 300px;
    }

    .btn-photo-save:hover {
        background: #3ec770;
    }

    @media (max-width: 768px) {
        .settings-layout {
            grid-template-columns: 1fr;
        }
        .profile-photo-section {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .file-input-wrapper, .btn-photo-save {
            max-width: 100%;
        }
    }
</style>