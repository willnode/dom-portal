<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
	<?= view('user/navbar') ?>
	<div class="container text-center" style="max-width: 576px;">
		<h1 class="mb-3"><?= lang('Interface.deleteAccountTitle') ?></h1>
		<?php if ($ok) : ?>
			<div class="card">
				<div class="card-body">
					<div class="alert alert-danger">
						<?= lang('Interface.deleteAccountHint') ?>
					</div>
					<p><?= lang('Interface.deleteAccountConf') ?></p>
					<form method="POST">
						<?= csrf_field() ?>
						<input type="text" name="wordpass" class="form-control" required>
						<input type="submit" value="Hapus Akun" class="mt-2 form-control btn btn-danger">
					</form>
				</div>
			</div>
		<?php else : ?>
			<div class="alert alert-danger">
				<?= lang('Interface.cantDeleteAccount') ?>
			</div>
		<?php endif ?>
		<a href="/user/profile" class="mt-3 btn btn-secondary"><?= lang('Interface.back') ?></a>
	</div>

</body>

</html>