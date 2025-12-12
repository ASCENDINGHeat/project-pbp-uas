export type Product = {
	id: string;
	category?: string;
	name: string;
	brand?: string;
	price?: number;
	specs?: string;
	image?: string;
	imagePlaceholder?: string;
	components?: string[];
	[key: string]: any;
};

export const pcReadyProducts: Product[] = [
	{
		id: 'pc-ready-001',
		category: 'pc-ready',
		name: 'PC Ready Aurora X1',
		brand: 'Aurora',
		price: 12500000,
		specs: 'Intel i5 • 16GB RAM • 512GB SSD • GTX 1660',
		imagePlaceholder: '/images/pc-ready-1.jpg',
		components: [
			'CPU: Intel Core i5-12400',
			'Motherboard: MSI B660M-ITX',
			'RAM: Kingston Fury 16GB DDR4',
			'Storage: Kingston NV1 512GB NVMe',
			'GPU: NVIDIA GTX 1660 6GB',
			'PSU: Corsair 550W 80+Bronze',
			'Cooling: Stock Intel Cooler',
			'Case: Fractal Design Core 1000'
		]
	},
	{
		id: 'pc-ready-002',
		category: 'pc-ready',
		name: 'PC Ready Stealth G5',
		brand: 'Stealth',
		price: 15900000,
		specs: 'AMD Ryzen 5 • 16GB RAM • 1TB SSD • RTX 3060',
		imagePlaceholder: '/images/pc-ready-2.jpg',
		components: [
			'CPU: AMD Ryzen 5 5600X',
			'Motherboard: ASUS ROG STRIX B550-F',
			'RAM: Corsair Vengeance LPX 16GB DDR4',
			'Storage: Samsung 870 QVO 1TB SSD',
			'GPU: NVIDIA RTX 3060 12GB',
			'PSU: EVGA 650W 80+Gold',
			'Cooling: Noctua NH-U12S',
			'Case: Lian Li Lancool 205'
		]
	},
	{
		id: 'pc-ready-003',
		category: 'pc-ready',
		name: 'PC Ready WorkPro W3',
		brand: 'WorkPro',
		price: 9800000,
		specs: 'Intel i3 • 8GB RAM • 256GB SSD • Integrated GPU',
		imagePlaceholder: '/images/pc-ready-3.jpg',
		components: [
			'CPU: Intel Core i3-12100',
			'Motherboard: ASRock H610M-IPS',
			'RAM: SK Hynix 8GB DDR4',
			'Storage: WD Green 256GB SSD',
			'GPU: Intel UHD Graphics 730',
			'PSU: Seasonic 400W 80+Bronze',
			'Cooling: Stock Intel Cooler',
			'Case: Zointra Z3 Micro ATX'
		]
	},
	{
		id: 'pc-ready-004',
		category: 'pc-ready',
		name: 'PC Ready Gamer Z9',
		brand: 'Gamerz',
		price: 20900000,
		specs: 'Intel i7 • 32GB RAM • 1TB NVMe • RTX 4070',
		imagePlaceholder: '/images/pc-ready-4.jpg',
		components: [
			'CPU: Intel Core i7-13700K',
			'Motherboard: ASUS ROG MAXIMUS Z790-HERO',
			'RAM: G.Skill Trident Z5 32GB DDR5',
			'Storage: Sabrent Rocket 4 Plus 1TB NVMe',
			'GPU: NVIDIA RTX 4070 12GB',
			'PSU: Seasonic Prime Ultra 850W 80+Titanium',
			'Cooling: Corsair H150i Elite Capellix',
			'Case: Corsair 5000T RGB'
		]
	},
	// ...tambahkan item sesuai data nyata Anda...
];

export function getPcReadyProducts(limit = 8) {
	return pcReadyProducts.slice(0, limit);
}
