<script lang="ts">
	import { goto } from '$app/navigation';

	function handleFooterClick(path: string) {
		goto(path);
	}
</script>

<footer class="site-footer">
	<div class="footer-content">
		<div class="footer-section">
			<h3>Tentang PC Store</h3>
			<p>Kami menyediakan komponen PC berkualitas tinggi dengan harga terjangkau.</p>
		</div>

		<div class="footer-section">
			<h3>Kategori</h3>
			<ul>
				<li><a href="/web/categories/processor">Processor</a></li>
				<li><a href="/web/categories/vga">VGA</a></li>
				<li><a href="/web/categories/ram">RAM</a></li>
			</ul>
		</div>

		<div class="footer-section">
			<h3>Layanan</h3>
			<ul>
				<li>Hubungi Kami</li>
				<li>FAQ</li>
			</ul>
		</div>
	</div>

	<div class="footer-bottom">
		<p>&copy; 2024 PC Store. All rights reserved.</p>
	</div>
</footer>

<style>
	.site-footer {
		background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
		color: #e0e0e0;
		padding: 48px 24px 24px;
		margin-top: 60px;
		border-top: 2px solid #8E42E1;
	}

	.footer-content {
		max-width: 1200px;
		margin: 0 auto;
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
		gap: 32px;
		margin-bottom: 32px;
	}

	.footer-section h3 {
		color: #fff;
		font-weight: 700;
		margin-bottom: 16px;
		font-size: 1.1rem;
	}

	.footer-section p {
		line-height: 1.6;
		color: #b0b0b0;
		margin: 0;
	}

	.footer-section ul {
		list-style: none;
		padding: 0;
		margin: 0;
	}

	.footer-section li {
		margin-bottom: 10px;
	}

	.footer-section a {
		color: #b0b0b0;
		text-decoration: none;
		transition: color 0.2s;
	}

	.footer-section a:hover {
		color: #8E42E1;
	}

	.footer-bottom {
		max-width: 1200px;
		margin: 0 auto;
		padding-top: 24px;
		border-top: 1px solid rgba(255, 255, 255, 0.1);
		text-align: center;
		color: #888;
		font-size: 0.9rem;
	}

	@media (max-width: 768px) {
		.footer-content {
			grid-template-columns: repeat(2, 1fr);
		}
	}

	@media (max-width: 480px) {
		.site-footer {
			padding: 32px 16px 16px;
		}

		.footer-content {
			grid-template-columns: 1fr;
		}
	}
</style>
