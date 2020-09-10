<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>

  <div class="container">
    <div class="alert alert-danger">
    <?= lang('Interface.confirmationHint') ?> <b><?= esc($email) ?></b>.
    </div>
    <div class="d-flex">
      <form method="post" class="my-2">
        <input type="hidden" name="action" value="resend">
        <input type="submit" class="btn btn-success" onclick="return confirm('<?= lang('Interface.confirmationPrompt') ?>')" value="<?= lang('Interface.resendConfirmationEmail') ?>">
      </form>
      <a href="/user/profile" class="btn btn-secondary ml-auto my-2"><?= lang('Interface.wrongEmail') ?></a>
    </div>
  </div>
</body>