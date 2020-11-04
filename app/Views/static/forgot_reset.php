<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?php include 'head.php' ?>

<body class="text-center" style="background: url(https://images.unsplash.com/photo-1504370805625-d32c54b16100?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=1953&q=80) center/cover #925a02; position: relative">
	<div class="justify-content-center container d-flex flex-column" style="min-height: 100vh; max-width: 476px">
		<p class="mt-5"><a href="//domcloud.id"><img src="/logo.svg" alt="Logo" width="150px"></a></p>
		<form method="POST" name="loginForm" class="container shadow d-flex flex-column justify-content-center pb-1 pt-3 text-white">
			<?= csrf_field() ?>
			<h1 class="mb-4 h3"><?= lang('Interface.recoverYourPassword') ?></h1>
			<p><small>Masukkan password baru untuk diganti</small></p>
			<input type="password" name="password" placeholder="<?= lang('Interface.password') ?>" class="form-control mb-2" required>
			<input type="password" name="passconf" placeholder="<?= lang('Interface.passwordAgain') ?>" class="form-control mb-2" required>
			<input type="submit" value="<?= lang('Interface.login') ?>" class="btn-warning btn mb-4">
		</form>
	</div>
</body>

</html>