import tailwindcss from '@tailwindcss/vite';
import { sveltekit } from '@sveltejs/kit/vite';
import { defineConfig } from 'vite';

export default defineConfig({
	plugins: [tailwindcss(), sveltekit()],
	server: {
		// Allows all ngrok subdomains (easier than updating the ID every time)
		allowedHosts: ['814c3767704f.ngrok-free.app'], 
		
		host: true,
	}
});
