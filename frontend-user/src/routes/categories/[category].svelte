<script lang="ts">
	import { page } from '$app/stores';
	$: category = $page.params.category;
</script>

<h1>Kategori: {category?.toUpperCase()}</h1>
<p>Menampilkan produk untuk kategori <b>{category}</b>.</p>
<!-- Tambahkan logika/komponen produk sesuai kebutuhan -->
