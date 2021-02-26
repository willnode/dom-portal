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
        <h3>Monthly Gross</h3>
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Periode</th>
                <th>Gross</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($gross as $k => $v) : ?>
                <tr>
                  <td><?= $k ?></td>
                  <td><?= format_money($v, 'idr') ?></td>
                </tr>
              <?php endforeach ?>
            </tbody>
            <tfoot>
              <tr>
                <th>Total</th>
                <th><?= format_money(array_sum($gross), 'idr') ?></th>
              </tr>
            </tfoot>
          </table>
        </div>
        <hr>
        <h3>Active Hosts Subscriptions</h3>
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Plan</th>
                <th>Qty</th>
                <th>GB</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach (imsort(array_keys($plans)) as $k) : ?>
                <tr>
                  <td><?= $item_plans[$k]->alias ?></td>
                  <td><?= $plans[$k] ?></td>
                  <td><?= format_bytes($plans[$k] * $item_plans[$k]->disk) ?></td>
                  <td><?= format_money($plans[$k] * $item_plans[$k]->price_idr, 'idr') ?></td>
                </tr>
              <?php endforeach ?>
            </tbody>
            <tfoot>
              <tr>
                <th>Total</th>
                <th><?= array_sum($plans) ?></th>
                <th><?= format_money(array_sum(array_map(function ($k) use ($item_plans, $plans) {
                      return $plans[$k] * $item_plans[$k]->price_idr;
                    }, array_keys($plans))), 'idr') ?></th>
              </tr>
            </tfoot>
          </table>
        </div>
        <hr>
        <h3>Active Domain Subscriptions</h3>
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Plan</th>
                <th>Qty</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach (imsort(array_keys($schemes)) as $k) : ?>
                <tr>
                  <td><?= $item_schemes[$k]->alias ?></td>
                  <td><?= $schemes[$k] ?></td>
                  <td><?= format_money($schemes[$k] * $item_schemes[$k]->price_idr, 'idr') ?></td>
                </tr>
              <?php endforeach ?>
            </tbody>
            <tfoot>
              <tr>
                <th>Total</th>
                <th><?= array_sum($schemes) ?></th>
                <th><?= format_money(array_sum(array_map(function ($k) use ($item_schemes, $schemes) {
                      return $schemes[$k] * $item_schemes[$k]->price_idr;
                    }, array_keys($schemes))), 'idr') ?></th>
              </tr>
            </tfoot>
          </table>
        </div>
        <hr>
        <h3>Detailed Purchase Archive</h3>
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
              <?php /** @var \App\Entities\Purchase[] $invoice */ ?>
              <?php foreach ($invoice as $pay) : ?>
                <tr>
                  <td><?= $pay->host_id ? $pay->host->domain . ($pay->domain_id ? ' (host-only)' : '') : ($pay->domain_id ? $pay->domain->name . ' (domain-only)' : '??') ?></td>
                  <td><?= $pay->status ?></td>
                  <td><?= format_money($pay->metadata->price, $pay->metadata->price_unit) ?></td>
                  <td><?= substr($pay->metadata->_invoiced, 0, 10) ?></td>
                </tr>
              <?php endforeach ?>
              <?php if (count($invoice) === 0) : ?>
                <tr>
                  <td colspan="4" class="p-2 text-center small text-muted">
                    <?= lang('Host.emptyinvoice') ?>
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