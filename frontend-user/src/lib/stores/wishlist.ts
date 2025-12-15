import { writable } from 'svelte/store';

// Definisikan tipe data item wishlist
export type WishlistItem = {
    id: number;
    name: string;
    price: number;
    image: string;
};

export const wishlist = writable<WishlistItem[]>([]);

// Fungsi untuk menambah ke store wishlist (Local)
export function addToWishlistStore(item: WishlistItem) {
    wishlist.update(items => {
        if (items.some(i => i.id === item.id)) return items;
        return [...items, item];
    });
}

// Fungsi untuk menghapus dari store wishlist (Local)
export function removeFromWishlistStore(id: number) {
    wishlist.update(items => items.filter(i => i.id !== id));
}