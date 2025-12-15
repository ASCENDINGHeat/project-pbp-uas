import { writable } from 'svelte/store';

export type CartItem = {
	id: string;       // ID dari tabel cart (Primary Key)
	product_id: number;
	name: string;
	price: number;
	image: string;
	quantity: number;
	stock?: number;   // Info stok dari backend
};

export const cart = writable<CartItem[]>([]);

// Store baru: Menyimpan daftar ID (cart.id) yang dicentang user
export const selectedItemIds = writable<string[]>([]);

// Helper: Tambah item (opsional, untuk optimis UI)
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

// Helper: Hapus item
export function removeFromCart(id: string) {
	cart.update(items => items.filter(i => i.id !== id));
	// Hapus juga dari daftar centang jika itemnya dihapus
	selectedItemIds.update(ids => ids.filter(itemId => itemId !== id));
}

// Helper: Reset
export function clearCart() {
	cart.set([]);
	selectedItemIds.set([]);
}