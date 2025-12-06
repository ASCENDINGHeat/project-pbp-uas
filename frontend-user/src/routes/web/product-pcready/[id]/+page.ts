import type { PageLoad } from './$types';
import { pcReadyProducts } from '$lib/data/pc-ready-products';
import { error } from '@sveltejs/kit';

export const load: PageLoad = ({ params }) => {
	const id = params.id;
	const product = pcReadyProducts.find(p => String(p.id) === id);

	if (!product) {
		throw error(404, 'Product tidak ditemukan');
	}

	return { product };
};
