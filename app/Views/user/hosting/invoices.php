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
              <?php $expired = strtotime('+16 hours', strtotime($data->purchase_invoiced)) ?>
              <p>Batas akhir pembayaran: <b><?= date('Y-m-d H:i', $expired) ?></b></p>
              <?php if (\time() < $expired && $data->purchase_session) : ?>
                <p>
                  <a class="btn btn-primary" href="https://my.ipaymu.com/payment/<?= $data->purchase_session ?>">Selesaikan Pembayaran</a>
                </p>
                <p>
                  Pembayaran Hosting Paket <b><?= $data->plan_alias ?></b> seharga <b> Rp. <?= number_format($data->purchase_price, 0, ',', '.') ?></b>
                </p>
                <p>
                  Apabila anda ingin mengganti jenis paket, anda harus membatalkan pembayaran ini terlebih dahulu.
                </p>
                <p>
                  <form method="post">
                    <input type="hidden" name="action" value="cancel">
                    <input type="submit" class="btn btn-danger" value="Batalkan Pembayaran">
                  </form>
                </p>
              <?php else : ?>
                <p>
                  Pembayaran terakhir sudah kadaluarsa. Silahkan klik tombol berikut untuk mengajukan pembayaran.
                </p>
                <p>
                  <form method="post">
                    <input type="hidden" name="action" value="renew">
                    <input type="submit" class="btn btn-success" value="Pembayaran Baru">
                  </form>
                </p>
                <p>
                  Apabila anda ingin mengganti jenis paket, anda harus membatalkan pembayaran ini terlebih dahulu.
                </p>
                <p>
                  <form method="post">
                    <input type="hidden" name="action" value="cancel">
                    <input type="submit" class="btn btn-danger" value="Batalkan Pembayaran" onclick="return confirm('Yakin ingin membatalkan pembayaran? Hosting anda akan kembali menggunakan status langganan lama. Apabila ini adalah langganan pertama maka hosting ini akan dihapus secara otomatis.')">
                  </form>
                </p>
              <?php endif ?>
            <?php elseif ($data->purchase_status === 'active') : ?>
              <p>Hosting ini sudah aktif dan akan kadaluarsa pada tanggal <b><?= $data->purchase_expired ?> </b></p>
            <?php elseif ($data->purchase_status === 'expired') : ?>
              <p>Hosting ini sudah kadaluarsa sejak <b><?= $data->purchase_expired ?> </b>.
                Anda harus membayar ulang sebelum dihapus secara otomatis oleh sistem pada 2 minggu berikutnya.
              </p>
              <p>
                <a class="btn btn-success" href="<?= base_url('user/hosting/upgrade/' . $hosting->hosting_id) ?>">Perpanjang Hosting</a>
              </p>
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