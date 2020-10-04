<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>
  <div class="container" style="max-width: 576px;">
    <h1 class="mb-3"><?= lang('Host.cnameTitle') ?></h1>
    <?php if ($host->liquid_id || $host->status !== 'active' || $host->plan_id === 1) : ?>
      <div class="alert alert-danger">
        <?= lang('Host.cnameDisabled') ?>
      </div>
    <?php else : ?>
      <div class="card">
        <div class="card-body">
          <p><?= lang('Host.cnameHint') ?></p>
          <form method="POST">
            <?= csrf_field() ?>
            <input type="text" class="form-control mb-3" name="cname" value="<?= $host->domain === $host->username . $host->server->domain ? '' : $host->domain ?>">
            <input type="submit" value="<?= lang('Interface.save') ?>" class="btn btn-primary">
          </form>
        </div>
      </div>
    <?php endif ?>
    <a href="/user/host/detail/<?= $host->id ?>" class="mt-3 btn btn-secondary"><?= lang('Interface.back') ?></a>
  </div>

</body>

</html>