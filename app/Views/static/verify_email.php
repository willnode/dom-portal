<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
</head>

<body>
	<main>
		<header>
			<h1><?= lang('Email.verifyTitle') ?></h1>
		</header>
		<p><?= lang('Email.greet') ?>, <?= $name ?></p>
		<?= lang('Email.verifyBody', [$link]) ?>
		<hr>
		<footer>
			<img src="<?= base_url('logo.svg') ?>" alt="Logo" style="width: 80px; float: left; margin-right: 20px">
			<p>DOM Cloud Hosting</p>
			<p>https://domcloud.id</p>
		</footer>
	</main>
</body>

</html>