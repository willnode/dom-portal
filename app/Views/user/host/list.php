<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>

  <div class="container-md mb-3" style="max-width: 992px;">
    <div class="card">
      <div class="card-body">
        <?php if (($_GET['status'] ?? '') === 'return') : ?>
          <div class="alert alert-primary">
            <?= lang('Host.purchasedHint') ?>
          </div>
        <?php endif ?>
        <a class="btn btn-primary my-2 float-sm-right" href="/user/host/create"><?= lang('Host.newOrder') ?></a>
        <h1 class="mb-4"><?= lang('Host.listTitle') ?></h1>
        <hr>
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th><?= lang('Interface.domain') ?></th>
                <th><?= lang('Interface.scheme') ?></th>
                <th><?= lang('Interface.status') ?></th>
                <th><?= lang('Interface.expiration') ?></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($list as $host) : ?>
                <tr>
                  <td><a href="/user/host/detail/<?= $host->id ?>"><?= $host->domain ?></a></td>
                  <td><?= $host->plan->alias ?></td>
                  <td><?= ucfirst($host->status) ?></td>
                  <td><?= $host->expiry_at->humanize() ?> &mdash; <?= $host->expiry_at->toDateString() ?></td>
                </tr>
              <?php endforeach ?>
              <?php if (count($list) === 0) : ?>
                <tr>
                  <td colspan="4" class="p-2 text-center small text-muted">
                    <?= lang('Host.emptyList') ?>
                  </td>
                </tr>
                <?php endif ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

</body>

</html>