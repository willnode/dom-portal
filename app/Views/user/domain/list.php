<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>

  <div class="container">
    <div class="card">
      <div class="card-body">
        <h1 class="mb-2">Daftar Domain</h1>
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Domain</th>
              <th>Status</th>
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
                <td><?= ucfirst($domain->domain_status) ?></td>
                <td><?= ucfirst($domain->domain_expired) ?></td>
              </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      </div>
    </div>
    <div class="d-flex">
      <a class="btn btn-success m-2" href="/user/domain/create">Order Baru</a>
      <form method="post" class="ml-auto"><input type="submit" name="action" value="Sync" class="btn btn-warning m-2"></form>
      <a href="/user/domain/login" class="btn btn-primary m-2">Login Domain Panel</a>
    </div>
  </div>

</body>

</html>