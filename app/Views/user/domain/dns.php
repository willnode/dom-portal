<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>

  <div class="container">
    <?php /** @var \App\Entities\Domain $domain */ ?>
    <?= view('user/domain/navbar') ?>
    <div class="row">
      <div class="col-md-6">
        <div class="card">
          <div class="card-body">
          </div>

        </div>
      </div>
    </div>
    <a href="/user/domain" class="mt-3 btn btn-secondary"><?= lang('Interface.back') ?></a>
  </div>

</body>

</html>