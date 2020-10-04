<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?php include 'head.php' ?>

<body>
	<div class="row no-gutters" style="min-height: 100vh">
		<div class="col-md-8 bg-warning d-flex flex-column justify-content-center">
			<div class="container">
				<h1><?= lang('Interface.appTitle') ?></h1>
				<p><?= lang('Interface.recoverYourPassword') ?></p>
			</div>
		</div>
		<div class="col-md-4">
			<form method="POST" class="container h-100 d-flex flex-column justify-content-center text-center">
				<?= csrf_field() ?>
				<h1 class="mb-4 h3"><?= lang('Interface.recoverYourPassword') ?></h1>
				<p><small>Masukkan password baru untuk diganti</small></p>
				<input type="password" name="password" placeholder="<?= lang('Interface.password') ?>" class="form-control mb-2" required>
				<input type="password" name="passconf" placeholder="<?= lang('Interface.passwordAgain') ?>" class="form-control mb-2" required>
				<input type="submit" value="<?= lang('Interface.login') ?>" class="btn-warning btn mb-4">
			</form>
		</div>
	</div>
</body>

</html>