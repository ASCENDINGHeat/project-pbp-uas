<script lang="ts">
    import { goto } from '$app/navigation';
    import { onMount } from 'svelte';
    import { PUBLIC_API_URL } from '$env/static/public';

    // 1. Types
    interface Slide {
        id: string;
        image: string;
        title: string;
        caption: string;
    }

    interface Promo {
        id: string;
        image: string;
        title: string;
        caption?: string; 
    }

    // --- DATA SLIDER KIRI (UTAMA) ---
    let slides: Slide[] = [
        { id: 's1', image: '/images/banner-utama.jpg', title: 'GeForce RTX', caption: 'GPU terbaru' },
        { id: 's2', image: '/images/GPU-NVIDIA-GeForce-RTX-5090.jpg', title: 'RTX Series', caption: 'Performa maksimal' },
        { id: 's3', image: '/images/pc-ready.jpg', title: 'PC Ready', caption: 'Siap pakai' }
    ];
    // --- DATA SLIDER KANAN (PROMO) ---
    const promos: Promo[] = [
        { id: 'p1', image: '/images/promo.jpg', title: 'Promo Besar', caption: 'Diskon 50%' },
        { id: 'p2', image: '/images/pc-ready1.jpg', title: 'GTR ZERO', caption: 'Edisi Terbatas' },
        { id: 'p3', image: '/images/promosi.jpg', title: 'Why PC Store', caption: 'Garansi Resmi' }
    ];
    // --- LOGIC SLIDER ---
    let idx = 0;
    let timer: ReturnType<typeof setInterval> | null = null;
    let promoIdx = 0;
    let promoTimer: ReturnType<typeof setInterval> | null = null;

    function next() { idx = (idx + 1) % slides.length; }
    function prev() { idx = (idx - 1 + slides.length) % slides.length; }
    function go(i: number) { idx = i; }

    function nextPromo() { promoIdx = (promoIdx + 1) % promos.length; }
    function prevPromo() { promoIdx = (promoIdx - 1 + promos.length) % promos.length; }
    function goPromo(i: number) { promoIdx = i; }

    function startAuto() {
        if (timer) clearInterval(timer);
        timer = setInterval(next, 4500);
    }
    function stopAuto() { if (timer) clearInterval(timer); }

    function startPromoAuto() {
        if (promoTimer) clearInterval(promoTimer);
        promoTimer = setInterval(nextPromo, 5000); 
    }
    function stopPromoAuto() { if (promoTimer) clearInterval(promoTimer); }

    let featured: any[] = [];
    let isLoading = true;

    onMount(async () => {
        startAuto();
        startPromoAuto();

        const y = sessionStorage.getItem('pc-list-scroll');
        if (y) {
            requestAnimationFrame(()=>{
                window.scrollTo(0, Number(y));
                sessionStorage.removeItem('pc-list-scroll');
            });
        }

        try {
            const endpoint = PUBLIC_API_URL ? `${PUBLIC_API_URL}/product` : 'http://127.0.0.1:8000/api/product';

            const res = await fetch(endpoint);
            if (res.ok) {
                const data = await res.json();
                const productsFromApi = data.data || [];

                // Mapping format Backend (Laravel) -> Frontend (Svelte)
                featured = productsFromApi.map((item: any) => {
                    return {
                        id: String(item.id),
                        name: item.title,
                        image: item.image_url || item.imagePlaceholder || '/images/placeholder.png',
                        price: Number(item.price),
                        rating: 5.0, 
                        reviews: 0
                    };
                }).slice(0, 8);
            } else {
                console.error("Gagal mengambil data produk");
            }
        } catch (err) {
            console.error("Error fetching API:", err);
        } finally {
            isLoading = false;
        }

        return () => {
            stopAuto();
            stopPromoAuto();
        };
    });

    function handleImgError(e: Event) {
        const img = e.target as HTMLImageElement;
        img.src = 'https://via.placeholder.com/1200x600?text=Image+Not+Found';
        img.onerror = null;
    }
    
    function navigateToPcReady(event: MouseEvent | PointerEvent, id: string) {
        if (event instanceof MouseEvent || event instanceof PointerEvent) {
            if ((event as any).metaKey || (event as any).ctrlKey || (event as any).shiftKey || (event as any).altKey || (event as any).button !== 0) {
                return;
            }
        }

        try {
            sessionStorage.setItem('pc-list-scroll', String(window.scrollY || window.pageYOffset || document.documentElement.scrollTop || 0));
        } catch (e) {}
        
        event.preventDefault();
        const href = `/web/product/${id}`;
        try {
            const p = goto(href);
            if (p && typeof (p as Promise<any>).catch === 'function') {
                (p as Promise<any>).catch(() => window.location.assign(href));
            }
        } catch {
            window.location.assign(href);
        }
    }

    const formatPrice = (price: number) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(price);
    };

    function handleWishlistClick(e: MouseEvent) {
        console.log('Wishlist clicked!');
    }
