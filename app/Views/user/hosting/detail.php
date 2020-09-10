<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>
  <div class="container">
    <h1 class="mb-3"><?= lang('Hosting.manageHost') ?></h1>
    <div class="row">
      <div class="col-md-6">
        <div class="card">
          <div class="card-body">
            <div><?= lang('Hosting.usernameHost') ?></div>
            <div class="input-group mb-3">
              <input type="text" class="form-control" value="<?= $data->hosting_username ?>" readonly>
              <a href="/user/hosting/rename/<?= $data->hosting_id ?>" class="btn btn-outline-secondary"><?= lang('Interface.change') ?></a>
            </div>
            <div><?= lang('Hosting.domainHost') ?></div>
            <div class="input-group mb-3">
              <input value="<?= $data->domain_name ?>" type="text" class="form-control" readonly>
              <a href="/user/hosting/cname/<?= $data->hosting_id ?>" class="btn btn-outline-secondary"><?= lang('Interface.change') ?></a>
            </div>
            <div><?= lang('Hosting.slaveID') ?> / <?= lang('Hosting.IPAddress') ?></div>
            <div class="input-group mb-3">
              <input value="<?= $data->slave_alias . ' / ' . $data->slave_ip ?>" type="text" class="form-control" readonly>
              <a href="/user/hosting/login/<?= $data->hosting_id ?>" target="_blank" class="btn btn-outline-secondary"><?= lang('Hosting.openPortal') ?></a>
            </div>
            <div><?= lang('Hosting.administrativeOption') ?></div>
            <div class="btn-group d-flex justify-content-end mt-2">
              <a href="/user/hosting/see/<?= $data->hosting_id ?>" class="btn btn-outline-primary"><?= lang('Hosting.manageHostLogin') ?></a>
              <a href="/user/hosting/delete/<?= $data->hosting_id ?>" class="btn btn-outline-danger"><?= lang('Hosting.deleteHost') ?></a>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-body">
            <div><?= lang('Hosting.hostStatus') ?></div>
            <div class="input-group mb-3">
              <h3><?= ucfirst($data->purchase_status) ?></h3>
              <?php if ($data->purchase_status === 'pending') : ?>
                <a href="/user/hosting/invoices/<?= $data->hosting_id ?>" class="ml-auto btn btn-primary"><?= lang('Hosting.finishPayment') ?></a>
              <?php elseif ($data->purchase_status === 'active') : ?>
                <a href="http://<?= $data->domain_name ?>" target="_blank" rel="noopener noreferrer" class="ml-auto btn btn-primary"><?= lang('Hosting.openWebsite') ?></a>
              <?php endif ?>
            </div>
            <div><?= lang('Hosting.activeScheme') ?></div>
            <div class="input-group mb-3">
              <input type="text" class="form-control" value="<?= $data->plan_alias ?>" readonly>
              <a href="/user/hosting/upgrade/<?= $data->hosting_id ?>" class="btn btn-outline-secondary"><?= lang('Interface.upgrade') ?></a>
            </div>
            <div><?= lang('Hosting.purchaseDate') ?></div>
            <div class="input-group mb-3">
              <input type="text" class="form-control" value="<?= $data->purchase_invoiced ?>" readonly>
              <a href="/user/hosting/invoices/<?= $data->hosting_id ?>" class="btn btn-outline-secondary"><?= lang('Interface.archives') ?></a>
            </div>
            <div><?= lang('Hosting.expiryDate') ?></div>
            <div class="input-group mb-3">
              <input type="text" class="form-control" value="<?= $data->purchase_expired ?>" readonly>
              <a href="/user/hosting/upgrade/<?= $data->hosting_id ?>#extend" class="btn btn-outline-secondary"><?= lang('Interface.extend') ?></a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <a href="/user/hosting" class="mt-3 btn btn-secondary"><?= lang('Interface.back') ?></a>
  </div>

</body>

</html>