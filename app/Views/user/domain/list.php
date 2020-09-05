<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>

  <div class="container">
    <div class="row">
      <div class="col-md-6 mb-3">
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
                  <td>Nama</td>
                  <td><?= $liquid->customer->name ?></td>
                </tr>
                <tr>
                  <td>Perusahaan</td>
                  <td><?= $liquid->customer->company ?></td>
                </tr>
                <tr>
                  <td>Kredit</td>
                  <td><?= $liquid->customer->total_receipts ?></td>
                </tr>
                <tr>
                  <td>Sinkronisasi Terakhir</td>
                  <td><?= $liquid->updated ?></td>
                </tr>
              </tbody>
            </table>
            <form method="post" class="d-flex">
              <input type="submit" name="action" value="Sinkronkan Sekarang" class="btn btn-success my-2 ml-2 mr-auto">
              <a class="btn btn-primary m-2" href="/user/domain/topup">Topup</a>
              <a href="/user/domain/login" target="_blank" class="btn btn-primary m-2">Login Portal Domain</a>
            </form>
          </div>
        </div>
      </div>
      <div class="col-md-6 mb-3">
        <div class="card">
          <div class="card-body">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Domain</th>
                  <th>Akses Portal</th>
                  <th>Masa Tenggang</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($list as $domain) : ?>
                  <tr>
                    <td>
                      <a href="/user/domain/detail/<?= $domain->domain_id ?>">
                        <?= $domain->domain_name ?>
                      </a>
                    </td>
                    <td><?= $domain->domain_liquid ? 'Ya' : 'Tidak' ?></td>
                    <td><?= ucfirst($domain->domain_expired) ?></td>
                  </tr>
                <?php endforeach ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <a class="btn btn-success m-2" href="/user/domain/create">Order Baru</a>
  </div>

</body>

</html>