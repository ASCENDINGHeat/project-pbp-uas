<main class="container">
	<div class="register-card">
		<h1>Daftar</h1>
		<form>
			<div class="form-group">
				<label for="name">Nama Lengkap</label>
				<input type="text" id="name" placeholder="John Doe" required />
			</div>
			<div class="form-group">
				<label for="email">Email</label>
				<input type="email" id="email" placeholder="your@email.com" required />
			</div>
			<div class="form-group">
				<label for="password">Password</label>
				<input type="password" id="password" placeholder="••••••••" required />
			</div>
			<div class="form-group">
				<label for="confirm">Konfirmasi Password</label>
				<input type="password" id="confirm" placeholder="••••••••" required />
			</div>
			<button type="submit" class="btn-submit">Daftar</button>
		</form>
		<p class="login-link">Sudah punya akun? <a href="/login">Login di sini</a></p>
	</div>
</main>

<style>
	.container {
		max-width: 500px;
		margin: 60px auto;
		padding: 20px;
	}

	.register-card {
		background: #fff;
		padding: 40px;
		border-radius: 12px;
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
	}

	h1 {
		font-size: 2rem;
		font-weight: 700;
		color: #1f2d3d;
		margin: 0 0 32px;
		text-align: center;
	}

	.form-group {
		margin-bottom: 20px;
	}

	label {
		display: block;
		margin-bottom: 8px;
		font-weight: 600;
		color: #1f2d3d;
	}

	input {
		width: 100%;
		padding: 12px;
		border: 1px solid #ddd;
		border-radius: 8px;
		font-size: 1rem;
		box-sizing: border-box;
	}

	input:focus {
		outline: none;
		border-color: #8E42E1;
		box-shadow: 0 0 0 3px rgba(142, 66, 225, 0.1);
	}

	.btn-submit {
		width: 100%;
		padding: 12px;
		background: #8E42E1;
		color: #fff;
		border: none;
		border-radius: 8px;
		font-weight: 700;
		font-size: 1.1rem;
		cursor: pointer;
		margin-top: 20px;
	}

	.btn-submit:hover {
		background: #7b3bcc;
	}

	.login-link {
		text-align: center;
		margin-top: 20px;
		color: #666;
	}

	.login-link a {
		color: #8E42E1;
		text-decoration: none;
		font-weight: 600;
	}

	.login-link a:hover {
		text-decoration: underline;
	}
</style>
