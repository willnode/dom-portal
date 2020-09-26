<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
	<?= view('user/navbar') ?>
	<div class="container text-center" style="max-width: 576px;">
		<h1 class="mb-3"><?= lang('Host.deleteTitle') ?></h1>
		<?php if ($host->plan_id === 1) : ?>
			<div class="card">
				<div class="card-body">
					<div class="alert alert-danger">
						<?= lang('Host.deleteWarn') ?>
					</div>
					<p><?= lang('Host.deleteHint') ?></p>
					<p>"<?= $host->username ?>"</p>
					<form method="POST">
						<input type="text" name="wordpass" class="form-control" required>
						<input type="submit" value="Hapus Host" class="mt-2 form-control btn btn-danger">
					</form>
				</div>
			</div>
		<?php else : ?>
			<div class="alert alert-danger">
			<p><?= lang('Host.deleteDisabled') ?></p>
			</div>
		<?php endif ?>
		<a href="/user/host/detail/<?= $host->id ?>" class="mt-3 btn btn-secondary"><?= lang('Interface.back')?></a>
	</div>

</body>

</html>