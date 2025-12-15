import { writable } from 'svelte/store';

// Definisikan tipe data user agar autocomplete jalan (Opsional tapi disarankan)
export interface UserProfile {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    phone?: string;
    is_vendor?: boolean;
}

// Default value null (artinya belum ada data user / belum login)
export const user = writable<UserProfile | null>(null);

// Helper untuk reset saat logout
export function clearUser() {
    user.set(null);
}