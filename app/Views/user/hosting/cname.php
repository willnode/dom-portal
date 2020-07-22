<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>
  <div class="container">
    <h1 class="mb-3">Mengganti Nama Domain Hosting</h1>
    <?php if ($data->plan_price == 0 || $data->purchase_status !== 'active') : ?>
      <div class="alert alert-danger">
        Mengganti Domain hosting tidak tersedia apabila belum terbayar atau sedang menggunakan hosting paket Free.
      </div>
    <?php else : ?>
      <div class="card">
        <div class="card-body">
            <p>Anda dapat mengganti Domain Utama hosting</p>
            <p>Masukkan ID baru:</p>
            <form method="POST">
              <input type="text" class="form-control" name="cname" value="<?= $data->hosting_cname ?>">
              <input type="submit" value="Simpan" class="btn btn-primary">
            </form>
        </div>
      </div>
    <?php endif ?>
    <a href="/user/hosting/detail/<?= $data->hosting_id ?>" class="mt-3 btn btn-secondary">Kembali</a>
  </div>

</body>

</html>