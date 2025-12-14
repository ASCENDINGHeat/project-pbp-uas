<script lang="ts">
    import { goto } from '$app/navigation';
    import { createEventDispatcher, onMount } from 'svelte';

    const dispatch = createEventDispatcher();

    // --- STATE VARIABLES ---
    let isLoggedIn = false;
    let isVendor = false;

    // --- ON MOUNT (Cek Status Login) ---
    onMount(() => {
        // Cek localStorage saat komponen dimuat di browser
        const userStatus = localStorage.getItem("userLoggedIn");
        const vendorStatus = localStorage.getItem("isVendor");

        if (userStatus === "true") isLoggedIn = true;
        if (vendorStatus === "true") isVendor = true;
    });

    // --- HANDLERS ---
    function handleMenuItemClick(path: string) {
        goto(path);
        dispatch('close');
    }

    function handleLogout() {
        if (confirm("Apakah Anda yakin ingin keluar?")) {
            localStorage.removeItem("userLoggedIn");
            localStorage.removeItem("isVendor");
            
            // Reset state
            isLoggedIn = false;
            isVendor = false;
            
            dispatch('close');
            goto('/web/login');
            // Opsional: Reload halaman untuk memastikan semua komponen ter-reset
            window.location.reload();
        }
    }
</script>

<div class="user-menu-dropdown" role="menu" aria-label="User menu">
    
    <div class="menu-header">
        <span class="status-badge {isLoggedIn ? 'logged-in' : 'logged-out'}">
            {isLoggedIn ? (isVendor ? '✓ Vendor' : '✓ Member') : '○ Belum Login'}
        </span>
    </div>

    {#if !isLoggedIn}
        <a href="/login" class="menu-item" on:click|preventDefault={() => handleMenuItemClick('/login')}>
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                <path d="M10 17l5-5-5-5"></path>
            </svg>
            <span>Login</span>
        </a>

        <div class="separator"></div>

        <a href="/register" class="menu-item" on:click|preventDefault={() => handleMenuItemClick('/register')}>
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="8.5" cy="7" r="4"></circle>
                <line x1="20" y1="8" x2="20" y2="14"></line>
                <line x1="23" y1="11" x2="17" y2="11"></line>
            </svg>
            <span>Register</span>
        </a>

    {:else}
        
        <a href="/web/profile" class="menu-item" on:click|preventDefault={() => handleMenuItemClick('/web/profile')}>
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <span>Profile</span>
        </a>

        <div class="separator"></div>

        {#if isVendor}
            <a href="/web/vendor/dashboard" class="menu-item highlight" on:click|preventDefault={() => handleMenuItemClick('/web/vendor/dashboard')}>
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                <span>Dashboard Toko</span>
            </a>
        {:else}
            <a href="/web/vendor/register" class="menu-item" on:click|preventDefault={() => handleMenuItemClick('/web/vendor/register')}>
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M3 3h18v18H3zM12 8v8M8 12h8"/>
                </svg>
                <span>Buka Toko</span>
            </a>
        {/if}

        <div class="separator"></div>

        <button class="menu-item danger" on:click={handleLogout}>
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            <span>Log Out</span>
        </button>

    {/if}

    <div class="separator"></div>

    <a href="/web/pengaturan" class="menu-item muted" on:click|preventDefault={() => handleMenuItemClick('/web/pengaturan')}>
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="12" cy="12" r="3"></circle>
            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
        </svg>
        <span>Pengaturan</span>
    </a>
</div>

<style>
    .user-menu-dropdown {
        background: #0f0d28; /* Warna background asli Anda */
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.45);
        padding: 8px 0; /* Padding vertikal disesuaikan */
        min-width: 220px;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,0.08);
    }

    .menu-header {
        padding: 8px 12px;
        margin-bottom: 6px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        color: #fff;
    }

    .status-badge.logged-in {
        background: rgba(34, 197, 94, 0.2);
        color: #86efac;
        border: 1px solid rgba(34, 197, 94, 0.3);
    }

    .status-badge.logged-out {
        background: rgba(239, 68, 68, 0.15);
        color: #fca5a5;
        border: 1px solid rgba(239, 68, 68, 0.3);
    }

    .menu-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        color: #ede7ff;
        text-decoration: none;
        transition: background 0.12s ease;
        cursor: pointer;
        background: transparent;
        border: none;
        width: 100%;
        text-align: left;
        font-family: inherit;
        font-size: 0.95rem;
        box-sizing: border-box;
    }

    .menu-item:hover {
        background: rgba(255, 255, 255, 0.08);
    }

    .menu-item svg {
        opacity: 0.9;
    }

    /* Style khusus untuk dashboard vendor */
    .menu-item.highlight {
        color: #d1b2ff; 
    }
    
    .menu-item.highlight svg {
        stroke: #bc85ff;
    }

    /* Style untuk tombol logout */
    .menu-item.danger:hover {
        background: rgba(239, 68, 68, 0.2);
        color: #fca5a5;
    }

    .menu-item.muted {
        opacity: 0.7;
        font-size: 0.9rem;
    }
    
    .menu-item.muted:hover {
        opacity: 1;
    }

    /* Garis pembatas antar kelompok menu */
    .separator {
        height: 1px;
        background: rgba(255, 255, 255, 0.1);
        margin: 4px 12px;
    }
</style>