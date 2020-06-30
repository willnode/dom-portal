<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>

  <div class="container">
    <form method="POST" class="row">
      <div class="col-md-6">
        <div class="card">
          <div class="card-body">
            <h1 class="mb-3">Registrasi Akun Domain</h1>
            <p>Sebelum anda dapat membeli domain, kami perlu memerlukan data berikut. Data berikut digunakan untuk keperluan administrasi domain, seperti WHOIS.</p>
          </div>
        </div>
        <div class="card my-2">
          <div class="card-body">
            <h3>Data Administrasi</h3>
            <div class="form-group">
              <label for="name">Nama Lengkap</label>
              <input class="form-control" id="name" name="name" value="<?= esc($data['name'], 'attr') ?>" autocomplete="name" readonly required>
            </div>
            <div class="form-group">
              <label for="name">Nama Bisnis / Perusahaan</label>
              <input class="form-control" id="company" name="company" autocomplete="organization" required>
            </div>
            <div class="form-group">
              <label for="name">Email</label>
              <input class="form-control" id="email" name="email" autocomplete="email" value="<?= esc($data['email'], 'attr') ?>" readonly required>
            </div>
            <div class="form-group">
              <label for="name">Password</label>
              <div class="input-group">
                <input class="form-control" id="password" oninput="this.type = 'password'" name="password" type="password" minlength="8" autocomplete="new-password" required pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$">
                <div class="input-group-append">
                  <input type="button" class="btn btn-success" onclick="useRandPass()" value="Random">
                </div>
              </div>
              <small class="form-text text-muted">
                Password ini digunakan sebagai akun masuk Portal Domain. Harus berbeda dengan password portal sekarang.
                Anda tidak perlu mengingat password ini karena akan disimpan untuk auto-login ke portal domain.
              </small>
            </div>
            <div class="form-group">
              <label for="name">Nomor Telepon</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <div class="input-group-text">+</div>
                </div>
                <input class="form-control" name="tel_cc_no" autocomplete="tel-country-code" style="max-width: 64px" maxlength="4" required>
                <input class="form-control" name="tel_no" autocomplete="tel-extension" required>
              </div>
            </div>
            <div class="form-group">
              <label for="name">Nomor Telepon Alternatif (opsional)</label>
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
            <h3 class="mb-3">Data Alamat</h3>
            <div class="form-group">
              <label for="name">Alamat (Baris 1)</label>
              <input class="form-control" autocomplete="address-level1" name="address_line_1" required>
            </div>
            <div class="form-group">
              <label for="name">Alamat (Baris 2, Opsional)</label>
              <input class="form-control" autocomplete="address-level2" name="address_line_2">
            </div>
            <div class="form-group">
              <label for="name">Alamat (Baris 3, Opsional)</label>
              <input class="form-control" autocomplete="address-level3" name="address_line_3">
            </div>
            <div class="form-group">
              <label for="name">Kota</label>
              <input class="form-control" autocomplete="address-level4" name="city" required>
            </div>
            <div class="form-group">
              <label for="name">Provinsi</label>
              <input class="form-control" name="state" required>
            </div>
            <div class="form-group">
              <label for="name">Negara</label>
              <select class="form-control" name="country_code" required>
                <?php foreach ($codes as $code) : $c = strtolower($code['country']);
                  $l = $data['lang'] === 'en' ? 'us' : 'id' ?>
                  <option value="<?= $c ?>" <?= $l === $c ? 'selected' : '' ?>><?= $code['name'] ?></option>
                <?php endforeach ?>
              </select>
            </div>
            <div class="form-group">
              <label for="name">Kode Zip</label>
              <input class="form-control" autocomplete="postal-code" name="zipcode" required minlength="4">
            </div>
          </div>
        </div>
        <div class="card my-2">
          <div class="card-body">
            <input type="submit" class="btn-primary btn" value="Simpan">
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