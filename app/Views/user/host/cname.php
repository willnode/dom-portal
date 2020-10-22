<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>
  <div class="container text-center" style="max-width: 576px;">
    <h1 class="mb-3"><?= lang('Host.cnameTitle') ?></h1>
    <?php if ($host->status !== 'active' || $host->plan_id === 1) : ?>
      <div class="alert alert-danger">
        <?= lang('Host.cnameDisabled') ?>
      </div>
    <?php else : ?>
      <div class="card">
        <form method="POST" class="card-body">
          <p>Domain saat ini: <b><?= $host->domain ?></b></p>
          <p><?= lang('Host.cnameHint') ?></p>
          <?= csrf_field() ?>
          <?php $default = $host->username . $host->server->domain ?>
          <input type="text" class="form-control text-center mb-3" name="cname" value="<?= $host->domain === $default ? '' : $host->domain ?>" placeholder="Kosongkan untuk default (<?= $default ?>)">
          <p>Pastikan bahwa anda punya kepemilikan domain tersebut dan anda sudah mengarahkan DNS record dengan benar.</p>
          <input type="submit" value="<?= lang('Interface.save') ?>" class="btn btn-primary btn-block">
        </form>
      </div>
    <?php endif ?>
    <a href="/user/host/detail/<?= $host->id ?>" class="mt-3 btn btn-secondary"><?= lang('Interface.back') ?></a>
  </div>

</body>

</html>