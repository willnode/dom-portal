<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>
  <div class="container-fluid">
    <h1><?= lang('Hosting.newHost') ?></h1>
    <?= $validation ? $validation->listErrors() : '' ?>
    <form method="POST" name="box">
      <div class="row">
        <div class="col-lg-4">
          <div class="card my-2">
            <div class="card-body">
              <div class="mb-3">
                <label class="form-label" for="username"><?= lang('Hosting.portalUsername') ?></label>
                <input class="form-control" id="username" minlength="5" maxlength="32" name="username" placeholder="<?= lang('Hosting.portalUsernameHint') ?>" pattern="^[a-zA-Z][-a-zA-Z0-9]+$" oninput="recalculate()" required>
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
                <label class="form-label" for="server"><?= lang('Hosting.slaveServer') ?></label>
                <select class="form-select" id="server" name="server" required>
                  <?php foreach ($servers as $server) : ?>
                    <label class="form-check">
                      <option value="<?= $server->id ?>"><?= $server->alias ?></option>
                    </label>
                  <?php endforeach ?>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label" for="template"><?= lang('Hosting.chooseTemplate') ?></label>
                <input type="text" name="template" class="form-control" list="quickTemplates">
                <datalist id="quickTemplates">
                    <option value="https://id.wordpress.org/latest-id_ID.zip">WordPress</option>
                </datalist>
                <small>Opsional, harus berisi alamat URL ke file ZIP</small>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card my-2">
            <div class="card-body">
              <h3 class="card-title"><?= lang('Hosting.scheme') ?></h3>
              <div class="mb-3">
                <label class="form-label" for="plan"><?= lang('Hosting.selectPacketType') ?></label>
                <select name="plan" id="plan" class="form-select" onchange="recalculate()" required>
                  <?php foreach ($plans as $plan) : ?>
                    <option value="<?= $plan->id ?>"><?= ucfirst($plan->alias) ?></option>
                  <?php endforeach ?>
                </select>
              </div>
              <div class="mb-3 row align-items-center">
                <div class="col">
                  <label for="years"><?= lang('Hosting.yearDuration') ?></label>
                </div>
                <div class="col">
                  <input type="number" disabled class="form-control" id="years" name="years" value="1" min="1" max="5" onchange="recalculate()">
                </div>
              </div>
              <div class="mb-3 row align-items-center">
                <div class="col">
                  <label for="addons">Data Add-ons (GB)</label>
                </div>
                <div class="col">
                  <input type="number" disabled class="form-control" name="addons" name="addons" value="0" min="0" max="1000" onchange="recalculate()">
                </div>
              </div>
              <p>
                <small class="form-text text-muted">
                  <a href="https://domcloud.id/price" target="_blank"><?= lang('Hosting.lookPacketDiff') ?></a>.
                </small>
              </p>
              <h3 class="card-title"><?= lang('Interface.domain') ?></h3>
              <div class="mb-3">
                <label class="form-label" for="domain_mode"><?= lang('Hosting.selectDomainKind') ?></label>
                <select name="domain_mode" id="domain_mode" disabled class="form-select" onchange="recalculate()" required>
                  <option value="free" selected><?= lang('Hosting.useFreeDomain') ?></option>
                  <option value="buy"><?= lang('Hosting.buyNewDomain') ?></option>
                  <option value="custom"><?= lang('Hosting.useExistingDomain') ?></option>
                </select>
              </div>
              <div id="dm-free">
                <div class="mb-3">
                  <input class="form-control" id="free_cname" value=".dom.my.id" disabled>
                  <small class="form-text text-muted">
                    <?= lang('Hosting.freeDomainHint') ?>
                    <br><a href="https://panduan.domcloud.id/domain" target="_blank" rel="noopener noreferrer"><?= lang('Interface.learnMore') ?></a>.
                  </small>
                </div>
              </div>
              <div id="dm-buy" class="d-none">
                <?php if ($liquid) : ?>
                  <div class="mb-3">
                    <label class="form-label"><?= lang('Hosting.findDomain') ?></label>
                    <div class="input-group">
                      <input name="buy_cname" id="buy_cname" class="form-control" pattern="^[-a-zA-Z0-9]+$" required oninput="recalculate()">
                      <select class="form-select" name="buy_scheme" id="buy_scheme" required style="max-width: 120px" onchange="recalculate()">
                        <?php foreach ($schemes as $s) : if ($s->price_idr) : ?>
                            <option value="<?= $s->id ?>"><?= $s->alias ?></option>
                        <?php endif;
                        endforeach; ?>
                      </select>
                      <input onclick="checkDomain()" type="button" value="Cek" class="btn btn-primary">
                    </div>
                    <small class="form-text text-muted">
                      <a href="https://dom.my.id/domain" target="_blank"><?= lang('Hosting.findAvailableGLTDs') ?></a>.
                    </small>
                  </div>
                  <p id="buy-status-prompt" class="alert alert-primary">
                    <?= lang('Hosting.findReady') ?>
                  </p>
                  <p id="buy-status-available" class="alert alert-success d-none">
                    <?= lang('Hosting.findAvailable') ?>
                  </p>
                  <p id="buy-status-loading" class="alert alert-warning d-none">
                    <?= lang('Hosting.findWait') ?>
                  </p>
                  <p id="buy-status-error" class="alert alert-danger d-none">
                    <?= lang('Hosting.findUnavailable') ?>
                  </p>
                <?php else : ?>
                  <p class="alert alert-danger">
                    <small>
                      <?= lang('Hosting.findNeedData') ?>
                      <br><a href="/user/domain?then=reload" target="_blank"><?= lang('Hosting.findNeedDataAction') ?></a>.
                    </small>
                  </p>
                <?php endif ?>
              </div>
              <div id="dm-custom" class="d-none">
                <div class="mb-3">
                  <input class="form-control" id="custom_cname" name="custom_cname" disabled oninput="recalculate()" required placeholder="masukkan domain kustom" pattern="^[a-zA-Z0-9][a-zA-Z0-9.-]{1,61}[a-zA-Z0-9]\.[a-zA-Z]{2,}$">
                  <small class="form-text text-muted">
                    <?= lang('Hosting.useExistingHint') ?>
                    <br><a href="https://panduan.domcloud.id/domain" target="_blank" rel="noopener noreferrer"><?= lang('Interface.learnMore') ?></a>.
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
                <h6><?= lang('Hosting.addonsPrice') ?></h6>
                <div class="ml-auto" id="outaddons">-</div>
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
              <hr>
              <div class="row">
                <div class="col">
                  <h6>Disk Space</h6>
                  <div class="ml-auto" id="specdisk">- MiB</div>
                </div>
                <div class="col">
                  <h6>Bandwidth</h6>
                  <div class="ml-auto" id="specbwt">- GiB</div>
                </div>
                <div class="col">
                  <h6>Add-ons</h6>
                  <div class="ml-auto" id="specbwb">- GiB</div>
                </div>
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
    const plans = JSON.parse(document.getElementById('plans').innerHTML).reduce((a, b) => (a[b.id] = b, a), {});
    const schemes = (x => x && x.reduce((a, b) => (a[b.id] = b, a), {}))(JSON.parse(document.getElementById('schemes').innerHTML));
    const currency = '<?= lang('Interface.currency') ?>';
    const digits = '<?= lang('Interface.currency') === 'usd' ? 2 : 0 ?>';
    const formatter = new Intl.NumberFormat('<?= lang('Interface.codeI8LN') ?>', {
      style: 'currency',
      currency: currency,
      maximumFractionDigits: digits,
      minimumFractionDigits: digits,
    });

    let activedomain = null;
    function checkDomain() {
      const name = window.box.buy_cname;
      const scheme = window.box.buy_scheme;
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
      var tip = {
        'usd': 0.4,
        'idr': 5000
      } [currency];
      var bww = {
        'usd': 0.32,
        'idr': 4000
      } [currency];
      var form = window.box;
      var dommod = form.domain_mode.value;
      var unit = parseInt(plans[form.plan.value]['price_' + currency]);
      var years = unit === 0 ? 1 / 6 : Math.min(5, parseInt(form.years.value));
      var addons = unit === 0 ? 0 : Math.min(1000, parseInt(form.addons.value));
      var exp = new Date(Date.now() + 1000 * 86400 * 365 * years);
      // Domain Calc
      var scheme = 0;
      if (dommod === 'buy' && form.buy_scheme) {
        scheme = parseInt(schemes[form.buy_scheme.value]['price_' + currency]);
        if (years > 1) {
          scheme += parseInt(schemes[form.buy_scheme.value]['renew_' + currency]) * (years - 1)
        }
      }
      // Alter UI
      if (unit == 0) {
        dommod = form.domain_mode.value = 'free';
        scheme = 0;
      }
      $('#dm-free').toggleClass('d-none', dommod !== 'free');
      $('#dm-buy').toggleClass('d-none', dommod !== 'buy');
      $('#dm-custom').toggleClass('d-none', dommod !== 'custom');
      form.free_cname.value = form.username.value + '.dom.my.id';
      form.domain_mode.disabled = unit === 0;
      form.years.disabled = unit === 0;
      form.addons.disabled = unit === 0;
      form.custom_cname.disabled = dommod !== 'custom';
      if (form.buy_cname) {
        form.buy_cname.disabled = dommod !== 'buy';
        form.buy_scheme.disabled = dommod !== 'buy';
      }

      // Show values
      if (unit == 0) {
        const free = '<?= lang('Hosting.free') ?>'
        $('#outprice').text('-');
        $('#outdomain').text('-');
        $('#outaddons').html('-');
        $('#outtip').text('-');
        $('#outbill').text(free);
      } else {
        $('#outprice').text(formatter.format(unit * years));
        $('#outdomain').text(formatter.format(scheme));
        $('#outaddons').html(formatter.format(addons * bww));
        $('#outtip').text(formatter.format(tip));
        $('#outbill').text(formatter.format(unit * years + addons * bww + scheme + tip));
      }
      $('#specdisk').text(plans[form.plan.value]['disk'] + ' MiB');
      $('#specbwt').text(plans[form.plan.value]['net'] * Math.max(years, 1) + ' GiB');
      $('#specbwb').text(plans[form.plan.value]['net'] / 12 * Math.floor(years) + addons + ' GiB');

      $('#outexp').text(exp.toISOString().substr(0, 10));
    }
  </script>
</body>

</html>