import { writable } from 'svelte/store';

export interface CartItem {
	id: string;
	name: string;
	price: number;
	quantity: number;
	image?: string;
}

interface CartState {
	items: CartItem[];
	total: number;
	count: number;
}

const initialState: CartState = {
	items: [],
	total: 0,
	count: 0
};

function createCartStore() {
	const { subscribe, set, update } = writable<CartState>(initialState);

	return {
		subscribe,
		addItem: (item: CartItem) =>
			update((state) => {
				const existingItem = state.items.find((i) => i.id === item.id);

				if (existingItem) {
					existingItem.quantity += item.quantity;
				} else {
					state.items.push(item);
				}

				state.count = state.items.reduce((sum, i) => sum + i.quantity, 0);
				state.total = state.items.reduce((sum, i) => sum + i.price * i.quantity, 0);

				return state;
			}),

		removeItem: (itemId: string) =>
			update((state) => {
				state.items = state.items.filter((i) => i.id !== itemId);
				state.count = state.items.reduce((sum, i) => sum + i.quantity, 0);
				state.total = state.items.reduce((sum, i) => sum + i.price * i.quantity, 0);
				return state;
			}),

		updateQuantity: (itemId: string, quantity: number) =>
			update((state) => {
				const item = state.items.find((i) => i.id === itemId);
				if (item && quantity > 0) {
					item.quantity = quantity;
					state.count = state.items.reduce((sum, i) => sum + i.quantity, 0);
					state.total = state.items.reduce((sum, i) => sum + i.price * i.quantity, 0);
				}
				return state;
			}),

		clearCart: () => set(initialState)
	};
}

export const cart = createCartStore();
