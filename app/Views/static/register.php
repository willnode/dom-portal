<!DOCTYPE html>
<html lang="id">

<?php include 'head.php' ?>

<body>

	<form method="POST" class="container">
		<h1>Register</h1>
		<?= $validation ? $validation->listErrors() : ''?>

		<input type="text" name="name" placeholder="Nama Lengkap" class="form-control">
		<input type="text" name="email" placeholder="Email Aktif" class="form-control">
		<input type="text" name="phone" placeholder="Nomor HP (08xx)" class="form-control">
		<input type="password" name="password" placeholder="Password" class="form-control">
		<input type="password" name="passconf" placeholder="Password (Lagi)" class="form-control">
		<input type="submit" value="Register" class="btn-primary btn">
	</form>

</body>

</html>