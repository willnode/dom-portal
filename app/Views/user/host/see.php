<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>
  <div class="container">
    <?= view('user/host/navbar') ?>
    <div class="card">
      <div class="p-3">
        <?php if (lang('Interface.code') === 'id') : ?>
          <p>Gunakan akses login berikut untuk mengupload atau mengedit konten website anda.
            <a href="https://github.com/domcloud/domcloud-id#bagaimana-cara-mengedit-website">Pelajari lebih lanjut</a>.</p>
        <?php else : ?>
          <p>Use the following login access to upload or edit your website content.
            <a href="https://github.com/domcloud/domcloud-io#how-to-edit-a-website">Learn more</a>.</p>
        <?php endif ?>
      </div>
      <div class="row">
        <div class="col-md-6 col-xl-3">
          <div class="card-body d-flex flex-column h-100">
            <h2>Webmin</h2>
            <p class="flex-grow-1">URL:<br><b style="user-select: all;">https://<?= $slave ?>.domcloud.id:<?= $webminport ?></b><br>
              Username:<br><b style="user-select: all;"><?= esc($user) ?></b><br>
              Password:<br><b style="user-select: all;"><?= $pass ?></b></p>
            <form target="_blank" action="https://<?= $slave ?>.domcloud.id:<?= $webminport ?>/session_login.cgi" method="POST">
              <input name="user" class="d-none" value="<?= esc($user) ?>">
              <input name="pass" class="d-none" value="<?= esc($rawpass) ?>">
              <p><small><a href="https://<?= $slave ?>.domcloud.id:<?= $webminport ?>/filemin/index.cgi?path=%2F&xnavigation=1" target="_blank"><?= lang('Host.openPortal') ?></a></small> / <input type="submit" value="Auto Login" class="btn btn-sm btn-link p-0"></p>
            </form>
          </div>
        </div>
        <div class="col-md-6 col-xl-3">
          <div class="card-body d-flex flex-column h-100">
            <h2>Database</h2>
            <p class="flex-grow-1">Hostname:<br><b style="user-select: all;"><?= $slave ?>.domcloud.id</b><br>
              Username:<br><b style="user-select: all;"><?= esc($user) ?></b><br>
              Password:<br><b style="user-select: all;"><?= $pass ?></b></p>
            <p><small>
                <a href="https://phpmyadmin<?= $alias ?>/" target="_blank" rel="noreferrer">phpMyAdmin</a> /
                <a href="https://phppgadmin<?= $alias ?>/" target="_blank" rel="noreferrer">phpPgAdmin</a>
              </small></p>
          </div>
        </div>
        <div class="col-md-6 col-xl-3">
          <div class="card-body d-flex flex-column h-100">
            <h2>SSH</h2>
            <p class="flex-grow-1">Run:<br><b style="user-select: all;">ssh <?= esc($user) ?>@<?= $slave ?>.domcloud.id</b><br>
              Password:<br><b style="user-select: all;"><?= $pass ?></b></p>
            <p><small>
                <a href="https://webssh<?= $alias ?>/?<?= http_build_query(['hostname' => 'localhost', 'username' => $user, 'password' => base64_encode($rawpass)]) ?>" target="_blank" rel="noreferrer">Web SSH</a>
              </small></p>
          </div>
        </div>
        <div class="col-md-6 col-xl-3">
          <div class="card-body d-flex flex-column h-100">
            <h2>FTP</h2>
            <p class="flex-grow-1">Hostname:<br><b style="user-select: all;"><?= $slave ?>.domcloud.id</b><br>
              Username:<br><b style="user-select: all;"><?= esc($user) ?></b><br>
              Password:<br><b style="user-select: all;"><?= $pass ?></b></p>
          </div>
        </div>
      </div>
      <div class="card-body">
        <?php if ($shown) : ?>
          <a href="?" class="mt-3 btn btn-warning float-end"><i class="fas fa-lock me-2"></i> <?= lang('Host.hidePassword') ?></a>
        <?php else : ?>
          <a href="?show=password" class="mt-3 btn btn-warning float-end"><i class="fas fa-unlock me-2"></i> <?= lang('Host.showPassword') ?></a>
        <?php endif ?>
        <a href="/user/host/detail/<?= $id ?>" class="mt-3 btn btn-secondary"><?= lang('Interface.back') ?></a>
      </div>
    </div>
  </div>
</body>