<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?php include 'head.php' ?>

<body class="text-center" style="background: url(https://images.unsplash.com/photo-1594664895623-94fd1857c50e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=1953&q=80) center/cover #104203; position: relative">
  <div class="justify-content-center container d-flex flex-column" style="min-height: 100vh; max-width: 476px">
    <p class="mt-5"><a href="//domcloud.id"><img src="/logo.svg" alt="Logo" width="150px"></a></p>
    <form method="POST" name="loginForm" class="container shadow d-flex flex-column justify-content-center pb-1 pt-3 text-white">

      <?= csrf_field() ?>
      <h1 class="mb-4"><?= lang('Interface.registerToPortal') ?></h1>
      <?= $errors ?>

      <input type="text" name="name" placeholder="<?= lang('Interface.fullName') ?>" value="<?= old('name') ?>" class="form-control mb-2">
      <input type="text" name="email" placeholder="<?= lang('Interface.activeEmail') ?>" value="<?= old('email') ?>" class="form-control mb-2">
      <input type="password" name="password" placeholder="<?= lang('Interface.password') ?>" class="form-control mb-2" autocomplete="new-password">
      <div class="g-recaptcha mb-2 mx-auto" data-sitekey="<?= $recapthaSite ?>"></div>
      <p><small><?= lang('Interface.registerAgreement') ?></small></p>
      <input type="submit" value="<?= lang('Interface.register') ?>" class="btn-success btn mb-3">

    </form>
		<div class="d-flex mb-5 text-shadow">
			<a href="<?= href('login') ?>" class="btn btn-link text-white mr-auto"><?= lang('Interface.login') ?></a>
			<?php $l = lang('Interface.code');
			foreach (\Config\Services::request()->config->supportedLocales as $lang) : ?>
				<?php if ($l !== $lang) : ?>
					<a href="<?= base_url("$lang/register") ?>" class="btn btn-link text-white"><?= lang('Interface.codename', [], $lang) ?>?</a>
				<?php endif ?>
			<?php endforeach ?>
		</div>

		<div class="floating">
			<small>
				<a href="https://unsplash.com/photos/aZt7Sh40NwM" target="_blank" rel="noopener noreferrer">Background by Ian Cylkowski</a>
			</small>
		</div>
  </div>

</body>

</html>