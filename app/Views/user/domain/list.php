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
            <h3>Daftar Domain</h3>
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Domain</th>
                  <th>Status</th>
                  <th>Masa Tenggang</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($domains as $domain) : ?>
                  <tr>
                    <td>
                      <a href="/user/domain/detail/<?= $domain->id ?>">
                        <?= $domain->name ?>
                      </a>
                    </td>
                    <td><?= ucfirst($domain->status) ?></td>
                    <td><?= $domain->expiry_at ?></td>
                  </tr>
                <?php endforeach ?>
              </tbody>
            </table>
            <hr>
            <a class="btn btn-success m-2" href="/user/domain/create">Order Domain Baru</a>
          </div>
        </div>
      </div>
    </div>

  </div>

</body>

</html>