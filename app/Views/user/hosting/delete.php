<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
	<?= view('user/navbar') ?>
	<div class="container text-center" style="max-width: 576px;">
		<h1 class="mb-3">Menghapus Hosting</h1>
		<?php if ($host->plan_id === 1) : ?>
			<div class="card">
				<div class="card-body">
					<div class="alert alert-danger">
						PERHATIAN! Penghapusan hosting bersifat permanen dan
						kami tidak dapat mengembalikan aksi tersebut.
					</div>
					<p>Mohon ketik username hosting untuk menyetujui penghapusan hosting.</p>
					<p>"<?= $host->username ?>"</p>
					<form method="POST">
						<input type="text" name="wordpass" class="form-control" required>
						<input type="submit" value="Hapus Hosting" class="mt-2 form-control btn btn-danger">
					</form>
				</div>
			</div>
		<?php else : ?>
			<div class="alert alert-danger">
				Anda tidak dapat menghapus hosting ini selama masa belum menyentuh masa tanggung.
			</div>
		<?php endif ?>
		<a href="/user/hosting/detail/<?= $host->id ?>" class="mt-3 btn btn-secondary">Kembali</a>
	</div>

</body>

</html>