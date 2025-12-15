import { writable } from 'svelte/store';
import { browser } from '$app/environment';

// Definisikan tipe data user
export interface UserProfile {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    phone?: string;
    is_vendor?: boolean;
}

// 1. Ambil data dari localStorage saat awal load (agar data bertahan saat refresh)
const storedUser = browser ? localStorage.getItem('user_data') : null;
const initialUser: UserProfile | null = storedUser ? JSON.parse(storedUser) : null;

// Gunakan initialUser sebagai nilai awal
export const user = writable<UserProfile | null>(initialUser);

// 2. Subscribe: Setiap kali data 'user' berubah, simpan otomatis ke localStorage
if (browser) {
    user.subscribe(value => {
        if (value) {
            localStorage.setItem('user_data', JSON.stringify(value));
        } else {
            // Jika null (logout), hapus dari storage
            localStorage.removeItem('user_data');
        }
    });
}

// Helper untuk reset saat logout
export function clearUser() {
    user.set(null);
}