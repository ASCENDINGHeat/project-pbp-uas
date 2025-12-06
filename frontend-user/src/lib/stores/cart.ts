import { writable } from 'svelte/store';

export type CartItem = {
	id: string;
	name: string;
	price: number;
	image: string;
	quantity: number;
};

export const cart = writable<CartItem[]>([]);

// Fungsi untuk menambah item ke keranjang
export function addToCart(item: CartItem) {
	cart.update(items => {
		const existing = items.find(i => i.id === item.id);
		if (existing) {
			return items.map(i =>
				i.id === item.id ? { ...i, quantity: i.quantity + item.quantity } : i
			);
		}
		return [...items, item];
	});
}

// Fungsi untuk menghapus item dari keranjang
export function removeFromCart(id: string) {
	cart.update(items => items.filter(i => i.id !== id));
}

// Fungsi untuk mengosongkan keranjang
export function clearCart() {
	cart.set([]);
}
