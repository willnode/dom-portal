<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
	<?= view('user/navbar') ?>

	<div class="container">
		<p>Sebentar lagi kamu akan diarahkan...</p>
		<form action="<?= $uri ?>" method="POST" class="d-none" name="logger">
			<input name="email" value="<?= esc($user, 'attr') ?>">
			<input name="password" value="<?= esc($pass, 'attr') ?>">
    </form>
    <script>
	if (!window.localStorage.getItem('ok_<?= $uri ?>')) {
		// Avoid cookie bug
		var w = window.open('<?= $uri ?>');
		setTimeout(function() {
			w.close();
			window.localStorage.setItem('ok_<?= $uri ?>', "1");
			window.logger.submit();
		}, 1500);
	} else {
      window.logger.submit();
	}
    </script>
	</div>
</body>