</script>

<div class="p-5 bg-transparent">
    <div class="max-w-[95%] mx-auto grid grid-cols-1 lg:grid-cols-[3fr_1fr] gap-5 h-auto lg:h-[480px]">
        
        <div class="relative overflow-hidden rounded-xl bg-slate-900 w-full h-[350px] lg:h-full shadow-md group" 
                role="region" 
                aria-label="Main Carousel"
                on:mouseenter={stopAuto} 
                on:mouseleave={startAuto}>
            
            <div class="flex h-full w-full transition-transform duration-500 ease-[cubic-bezier(0.25,1,0.5,1)]" style="transform:translateX({-idx * 100}%);">
                {#each slides as s (s.id)}
                    <div class="min-w-full relative h-full">
                        <img src={s.image} alt={s.title} class="w-full h-full object-cover block" on:error={handleImgError} />
                        <div class="absolute left-8 bottom-10 text-white z-[2] pointer-events-none drop-shadow-[0_4px_12px_rgba(0,0,0,0.8)]">
                            <h3 class="m-0 mb-1.5 text-4xl lg:text-5xl font-extrabold tracking-tight">{s.title}</h3>
                            <p class="m-0 text-lg">{s.caption}</p>
                        </div>
                    </div>
                {/each}
            </div>

            <button class="absolute top-1/2 -translate-y-1/2 left-3 w-9 h-9 rounded-full bg-white/20 backdrop-blur-sm text-white border border-white/30 flex items-center justify-center cursor-pointer z-10 transition-all duration-200 opacity-0 group-hover:opacity-100 hover:bg-white/40 text-xl pb-1" on:click={prev}>‹</button>
            <button class="absolute top-1/2 -translate-y-1/2 right-3 w-9 h-9 rounded-full bg-white/20 backdrop-blur-sm text-white border border-white/30 flex items-center justify-center cursor-pointer z-10 transition-all duration-200 opacity-0 group-hover:opacity-100 hover:bg-white/40 text-xl pb-1" on:click={next}>›</button>
            
            <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1.5 z-10">
                {#each slides as _, i}
                    <button class="h-1.5 rounded-full border-none cursor-pointer transition-all duration-300 p-0 {i === idx ? 'bg-white w-5' : 'bg-white/40 w-1.5 hover:bg-white/60'}" on:click={() => go(i)}></button>
                {/each}
            </div>
        </div>

        <aside class="relative overflow-hidden rounded-xl bg-slate-900 w-full h-[250px] lg:h-full shadow-md group"
                role="region"
                aria-label="Promo Carousel"
                on:mouseenter={stopPromoAuto}
                on:mouseleave={startPromoAuto}>

            <div class="flex h-full w-full transition-transform duration-500 ease-[cubic-bezier(0.25,1,0.5,1)]" style="transform:translateX({-promoIdx * 100}%);">
                {#each promos as p (p.id)}
                    <div class="min-w-full relative h-full">
                        <img src={p.image} alt={p.title} class="w-full h-full object-cover block" on:error={handleImgError} />
                        <div class="absolute left-8 bottom-10 text-white z-[2] pointer-events-none drop-shadow-[0_4px_12px_rgba(0,0,0,0.8)]">
                            <h3 class="m-0 mb-1.5 text-3xl font-extrabold tracking-tight">{p.title}</h3>
                            {#if p.caption}<p class="m-0 text-sm">{p.caption}</p>{/if}
                        </div>
                    </div>
                {/each}
            </div>

            <button class="absolute top-1/2 -translate-y-1/2 left-3 w-9 h-9 rounded-full bg-white/20 backdrop-blur-sm text-white border border-white/30 flex items-center justify-center cursor-pointer z-10 transition-all duration-200 opacity-0 group-hover:opacity-100 hover:bg-white/40 text-xl pb-1" on:click={prevPromo}>‹</button>
            <button class="absolute top-1/2 -translate-y-1/2 right-3 w-9 h-9 rounded-full bg-white/20 backdrop-blur-sm text-white border border-white/30 flex items-center justify-center cursor-pointer z-10 transition-all duration-200 opacity-0 group-hover:opacity-100 hover:bg-white/40 text-xl pb-1" on:click={nextPromo}>›</button>
            
            <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1.5 z-10">
                {#each promos as _, i}
                    <button class="h-1.5 rounded-full border-none cursor-pointer transition-all duration-300 p-0 {i === promoIdx ? 'bg-white w-5' : 'bg-white/40 w-1.5 hover:bg-white/60'}" on:click={() => goPromo(i)}></button>
                {/each}
            </div>
        </aside>

    </div>
</div>

<div class="max-w-[95%] mx-auto mt-10 px-5 pb-[70px] max-sm:px-2.5">
    
    <div class="grid gap-5 grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 max-sm:gap-2.5">
        {#each featured as product (product.id)}
            <a
                href="/web/product/{product.id}"
                class="group flex flex-col bg-white rounded-xl border border-slate-200 no-underline transition-all duration-200 overflow-hidden relative h-full text-inherit hover:-translate-y-1 hover:shadow-lg hover:border-slate-300"
                data-sveltekit-preload-data="hover"
                aria-label={"Lihat PC Ready"}
                on:pointerdown|capture={(e) => navigateToPcReady(e as PointerEvent, String(product.id))}
                on:click|capture={(e) => navigateToPcReady(e as MouseEvent, String(product.id))}
            >
                <div class="w-full aspect-square bg-slate-50 flex items-center justify-center p-5 relative">
                    <img 
                        src={product.image || product.imagePlaceholder || '/images/placeholder.png'} 
                        alt={product.name} 
                        class="max-w-full max-h-full object-contain mix-blend-multiply"
                        on:error={handleImgError} 
                    />
                    
                    <button 
                        class="absolute top-2.5 right-2.5 bg-white border border-slate-200 rounded-full w-8 h-8 flex items-center justify-center cursor-pointer text-red-500 transition-all shadow-sm z-10 hover:scale-110 hover:shadow-md" 
                        on:click|stopPropagation={handleWishlistClick} 
                        aria-label="Tambah ke wishlist"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01L8 2.748zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143c.06.055.119.112.176.171a3.12 3.12 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15z"/>
                        </svg>
                    </button>
                </div>

                <div class="p-4 flex-1 flex flex-col gap-1.5 max-md:p-3">
                    <div class="font-medium text-slate-700 text-[0.95rem] leading-snug line-clamp-2 h-[42px] max-md:text-sm">{product.name}</div>
                    
                    <div class="flex items-center gap-1 text-[0.85rem] text-slate-500 mb-1">
                        <span class="text-amber-400">★</span> 
                        <span>5.0</span>
                    </div>

                    <div class="font-extrabold text-lg text-purple-600 mt-auto max-md:text-base">
                        {product.price ? formatPrice(product.price) : '-'}
                    </div>
                </div>

                <div class="px-4 pb-4">
                    <div class="w-full py-2.5 bg-blue-500 text-white font-semibold text-sm rounded-lg text-center transition-colors group-hover:bg-blue-600 max-md:text-xs max-md:py-2">
                        Lihat Series
                    </div>
                </div>
            </a>
        {/each}
    </div>
 
    <div class="mt-10 flex items-center justify-center gap-4">
        <button class="px-4.5 py-2.5 rounded-lg border-none bg-slate-200 text-slate-600 font-medium cursor-pointer transition-colors hover:bg-slate-300">‹ Previous</button>
        <span class="px-4.5 py-2.5 rounded-lg bg-blue-600 text-white font-medium">1</span>
        <button class="px-4.5 py-2.5 rounded-lg border-none bg-slate-200 text-slate-600 font-medium cursor-pointer transition-colors hover:bg-slate-300">Next ›</button>
    </div>
</div>