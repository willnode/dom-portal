<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?php include 'head.php' ?>

<body class="text-center" style="background: url(https://images.unsplash.com/photo-1520995075477-7fddc4fc8cd6?ixlib=rb-1.2.1&auto=format&fit=crop&w=1953&q=80) center/cover #2770c5; position: relative">
	<div class="justify-content-center container d-flex flex-column" style="min-height: 100vh; max-width: 476px">
		<p class="mt-5"><a href="//domcloud.id"><img src="/logo.svg" alt="Logo" width="150px"></a></p>
		<h1 class="mb-4"><?= lang('Interface.enterToPortal') ?></h1>
		<form method="POST" name="loginForm" class="container shadow d-flex flex-column justify-content-center pb-1 pt-3 text-white mb-5">
			<?= csrf_field() ?>
			<?php if ($message) : ?>
				<div class="alert alert-<?= isset($_GET['msg']) ? 'primary' : 'danger' ?>">
					<?= $message ?>
				</div>
			<?php endif ?>
			<input type="text" name="email" placeholder="<?= lang('Interface.email') ?>" class="form-control mb-2">
			<input type="password" name="password" autocomplete="current-password" placeholder="<?= lang('Interface.password') ?>" class="form-control mb-2">
			<input type="submit" value="<?= lang('Interface.login') ?>" class="btn-primary btn btn-block mb-3">
			<div class="separator mb-3">Atau</div>
			<fieldset class="signin-group">
				<?php $r = urlencode(\Config\Services::request()->getGet('r')) ?>
				<a href="<?= href('register' . ($r ? '?r=' . $r : '')) ?>" class="btn d-flex align-items-center btn-light border-secondary mb-2">
					<span class="mx-auto">Register Akun Baru</span>
				</a>
				<button type="button" id="google-signin2" class="btn d-flex align-items-center btn-light  border-secondary">
					<svg role="img" xmlns="http://www.w3.org/2000/svg" width="18px" viewBox="0 0 48 48">
						<path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z" />
						<path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z" />
						<path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z" />
						<path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z" />
						<path fill="none" d="M0 0h48v48H0z" /></svg>
					<span class="mx-auto">Masuk dengan Google</span>
				</button>
				<button type="button" id="github-signin2" onclick="location = '/api/signin/github/'" class="btn d-flex align-items-center btn-light  border-secondary">
					<svg role="img" viewBox="0 0 24 24" width="18px" xmlns="http://www.w3.org/2000/svg">
						<path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12" />
					</svg>
					<span class="mx-auto">Masuk dengan GitHub</span>
				</button>

			</fieldset>
		</form>
		<div class="floating"><small><a href="<?= href('forgot') ?>" class="btn btn-link"><?= lang('Interface.forgotPrompt') ?></a></small></div>
	</div>
	<style>
		form {
			border-radius: 10px;
			-webkit-backdrop-filter: blur(10px);
			backdrop-filter: blur(10px);
			box-sizing: content-box;
			width: calc(100% - 30px) !important;
			border: 2px solid whitesmoke;
		}

		.signin-group>button {
			width: 100%;
			margin-bottom: .5em;
			background: white;
		}

		.floating {
			position: absolute;
			left: 10px;
			bottom: 10px;
		}

		.floating a {
			color: white;
		}

		.separator {
			display: flex;
			align-items: center;
			text-align: center;
			opacity: .8;
		}

		.separator::before,
		.separator::after {
			content: '';
			flex: 1;
			border-bottom: 1px solid white;
		}

		.separator::before {
			margin-right: .5em;
		}

		.separator::after {
			margin-left: .5em;
		}
	</style>
	<script>
		function init() {
			gapi.load('auth2', function() {
				auth2 = gapi.auth2.init({
					client_id: '<?= \Config\Services::request()->config->googleClient ?>',
					cookiepolicy: 'single_host_origin',
					ux_mode: 'redirect',
				});
				auth2.attachClickHandler(document.getElementById('google-signin2'), {});
				/* Ready. Make a call to gapi.auth2.init or some other API */
			});
		}

		function onSuccess(googleUser) {
			console.log('logging in');
			var form = window.loginForm;
			form.email.value = '';
			form.password.value = '';
			form.googleToken.value = googleUser.getAuthResponse().id_token;
			form.submit();
		}

		function onFailure(error) {
			console.log(error);
			alert('Mohon maaf fitur ini masih dalam tahap review oleh Google. Mohon gunakan fitur ini lain waktu.');
		}

		function renderButton() {
			gapi.signin2.render('my-signin2', {
				'scope': 'profile email',
				'width': 240,
				'height': 38,
				'longtitle': true,
				'onsuccess': onSuccess,
				'onfailure': onFailure
			});
		}
	</script>

	<script src="https://apis.google.com/js/platform.js?onload=init" async defer></script>
</body>

</html>