<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>
  <div class="container">
    <?= view('user/host/navbar') ?>
    <div class="card">
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
              <p><input type="submit" value="<?= lang('Host.loginTo') ?> Portal Host" class="btn btn-sm btn-link p-0"></p>
              <p><small><a href="https://domcloud.id/faq#:~:text=Saya%20Mendapat%20cookie,portal%20Webmin." target="_blank" rel="noopener noreferrer">Punya masalah?</a></small></p>
            </form>
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
        <div class="col-md-6 col-xl-3">
          <div class="card-body d-flex flex-column h-100">
            <h2>Database</h2>
            <p class="flex-grow-1">Hostname:<br><b style="user-select: all;"><?= $slave ?>.domcloud.id</b><br>
              Username:<br><b style="user-select: all;"><?= esc($user) ?></b><br>
              Password:<br><b style="user-select: all;"><?= $pass ?></b></p>
            <p><small>
                <a href="https://phpmyadmin.dom.my.id/" target="_blank" rel="noreferrer">phpMyAdmin</a> /
                <a href="https://phppgadmin.dom.my.id/" target="_blank" rel="noreferrer">phpPgAdmin</a>
              </small></p>
            <p><small><a href="https://domcloud.id/faq#:~:text=Saya%20tidak%20bisa%20login%20ke%20database" target="_blank" rel="noopener noreferrer">Punya masalah?</a></small></p>
          </div>
        </div>
        <div class="col-md-6 col-xl-3">
          <div class="card-body d-flex flex-column h-100">
            <h2>SSH</h2>
            <p class="flex-grow-1">Run:<br><b style="user-select: all;">ssh <?= esc($user) ?>@<?= $slave ?>.domcloud.id</b><br>
              Password:<br><b style="user-select: all;"><?= $pass ?></b></p>
            <p><small><a href="https://domcloud.id/faq#:~:text=Bagaimana%20cara%20mengakses%20SSH?" target="_blank" rel="noopener noreferrer">Punya masalah?</a></small></p>
          </div>
        </div>
      </div>
      <div class="card-body">
        <?php if ($shown) : ?>
          <a href="?" class="mt-3 btn btn-warning float-right"><?= lang('Host.hidePassword') ?></a>
        <?php else : ?>
          <a href="?show=password" class="mt-3 btn btn-warning float-right"><?= lang('Host.showPassword') ?></a>
        <?php endif ?>
        <a href="/user/host/detail/<?= $id ?>" class="mt-3 btn btn-secondary"><?= lang('Interface.back') ?></a>
      </div>
    </div>
  </div>
</body>