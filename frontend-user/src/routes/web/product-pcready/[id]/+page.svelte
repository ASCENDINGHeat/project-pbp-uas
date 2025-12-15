<script lang="ts">
  import { page } from '$app/stores';
  import { goto } from '$app/navigation'; // Import untuk navigasi
  import { pcReadyProducts } from '$lib/data/pc-ready-products'; 
  import { addToCart } from '$lib/stores/cart';

  // --- 1. Logic Data Produk ---
  $: id = $page.params.id;
  $: found = pcReadyProducts.find((p) => p.id === id);

  $: productTitle = found?.name ?? "Produk Tidak Ditemukan";
  $: price = found?.price ?? 0;
  
  // Logic Parsing Komponen
  $: components = (() => {
    if (!found || !found.components) return [];

    return found.components.map((itemString) => {
      const splitIndex = itemString.indexOf(':');
      let category = 'Komponen';
      let name = itemString;

      if (splitIndex !== -1) {
        category = itemString.substring(0, splitIndex).trim();
        name = itemString.substring(splitIndex + 1).trim();
      }

      let iconType = 'default';
      const catLower = category.toLowerCase();

      if (catLower.includes('cpu') || catLower.includes('processor')) iconType = 'cpu';
      else if (catLower.includes('cool') || catLower.includes('fan')) iconType = 'fan';
      else if (catLower.includes('motherboard') || catLower.includes('board')) iconType = 'board';
      else if (catLower.includes('gpu') || catLower.includes('vga')) iconType = 'gpu';
      else if (catLower.includes('ram') || catLower.includes('memory')) iconType = 'ram';
      else if (catLower.includes('storage') || catLower.includes('ssd') || catLower.includes('hdd')) iconType = 'disk';
      else if (catLower.includes('psu') || catLower.includes('power')) iconType = 'power';
      else if (catLower.includes('case')) iconType = 'case';

      return { category, name, icon: iconType };
    });
  })();

  $: productInfo = [
    { label: "Kategori", value: found?.category ?? "PC Ready" },
    { label: "Status", value: "Tersedia" },
  ];

  const formatCurrency = (val: number) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);

  // --- 2. Logic Tombol Kembali ---
  function goBack() {
    if (history.length > 1) {
      history.back();
    } else {
      goto('/web'); // Fallback ke halaman utama jika dibuka di tab baru
    }
  }

  function handleAddToCart() {
    if (!found) return;
    addToCart({
      id: found.id ?? `pc-${id}`,
      name: found.name ?? productTitle,
      price: Number(found.price ?? price),
      image: found.image ?? found.imagePlaceholder ?? '',
      quantity: 1
    });
    goto('/web/cart');
  }
</script>

