import type { PageLoad } from './$types';
import { products } from '$lib/data/products';
import { error } from '@sveltejs/kit';

export const load: PageLoad = ({ params }) => {
	// Cari produk berdasarkan id dari params
	const product = products.find(p => p.id === params.id);

	if (!product) {
		throw error(404, 'Product not found');
	}

	return { product };
};
