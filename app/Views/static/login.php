<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?php include 'head.php' ?>

<body>
	<div class="row no-gutters" style="min-height: 100vh">
		<div class="col-md-8 bg-primary text-white d-flex flex-column justify-content-center">
			<div class="container">
				<h1><a href="https://domcloud.id" style="color: white"><?= lang('Interface.appTitle') ?></a></h1>
				<p><?= lang('Interface.enterToPortal') ?></p>
			</div>
		</div>
		<div class="col-md-4">
			<form method="POST" name="loginForm" class="container h-100 d-flex flex-column justify-content-center text-center">
				<?= csrf_field() ?>
				<h1 class="mb-2"><?= lang('Interface.login') ?></h1>
				<?php if ($message) : ?>
					<div class="alert alert-<?= isset($_GET['msg']) ? 'primary' : 'danger' ?>">
						<?= $message ?>
					</div>
				<?php endif ?>
				<div id="my-signin2" class="my-4 mx-auto"></div>
				<input type="hidden" name="googletoken">
				<input type="text" name="email" placeholder="<?= lang('Interface.email') ?>" class="form-control mb-2">
				<input type="password" name="password" autocomplete="current-password" placeholder="<?= lang('Interface.password') ?>" class="form-control mb-2">
				<input type="submit" value="<?= lang('Interface.login') ?>" class="btn-primary btn mb-4">
				<?php $r = urlencode(\Config\Services::request()->getGet('r')) ?>
				<p><a href="<?= href('register' . ($r ? '?r=' . $r : '')) ?>" class="btn btn-link"><?= lang('Interface.registerPrompt') ?></a>
					<a href="<?= href('forgot') ?>" class="btn btn-link"><?= lang('Interface.forgotPrompt') ?></a></p>
			</form>
		</div>
	</div>
	<script>
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
		}

		function renderButton() {
			gapi.signin2.render('my-signin2', {
				'scope': 'profile email',
				'width': 240,
				'height': 50,
				'longtitle': true,
				'onsuccess': onSuccess,
				'onfailure': onFailure
			});
		}
	</script>

	<script src="https://apis.google.com/js/platform.js?onload=renderButton" async defer></script>
</body>

</html>