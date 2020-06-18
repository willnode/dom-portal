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
              <div class="input-group-append">
                <a href="/user/hosting/rename/<?= $data->hosting_id ?>" class="btn btn-outline-secondary">Ganti</a>
              </div>
            </div>
            <div>Custom Domain</div>
            <div class="input-group mb-3">
              <input value="<?=
                              $data->hosting_cname ?: $data->default_domain
                            ?>" type="text" class="form-control" readonly>
              <div class="input-group-append">
                <a href="/user/hosting/cname/<?= $data->hosting_id ?>" class="btn btn-outline-secondary">Ganti</a>
              </div>
            </div>
            <div>Slave ID / Alamat IP</div>
            <div class="input-group mb-3">
              <input value="<?= $data->slave_alias . ' / ' . $data->slave_ip ?>" type="text" class="form-control" readonly>
              <div class="input-group-append">
                <a href="https://<?= $data->slave_alias ?>.dom.my.id:8443/" target="_blank" class="btn btn-outline-secondary">Buka Panel Webmin</a>
              </div>
            </div>
            <div>Opsi Administratif</div>
            <div class="btn-group d-flex justify-content-end mt-2">
              <a href="/user/hosting/reset/<?= $data->hosting_id ?>" class="btn btn-outline-primary">Ganti Password Webmin</a>
              <a href="/user/hosting/delete/<?= $data->hosting_id ?>" class="btn btn-outline-danger">Hapus Hosting Permanen</a>
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
                <a href="/user/hosting/invoices/<?= $data->hosting_id?>"
                class="ml-auto btn btn-primary">Selesaikan Pembayaran</a>
              <?php elseif ($data->purchase_status === 'active') : ?>
                <a href="http://<?= $data->hosting_cname ?: $data->default_domain?>" target="_blank" rel="noopener noreferrer"
                class="ml-auto btn btn-primary">Buka Website</a>
              <?php endif ?>
            </div>
            <div>Paket Terpilih</div>
            <div class="input-group mb-3">
              <input type="text" class="form-control" value="<?= $data->plan_alias ?>" readonly>
              <div class="input-group-append">
                <a href="/user/hosting/upgrade/<?= $data->hosting_id ?>" class="btn btn-outline-secondary">Ganti</a>
              </div>
            </div>
            <div>Tanggal Pembelian</div>
            <div class="input-group mb-3">
              <input type="text" class="form-control" value="<?= $data->purchase_invoiced ?>" readonly>
              <div class="input-group-append">
                <a href="/user/hosting/invoices/<?= $data->hosting_id ?>" class="btn btn-outline-secondary">Arsip</a>
              </div>
            </div>
            <div>Tanggal Kadaluarsa</div>
            <div class="input-group mb-3">
              <input type="text" class="form-control" value="<?= $data->purchase_expired ?>" readonly>
              <div class="input-group-append">
                <a href="/user/hosting/upgrade/<?= $data->hosting_id ?>" class="btn btn-outline-secondary">Perpanjang</a>
              </div>
            </div>
            <?php if ($data->plan_alias !== 'Free' && $data->purchase_status === 'active' && !$data->hosting_cname) : ?>
              <div class="alert alert-warning">
                <p>Anda mengaktifkan paket premium namun belum mengganti kustom domain!</p>
                <p>Perlu diperhatikan bahwa selama anda belum memasang kustom domain pada hosting maka
                  anda tidak dapat menikmati fitur premium yang sudah anda bayar.
                  <a href="/user/hosting/cname/<?= $data->hosting_id ?>">Ganti sekarang.</a>
                </p>
              </div>
            <?php endif ?>
          </div>
        </div>
      </div>
    </div>
    <a href="/user/hosting" class="mt-3 btn btn-secondary">Kembali</a>
  </div>

</body>

</html>