<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
	<?= view('user/navbar') ?>

	<div class="container">
		<p>Sebentar lagi kamu akan diarahkan...</p>
		<form action="<?= $uri ?>" method="POST" class="d-none" name="logger">
			<input name="user" value="<?= esc($user, 'attr') ?>">
			<input name="pass" value="<?= esc($pass, 'attr') ?>">
    </form>
    <script>
      window.logger.submit();
    </script>
	</div>
</body>