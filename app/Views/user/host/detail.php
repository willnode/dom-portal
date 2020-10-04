<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>
  <div class="container">
    <?= view('user/host/navbar') ?>
    <div class="row">
      <div class="col-lg-4">
        <div class="card">
          <div class="card-body">
            <div><?= lang('Host.hostStatus') ?></div>
            <div class="input-group mb-3">
              <h3><?= ucfirst($host->status) ?></h3>
              <?php if ($host->status === 'pending') : ?>
                <a href="/user/host/invoices/<?= $host->id ?>" class="ml-auto btn btn-primary"><?= lang('Host.finishPayment') ?></a>
              <?php elseif ($host->status === 'active') : ?>
                <a href="http://<?= $host->domain ?>" target="_blank" rel="noopener noreferrer" class="ml-auto btn btn-primary"><?= lang('Host.openWebsite') ?></a>
              <?php endif ?>
            </div>
            <div><?= lang('Host.usernameHost') ?></div>
            <div class="input-group mb-3">
              <input type="text" class="form-control" value="<?= $host->username ?>" readonly>
              <a href="/user/host/rename/<?= $host->id ?>" class="btn btn-outline-secondary"><?= lang('Interface.change') ?></a>
            </div>
            <div><?= lang('Host.domainHost') ?></div>
            <div class="input-group mb-3">
              <input value="<?= $host->domain ?>" type="text" class="form-control" readonly>
              <a href="/user/host/cname/<?= $host->id ?>" class="btn btn-outline-secondary"><?= lang('Interface.change') ?></a>
            </div>
            <div><?= lang('Host.slaveID') ?> / <?= lang('Host.IPAddress') ?></div>
            <div class="input-group mb-3">
              <input value="<?= $host->server->alias . ' / ' . $host->server->ip ?>" type="text" class="form-control" readonly>
              <a href="/user/host/see/<?= $host->id ?>" class="btn btn-outline-secondary"><?= lang('Host.manageHostLogin') ?></a>
            </div>
            <div><?= lang('Host.activeScheme') ?></div>
            <div class="input-group mb-3">
              <input type="text" class="form-control" value="<?= $host->plan->alias ?>" readonly>
              <a href="/user/host/upgrade/<?= $host->id ?>" class="btn btn-outline-secondary"><?= lang('Interface.upgrade') ?></a>
            </div>
            <div><?= lang('Host.purchaseDate') ?></div>
            <div class="input-group mb-3">
              <input type="text" class="form-control" value="<?= $host->created_at ?>" readonly>
              <a href="/user/host/invoices/<?= $host->id ?>" class="btn btn-outline-secondary"><?= lang('Interface.archives') ?></a>
            </div>
            <div><?= lang('Host.expiryDate') ?></div>
            <div class="input-group mb-3">
              <input type="text" class="form-control" value="<?= $host->expiry_at ?>" readonly>
              <a href="/user/host/upgrade/<?= $host->id ?>#extend" class="btn btn-outline-secondary"><?= lang('Interface.extend') ?></a>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-8">
        <?php if ($stat) : ?>
          <div class="card">
            <div class="card-body">
              <div class="row g-0 mb-3">
                <div class="col-12 col-lg-3">
                  <h4 class="my-2">Disk Space</h4>
                </div>
                <div class="col-12 col-lg-9">
                  <div class="progress my-3">
                    <div class="progress-bar bg-danger" role="progressbar" style="width: <?= 100 * $stat->quota_user / $plan->disk_bytes ?>%"></div>
                    <div class="progress-bar bg-warning" role="progressbar" style="width: <?= 100 * $stat->quota_db / $plan->disk_bytes ?>%"></div>
                    <div class="progress-bar progress-bar-striped bg-primary" role="progressbar" style="width: <?= 100 * ($plan->disk_bytes - $stat->quota_server) / $plan->disk_bytes ?>%"></div>
                  </div>
                </div>
                <div class="col">
                  <div class="h3 font-weight-normal m-0"><?= format_bytes($plan->disk_bytes) ?></div>
                  <div class="text-black"><?= lang('Host.total')?></div>
                </div>
                <div class="col text-danger">
                  <div class="h3 font-weight-normal m-0"><?= format_bytes($stat->quota_user) ?></div>
                  <div>Files</div>
                </div>
                <div class="col text-warning">
                  <div class="h3 font-weight-normal m-0"><?= format_bytes($stat->quota_db) ?></div>
                  <div>Database</div>
                </div>
                <div class="col text-primary">
                  <div class="h3 font-weight-normal m-0"><?= format_bytes($plan->disk_bytes - $stat->quota_server) ?></div>
                  <div><?= lang('Host.free')?></div>
                </div>
              </div>
              <div class="row g-0 mb-3">
                <div class="col-12 col-lg-3">
                  <h4 class="my-2">Bandwidth</h4>
                </div>
                <div class="col-12 col-lg-9">
                  <div class="progress justify-content-end my-3">
                    <div class="progress-bar bg-danger" role="progressbar" style="width: <?= 100 * $stat->quota_net / ($host->addons_bytes + $plan->net_monthly_bytes) ?>%"></div>
                    <div class="progress-bar progress-bar-striped bg-primary" role="progressbar" style="width: <?= 100 * max(0, $plan->net_monthly_bytes - $stat->quota_net) / ($host->addons_bytes + $plan->net_monthly_bytes) ?>%"></div>
                    <div class="progress-bar progress-bar-striped bg-success" role="progressbar" style="width: <?= 100 * min(max(0, $plan->net_monthly_bytes + $host->addons_bytes - $stat->quota_net), $host->addons_bytes) / ($host->addons_bytes + $plan->net_monthly_bytes) ?>%"></div>
                  </div>
                </div>
                <div class="col">
                  <div class="h3 font-weight-normal m-0"><?= format_bytes($host->addons_bytes + $plan->net_monthly_bytes) ?></div>
                  <div class="text-black"><?= lang('Host.total')?></div>
                </div>
                <div class="col text-danger">
                  <div class="h3 font-weight-normal m-0"><?= format_bytes($stat->quota_net) ?></div>
                  <div><?= lang('Host.used')?></div>
                </div>
                <div class="col text-primary">
                  <div class="h3 font-weight-normal m-0"><?= format_bytes(max(0, -$stat->quota_net + $plan->net_monthly_bytes)) ?></div>
                  <div><?= lang('Host.remainingMonthlyPlan')?></div>
                </div>
                <div class="col text-success">
                  <div class="h3 font-weight-normal m-0"><?= format_bytes(min($host->addons_bytes, $host->addons_bytes - max(0, $stat->quota_net - $plan->net_monthly_bytes))) ?></div>
                  <div><?= lang('Host.remainingAddons')?></div>
                </div>
              </div>
              <div class="mb-3">
                <canvas id="canvas"></canvas>
              </div>
              <div class="row g-0 text-muted">
                <div class="col-6">
                  <small> <?= lang('Host.planDetail', [$plan->alias, format_bytes($plan->disk_bytes), format_bytes($plan->net_monthly_bytes)]) ?></small>
                </div>
                <div class="col-6 text-right">
                  <small><?= lang('Interface.lastUpdated')?> <span title="<?=$stat->updated_at?> UTC"><?=$stat->updated_at->humanize()?></span></small>
                </div>
              </div>
            </div>
          </div>
        <?php else : ?>
          <div class="card">
            <div class="card-body">
              <p class="text-center text-muted"><?= lang('Host.waitForDataHint') ?></p>
            </div>
          </div>
        <?php endif ?>
      </div>
    </div>
    <div class="d-flex mb-3">
      <a href="/user/host" class="mt-3 btn btn-secondary"><?= lang('Interface.back') ?></a>
      <a href="/user/host/delete/<?= $host->id ?>" class="mt-3 ml-auto btn btn-danger"><?= lang('Host.deleteHost') ?></a>
    </div>
  </div>
  <script id="bandwidths" type="application/json">
    <?= json_encode($stat->bandwidths ?? []) ?>
  </script>
  <script defer src="https://www.chartjs.org/dist/2.9.3/Chart.min.js"></script>
  <script>
    window.onload = function() {
      var ctx = document.getElementById('canvas');
      if (ctx) {
        var data = JSON.parse($('#bandwidths').html());
        window.myBar = new Chart(ctx.getContext('2d'), {
          type: 'bar',
          data: {
            labels: Object.keys(data).map(x => x.substring(5, 10)),
            datasets: [{
              label: 'Bandwidth (MB)',
              backgroundColor: 'rgba(255,0,0,0.4)',
              borderColor: 'red',
              borderWidth: 1,
              data: Object.values(data).map(x => Math.floor(x / 1024 / 1024 * 10) / 10)
            }]
          },
          options: {
            responsive: true,
            aspectRatio: 3,
            legend: false,
            title: {
              display: true,
              text: '<?= lang('Host.bandwidthLegend') ?>'
            }
          }
        });
      }
    };
  </script>
</body>

</html>