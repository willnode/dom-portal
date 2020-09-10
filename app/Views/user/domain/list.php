<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>

  <div class="container">
    <div class="row">
      <div class="col-lg-6 mb-3">
        <div class="card">
          <div class="card-body">
            <h2 class="mb-2">Biodata Domain</h2>
            <table class="table">
              <tbody>
                <tr>
                  <td width="40%">ID</td>
                  <td><?= $liquid->customer->customer_id ?></td>
                </tr>
                <tr>
                  <td><?= lang('Interface.name') ?></td>
                  <td><?= $liquid->customer->name ?></td>
                </tr>
                <tr>
                  <td><?= lang('Interface.company') ?></td>
                  <td><?= $liquid->customer->company ?></td>
                </tr>
                <tr>
                  <td><?= lang('Interface.credit') ?></td>
                  <td><?= $liquid->customer->total_receipts ?></td>
                </tr>
                <tr>
                  <td>Sinkronisasi Terakhir</td>
                  <td><?= $liquid->updated ?></td>
                </tr>
              </tbody>
            </table>
            <form method="post" class="d-flex">
              <input type="hidden" name="action" value="sync">
              <button type="submit" class="btn btn-success my-2 ml-2 mr-auto">
                Sinkronkan Sekarang
              </button>
              <a class="btn btn-primary m-2" href="/user/domain/topup">Topup</a>
              <a href="/user/domain/login" target="_blank" class="btn btn-primary m-2">Login Portal Domain</a>
            </form>
          </div>
        </div>
      </div>
      <div class="col-lg-6 mb-3">
        <div class="card">
          <div class="card-body">
            <?php if (count($liquid->domains) > 0) : ?>
            <h3>Daftar Domain Dibeli</h3>
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Domain</th>
                  <th>Status</th>
                  <th>Masa Tenggang</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($liquid->domains as $domain) : ?>
                  <tr>
                    <td>
                      <a href="/user/domain/detail/<?= $domain->domain_id ?>">
                        <?= $domain->domain_name ?>
                      </a>
                    </td>
                    <td><?= ucfirst($domain->order_status) ?></td>
                    <td><?= $domain->expiry_date ?></td>
                  </tr>
                <?php endforeach ?>
              </tbody>
            </table>
            <hr>
            <?php endif ?>
            <?php if (count($liquid->pending_transactions) > 0) : ?>
            <h3>Daftar Transaksi Pending</h3>
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Domain</th>
                  <th>Status</th>
                  <th>Masa Tenggang</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($liquid->pending_transactions as $domain) : ?>
                  <tr>
                    <td>
                      <a href="/user/domain/detail/<?= $domain->domain_id ?>">
                        <?= $domain->domain_name ?>
                      </a>
                    </td>
                    <td><?= ucfirst($domain->order_status) ?></td>
                    <td><?= $domain->expiry_date ?></td>
                  </tr>
                <?php endforeach ?>
              </tbody>
            </table>
            <hr>
            <?php endif ?>
            <a class="btn btn-success m-2" href="/user/domain/create">Order Domain Baru</a>
          </div>
        </div>
      </div>
    </div>

  </div>

</body>

</html>