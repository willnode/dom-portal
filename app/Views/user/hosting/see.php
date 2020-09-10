<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>

  <div class="container">
    <div class="card">
      <div class="row">
        <div class="col-md-6 col-xl-3">
          <div class="card-body d-flex flex-column h-100">
            <h2>Webmin</h2>
            <p class="flex-grow-1">URL:<br><b>https://<?= $slave ?>.dom.my.id:8443</b><br>
              Username:<br><b><?= esc($user) ?></b><br>
              Password:<br><b><?= $pass ?></b></p>
            <p><small><a href="/user/hosting/login/<?= $id ?>">Login ke Portal</a></small></p>
          </div>
        </div>
        <div class="col-md-6 col-xl-3">
          <div class="card-body d-flex flex-column h-100">
            <h2>FTP</h2>
            <p class="flex-grow-1">Hostname:<br><b><?= $slave ?>.dom.my.id</b><br>
              Username:<br><b><?= esc($user) ?></b><br>
              Password:<br><b><?= $pass ?></b></p>
            <p><small><a href="https://panduan.domcloud.id/hosting/remote.html#bagaimana-cara-menggunakan-filezilla-client">Panduan FTP</a></small></p>
          </div>
        </div>
        <div class="col-md-6 col-xl-3">
          <div class="card-body d-flex flex-column h-100">
            <h2>MySQL</h2>
            <p class="flex-grow-1">Hostname:<br><b><?= $slave ?>.dom.my.id</b><br>
              Username:<br><b><?= esc($user) ?></b><br>
              Password:<br><b><?= $pass ?></b></p>
            <p><small><a href="https://panduan.domcloud.id/hosting/remote.html#bagaimana-cara-menggunakan-heidisql">Panduan MySQL</a></small></p>
          </div>
        </div>
        <div class="col-md-6 col-xl-3">
          <div class="card-body d-flex flex-column h-100">
            <h2>SSH</h2>
            <p class="flex-grow-1">Username:<br><b><?= esc($user) ?>@<?= $slave ?>.dom.my.id</b><br>
              Password:<br><b><?= $pass ?></b></p>
            <p><small><a href="https://panduan.domcloud.id/hosting/remote.html#bagaimana-cara-menggunakan-putty-untuk-ssh">Panduan SSH</a></small></p>
          </div>
        </div>
      </div>
      <div class="card-body">
        <?php if ($shown) : ?>
          <p class="text-muted">Password valid selama anda belum menggatinya secara manual dalam portal Webmin. <a href="https://domcloud.id/privacy#kata-sandi-hosting" target="_blank" rel="noopener noreferrer">Pernyataan Privasi Kami</a>.</p>
          <a href="?" class="mt-3 btn btn-warning float-right">Sembunyikan Password</a>
        <?php else : ?>
          <a href="?show=password" class="mt-3 btn btn-outline-warning float-right">Tampilkan Password</a>
        <?php endif ?>
        <a href="/user/hosting/<?= $id ?>" class="mt-3 btn btn-secondary"><?= lang('Interface.back') ?></a>
      </div>
    </div>
  </div>
</body>