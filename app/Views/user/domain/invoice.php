<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>

  <div class="container">
    <?php /** @var \App\Entities\Domain $domain */ ?>
    <h1 class="mb-2">Detail Domain</h1>
    <div class="row">
      <div class="col-md-6">
        <div class="card">
          <div class="card-body">
            <?php if ($domain->status === 'pending') : ?>
              <form method="post" class="my-2">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="pay">
                <input type="submit" class="btn btn-primary" value="<?= lang('Host.finishPayment') ?>">
              </form>
            <?php endif ?>
          </div>

        </div>
      </div>
    </div>
    <a href="/user/domain" class="mt-3 btn btn-secondary">Kembali</a>
  </div>

</body>

</html>