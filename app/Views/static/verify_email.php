<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
</head>

<body>
	<main>
		<header>
			<h1>Konfimasikan Email Kamu</h1>
		</header>
		<p>Yth, <?= $name ?></p>
		<p>Terima kasih sudah mendaftar di DOM Cloud. Segera konfirmasikan email kamu agar kamu bisa memulai menyiapkan hosting.</p>
		<a href="<?= $link ?>" class="button"> Konfirmasi Email Sekarang</a>
		<p>Apabila link diatas bermasalah, salin link berikut:</p>
		<pre><?= $link ?></pre>
		<hr>
		<footer>
			<img src="<?= base_url('logo.svg')?>" alt="Logo" style="width: 80px; float: left; margin-right: 20px">
			<p>DOM Cloud Hosting</p>
			<p>https://dom.my.id</p>
		</footer>
	</main>
</body>

</html>