<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>

  <div class="container">
    <div class="card">
      <div class="card-body">
        <a class="btn btn-primary my-2 float-sm-right" href="/user/domain/create"><?= lang('Domain.newOrder') ?></a>
        <h1 class="mb-4"><?= lang('Domain.listTitle') ?></h1>
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
      </div>
    </div>

  </div>

</body>

</html>