<div class="page-bg">
  <div class="container">
    
    <div class="nav-container">
        <button class="btn-back" on:click={goBack} aria-label="Kembali">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span>Kembali</span>
        </button>
    </div>

    <div class="header-section">
      <div class="store-badge">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"></path><path d="M4 6v12a2 2 0 0 0 2 2h14v-4"></path><path d="M18 12a2 2 0 0 0-2 2c0 1.1.9 2 2 2h4v-4h-4z"></path></svg>
        Official Store
      </div>
      <h1 class="title">{productTitle}</h1>
    </div>

    <div class="layout-grid">
      
      <div class="left-column">
        <div class="main-image-placeholder">
           {#if found?.imagePlaceholder}
              <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect><rect x="9" y="9" width="6" height="6"></rect><line x1="9" y1="1" x2="9" y2="4"></line><line x1="15" y1="1" x2="15" y2="4"></line></svg>
           {:else}
              <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect><rect x="9" y="9" width="6" height="6"></rect></svg>
           {/if}
        </div>

        <div class="components-card">
          <div class="card-header">
            <h3>Spesifikasi Komponen</h3>
          </div>
          <div class="components-list">
            {#each components as comp}
              <div class="component-item">
                <div class="comp-icon">
                  {#if comp.icon === 'cpu'}
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect><rect x="9" y="9" width="6" height="6"></rect></svg>
                  {:else if comp.icon === 'fan'}
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12c-2-2.3-4.5-3-6-3a2 2 0 0 0-2 2c0 2 1.5 5 4 6 2.3 1 4 .5 6-1"></path><path d="M12 12c2.3 2 4.5 3 6 3a2 2 0 0 0 2-2c0-2-1.5-5-4-6-2.3-1-4-.5-6 1"></path><path d="M12 12c-2 2.3-3 4.5-3 6a2 2 0 0 0 2 2c2 0 5-1.5 6-4 1-2.3.5-4-1-6"></path></svg>
                  {:else if comp.icon === 'board'}
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="2"></rect><path d="M8 6h.01"></path><path d="M16 6h.01"></path><path d="M12 12h.01"></path></svg>
                  {:else if comp.icon === 'gpu'}
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                  {:else if comp.icon === 'ram' || comp.icon === 'disk'}
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                  {:else if comp.icon === 'case' || comp.icon === 'power'}
                      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><line x1="12" y1="14" x2="12" y2="14.01"></line></svg>
                  {:else}
                      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                  {/if}
                </div>
                <div class="comp-text">
                  <span class="comp-cat">{comp.category}</span>
                  <span class="comp-name">{comp.name}</span>
                </div>
              </div>
            {/each}
          </div>
        </div>
      </div>

      <div class="right-column">
        
        <div class="price-card">
          <h2 class="price-text">{formatCurrency(price)}</h2>
          
          <button class="btn btn-primary" on:click={handleAddToCart}>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
            Tambah ke Keranjang
          </button>
          
          <button class="btn btn-outline">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
            Tambah ke Wishlist
          </button>
          
          <button class="btn btn-outline">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
            Bagikan
          </button>
        </div>

        <div class="info-box-purple">
          <h3>Informasi Produk</h3>
          <div class="info-content">
            {#each productInfo as info}
              <div class="info-row">
                <span class="info-label">{info.label}:</span>
                <span class="info-val">{info.value}</span>
              </div>
            {/each}
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<style>
  :global(body) {
    margin: 0;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    background-color: #f8f9fa;
    color: #1f2937;
  }

  .page-bg {
    padding: 10px 24px 40px; /* Padding atas dikurangi jadi 10px agar tombol kembali pas */
    min-height: 100vh;
  }

  .container {
    max-width: 1350px; 
    margin: 0 auto;
    width: 100%;
  }

  /* --- STYLE TOMBOL KEMBALI (HITAM-MERAH) --- */
  .nav-container {
      width: 100%;
      margin-bottom: 20px;
  }

  .btn-back {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    margin: 0;
    padding: 8px 16px;
    
    background: #111111; /* Hitam */
    color: #ef4444;      /* Merah */
    border: none;
    border-radius: 50px; 
    
    font-weight: 700;
    font-size: 0.9rem;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s, background 0.2s;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  }

  .btn-back:hover {
    background: #000000;
    transform: translateY(-2px); 
    box-shadow: 0 8px 20px rgba(0,0,0,0.25);
  }

  .btn-back svg { stroke: currentColor; }

  /* --- HEADER (Tanpa Breadcrumbs) --- */
  .header-section {
    margin-bottom: 32px;
  }
  .store-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background-color: #ede9fe; 
    color: #8b5cf6;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    margin-bottom: 12px;
  }
  .title {
    font-size: 1.8rem;
    font-weight: 800;
    margin: 0;
    color: #111827;
  }

  /* --- LAYOUT GRID --- */
  .layout-grid {
    display: grid;
    grid-template-columns: 2.2fr 1fr;
    gap: 40px;
    align-items: start;
  }

  /* --- KOLOM KIRI --- */
  .main-image-placeholder {
    background-color: #ffffff;
    border-radius: 16px;
    height: 450px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 30px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
  }
  
  .components-card {
    background-color: #ffffff;
    border-radius: 16px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    overflow: hidden;
  }
  
  .card-header {
    background-color: #fcfdfe;
    padding: 20px 24px;
    border-bottom: 1px solid #e5e7eb;
  }
  .card-header h3 {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 700;
    color: #374151;
  }

  .component-item {
    display: flex;
    align-items: center;
    padding: 18px 24px;
    border-bottom: 1px solid #f3f4f6;
    transition: background-color 0.2s;
  }
  .component-item:last-child {
    border-bottom: none;
  }
  .component-item:hover {
    background-color: #f9fafb;
  }

  .comp-icon {
    width: 48px;
    height: 48px;
    background-color: #f3f4f6;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 20px;
    color: #6b7280;
    flex-shrink: 0;
  }

  .comp-text {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }
  .comp-cat {
    font-size: 0.8rem;
    color: #6b7280;
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: 0.5px;
  }
  .comp-name {
    font-size: 0.95rem;
    font-weight: 600;
    color: #1f2937;
    line-height: 1.4;
  }

  /* --- KOLOM KANAN --- */
  .price-card {
    background-color: #ffffff;
    border-radius: 16px;
    padding: 32px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    margin-bottom: 24px;
    text-align: center;
  }

  .price-text {
    font-size: 2.2rem;
    font-weight: 800;
    color: #111827;
    margin: 0 0 30px 0;
  }

  .btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    padding: 14px;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    font-size: 1rem;
    margin-bottom: 14px;
    transition: all 0.2s ease;
  }

  .btn-primary {
    background: linear-gradient(to bottom right, #8b5cf6, #7e22ce);
    color: white;
    border: none;
    box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
  }
  .btn-primary:hover {
    background: linear-gradient(to bottom right, #7c3aed, #6b21a8);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(139, 92, 246, 0.4);
  }

  .btn-outline {
    background-color: transparent;
    border: 1.5px solid #e5e7eb;
    color: #4b5563;
  }
  .btn-outline:hover {
    background-color: #f9fafb;
    border-color: #d1d5db;
    color: #111827;
  }

  .info-box-purple {
    background: linear-gradient(to bottom right, #8b5cf6, #7c3aed);
    border-radius: 16px;
    padding: 28px;
    color: white;
    box-shadow: 0 10px 25px -5px rgba(139, 92, 246, 0.4);
  }

  .info-box-purple h3 {
    margin: 0 0 20px 0;
    font-size: 1.1rem;
    font-weight: 700;
    border-bottom: 1px solid rgba(255,255,255,0.2);
    padding-bottom: 15px;
  }

  .info-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 14px;
    font-size: 0.95rem;
  }
  .info-label {
    opacity: 0.9;
    font-weight: 500;
  }
  .info-val {
    font-weight: 700;
    text-align: right;
  }

  @media (max-width: 1024px) {
    .container { max-width: 95%; }
    .layout-grid { gap: 24px; }
  }

  @media (max-width: 768px) {
    .layout-grid { grid-template-columns: 1fr; }
    .main-image-placeholder { height: 300px; }
    .price-card, .info-box-purple { padding: 24px; }
  }
</style>