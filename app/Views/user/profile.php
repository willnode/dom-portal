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
            <?= csrf_field() ?>
            <h2 class="mb-3"><?= lang('Interface.editProfile') ?></h2>
            <p><?= lang('Interface.editProfileMessage') ?></p>
            <div class="mb-3">
              <label for="username"><?= lang('Interface.name') ?></label>
              <input class="form-control" id="name" maxlength="255" name="name" required placeholder="<?= lang('Interface.fullName') ?>" value="<?= esc($data->name, 'attr') ?>">
            </div>
            <div class="mb-3">
              <label for="email"><?= lang('Interface.email') ?></label>
              <input class="form-control" <?= $email_verified_at ? 'disabled' : '' ?> id="email" maxlength="255" name="email" type="email" required placeholder="<?= lang('Interface.activeEmail') ?>" value="<?= esc($data->email, 'attr') ?>">
            </div>
            <div class="mb-3">
              <label for="phone"><?= lang('Interface.phone') ?></label>
              <input class="form-control" id="phone" maxlength="16" name="phone" placeholder="<?= lang('Interface.phoneHint') ?>" value="<?= esc($data->phone, 'attr') ?>">
            </div>
            <div class="mb-3">
              <label for="lang"><?= lang('Interface.language') ?></label>
              <select name="lang" id="lang" required class="form-select">
                <option value="id" <?= $data->lang === 'id' ? 'selected' : '' ?>>Bahasa Indonesia</option>
                <option value="en" <?= $data->lang === 'en' ? 'selected' : '' ?>>English</option>
              </select>
              <?php if ($data->lang === 'en') : ?>
                <p class="mt-2 alert alert-info"><small><i> By using English Language, all purchases will be translated to USD dollar and payments will be processed through PayPal.</i></small></p>
              <?php elseif ($data->lang === 'id') : ?>
                <p class="mt-2 alert alert-info"><small><i> Dengan menggunakan Bahasa Indonesia, semua pembayaran akan menggunakan IDR (Indonesia rupiah), pembelian domain Indonesia tersedia, dan pembayaran akan diproses oleh Payment Gate iPaymu.</i></small></p>
              <?php endif ?>
            </div>
            <p><input type="submit" class="btn btn-primary" value="<?= lang('Interface.saveProfile') ?>"></p>
          </form>
        </div>
      </div>
      <div class="col-md-6">
        <?php if (!$email_verified_at) : ?>
          <div class="alert alert-danger mb-3">
            <p><?= lang('Interface.confirmationHint') ?> <b><?= esc($data->email) ?></b></p>
            <form method="post" class="my-2">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="resend">
              <input type="submit" class="btn btn-success" onclick="return confirm('<?= lang('Interface.confirmationPrompt') ?>')" value="<?= lang('Interface.resendConfirmationEmail') ?>">
            </form>
          </div>
        <?php endif ?>
        <div class="card">
          <form action="/user/reset" method="POST" class="card-body">
            <?= csrf_field() ?>
            <h2 class="mb-3"><?= lang('Interface.changePassword') ?></h2>
            <p><?= lang('Interface.changePasswordMessage') ?></p>
            <div class="mb-3">
              <label for="username"><?= lang('Interface.currentPassword') ?></label>
              <input class="form-control" id="passtest" maxlength="72" autocomplete="current-password" name="passtest" type="password" required>
            </div>
            <div class="mb-3">
              <label for="username"><?= lang('Interface.newPassword') ?></label>
              <input class="form-control" id="password" minlength="8" autocomplete="new-password" maxlength="72" name="password" type="password" required>
            </div>
            <div class="mb-3">
              <label for="username"><?= lang('Interface.confirmNewPassword') ?></label>
              <input class="form-control" id="passconf" minlength="8" autocomplete="new-password" maxlength="72" name="passconf" type="password" required>
            </div>
            <p><input type="submit" class="btn btn-primary" value="<?= lang('Interface.savePassword') ?>"></p>
          </form>
        </div>
        <div class="card my-2">
          <div class="card-body">
            <a href="/user/delete" class="float-end btn btn-danger"><?= lang('Interface.deleteAccount') ?></a>
          </div>
        </div>
      </div>
    </div>
  </div>
  </div>

</body>

</html>