<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>
  <div class="container">
    <div class="row">
      <div class="col-md-6">
        <div class="card">
          <form method="POST" class="card-body">
            <h2 class="mb-3"><?= lang('Interface.editProfile') ?></h2>
            <p><?= lang('Interface.editProfileMessage') ?></p>
            <div class="mb-3">
              <label for="username"><?= lang('Interface.name') ?></label>
              <input class="form-control" id="name" maxlength="255" name="name" required placeholder="<?= lang('Interface.fullName') ?>" value="<?= esc($data->name, 'attr') ?>">
            </div>
            <div class="mb-3">
              <label for="email"><?= lang('Interface.email') ?></label>
              <input class="form-control" <?=$email_verified ? 'disabled' : ''?> id="email" maxlength="255" name="email" type="email" required placeholder="<?= lang('Interface.activeEmail') ?>" value="<?= esc($data->email, 'attr') ?>">
            </div>
            <div class="mb-3">
              <label for="phone"><?= lang('Interface.phone') ?></label>
              <input class="form-control" id="phone" maxlength="16" name="phone" pattern="(\+|08)\d{8,16}" placeholder="08xxx untuk nomor lokal atau sertakan kode internasional" required value="<?= esc($data->phone, 'attr') ?>">
            </div>
            <div class="mb-3">
              <label for="lang"><?= lang('Interface.language') ?></label>
              <select name="lang" id="lang" required class="form-select">
                <option value="id" <?= $data->lang === 'id' ? 'selected' : '' ?>>Bahasa Indonesia</option>
                <option value="en" <?= $data->lang === 'en' ? 'selected' : '' ?>>English</option>
              </select>
              <?php if ($data->lang === 'en') : ?>
                <p><small>The english translation currently is in progress and very limited.</small></p>
              <?php endif ?>
            </div>
            <p><input type="submit" class="btn btn-primary" value="<?= lang('Interface.saveProfile') ?>"></p>
          </form>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <form action="/user/reset" method="POST" class="card-body">
            <h2 class="mb-3"><?= lang('Interface.changePassword') ?></h2>
            <p><?= lang('Interface.changePasswordMessage') ?></p>
            <div class="mb-3">
              <label for="username"><?= lang('Interface.currentPassword') ?></label>
              <input class="form-control" id="passnow" maxlength="72" name="passnow" type="password" required>
            </div>
            <div class="mb-3">
              <label for="username"><?= lang('Interface.newPassword') ?></label>
              <input class="form-control" id="password" minlength="8" maxlength="72" name="password" type="password" required>
            </div>
            <div class="mb-3">
              <label for="username"><?= lang('Interface.confirmNewPassword') ?></label>
              <input class="form-control" id="passconf" minlength="8" maxlength="72" name="passconf" type="password" required>
            </div>
            <p><input type="submit" class="btn btn-primary" value="<?= lang('Interface.savePassword') ?>"></p>
          </form>
        </div>
        <div class="card my-2">
          <div class="card-body">
            <a href="/user/delete" class="float-right btn btn-danger"><?= lang('Interface.deleteAccount') ?></a>
          </div>
        </div>
      </div>
    </div>
  </div>
  </div>

</body>

</html>