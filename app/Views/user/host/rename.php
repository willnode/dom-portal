<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>
  <div class="container" style="max-width: 576px;">
    <h1 class="mb-3"><?= lang('Host.renameTitle') ?></h1>
    <?php if ($host->status !== 'active') : ?>
      <div class="alert alert-danger">
        <?= lang('Host.renameDisabled') ?>
      </div>
    <?php else : ?>
      <div class="card">
        <div class="card-body">
          <p><?= lang('Host.renameHint') ?></p>
          <form method="POST">
            <?= csrf_field() ?>
            <input type="text" name="username" class="form-control mb-3" value="<?= $host->username ?>" required>
            <input type="submit" value="<?= lang('Interface.save') ?>" class="btn btn-primary">
          </form>
        </div>
      </div>
    <?php endif ?>
    <a href="/user/host/detail/<?= $host->id ?>" class="mt-3 btn btn-secondary"><?= lang('Interface.back') ?></a>
  </div>

</body>

</html>