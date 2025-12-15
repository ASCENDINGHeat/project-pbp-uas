import type { PageLoad } from './$types';
import { error } from '@sveltejs/kit';
import { PUBLIC_API_URL } from '$env/static/public';

export const load: PageLoad = async ({ params, fetch }) => {
    try {
        const res = await fetch(`${PUBLIC_API_URL}/product/${params.id}`);

        if (!res.ok) {
            throw error(res.status, 'Product not found');
        }

        const apiData = await res.json();

        // Map API Data to Frontend Interface
        const product = {
            id: apiData.id.toString(),
            name: apiData.title,
            price: Number(apiData.price),
            // Use the accessor 'image_url' from Product model, or fallback
            image: apiData.image_url || '/images/placeholder.jpg', 
            // 'details' is a JSON column; handle missing fields safely
            category: apiData.details?.category || 'Uncategorized',
            description: apiData.details?.description || 'No description available.',
            stock: apiData.stock_quantity,
            inStock: apiData.stock_quantity > 0,
            status: apiData.stock_quantity > 0 ? 'Tersedia' : 'Habis',
            // Vendor relationship loaded via ProductController::show
            shop_name: apiData.vendor?.store_name || 'Official Store',
            // Placeholders for data not yet in API
            rating: 5.0,
            reviews: 0,
            sold: 0 
        };

        return { product };

    } catch (err) {
        console.error("Error fetching product:", err);
        throw error(404, 'Product not found');
    }
};