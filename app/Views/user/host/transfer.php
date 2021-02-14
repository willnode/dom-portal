<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>
  <div class="container text-center" style="max-width: 576px;">
    <h1 class="mb-3">Transfer Hosting</h1>
    <?php if ($host->status === 'active' && !$host->domain_detail) : ?>
      <div class="card">
        <div class="card-body">
          <div class="alert alert-warning">
            PERHATIAN! Transfer Hosting ini bertujuan untuk memindahkan hosting ke akun lain.
            Sekali anda transfer anda akan kehilangan akses hosting ini karena sudah pindah tangan!
          </div>
          <p>Pemilik saat ini: <b><?= $host->login->email ?></b></p>
          <p>Mohon ketik email akun yang ingin menerima:</p>
          <form method="POST">
            <?= csrf_field() ?>
            <input type="email" name="email" class="form-control text-center" required>
            <input type="submit" value="Transfer Hosting" class="mt-2 form-control btn btn-warning">
          </form>
        </div>
      </div>
    <?php else : ?>
      <div class="alert alert-warning">
        Anda tidak dapat mentransfer hosting ini selama tidak aktif atau ada pembelian domain yang terikat
      </div>
    <?php endif ?>
    <a href="/user/host/detail/<?= $host->id ?>" class="mt-3 btn btn-secondary"><?= lang('Interface.back') ?></a>
  </div>

</body>

</html>