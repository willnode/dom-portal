<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>
  <div class="container">
    <h1 class="mb-3">Kelola Hosting</h1>
    <div class="row">
      <div class="col-md-6">
        <div class="card">
          <div class="card-body">
            <div>Username Hosting</div>
            <div class="input-group mb-3">
              <input type="text" class="form-control" value="<?= $data->hosting_username ?>" readonly>
              <a href="/user/hosting/rename/<?= $data->hosting_id ?>" class="btn btn-outline-secondary">Ganti</a>
            </div>
            <div>Custom Domain</div>
            <div class="input-group mb-3">
              <input value="<?= $data->domain_name ?>" type="text" class="form-control" readonly>
              <a href="/user/hosting/cname/<?= $data->hosting_id ?>" class="btn btn-outline-secondary">Ganti</a>
            </div>
            <div>Slave ID / Alamat IP</div>
            <div class="input-group mb-3">
              <input value="<?= $data->slave_alias . ' / ' . $data->slave_ip ?>" type="text" class="form-control" readonly>
              <a href="/user/hosting/login/<?= $data->hosting_id ?>" target="_blank" class="btn btn-outline-secondary">Buka Portal Webmin</a>
            </div>
            <div>Opsi Administratif</div>
            <div class="btn-group d-flex justify-content-end mt-2">
              <a href="/user/hosting/see/<?= $data->hosting_id ?>" class="btn btn-outline-primary">Atur Server Login</a>
              <!-- <a href="/user/hosting/reset/<?= $data->hosting_id ?>" class="btn btn-outline-primary">Ganti Password</a> -->
              <a href="/user/hosting/delete/<?= $data->hosting_id ?>" class="btn btn-outline-danger">Hapus Hosting</a>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-body">
            <div>Status Paket</div>
            <div class="input-group mb-3">
              <h3><?= ucfirst($data->purchase_status) ?></h3>
              <?php if ($data->purchase_status === 'pending') : ?>
                <a href="/user/hosting/invoices/<?= $data->hosting_id ?>" class="ml-auto btn btn-primary">Selesaikan Pembayaran</a>
              <?php elseif ($data->purchase_status === 'active') : ?>
                <a href="http://<?= $data->domain_name ?>" target="_blank" rel="noopener noreferrer" class="ml-auto btn btn-primary">Buka Website</a>
              <?php endif ?>
            </div>
            <div>Paket Terpilih</div>
            <div class="input-group mb-3">
              <input type="text" class="form-control" value="<?= $data->plan_alias ?>" readonly>
              <a href="/user/hosting/upgrade/<?= $data->hosting_id ?>" class="btn btn-outline-secondary">Ganti</a>
            </div>
            <div>Tanggal Pembelian</div>
            <div class="input-group mb-3">
              <input type="text" class="form-control" value="<?= $data->purchase_invoiced ?>" readonly>
              <a href="/user/hosting/invoices/<?= $data->hosting_id ?>" class="btn btn-outline-secondary">Arsip</a>
            </div>
            <div>Tanggal Kadaluarsa</div>
            <div class="input-group mb-3">
              <input type="text" class="form-control" value="<?= $data->purchase_expired ?>" readonly>
              <a href="/user/hosting/upgrade/<?= $data->hosting_id ?>" class="btn btn-outline-secondary">Perpanjang</a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <a href="/user/hosting" class="mt-3 btn btn-secondary">Kembali</a>
  </div>

</body>

</html>