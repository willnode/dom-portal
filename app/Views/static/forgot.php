<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?php include 'head.php' ?>


<body class="text-center" style="background: url(https://images.unsplash.com/photo-1504370805625-d32c54b16100?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=1953&q=80) center/cover #925a02; position: relative">
	<div class="justify-content-center container d-flex flex-column" style="min-height: 100vh; max-width: 476px">
		<p class="mt-5"><a href="//domcloud.id"><img src="/logo.svg" alt="Logo" width="150px"></a></p>
		<form method="POST" name="loginForm" class="container shadow d-flex flex-column justify-content-center pb-1 pt-3 text-white">
			<h1 class="mb-4"><?= lang('Interface.recoverYourPassword') ?></h1>
			<?= csrf_field() ?>
			<input type="text" name="email" placeholder="<?= lang('Interface.email') ?>" class="form-control mb-2">
			<div class="g-recaptcha mb-2 mx-auto" data-sitekey="<?= $recapthaSite ?>"></div>
			<input type="submit" value="<?= lang('Interface.login') ?>" class="btn-warning btn mb-4">
		</form>

		<div class="d-flex mb-5 text-shadow">
			<a href="<?= href('login') ?>" class="btn btn-link text-white me-auto"><?= lang('Interface.login') ?></a>
			<?php $l = lang('Interface.code');
			foreach (\Config\Services::request()->config->supportedLocales as $lang) : ?>
				<?php if ($l !== $lang) : ?>
					<a href="<?= base_url("$lang/login") ?>" class="btn btn-link text-white"><?= lang('Interface.codename', [], $lang) ?>?</a>
				<?php endif ?>
			<?php endforeach ?>
		</div>
		<div class="floating">
			<small>
				<a href="https://unsplash.com/photos/7KrWmnpRafw" target="_blank" rel="noopener noreferrer">Background by Scott Goodwill</a>
			</small>
		</div>
	</div>
</body>

</html>