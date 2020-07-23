<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?php include 'head.php' ?>

<body>
  <div class="row no-gutters" style="min-height: 100vh">
    <div class="col-md-8 bg-success text-white d-flex flex-column justify-content-center">
      <div class="container">
        <h1><?= lang('Interface.appTitle') ?></h1>
        <p><?= lang('Interface.registerToPortal') ?></p>
      </div>
    </div>
    <div class="col-md-4">
      <form method="POST" class="container h-100 d-flex flex-column justify-content-center text-center">
        <h1 class="mb-2"><?= lang('Interface.register') ?></h1>
        <?= $validation ? $validation->listErrors() : '' ?>

        <input type="text" name="name" placeholder="<?= lang('Interface.fullName') ?>" class="form-control mb-2">
        <input type="text" name="email" placeholder="<?= lang('Interface.activeEmail') ?>" class="form-control mb-2">
        <input type="text" name="phone" placeholder="Nomor HP lokal (08xx) atau internasional (+...)" class="form-control mb-2">
        <input type="password" name="password" placeholder="<?= lang('Interface.password') ?>" class="form-control mb-2">
        <input type="password" name="passconf" placeholder="<?= lang('Interface.passwordAgain') ?>" class="form-control mb-2">
        <div class="g-recaptcha mb-2 mx-auto" data-sitekey="<?= $recapthaSite ?>"></div>
        <p><small>Dengan mendaftar anda menyetujui Terms of Service Kami.</small></p>
        <input type="submit" value="<?= lang('Interface.register') ?>" class="btn-success btn">

      </form>
    </div>
  </div>
</body>

</html>