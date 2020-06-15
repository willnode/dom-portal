<!DOCTYPE html>
<html lang="id">

<?php include 'head.php' ?>

<body>
	<div class="row no-gutters" style="min-height: 100vh">
		<div class="col-md-8 bg-primary text-white d-flex flex-column justify-content-center">
			<div class="container">
				<h1><?= lang('Interface.appTitle') ?></h1>
				<p><?= lang('Interface.enterToPortal') ?></p>
			</div>
		</div>
		<div class="col-md-4">
			<form method="POST" class="container h-100 d-flex flex-column justify-content-center text-center">
				<h1 class="mb-2"><?= lang('Interface.login') ?></h1>
				<input type="text" name="email" placeholder="Email" class="form-control mb-2">
				<input type="password" name="password" autocomplete="current-password"
				placeholder="Password" class="form-control mb-2">
				<input type="submit" value="<?= lang('Interface.login') ?>" class="btn-primary btn mb-4">
				<p><a href="<?= href('register') ?>" class="btn btn-link"><?= lang('Interface.registerPrompt') ?></a></p>
			</form>
		</div>
	</div>

</body>

</html>