<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>
  <div class="container-fluid">
    <h1>Order Hosting Baru</h1>
    <?= $validation ? $validation->listErrors() : '' ?>
    <form method="POST" name="upgrade">
      <div class="row">
        <div class="col-lg-4">
          <div class="card my-2">
            <div class="card-body">
              <div class="mb-3">
                <label class="form-label" for="username"><?= lang('Hosting.portalUsername') ?></label>
                <input class="form-control" id="username" minlength="5" maxlength="32" name="username" placeholder="hanya huruf, digit dan strip" pattern="^[-a-zA-Z0-9]+$" oninput="recalculate()" required>
              </div>
              <div class="mb-3">
                <label class="form-label" for="name"><?= lang('Hosting.portalPassword') ?></label>
                <div class="input-group">
                  <input class="form-control" id="password" oninput="this.type = 'password'" name="password" type="password" minlength="8" autocomplete="new-password" required pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$">
                  <input type="button" class="btn btn-success" onclick="useRandPass()" value="Random">
                </div>
                <small class="form-text text-muted">
                  <?= lang('Interface.passwordNotice') ?>.
                </small>
              </div>
              <div class="mb-3">
                <label class="form-label" for="slave"><?= lang('Hosting.slaveServer') ?></label>
                <select class="form-select" id="slave" name="slave" required>
                  <?php foreach ($slaves as $slave) : ?>
                    <label class="form-check">
                      <option value="<?= $slave->slave_id ?>"><?= $slave->slave_alias . " (Penggunaan: " . ($slave->utilization * 100) . "%)" ?></option>
                    </label>
                  <?php endforeach ?>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label" for="template"><?= lang('Hosting.chooseTemplate') ?></label>
                <select name="template" class="form-select" required>
                  <?php $tt = "Default" ?>
                  <optgroup label="Default">
                    <?php foreach ($templates as $t) : ?>
                      <?php if ($t->template_category !== $tt) : $tt = $t->template_category ?>
                  </optgroup>
                  <optgroup label="<?= $tt ?>">
                  <?php endif ?>
                  <option value="<?= $t->template_id ?>"><?= $t->template_name ?></option>
                <?php endforeach ?>
                  </optgroup>
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card my-2">
            <div class="card-body">
              <h3 class="card-title">Paket</h3>
              <div class="mb-3">
                <label class="form-label" for="plan"><?= lang('Hosting.selectPacketType') ?></label>
                <select name="plan" id="plan" class="form-select" onchange="recalculate()" required>
                  <?php foreach ($plans as $plan) : ?>
                    <option value="<?= $plan->plan_id ?>"><?= ucfirst($plan->plan_alias) ?></option>
                  <?php endforeach ?>
                </select>
                <small class="form-text text-muted">
                  <a href="https://dom.my.id/price" target="_blank"><?= lang('Hosting.lookPacketDiff') ?></a>.
                </small>
              </div>
              <div class="mb-3 row align-items-center">
                <div class="col">
                  <label for="years"><?= lang('Hosting.yearDuration') ?></label>
                </div>
                <div class="col">
                  <input type="number" disabled class="form-control" name="years" value="1" min="1" max="5" onchange="recalculate()">
                </div>
              </div>
              <h3 class="card-title">Domain</h3>
              <div class="mb-3">
                <label class="form-label" for="domain_mode"><?= lang('Hosting.selectDomainKind') ?></label>
                <select name="domain_mode" id="domain_mode" disabled class="form-select" onchange="recalculate()" required>
                  <option value="free" selected>Gunakan domain gratis</option>
                  <option value="buy">Beli domain baru</option>
                  <option value="custom">Gunakan yang sudah ada</option>
                </select>
              </div>
              <div id="dm-free">
                <div class="mb-3">
                  <input class="form-control" id="free_cname" value=".dom.my.id" disabled>
                  <small class="form-text text-muted">
                    Domain gratis hanya menyediakan fitur terbatas.
                    <br><a href="https://panduan.domcloud.id/domain" target="_blank" rel="noopener noreferrer">Pelajari lebih lanjut</a>.
                  </small>
                </div>
              </div>
              <div id="dm-buy" class="d-none">
                <?php if ($liquid) : ?>
                  <div class="mb-3">
                    <label class="form-label">Cari Domain</label>
                    <div class="input-group">
                      <input name="buy_cname" id="buy_cname" class="form-control" pattern="^[-a-zA-Z0-9]+$" required oninput="recalculate()">
                      <select class="form-select" name="buy_scheme" id="buy_scheme" required style="max-width: 120px" onchange="recalculate()">
                        <?php foreach ($schemes as $s) : if ($s->scheme_price !== '0') : ?>
                            <option value="<?= $s->scheme_id ?>"><?= $s->scheme_alias ?></option>
                        <?php endif;
                        endforeach; ?>
                      </select>
                      <input onclick="checkDomain()" type="button" value="Cek" class="btn btn-primary">
                    </div>
                    <small class="form-text text-muted">
                      <a href="https://dom.my.id/domain" target="_blank">Lihat daftar top level domain tersedia</a>.
                    </small>
                  </div>
                  <p id="buy-status-prompt" class="alert alert-primary">
                    Silahkan cek ketersediaan domain sebelum lanjut.
                  </p>
                  <p id="buy-status-available" class="alert alert-success d-none">
                    Domain tersedia!
                  </p>
                  <p id="buy-status-loading" class="alert alert-warning d-none">
                    Sedang mengecek...
                  </p>
                  <p id="buy-status-error" class="alert alert-danger d-none">
                    Domain sedang tidak tersedia.
                  </p>
                <?php else : ?>
                  <p class="alert alert-danger">
                    <small>
                      Kami tidak dapat memproses pembelian domain sebelum anda mengisi data yang kami butuhkan.
                      <br><a href="/user/domain?then=reload" target="_blank">Isi sekarang</a>.
                    </small>
                  </p>
                <?php endif ?>
              </div>
              <div id="dm-custom" class="d-none">
                <div class="mb-3">
                  <input class="form-control" id="custom_cname" name="custom_cname" disabled oninput="recalculate()" required placeholder="masukkan domain kustom" pattern="^[a-zA-Z0-9][a-zA-Z0-9.-]{1,61}[a-zA-Z0-9]\.[a-zA-Z]{2,}$">
                  <small class="form-text text-muted">
                    Anda perlu mengarahkan domain setelah ini.
                    <br><a href="https://panduan.domcloud.id/domain" target="_blank" rel="noopener noreferrer">Pelajari lebih lanjut</a>.
                  </small>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card my-2">
            <div class="card-body">
              <div class="d-flex">
                <h6><?= lang('Hosting.schemePrice') ?></h6>
                <div class="ml-auto" id="outprice">-</div>
              </div>
              <div class="d-flex">
                <h6><?= lang('Hosting.domainPrice') ?></h6>
                <div class="ml-auto" id="outdomain">-</div>
              </div>
              <div class="d-flex">
                <h6><?= lang('Hosting.hostingDuration') ?></h6>
                <div class="ml-auto" id="outyear">-</div>
              </div>
              <hr>
              <div class="d-flex">
                <h6><?= lang('Hosting.hostingCost') ?></h6>
                <div class="ml-auto" id="outtotal">-</div>
              </div>
              <div class="d-flex">
                <h6><?= lang('Hosting.transactionCost') ?></h6>
                <div class="ml-auto" id="outtip">-</div>
              </div>
              <hr>
              <div class="d-flex">
                <h6><?= lang('Hosting.totalPayment') ?></h6>
                <div class="ml-auto" id="outbill">-</div>
              </div>
              <div class="d-flex">
                <h6><?= lang('Hosting.expirationDate') ?></h6>
                <div class="ml-auto" id="outexp">-</div>
              </div>
              <input type="submit" value="<?= lang('Hosting.orderNow') ?>" class="form-control btn-lg btn btn-primary mt-3">
            </div>
          </div>
        </div>
      </div>

    </form>
  </div>

  <script>
    function useRandPass() {
      document.getElementById('password').value = genRandPass(12);
      document.getElementById('password').type = 'text';
    }
    useRandPass();

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
  <script id="plans" type="application/json">
    <?= json_encode($plans) ?>
  </script>
  <script id="schemes" type="application/json">
    <?= $liquid ? json_encode($schemes) : 'null' ?>
  </script>
  <script>
    var plans = JSON.parse(document.getElementById('plans').innerHTML).reduce((a, b) => (a[b.plan_id] = b, a), {});
    var schemes = JSON.parse(document.getElementById('schemes').innerHTML);
    schemes = schemes && schemes.reduce((a, b) => (a[b.scheme_id] = b, a), {});
    var formatter = new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      maximumFractionDigits: 0,
      minimumFractionDigits: 0,
    });
    var activedomain = null;

    function checkDomain() {
      var name = window.upgrade.buy_cname;
      var scheme = window.upgrade.buy_scheme;
      if (name.reportValidity && !name.reportValidity()) {
        return;
      }
      activedomain = null;
      recalculate();
      $('#buy-status-available,#buy-status-error,#buy-status-prompt').toggleClass('d-none', true);
      $('#buy-status-loading').toggleClass('d-none', false);

      fetch(`/user/domain/check?name=${name.value}&scheme=${scheme.value}`).then(r =>
        r.json()).then(r => {
        activedomain = r;
        $(r.status === 'available' ? '#buy-status-available' : '#buy-status-error').toggleClass('d-none', false);
        $('#buy-status-loading').toggleClass('d-none', true);
        recalculate();
      }).catch(e => {
        activedomain = null;
        $('#buy-status-loading').toggleClass('d-none', true);
        $('#buy-status-error').toggleClass('d-none', false);
      });
    }

    function recalculate() {
      // Get values
      var tip = 5000;
      var form = window.upgrade;
      var dommod = form.domain_mode.value;
      var scheme = dommod === 'buy' && form.buy_scheme ? parseInt(schemes[form.buy_scheme.value].scheme_price) * 1000 : 0;
      var unit = parseInt(plans[form.plan.value].plan_price) * 1000;
      var years = unit === 0 ? 0.25 : parseInt(form.years.value);
      var exp = new Date(Date.now() + 1000 * 86400 * 365 * years);
      // Alter UI
      if (unit == 0) {
        dommod = form.domain_mode.value = 'free';
        scheme = 0;
      } else if (form.custom_cname.value.endsWith('dom.my.id')) {
        form.custom_cname.value = '';
      }
      $('#dm-free').toggleClass('d-none', dommod !== 'free');
      $('#dm-buy').toggleClass('d-none', dommod !== 'buy');
      $('#dm-custom').toggleClass('d-none', dommod !== 'custom');
      form.free_cname.value = form.username.value + '.dom.my.id';
      form.domain_mode.disabled = unit === 0;
      form.years.disabled = unit === 0;
      form.custom_cname.disabled = dommod !== 'custom';
      if (form.buy_cname) {
        form.buy_cname.disabled = dommod !== 'buy';
        form.buy_scheme.disabled = dommod !== 'buy';
      }

      // Show values
      if (unit == 0) {
        $('#outprice').text('Gratis');
        $('#outdomain').text('Gratis');
        $('#outyear').html('2 Bulan');
        $('#outtotal').text('Gratis');
        $('#outtip').text('-');
        $('#outbill').text('Gratis');
      } else {
        $('#outprice').text(formatter.format(unit));
        $('#outdomain').text(formatter.format(scheme));
        $('#outyear').html('&times; ' + years + ' Tahun');
        $('#outtotal').text(formatter.format((unit + scheme) * years));
        $('#outtip').text(formatter.format(tip));
        $('#outbill').text(formatter.format((unit + scheme) * years + tip));
      }

      $('#outexp').text(exp.toISOString().substr(0, 10));
    }
  </script>
</body>

</html>