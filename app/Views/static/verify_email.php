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
			<p>DOM Cloud Hosting Solution Indonesia</p>
			<p>https://dom.my.id</p>
		</footer>
	</main>
</body>

</html>