<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>
  <?php if ($ok) : ?>
    <div class="container-fluid">
      <h1><?= lang('Host.newHost') ?></h1>
      <?= $validation ? $validation->listErrors() : '' ?>
      <form method="POST" name="box">
        <div class="row">
          <div class="col-lg-4">
            <div class="card my-2">
              <div class="card-body">
                <div class="mb-3">
                  <label class="form-label" for="username"><?= lang('Host.portalUsername') ?></label>
                  <input class="form-control" id="username" minlength="5" maxlength="32" name="username" placeholder="<?= lang('Host.portalUsernameHint') ?>" pattern="^[a-zA-Z][-a-zA-Z0-9]+$" oninput="recalculate()" required>
                </div>
                <div class="mb-3">
                  <label class="form-label" for="name"><?= lang('Host.portalPassword') ?></label>
                  <div class="input-group">
                    <input class="form-control" id="password" oninput="this.type = 'password'" name="password" type="password" minlength="8" autocomplete="one-time-code" required pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$">
                    <input type="button" class="btn btn-success" onclick="useRandPass()" value="Random">
                  </div>
                  <small class="form-text text-muted">
                    <?= lang('Interface.passwordNotice') ?>.
                  </small>
                </div>
                <div class="mb-3">
                  <label class="form-label" for="server"><?= lang('Host.slaveServer') ?></label>
                  <select class="form-select" id="server" name="server" required>
                    <?php foreach ($servers as $server) : ?>
                      <label class="form-check">
                        <option value="<?= $server->id ?>"><?= $server->alias ?> &mdash; <?= $server->description ?></option>
                      </label>
                    <?php endforeach ?>
                  </select>
                </div>
                <div class="mb-3">
                  <div class="d-flex mb-2">
                    <label class="form-label" for="template"><?= lang('Host.template') ?></label>
                    <button type="button" class="ml-auto btn btn-sm btn-primary" data-toggle="modal" data-target="#templateModal">
                      <?= lang('Host.chooseTemplate') ?>
                    </button>
                  </div>
                  <textarea name="template" class="form-control font-monospace text-nowrap" placeholder="Config File" rows=7></textarea>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="card my-2">
              <div class="card-body">
                <h3 class="card-title"><?= lang('Host.scheme') ?></h3>
                <div class="mb-3">
                  <label class="form-label" for="plan"><?= lang('Host.selectPacketType') ?></label>
                  <select name="plan" id="plan" class="form-select" onchange="recalculate()" required>
                    <?php foreach ($plans as $plan) : ?>
                      <option value="<?= $plan->id ?>"><?= ucfirst($plan->alias) ?></option>
                    <?php endforeach ?>
                  </select>
                </div>
                <div class="mb-3 row align-items-center">
                  <div class="col">
                    <label for="years"><?= lang('Host.yearDuration') ?></label>
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
                    <input type="number" disabled class="form-control" name="addons" name="addons" value="0" min="0" max="10000" step="10" onchange="recalculate()">
                  </div>
                </div>
                <p>
                  <small class="form-text text-muted">
                    <a href="<?= lang('Interface.lang') == 'id' ? 'https://domcloud.id/price' : 'https://domcloud.id/en/price' ?>" target="_blank"><?= lang('Host.lookPacketDiff') ?></a>.
                  </small>
                </p>
                <h3 class="card-title"><?= lang('Interface.domain') ?></h3>
                <div class="mb-3">
                  <label class="form-label" for="domain_mode"><?= lang('Host.selectDomainKind') ?></label>
                  <select name="domain_mode" id="domain_mode" disabled class="form-select" onchange="recalculate()" required>
                    <option value="free" selected><?= lang('Host.useFreeDomain') ?></option>
                    <option value="buy"><?= lang('Host.buyNewDomain') ?></option>
                    <option value="custom"><?= lang('Host.useExistingDomain') ?></option>
                  </select>
                </div>
                <div id="dm-free">
                  <div class="mb-3">
                    <input class="form-control" id="free_cname" value=".dom.my.id" disabled>
                    <small class="form-text text-muted">
                      <?= lang('Host.freeDomainHint') ?>
                      <br><a href="https://panduan.domcloud.id/domain" target="_blank" rel="noopener noreferrer"><?= lang('Interface.learnMore') ?></a>.
                    </small>
                  </div>
                </div>
                <div id="dm-buy" class="d-none">
                  <?php if ($liquid) : ?>
                    <div class="mb-3">
                      <label class="form-label"><?= lang('Host.findDomain') ?></label>
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
                        <a href="https://dom.my.id/domain" target="_blank"><?= lang('Host.findAvailableGLTDs') ?></a>.
                      </small>
                    </div>
                    <p id="buy-status-prompt" class="alert alert-primary">
                      <?= lang('Host.findReady') ?>
                    </p>
                    <p id="buy-status-available" class="alert alert-success d-none">
                      <?= lang('Host.findAvailable') ?>
                    </p>
                    <p id="buy-status-loading" class="alert alert-warning d-none">
                      <?= lang('Host.findWait') ?>
                    </p>
                    <p id="buy-status-error" class="alert alert-danger d-none">
                      <?= lang('Host.findUnavailable') ?>
                    </p>
                  <?php else : ?>
                    <p class="alert alert-danger">
                      <small>
                        <?= lang('Host.findNeedData') ?>
                        <br><a href="/user/domain?then=reload" target="_blank"><?= lang('Host.findNeedDataAction') ?></a>.
                      </small>
                    </p>
                  <?php endif ?>
                </div>
                <div id="dm-custom" class="d-none">
                  <div class="mb-3">
                    <input class="form-control" id="custom_cname" name="custom_cname" disabled oninput="recalculate()" required placeholder="<?= lang('Host.enterCustomDomain') ?>" pattern="^[a-zA-Z0-9][a-zA-Z0-9.-]{1,61}[a-zA-Z0-9]\.[a-zA-Z]{2,}$">
                    <small class="form-text text-muted">
                      <?= lang('Host.useExistingHint') ?>
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
                  <h6><?= lang('Host.schemePrice') ?></h6>
                  <div class="ml-auto" id="outprice">-</div>
                </div>
                <div class="d-flex">
                  <h6><?= lang('Host.domainPrice') ?></h6>
                  <div class="ml-auto" id="outdomain">-</div>
                </div>
                <div class="d-flex">
                  <h6><?= lang('Host.addonsPrice') ?></h6>
                  <div class="ml-auto" id="outaddons">-</div>
                </div>
                <div class="d-flex">
                  <h6><?= lang('Host.transactionCost') ?></h6>
                  <div class="ml-auto" id="outtip">-</div>
                </div>
                <hr>
                <div class="d-flex">
                  <h6><?= lang('Host.totalPayment') ?></h6>
                  <div class="ml-auto" id="outbill">-</div>
                </div>
                <div class="d-flex">
                  <h6><?= lang('Host.expirationDate') ?></h6>
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
                <input type="submit" id="submitBtn" value="<?= lang('Host.orderNow') ?>" class="form-control btn-lg btn btn-primary mt-3">
              </div>
            </div>
          </div>
        </div>

      </form>
    </div>


    <!-- Modal Template -->
    <div class="modal fade" id="templateModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><?= lang('Host.chooseTemplate') ?></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="row g-2" style="color: white; fill: white">
              <?php foreach ($templates as $t) : ?>
                <div class="col-md-6 my-1">
                  <div class="btn btn-block btn-dark text-center" style="background-color: <?= $t->color ?>;" onclick="submitT(this)" data-dismiss="modal" data-template="<?= base64_encode($t->template) ?>">
                    <div class="w-50 mx-auto my-2"><?= $t->logo ?></div>
                    <p class="mb-0"><?= $t->name ?></p>
                  </div>
                </div>
              <?php endforeach ?>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= lang('Interface.back') ?></button>
          </div>
        </div>
      </div>
    </div>

    <script>
      function submitT(t) {
        window.box.template.value = atob($(t).data('template'));
      }

      function useRandPass() {
        document.getElementById('password').value = genRandPass(12);
        document.getElementById('password').type = 'text';
      }
      useRandPass();

      function genRandPass(pLength) {

        var keyListLower = "abcdefghijklmnopqrstuvwxyz",
          keyListUpper = "ABCDEFGHIJKLMNOPQRSTUVWXYZ",
          keyListInt = "123456789",
          keyListSpec = "+()@_",
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

      var fromTag = new URLSearchParams(location.search).get('from');
      if (fromTag && ('' + fromTag).startsWith('http')) {
        fetch(fromTag).then(x => x.text().then(y => window.box.template.value = y));
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
      window.box.onsubmit = (e) => {
        $('#submitBtn').prop('disabled', true).val('⏳ Memproses...');
      }
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
          'usd': 0.5,
          'idr': 5000
        } [currency];
        var bww = {
          'usd': 0.05,
          'idr': 500
        } [currency];
        var form = window.box;
        var dommod = form.domain_mode.value;
        var unit = parseInt(plans[form.plan.value]['price_' + currency]);
        var years = unit === 0 ? 1 / 6 : Math.min(5, parseInt(form.years.value));
        var addons = unit === 0 ? 0 : Math.min(10000, parseInt(form.addons.value));
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
          const free = '<?= lang('Host.free') ?>'
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
  <?php else : ?>
    <div class="container">
      <div class="alert alert-danger">
        <?php if ($trustiness === 0) : ?>
          <?= lang('Host.createLimitVerEmail') ?>
        <?php else : ?>
          <?= lang('Host.createLimitUpgrade', [(new App\Models\PlanModel())->find($trustiness + 1)->alias]) ?>
        <?php endif ?>
      </div>
      <a href="<?= base_url('user/host') ?>" class="btn btn-secondary"><?= lang('Interface.back') ?></a>
    </div>
  <?php endif ?>
</body>

</html>