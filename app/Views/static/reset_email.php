<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
</head>

<body>
	<main>
		<header>
			<h1>Reset Ulang Password Anda</h1>
		</header>
		<p>Yth, <?= $name ?></p>
		<p>Berikut link untuk mereset password anda. Apabila anda tidak memintanya, mohon abaikan email ini.</p>
		<a href="<?= $link ?>" class="button"> Konfirmasi Email Sekarang</a>
		<p>Apabila link diatas bermasalah, salin link berikut:</p>
		<pre><?= $link ?></pre>
		<hr>
		<footer>
			<p>DOM Cloud Hosting</p>
			<p>https://dom.my.id</p>
		</footer>
	</main>
</body>

</html>