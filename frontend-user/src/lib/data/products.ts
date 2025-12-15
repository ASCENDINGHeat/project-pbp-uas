export interface Product {
	id: number;
	vendor_id: number;
	name: string;
	price: number;
	stock_quantity:number;
	image: string;
	created_at: string;
	updated_at: string;
	category: string;
	rating: number;
	reviews: number;
	// NEW: optional description for detail modal
	description?: string;
}

export const products: Product[] = [
	// {
	// 	id: '1',
	// 	name: 'Intel Core i9-13900K',
	// 	price: 7500000,
	// 	image: 'https://via.placeholder.com/250?text=Intel+i9',
	// 	category: 'processor',
	// 	rating: 4.8,
	// 	reviews: 145,
	// 	inStock: true,
	// 	description: 'Intel Core i9 generasi terakhir, performa tinggi untuk gaming dan content creation.'
	// },
	// {
	// 	id: '2',
	// 	name: 'AMD Ryzen 9 7950X',
	// 	price: 8200000,
	// 	image: 'https://via.placeholder.com/250?text=AMD+Ryzen+9',
	// 	category: 'processor',
	// 	rating: 4.9,
	// 	reviews: 198,
	// 	inStock: true,
	// 	description: 'Ryzen 9 dengan banyak core & thread, sangat cocok untuk multitasking berat.'
	// },
	// {
	// 	id: '3',
	// 	name: 'ASUS ROG MAXIMUS Z790',
	// 	price: 3200000,
	// 	image: 'https://via.placeholder.com/250?text=ASUS+ROG',
	// 	category: 'motherboard',
	// 	rating: 4.7,
	// 	reviews: 92,
	// 	inStock: true,
	// 	description: 'Motherboard high-end untuk platform Intel dengan fitur overclock dan I/O lengkap.'
	// },
	// {
	// 	id: 4,
	// 	name: 'NVIDIA RTX 4090',
	// 	price: 15000000,
	// 	image: 'https://via.placeholder.com/250?text=RTX+4090',
	// 	category: 'vga',
	// 	rating: 4.9,
	// 	reviews: 234,
	// 	inStock: true,
	// 	description: 'GPU flagship untuk gaming 4K dan content creation intensif.'
	// },
	// {
	// 	id: 'vga-2',
	// 	name: 'NVIDIA RTX 4080',
	// 	price: 11000000,
	// 	image: 'https://via.placeholder.com/250?text=RTX+4080',
	// 	category: 'vga',
	// 	rating: 4.7,
	// 	reviews: 189,
	// 	inStock: true,
	// 	description: 'Pilihan high-end dengan nilai performa-per-harga solid.'
	// },
	// {
	// 	id: 'ram-1',
	// 	name: 'Corsair Vengeance DDR5 64GB',
	// 	price: 3500000,
	// 	image: 'https://via.placeholder.com/250?text=Corsair+DDR5',
	// 	category: 'ram',
	// 	rating: 4.8,
	// 	reviews: 178,
	// 	inStock: true,
	// 	description: 'RAM DDR5 kapasitas besar, cocok untuk aplikasi memory-intensive.'
	// },
	// {
	// 	id: 'stor-1',
	// 	name: 'Samsung 990 Pro 4TB',
	// 	price: 5800000,
	// 	image: 'https://via.placeholder.com/250?text=Samsung+NVMe',
	// 	category: 'storage',
	// 	rating: 4.9,
	// 	reviews: 267,
	// 	inStock: true,
	// 	description: 'NVMe super cepat dengan kapasitas besar untuk storage utama.'
	// },
	// {
	// 	id: 'psu-1',
	// 	name: 'Corsair RM1000x 1000W',
	// 	price: 2200000,
	// 	image: 'https://via.placeholder.com/250?text=Corsair+PSU',
	// 	category: 'psu',
	// 	rating: 4.8,
	// 	reviews: 134,
	// 	inStock: true,
	// 	description: 'PSU berkualitas tinggi dengan efisiensi dan kabel modular.'
	// }
	{
		id: 1,
    vendor_id: 101, // ID Vendor pemilik produk

    // 2. Info Utama
    name: "NVIDIA GeForce RTX 4060 Ti",
    description: "Kartu grafis mid-range dengan performa gaming 1080p ultra. Dilengkapi fitur DLSS 3.",
    
    // 3. Harga & Stok (Sesuai kolom DB)
    price: 6500000, 
    stock_quantity: 15, // Backend pakai 'stock_quantity', BUKAN 'inStock'

    // 4. Kategorisasi
    category: "vga",

    // 5. Gambar (Format path penyimpanan backend, bukan URL lengkap)
    // Nanti di frontend digabung dengan http://localhost:8000/storage/
    image: "products/rtx-4060-dummy.jpg", 

    // 6. Timestamps (Biasanya dikirim backend)
    created_at: "2023-12-01T10:00:00.000000Z",
    updated_at: "2023-12-05T14:30:00.000000Z",

    // 7. Opsional Frontend (Mocking karena belum ada di DB)
    rating: 0,	
    reviews: 25,
	},
	{
    	id: 3,
    	vendor_id: 2, // Vendor berbeda
    	name: "Samsung 980 Pro SSD 1TB NVMe",
    	description: "SSD Super kencang Gen 4 untuk PS5 dan PC High-End.",
    	price: 1850000,
    	stock_quantity: 50,
    	category: "storage",
    	image: "products/ssd-980-pro.jpg",
    
    	// Rating 0
    	rating: 0,
    	reviews: 0,

    created_at: "2023-12-14T10:00:00.000000Z",
	updated_at:'-',
  }
];

export function getProductsByCategory(category: string): Product[] {
	if (category === 'all') return products;
	return products.filter((p) => p.category === category.toLowerCase());
}
