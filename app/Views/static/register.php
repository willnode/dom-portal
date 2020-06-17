<!DOCTYPE html>
<html lang="id">

<?php include 'head.php' ?>

<body>
  <div class="row no-gutters" style="min-height: 100vh">
    <div class="col-md-8 bg-primary text-white d-flex flex-column justify-content-center">
      <div class="container">
        <h1><?= lang('Interface.appTitle') ?></h1>
        <p><?= lang('Interface.registerToPortal') ?></p>
      </div>
    </div>
    <div class="col-md-4">
      <form method="POST" class="container h-100 d-flex flex-column justify-content-center text-center">
        <h1 class="mb-2"><?= lang('Interface.register') ?></h1>
        <?= $validation ? $validation->listErrors() : '' ?>

        <input type="text" name="name" placeholder="Nama Lengkap" class="form-control mb-2">
        <input type="text" name="email" placeholder="Email Aktif" class="form-control mb-2">
        <input type="text" name="phone" placeholder="Nomor HP (08xx)" class="form-control mb-2">
        <input type="password" name="password" placeholder="Password" class="form-control mb-2">
        <input type="password" name="passconf" placeholder="Password (Lagi)" class="form-control mb-2">
        <p><small>Dengan mendaftar anda menyetujui Terms of Service Kami.</small></p>
        <input type="submit" value="Register" class="btn-primary btn">

      </form>
    </div>
  </div>
</body>

</html>