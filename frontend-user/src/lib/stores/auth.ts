import { writable } from 'svelte/store';
import { browser } from '$app/environment';

// Tipe data User (sesuaikan dengan database)
export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
}

// Inisialisasi Store
// Jika di browser ada token, kita asumsikan isLoggedIn true dulu (sampai divalidasi)
const initialToken = browser ? localStorage.getItem('auth_token') : null;

export const user = writable<User | null>(null);
export const isLoggedIn = writable<boolean>(!!initialToken);

// Helper untuk Logout
export function logout() {
    if (browser) {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user_data'); // Jika ada
    }
    user.set(null);
    isLoggedIn.set(false);
}