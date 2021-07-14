<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>
  <div class="container text-center">
    <?= view('user/host/navbar') ?>
    <div class="m-auto" style="max-width: 576px;">
      <h1 class="mb-3"><?= lang('Host.transferTitle') ?></h1>
      <?php if ($host->status === 'active' && !$host->domain_detail) : ?>
        <div class="card">
          <div class="card-body">
            <div class="alert alert-warning">
            <?= lang('Host.transferWarn') ?>
            </div>
            <p><?= lang('Host.transferCurHint') ?> <b><?= $host->login->email ?></b></p>
            <p><?= lang('Host.transferHint') ?></p>
            <form method="POST">
              <?= csrf_field() ?>
              <input type="email" name="email" class="form-control text-center" required>
              <input type="submit" value="Transfer Hosting" class="mt-2 form-control btn btn-warning">
            </form>
          </div>
        </div>
      <?php else : ?>
        <div class="alert alert-warning">
        <?= lang('Host.transferDisabled') ?>
        </div>
      <?php endif ?>
      <a href="/user/host/detail/<?= $host->id ?>" class="mt-3 btn btn-secondary"><?= lang('Interface.back') ?></a>
    </div>
  </div>

</body>

</html>