import adapter from '@sveltejs/adapter-node'; // Change from adapter-auto

/** @type {import('@sveltejs/kit').Config} */
const config = {
    kit: {
        adapter: adapter()
    }
};

export default config;
