export interface Product {
	id: string;
	name: string;
	price: number;
	image: string;
	category: string;
	rating: number;
	reviews: number;
	inStock: boolean;
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
		inStock: true
	},
	{
		id: 'proc-2',
		name: 'AMD Ryzen 9 7950X',
		price: 8200000,
		image: 'https://via.placeholder.com/250?text=AMD+Ryzen+9',
		category: 'processor',
		rating: 4.9,
		reviews: 198,
		inStock: true
	},
	{
		id: 'mb-1',
		name: 'ASUS ROG MAXIMUS Z790',
		price: 3200000,
		image: 'https://via.placeholder.com/250?text=ASUS+ROG',
		category: 'motherboard',
		rating: 4.7,
		reviews: 92,
		inStock: true
	},
	{
		id: 'vga-1',
		name: 'NVIDIA RTX 4090',
		price: 15000000,
		image: 'https://via.placeholder.com/250?text=RTX+4090',
		category: 'vga',
		rating: 4.9,
		reviews: 234,
		inStock: true
	},
	{
		id: 'vga-2',
		name: 'NVIDIA RTX 4080',
		price: 11000000,
		image: 'https://via.placeholder.com/250?text=RTX+4080',
		category: 'vga',
		rating: 4.7,
		reviews: 189,
		inStock: true
	},
	{
		id: 'ram-1',
		name: 'Corsair Vengeance DDR5 64GB',
		price: 3500000,
		image: 'https://via.placeholder.com/250?text=Corsair+DDR5',
		category: 'ram',
		rating: 4.8,
		reviews: 178,
		inStock: true
	},
	{
		id: 'stor-1',
		name: 'Samsung 990 Pro 4TB',
		price: 5800000,
		image: 'https://via.placeholder.com/250?text=Samsung+NVMe',
		category: 'storage',
		rating: 4.9,
		reviews: 267,
		inStock: true
	},
	{
		id: 'psu-1',
		name: 'Corsair RM1000x 1000W',
		price: 2200000,
		image: 'https://via.placeholder.com/250?text=Corsair+PSU',
		category: 'psu',
		rating: 4.8,
		reviews: 134,
		inStock: true
	}
];

export function getProductsByCategory(category: string): Product[] {
	if (category === 'all') return products;
	return products.filter((p) => p.category === category.toLowerCase());
}
