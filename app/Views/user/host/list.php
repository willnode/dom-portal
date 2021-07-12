<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <style>
    .favicon {
      width: 24px;
      height: 24px;
      object-fit: contain;
      vertical-align: middle;
    }

    i.favicon::before {
      font-size: 24px;
    }

    img.favicon::before {
      content: ' ';
      display: block;
      position: absolute;
      height: 24px;
      width: 24px;
      background: url(/default_favicon.png) center/contain;
    }
  </style>
  <?= view('user/navbar') ?>

  <div class="container-md mb-3" style="max-width: 992px;">
    <div class="card">
      <div class="card-body">
        <?php if (($_GET['status'] ?? '') === 'return') : ?>
          <div class="alert alert-primary">
            <?= lang('Host.purchasedHint') ?>
          </div>
        <?php endif ?>
        <a class="btn btn-primary my-2 float-sm-end" href="/user/host/create"><i class="fas fa-plus me-2"></i> <?= lang('Host.newOrder') ?></a>
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
                <?php
                $severity = $host->expiry_at->getTimestamp() < time() ? 2 : ($host->expiry_at->subMonths(1)->getTimestamp() < time() ? 1 : 0);

                ?>
                <tr>
                  <td>
                    <?php if ($host->status === 'active') : ?>
                      <img src="http://<?= $host->domain ?>/favicon.ico" loading="lazy" class="me-2 favicon" alt="">
                    <?php else : ?>
                      <i class="fas fa-question-circle me-2 favicon"></i>
                    <?php endif ?>
                    <a href="/user/host/detail/<?= $host->id ?>"><?= $host->domain ?></a>
                  </td>
                  <td><?= $host->plan->alias ?></td>
                  <td><?= ucfirst($host->status) ?></td>
                  <td class="<?= ['', 'bg-warning text-danger font-weight-bold', 'bg-dark text-danger font-weight-bold'][$severity] ?>">
                    <i class="fas <?= ['me-3', 'fa-exclamation-triangle me-2', 'fa-calendar-times me-2'][$severity] ?>"></i> <?= humanize($host->expiry_at) ?> &mdash; <?= $host->expiry_at->toDateString() ?>
                  </td>
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