<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
	<?= view('user/navbar') ?>
	<div class="container text-center" style="max-width: 576px;">
		<h1 class="mb-3">Menghapus Akun</h1>
		<?php if ($ok) : ?>
			<div class="card">
				<div class="card-body">
					<div class="alert alert-danger">
						PERHATIAN! Penghapusan akun bersifat permanen dan
						kami tidak dapat mengembalikan aksi tersebut.
					</div>
					<p>Mohon ketik "YA" untuk menyetujui penghapusan akun.</p>
					<form method="POST">
						<?= csrf_field() ?>
						<input type="text" name="wordpass" class="form-control" required>
						<input type="submit" value="Hapus Akun" class="mt-2 form-control btn btn-danger">
					</form>
				</div>
			</div>
		<?php else : ?>
			<div class="alert alert-danger">
				Anda tidak dapat menghapus akun ini jika masih ada hosting atau domain yang terdaftar.
			</div>
		<?php endif ?>
		<a href="/user/profile" class="mt-3 btn btn-secondary">Kembali</a>
	</div>

</body>

</html>