<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>

  <div class="container-fluid">
    <div class="card">
      <div class="card-body">
        <?php if (($_GET['status'] ?? '') === 'return') : ?>
          <div class="alert alert-primary">
            Terimakasih. Apabila sistem kami sudah memverifikasi pembayaran anda,
            email konfirmasi akan dikirim pada email anda.
          </div>
        <?php endif ?>
        <a class="btn btn-primary my-2 float-sm-right" href="/user/hosting/create">Order Baru</a>
        <h1 class="mb-4">Daftar Hosting</h1>
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
                  <td><a href="/user/hosting/detail/<?= $host->hosting_id ?>"><?= $host->domain_name ?></a></td>
                  <td><?= $host->plan_alias ?></td>
                  <td><?= ucfirst($host->purchase_status) ?></td>
                  <td><?= ucfirst($host->purchase_expired) ?></td>
                </tr>
              <?php endforeach ?>
              <?php if (count($list) === 0) : ?>
                <tr>
                  <td colspan="4" class="p-2 text-center small text-muted">
                    Belum ada hosting ditambahkan.
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