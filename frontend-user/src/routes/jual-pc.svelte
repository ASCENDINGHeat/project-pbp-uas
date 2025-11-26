<script lang="ts">
	import { goto } from '$app/navigation';
</script>

<main class="container">
	<div class="header-section">
		<h1>Jual PC Anda</h1>
		<p>Platform terpercaya untuk menjual komputer bekas Anda</p>
	</div>

	<div class="jual-pc-content">
		<div class="info-section">
			<h2>Cara Menjual PC</h2>
			<div class="steps">
				<div class="step">
					<div class="step-number">1</div>
					<h3>Daftarkan PC Anda</h3>
					<p>Isi data lengkap spesifikasi PC Anda dengan akurat</p>
				</div>
				<div class="step">
					<div class="step-number">2</div>
					<h3>Upload Foto</h3>
					<p>Sertakan foto PC dari berbagai sudut</p>
				</div>
				<div class="step">
					<div class="step-number">3</div>
					<h3>Tentukan Harga</h3>
					<p>Atur harga kompetitif untuk PC Anda</p>
				</div>
				<div class="step">
					<div class="step-number">4</div>
					<h3>Terima Pembeli</h3>
					<p>Kelola pesanan dan pengiriman dengan aman</p>
				</div>
			</div>
		</div>

		<div class="cta-section">
			<h2>Siap Menjual?</h2>
			<p>Mulai jual PC Anda sekarang dan dapatkan pembeli yang tepat!</p>
			<button class="btn-primary" on:click={() => goto('/jual-pc/buat')}>
				Jual PC Sekarang
			</button>
		</div>
	</div>
</main>

<style>
	.container {
		max-width: 1200px;
		margin: 0 auto;
		padding: 40px 20px;
	}

	.header-section {
		margin-bottom: 60px;
		text-align: center;
	}

	.header-section h1 {
		font-size: 2.5rem;
		font-weight: 700;
		color: #1f2d3d;
		margin: 0 0 12px;
	}

	.header-section p {
		font-size: 1.1rem;
		color: #666;
		margin: 0;
	}

	.jual-pc-content {
		display: grid;
		gap: 60px;
	}

	.info-section {
		display: grid;
		gap: 32px;
	}

	.info-section h2 {
		font-size: 2rem;
		font-weight: 700;
		color: #1f2d3d;
		margin: 0;
	}

	.steps {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
		gap: 24px;
	}

	.step {
		background: linear-gradient(135deg, #f5f7fa 0%, #f0f0f5 100%);
		padding: 32px 24px;
		border-radius: 12px;
		text-align: center;
		transition: transform 0.2s, box-shadow 0.2s;
	}

	.step:hover {
		transform: translateY(-4px);
		box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
	}

	.step-number {
		width: 50px;
		height: 50px;
		background: #8E42E1;
		color: #fff;
		border-radius: 50%;
		display: flex;
		align-items: center;
		justify-content: center;
		font-weight: 700;
		font-size: 1.5rem;
		margin: 0 auto 16px;
	}

	.step h3 {
		font-size: 1.1rem;
		font-weight: 700;
		color: #1f2d3d;
		margin: 0 0 8px;
	}

	.step p {
		color: #666;
		margin: 0;
		font-size: 0.95rem;
	}

	.cta-section {
		background: linear-gradient(135deg, #8E42E1 0%, #6b3bff 100%);
		color: #fff;
		padding: 60px 40px;
		border-radius: 16px;
		text-align: center;
	}

	.cta-section h2 {
		font-size: 2rem;
		font-weight: 700;
		margin: 0 0 12px;
	}

	.cta-section p {
		font-size: 1.1rem;
		margin: 0 0 24px;
		opacity: 0.95;
	}

	.btn-primary {
		background: #fff;
		color: #8E42E1;
		border: none;
		padding: 14px 40px;
		border-radius: 8px;
		font-weight: 700;
		font-size: 1.1rem;
		cursor: pointer;
		transition: transform 0.2s, box-shadow 0.2s;
	}

	.btn-primary:hover {
		transform: scale(1.05);
		box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
	}

	@media (max-width: 768px) {
		.container {
			padding: 24px 16px;
		}

		.header-section h1 {
			font-size: 2rem;
		}

		.info-section h2 {
			font-size: 1.6rem;
		}

		.steps {
			grid-template-columns: repeat(2, 1fr);
			gap: 16px;
		}

		.step {
			padding: 24px 16px;
		}

		.cta-section {
			padding: 40px 24px;
		}

		.cta-section h2 {
			font-size: 1.5rem;
		}
	}

	@media (max-width: 480px) {
		.steps {
			grid-template-columns: 1fr;
		}
	}
</style>
