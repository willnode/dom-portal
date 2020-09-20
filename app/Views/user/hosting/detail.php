<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>
  <div class="container">
    <h1 class="mb-3"><?= lang('Hosting.manageHost') ?></h1>
    <div class="row">
      <div class="col-lg-4">
        <div class="card">
          <div class="card-body">
            <div><?= lang('Hosting.hostStatus') ?></div>
            <div class="input-group mb-3">
              <h3><?= ucfirst($data->status) ?></h3>
              <?php if ($data->status === 'pending') : ?>
                <a href="/user/hosting/invoices/<?= $data->id ?>" class="ml-auto btn btn-primary"><?= lang('Hosting.finishPayment') ?></a>
              <?php elseif ($data->status === 'active') : ?>
                <a href="http://<?= $data->domain ?>" target="_blank" rel="noopener noreferrer" class="ml-auto btn btn-primary"><?= lang('Hosting.openWebsite') ?></a>
              <?php endif ?>
            </div>
            <div><?= lang('Hosting.usernameHost') ?></div>
            <div class="input-group mb-3">
              <input type="text" class="form-control" value="<?= $data->username ?>" readonly>
              <a href="/user/hosting/rename/<?= $data->id ?>" class="btn btn-outline-secondary"><?= lang('Interface.change') ?></a>
            </div>
            <div><?= lang('Hosting.domainHost') ?></div>
            <div class="input-group mb-3">
              <input value="<?= $data->domain ?>" type="text" class="form-control" readonly>
              <a href="/user/hosting/cname/<?= $data->id ?>" class="btn btn-outline-secondary"><?= lang('Interface.change') ?></a>
            </div>
            <div><?= lang('Hosting.slaveID') ?> / <?= lang('Hosting.IPAddress') ?></div>
            <div class="input-group mb-3">
              <input value="<?= $data->server->alias . ' / ' . $data->server->ip ?>" type="text" class="form-control" readonly>
              <a href="/user/hosting/see/<?= $data->id ?>" class="btn btn-outline-secondary"><?= lang('Hosting.manageHostLogin') ?></a>
            </div>
            <div><?= lang('Hosting.activeScheme') ?></div>
            <div class="input-group mb-3">
              <input type="text" class="form-control" value="<?= $data->plan->alias ?>" readonly>
              <a href="/user/hosting/upgrade/<?= $data->id ?>" class="btn btn-outline-secondary"><?= lang('Interface.upgrade') ?></a>
            </div>
            <div><?= lang('Hosting.purchaseDate') ?></div>
            <div class="input-group mb-3">
              <input type="text" class="form-control" value="<?= $data->created_at ?>" readonly>
              <a href="/user/hosting/invoices/<?= $data->id ?>" class="btn btn-outline-secondary"><?= lang('Interface.archives') ?></a>
            </div>
            <div><?= lang('Hosting.expiryDate') ?></div>
            <div class="input-group mb-3">
              <input type="text" class="form-control" value="<?= $data->expiry_at ?>" readonly>
              <a href="/user/hosting/upgrade/<?= $data->id ?>#extend" class="btn btn-outline-secondary"><?= lang('Interface.extend') ?></a>
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
                    <div class="progress-bar progress-bar-striped bg-primary" role="progressbar" style="width: <?= 100 * $stat->quota_user / $plan->disk_bytes ?>%"></div>
                    <div class="progress-bar progress-bar-striped bg-success" role="progressbar" style="width: <?= 100 * $stat->quota_db / $plan->disk_bytes ?>%"></div>
                    <div class="progress-bar progress-bar-striped bg-warning" role="progressbar" style="width: <?= 100 * ($stat->quota_server - $stat->quota_user - $stat->quota_db) / $plan->disk_bytes ?>%"></div>
                  </div>
                </div>
                <div class="col">
                  <div class="h3 font-weight-normal m-0"><?= format_bytes($plan->disk_bytes) ?></div>
                  <div class="text-black">Total</div>
                </div>
                <div class="col text-primary">
                  <div class="h3 font-weight-normal m-0"><?= format_bytes($stat->quota_user) ?></div>
                  <div>Files</div>
                </div>
                <div class="col text-success">
                  <div class="h3 font-weight-normal m-0"><?= format_bytes($stat->quota_db) ?></div>
                  <div>Database</div>
                </div>
                <div class="col text-muted">
                  <div class="h3 font-weight-normal m-0"><?= format_bytes(-$stat->disk_bytes + $stat->quota_server) ?></div>
                  <div>Kosong</div>
                </div>
              </div>
              <div class="row g-0 mb-3">
                <div class="col-12 col-lg-3">
                  <h4 class="my-2">Bandwidth</h4>
                </div>
                <div class="col-12 col-lg-9">
                  <div class="progress justify-content-end my-3">
                    <div class="progress-bar progress-bar-striped bg-warning" role="progressbar" style="width: <?= 100 * $stat->quota_net / ($data->addons_bytes + $plan->net_monthly_bytes) ?>%"></div>
                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?= 100 * max(0, $plan->net_monthly_bytes - $stat->quota_net) / ($data->addons_bytes + $plan->net_monthly_bytes) ?>%"></div>
                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= 100 * min(max(0, $plan->net_monthly_bytes + $data->addons_bytes - $stat->quota_net), $data->addons_bytes) / ($data->addons_bytes + $plan->net_monthly_bytes) ?>%"></div>
                  </div>
                </div>
                <div class="col">
                  <div class="h3 font-weight-normal m-0"><?= format_bytes($data->addons_bytes + $plan->net_monthly_bytes) ?></div>
                  <div class="text-black">Total</div>
                </div>
                <div class="col text-secondary">
                  <div class="h3 font-weight-normal m-0"><?= format_bytes($stat->quota_net) ?></div>
                  <div>Digunakan</div>
                </div>
                <div class="col text-primary">
                  <div class="h3 font-weight-normal m-0"><?= format_bytes(max(0, -$stat->quota_net + $plan->net_monthly_bytes)) ?></div>
                  <div>Sisa Bulanan Paket</div>
                </div>
                <div class="col text-success">
                  <div class="h3 font-weight-normal m-0"><?= format_bytes(min($data->addons_bytes, $data->addons_bytes - max(0, $stat->quota_net - $plan->net_monthly_bytes))) ?></div>
                  <div>Sisa Add-Ons</div>
                </div>
              </div>
              <div class="mb-3">
                <canvas id="canvas"></canvas>
              </div>
              <div class="row g-0 text-muted">
                <div class="col-6">
                  <small> Paket <?= $plan->alias ?>, <?= format_bytes($plan->disk_bytes) ?> disk space, <?= format_bytes($plan->net_monthly_bytes) ?> data bulanan.</small>
                </div>
                <div class="col-6 text-right">
                  <small>Terakhir update <?= $stat->updated_at ?></small>
                </div>
              </div>
            </div>
          </div>
        <?php else : ?>
          <div class="card">
            <div class="card-body">
              <p class="text-center text-muted">Data penggunaan hosting akan muncul disini. Tunggulah satu jam kemudian agar datanya muncul.</p>
            </div>
          </div>
        <?php endif ?>
      </div>
    </div>
    <div class="d-flex mb-3">
      <a href="/user/hosting" class="mt-3 btn btn-secondary"><?= lang('Interface.back') ?></a>
      <a href="/user/hosting/delete/<?= $data->id ?>" class="mt-3 btn btn-danger ml-auto"><?= lang('Hosting.deleteHost') ?></a>
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
          type: 'line',
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
              text: 'Penggunaan Harian Bandwidth dalam MB'
            }
          }
        });
      }
    };
  </script>
</body>

</html>