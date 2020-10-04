<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>

  <div class="container">
    <form method="POST" class="row">
      <?= csrf_field() ?>
      <div class="col-md-6">
        <div class="card">
          <div class="card-body">
            <h1 class="mb-3"><?= lang('Domain.registerTitle') ?></h1>
            <p><?= lang('Domain.registerHint') ?></p>
          </div>
        </div>
        <div class="card my-2">
          <div class="card-body">
            <h3><?= lang('Domain.administrationData') ?></h3>
            <div class="mb-3">
              <label for="name"><?= lang('Interface.fullName') ?></label>
              <input class="form-control" id="name" name="name" value="<?= esc($data->name, 'attr') ?>" autocomplete="name" readonly required>
            </div>
            <div class="mb-3">
              <label for="company"><?= lang('Interface.companyName') ?></label>
              <input class="form-control" id="company" name="company" autocomplete="organization" required>
            </div>
            <div class="mb-3">
              <label for="name"><?= lang('Interface.email') ?></label>
              <input class="form-control" id="email" name="email" autocomplete="email" value="<?= esc($data->email, 'attr') ?>" readonly required>
            </div>
            <div class="mb-3">
              <label for="name"><?= lang('Interface.password') ?></label>
              <div class="input-group">
                <input class="form-control" id="password" oninput="this.type = 'password'" name="password" type="password" minlength="8" autocomplete="new-password" required pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$">
                <input type="button" class="btn btn-success" onclick="useRandPass()" value="Random">
              </div>
              <small class="form-text text-muted">
                <?= lang('Interface.passwordNotice') ?>
              </small>
            </div>
            <div class="mb-3">
              <label for="name"><?= lang('Interface.phone') ?></label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <div class="input-group-text">+</div>
                </div>
                <input class="form-control" name="tel_cc_no" autocomplete="tel-country-code" style="max-width: 64px" maxlength="4" required>
                <input class="form-control" name="tel_no" autocomplete="tel-extension" required>
              </div>
            </div>
            <div class="mb-3">
              <label for="name"><?= lang('Interface.phoneAlt') ?> <?= lang('Interface.optional') ?></label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <div class="input-group-text">+</div>
                </div>
                <input class="form-control" name="alt_tel_cc_no" style="max-width: 64px" maxlength="4">
                <input class="form-control" name="alt_tel_no">
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-body">
            <h3 class="mb-3"><?= lang('Domain.addressData') ?></h3>
            <div class="mb-3">
              <label for="address_line_1"><?= lang('Domain.address1') ?></label>
              <input class="form-control" autocomplete="address-level1" name="address_line_1" required>
            </div>
            <div class="mb-3">
              <label for="address_line_2"><?= lang('Domain.address2') ?> <?= lang('Interface.optional') ?></label>
              <input class="form-control" autocomplete="address-level2" name="address_line_2">
            </div>
            <div class="mb-3">
              <label for="address_line_3"><?= lang('Domain.address3') ?> <?= lang('Interface.optional') ?></label>
              <input class="form-control" autocomplete="address-level3" name="address_line_3">
            </div>
            <div class="mb-3">
              <label for="city"><?= lang('Domain.city') ?></label>
              <input class="form-control" autocomplete="address-level4" name="city" required>
            </div>
            <div class="mb-3">
              <label for="state"><?= lang('Domain.state') ?></label>
              <input class="form-control" name="state" required>
            </div>
            <div class="mb-3">
              <label for="country_code"><?= lang('Domain.country') ?></label>
              <select class="form-select" name="country_code" required>
                <?php foreach ($codes as $code) : $c = strtolower($code['country']);
                  $l = $data->lang === 'en' ? 'us' : 'id' ?>
                  <option value="<?= $c ?>" <?= $l === $c ? 'selected' : '' ?>><?= $code['name'] ?></option>
                <?php endforeach ?>
              </select>
            </div>
            <div class="mb-3">
              <label for="zipcode"><?= lang('Domain.zipCode') ?></label>
              <input class="form-control" autocomplete="postal-code" name="zipcode" required minlength="4">
            </div>
          </div>
        </div>
        <div class="card my-2">
          <div class="card-body">
            <input type="submit" class="btn-primary btn" value="<?= lang('Interface.save') ?>">
          </div>
        </div>
      </div>
    </form>
  </div>
</body>

<script>
  function useRandPass() {
    $('#password').val(genRandPass(12));
    $('#password').attr('type', 'text');
  }

  function genRandPass(pLength) {

    var keyListLower = "abcdefghijklmnopqrstuvwxyz",
      keyListUpper = "ABCDEFGHIJKLMNOPQRSTUVWXYZ",
      keyListInt = "123456789",
      keyListSpec = "!@#_",
      password = '';
    var len = Math.ceil(pLength / 3) - 1;
    var lenSpec = pLength - 3 * len;

    for (i = 0; i < len; i++) {
      password += keyListLower.charAt(Math.floor(Math.random() * keyListLower.length));
      password += keyListUpper.charAt(Math.floor(Math.random() * keyListUpper.length));
      password += keyListInt.charAt(Math.floor(Math.random() * keyListInt.length));
    }

    for (i = 0; i < lenSpec; i++)
      password += keyListSpec.charAt(Math.floor(Math.random() * keyListSpec.length));

    password = password.split('').sort(function() {
      return 0.5 - Math.random()
    }).join('');

    return password;
  }
</script>

</html>