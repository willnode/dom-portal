<!DOCTYPE html>
<html lang="id">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>
  <div class="container">
    <h1 class="mb-3">Mengganti ID hosting</h1>
    <?php if ($data->plan_alias === 'Free' || $data->purchase_status !== 'active') : ?>
      <div class="alert alert-danger">
        Mengganti ID hosting tidak tersedia apabila belum terbayar atau sedang menggunakan hosting paket Free.
      </div>
    <?php else : ?>
      <div class="card">
        <div class="card-body">
            <p>Anda dapat mengganti ID hosting untuk mengganti username pada panel Webmin.</p>
            <p>Masukkan ID baru:</p>
            <form method="POST">
              <input type="text" class="form-control" value="<?= $data->hosting_username ?>" required>
              <input type="submit" value="Simpan" class="btn btn-primary">
            </form>
        </div>
      </div>
    <?php endif ?>
    <a href="/user/hosting/detail/<?= $data->hosting_id ?>" class="mt-3 btn btn-secondary">Kembali</a>
  </div>

</body>

</html>