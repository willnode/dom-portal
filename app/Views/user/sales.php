<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>

  <div class="container-md mb-3" style="max-width: 992px;">
    <div class="card">
      <div class="card-body">
        <h1 class="mb-4">Sales</h1>
        <hr>
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Periode</th>
                <th>Gross</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($summ as $k => $v) : ?>
                <tr>
                  <td><?= $k ?></td>
                  <td><?= format_money($v, 'idr') ?></td>
                </tr>
              <?php endforeach ?>
            </tbody>
            <tfoot>
              <tr>
                <th>Total</th>
                <th><?= format_money(array_sum($summ), 'idr') ?></th>
              </tr>
            </tfoot>
          </table>
        </div>
        <hr>
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th><?= lang('Interface.domain') ?></th>
                <th><?= lang('Interface.status') ?></th>
                <th><?= lang('Interface.price') ?></th>
                <th><?= lang('Interface.date') ?></th>
              </tr>
            </thead>
            <tbody>
              <?php /** @var \App\Entities\Purchase[] $list */ ?>
              <?php foreach ($list as $pay) : ?>
                <tr>
                  <td><?= $pay->host_id ? $pay->host->domain : ($pay->domain_id ? $pay->domain->name . ' (domain-only)' : '??') ?></td>
                  <td><?= $pay->status ?></td>
                  <td><?= format_money($pay->metadata->price, $pay->metadata->price_unit) ?></td>
                  <td><?= substr($pay->metadata->_invoiced, 0, 10) ?></td>
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