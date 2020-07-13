<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>

  <div class="container">
    <div class="alert alert-danger">
      Anda harus mengkonfirmasi email anda terlebih dahulu, silahkan cek inbox email <b><?= esc($email) ?></b>.
    </div>
    <div class="d-flex">
      <form method="post" class="my-2">
        <input type="hidden" name="action" value="resend">
        <input type="submit" class="btn btn-success" onclick="return confirm('Yakin untuk mengirim ulang email? Tindakan ini juga melogout sesi anda sekarang. Mohon cek inbox email setelah ini.')" value="Kirim Ulang Email Konfirmasi">
      </form>
      <a href="/user/profile" class="btn btn-secondary ml-auto my-2">Salah email?</a>
    </div>
  </div>
</body>