import { writable } from 'svelte/store';
import { browser } from '$app/environment';

// Tipe data User (sesuaikan dengan respon API database anda)
export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    phone_number?: string;
    address?: string;
}

// 1. Ambil Token & User Data dari LocalStorage saat inisialisasi
const initialToken = browser ? localStorage.getItem('auth_token') : null;
let initialUser: User | null = null;

if (browser) {
    const storedUser = localStorage.getItem('user_data');
    try {
        initialUser = storedUser ? JSON.parse(storedUser) : null;
    } catch (e) {
        console.error("Gagal parsing user_data", e);
        initialUser = null;
    }
}

// 2. Buat Store dengan nilai awal tersebut
export const user = writable<User | null>(initialUser);
export const isLoggedIn = writable<boolean>(!!initialToken);

// Helper untuk Logout
export function logout() {
    if (browser) {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user_data');
    }
    user.set(null);
    isLoggedIn.set(false);
}