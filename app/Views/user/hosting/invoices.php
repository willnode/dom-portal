<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>
  <div class="container">
    <h1 class="mb-3">Kelola Langganan</h1>
    <div class="row">
      <div class="col-md-6">
        <div class="card">
          <div class="card-body">
            <div>Status Langganan Saat Ini</div>
            <div class="input-group mb-3">
              <h3><?= ucfirst($data->purchase_status) ?></h3>
            </div>
            <?php if ($data->purchase_status === 'pending') : ?>

              <form method="post" class="my-2">
                <input type="hidden" name="action" value="pay">
                <input type="submit" class="btn btn-primary" value="Selesaikan Pembayaran">
              </form>
              <p>
                Pembayaran Hosting Paket <b><?= $data->plan_alias ?></b>
                <?= $data->purchase_liquid ? 'dengan domain <b>'.$data->domain_name.'</b>' : '' ?>
                seharga <b> Rp. <?= number_format($data->purchase_price, 0, ',', '.') ?></b>
              </p>
              <p>
                Apabila anda ingin mengganti jenis paket, anda harus membatalkan pembayaran ini terlebih dahulu.
              </p>
              <form method="post" class="my-2">
                <input type="hidden" name="action" value="cancel">
                <input type="submit" class="btn btn-danger" value="Batalkan Pembayaran" onclick="return confirm('Yakin ingin membatalkan pembayaran? Hosting anda akan kembali menggunakan status langganan lama. Apabila ini adalah langganan pertama maka hosting ini akan dihapus secara otomatis.')">
              </form>
            <?php endif ?>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-body">
            <h3>Arsip Langganan</h3>
            <?php foreach ($purchases as $item) : ?>
              <div class="card">
                <div class="card-body">
                  <div class="d-flex">
                    <div><b><?= $item->purchase_invoiced ?></b></div>
                    <div class="ml-auto"><?= ucfirst($item->purchase_status) ?></div>
                  </div>
                </div>
              </div>
            <?php endforeach ?>
          </div>
        </div>
      </div>
    </div>
    <a href="/user/hosting/detail/<?= $data->hosting_id ?>" class="mt-3 btn btn-secondary">Kembali</a>
  </div>

</body>

</html>