<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>

  <div class="container">
    <div class="card">
      <div class="card-body">
        <h1 class="mb-2">Detail Domain</h1>
        <?php if ($data->domain_scheme == 1) : ?>
          <p>Domain <b><?= $data->domain_name ?></b> adalah domain gratis dan masa pemakaiannya sama dengan <a href="<?= base_url('user/host/detail/' . ($hosting->hosting_id ?? '')) ?>">durasi hosting yang dipakai</a>.</b></p>
        <?php endif ?>
        <a href="/user/domain" class="mt-3 btn btn-secondary">Kembali</a>

      </div>
    </div>
  </div>

</body>

</html>