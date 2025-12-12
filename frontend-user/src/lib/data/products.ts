export interface Product {
	id: string;
	name: string;
	price: number;
	image: string;
	category: string;
	rating: number;
	reviews: number;
	inStock: boolean;
	// NEW: optional description for detail modal
	description?: string;
}

export const products: Product[] = [
	{
		id: 'proc-1',
		name: 'Intel Core i9-13900K',
		price: 7500000,
		image: 'https://via.placeholder.com/250?text=Intel+i9',
		category: 'processor',
		rating: 4.8,
		reviews: 145,
		inStock: true,
		description: 'Intel Core i9 generasi terakhir, performa tinggi untuk gaming dan content creation.'
	},
	{
		id: 'proc-2',
		name: 'AMD Ryzen 9 7950X',
		price: 8200000,
		image: 'https://via.placeholder.com/250?text=AMD+Ryzen+9',
		category: 'processor',
		rating: 4.9,
		reviews: 198,
		inStock: true,
		description: 'Ryzen 9 dengan banyak core & thread, sangat cocok untuk multitasking berat.'
	},
	{
		id: 'mb-1',
		name: 'ASUS ROG MAXIMUS Z790',
		price: 3200000,
		image: 'https://via.placeholder.com/250?text=ASUS+ROG',
		category: 'motherboard',
		rating: 4.7,
		reviews: 92,
		inStock: true,
		description: 'Motherboard high-end untuk platform Intel dengan fitur overclock dan I/O lengkap.'
	},
	{
		id: 'vga-1',
		name: 'NVIDIA RTX 4090',
		price: 15000000,
		image: 'https://via.placeholder.com/250?text=RTX+4090',
		category: 'vga',
		rating: 4.9,
		reviews: 234,
		inStock: true,
		description: 'GPU flagship untuk gaming 4K dan content creation intensif.'
	},
	{
		id: 'vga-2',
		name: 'NVIDIA RTX 4080',
		price: 11000000,
		image: 'https://via.placeholder.com/250?text=RTX+4080',
		category: 'vga',
		rating: 4.7,
		reviews: 189,
		inStock: true,
		description: 'Pilihan high-end dengan nilai performa-per-harga solid.'
	},
	{
		id: 'ram-1',
		name: 'Corsair Vengeance DDR5 64GB',
		price: 3500000,
		image: 'https://via.placeholder.com/250?text=Corsair+DDR5',
		category: 'ram',
		rating: 4.8,
		reviews: 178,
		inStock: true,
		description: 'RAM DDR5 kapasitas besar, cocok untuk aplikasi memory-intensive.'
	},
	{
		id: 'stor-1',
		name: 'Samsung 990 Pro 4TB',
		price: 5800000,
		image: 'https://via.placeholder.com/250?text=Samsung+NVMe',
		category: 'storage',
		rating: 4.9,
		reviews: 267,
		inStock: true,
		description: 'NVMe super cepat dengan kapasitas besar untuk storage utama.'
	},
	{
		id: 'psu-1',
		name: 'Corsair RM1000x 1000W',
		price: 2200000,
		image: 'https://via.placeholder.com/250?text=Corsair+PSU',
		category: 'psu',
		rating: 4.8,
		reviews: 134,
		inStock: true,
		description: 'PSU berkualitas tinggi dengan efisiensi dan kabel modular.'
	}
];

export function getProductsByCategory(category: string): Product[] {
	if (category === 'all') return products;
	return products.filter((p) => p.category === category.toLowerCase());
}
