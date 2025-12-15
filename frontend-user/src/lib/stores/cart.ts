import { writable } from 'svelte/store';

export type CartItem = {
	id: string;       // ID Cart (Primary Key)
	product_id: number;
	name: string;
	price: number;
	image: string;
	quantity: number;
	stock?: number;
};

export const cart = writable<CartItem[]>([]);
export const selectedItemIds = writable<string[]>([]);

export function addToCart(item: CartItem) {
	cart.update(items => {
		const existing = items.find(i => String(i.id) === String(item.id));
		if (existing) {
			return items.map(i =>
				String(i.id) === String(item.id) ? { ...i, quantity: i.quantity + item.quantity } : i
			);
		}
		return [...items, item];
	});
}

// Helper Hapus (Pastikan konversi string agar aman)
export function removeFromCart(id: string | number) {
	cart.update(items => items.filter(i => String(i.id) !== String(id)));
	selectedItemIds.update(ids => ids.filter(itemId => String(itemId) !== String(id)));
}

export function clearCart() {
	cart.set([]);
	selectedItemIds.set([]);
}