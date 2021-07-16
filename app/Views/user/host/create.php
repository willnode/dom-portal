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
        <?= csrf_field() ?>
        <div class="row">
          <div class="col-lg-4">
            <div class="card my-2">
              <div class="card-body">
                <!-- Username -->
                <div class="mb-3">
                  <label class="form-label" for="username"><?= lang('Host.portalUsername') ?></label>
                  <input class="form-control" id="username" minlength="5" maxlength="32" name="username" placeholder="<?= lang('Host.portalUsernameHint') ?>" pattern="^[a-zA-Z][-a-zA-Z0-9]+$" oninput="recalculate()" required autofocus autocomplete="off">
                </div>
                <!-- Password -->
                <div class="mb-3">
                  <label class="form-label" for="name"><?= lang('Host.portalPassword') ?></label>
                  <div class="input-group">
                    <input class="form-control" id="password" oninput="this.type = 'password'; recalculate()" name="password" type="password" minlength="8" autocomplete="one-time-code" required pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$">
                    <input type="button" class="btn btn-success" onclick="generateRandomPassword(); recalculate()" value="Random">
                  </div>
                  <small class="form-text text-muted">
                    <?= lang('Interface.passwordNotice') ?>.
                  </small>
                </div>
                <!-- Server -->
                <div class="mb-3">
                  <label class="form-label" for="server"><?= lang('Host.slaveServer') ?></label>
                  <select class="form-select" id="server" name="server" required onchange="recalculate()">
                    <?php foreach ($servers as $server) : ?>
                      <label class="form-check">
                        <option value="<?= $server->id ?>" <?= $server->lang == lang('Interface.code') ? 'selected' : '' ?> data-domain="<?= $server->domain ?>"><?= $server->alias ?> &mdash; <?= $server->description ?></option>
                      </label>
                    <?php endforeach ?>
                  </select>
                </div>
                <!-- Template -->
                <div class="mb-3">
                  <div class="d-flex mb-2">
                    <label class="form-label" for="template"><?= lang('Host.template') ?></label>
                    <button type="button" class="ms-auto btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#templateModal">
                      <?= lang('Host.chooseTemplate') ?>
                    </button>
                  </div>
                  <textarea name="template" class="form-control font-monospace text-nowrap" placeholder="Config File" rows=7 data-enable-grammarly="false"><?= "features: ['mysql', 'ssl']\nnginx:\n  ssl: on" ?></textarea>
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
                    <input type="number" disabled class="form-control" id="addons" name="addons" value="0" min="0" max="10000" step="10" onchange="recalculate()">
                  </div>
                </div>
                <p>
                  <small class="form-text text-muted">
                    <a href="<?= lang('Interface.lang') == 'id' ? 'https://domcloud.id/price' : 'https://domcloud.io/price' ?>" target="_blank"><?= lang('Host.lookPacketDiff') ?></a>.
                  </small>
                </p>
                <h3 class="card-title"><?= lang('Interface.domain') ?></h3>
                <div class="mb-3">
                  <label class="form-label" for="domain_mode"><?= lang('Host.selectDomainKind') ?></label>
                  <select id="domain_mode" disabled class="form-select" onchange="recalculate()" required>
                    <option value="free" selected><?= lang('Host.useFreeDomain') ?></option>
                    <option value="buy"><?= lang('Host.buyNewDomain') ?></option>
                    <option value="custom"><?= lang('Host.useExistingDomain') ?></option>
                  </select>
                </div>
                <fieldset id="dm-free">
                  <div class="mb-3">
                    <input class="form-control" id="free_cname" value="" disabled>
                    <small class="form-text text-muted">
                      <?= lang('Host.freeDomainHint') ?>
                      <br><a href="https://github.com/domcloud/domcloud-io#how-to-point-a-custom-domain-here" target="_blank" rel="noopener noreferrer"><?= lang('Interface.learnMore') ?></a>.
                    </small>
                  </div>
                </fieldset>
                <fieldset disabled id="dm-buy" class="d-none">
                  <div class="mb-3">
                    <label class="form-label d-flex align-items-center"><?= lang('Host.findDomain') ?>
                      <button type="button" id="domainBioModalBtn" class="ms-auto btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#domainBioModal">
                        <?= lang('Domain.fillBiodata') ?>
                      </button>
                    </label>
                    <div class="input-group">
                      <input id="domain_bio" hidden name="domain[bio]" required>
                      <input id="domain_available" hidden <?= ENVIRONMENT === 'production' ? 'required' : '' ?>>
                      <input id="domain_name" name="domain[name]" class="form-control" pattern="^[-a-zA-Z0-9]+$" required oninput="recalculate()">
                      <select class="form-select" name="domain[scheme]" id="domain_scheme" required style="max-width: 120px" onchange="recalculate()">
                        <?php foreach ($schemes as $s) : if ($s->price_local) : ?>
                            <option value="<?= $s->id ?>"><?= $s->alias ?></option>
                        <?php endif;
                        endforeach; ?>
                      </select>
                      <input onclick="checkDomain()" type="button" value="<?= lang('Domain.check') ?>" class="btn btn-primary">
                    </div>
                    <small class="form-text text-muted">
                      <a href="<?= lang('Host.findAvailableGLTDsLink') ?>" target="_blank"><?= lang('Host.findAvailableGLTDs') ?></a>.
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
                </fieldset>
                <fieldset disabled id="dm-custom" class="d-none">
                  <div class="mb-3">
                    <input class="form-control" id="custom_cname" name="domain[custom]" oninput="recalculate()" required placeholder="<?= lang('Host.enterCustomDomain') ?>" pattern="^[a-zA-Z0-9][a-zA-Z0-9.-]{1,61}[a-zA-Z0-9]\.[a-zA-Z]{2,}$">
                    <small class="form-text text-muted">
                      <?= lang('Host.useExistingHint') ?>
                      <br><a href="https://panduan.domcloud.id/domain" target="_blank" rel="noopener noreferrer"><?= lang('Interface.learnMore') ?></a>.
                    </small>
                  </div>
                </fieldset>
              </div>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="card my-2">
              <div class="card-body">
                <div class="d-flex">
                  <h6><?= lang('Host.schemePrice') ?></h6>
                  <div class="ms-auto" id="outprice">-</div>
                </div>
                <div class="d-flex">
                  <h6><?= lang('Host.domainPrice') ?></h6>
                  <div class="ms-auto" id="outdomain">-</div>
                </div>
                <div class="d-flex">
                  <h6><?= lang('Host.addonsPrice') ?></h6>
                  <div class="ms-auto" id="outaddons">-</div>
                </div>
                <div class="d-flex">
                  <h6><?= lang('Host.transactionCost') ?></h6>
                  <div class="ms-auto" id="outtip">-</div>
                </div>
                <hr>
                <div class="d-flex">
                  <h6><?= lang('Host.totalPayment') ?></h6>
                  <div class="ms-auto" id="outbill">-</div>
                </div>
                <div class="d-flex">
                  <h6><?= lang('Host.expirationDate') ?></h6>
                  <div class="ms-auto" id="outexp">-</div>
                </div>
                <hr>
                <div class="row">
                  <div class="col-12">
                    <?php if ($coupon) : ?>
                      <div class="alert alert-primary">
                        <?php /** @var \App\Entities\HostCoupon $coupon */ ?>
                        Selamat, kupon <b><?= $coupon->code ?></b> dapat diaplikasikan!<br>
                        <small>Kupon berlaku sampai <b><?= $coupon->expiry_at->toDateString() ?></b>.</small>
                      </div>
                    <?php endif ?>
                  </div>
                  <div class="col">
                    <h6>Disk Space</h6>
                    <div class="ms-auto" id="specdisk">- MiB</div>
                  </div>
                  <div class="col">
                    <h6>Data Transfer</h6>
                    <div class="ms-auto" id="specbwt">- GiB</div>
                  </div>
                  <div class="col">
                    <h6>Add-ons</h6>
                    <div class="ms-auto" id="specbwb">- GiB</div>
                  </div>
                </div>
                <button type="submit" id="submitBtn" class="form-control btn-lg btn btn-outline-warning mt-3">
                  <i class="fas fa-shopping-cart me-2"></i> <?= lang('Host.orderNow') ?>
                </button>
              </div>
            </div>
            <p class="text-end d-none" id="wrong-notice">
              <i>Wrong currency? <a href="/user/profile">Change your language.</a></i>
            </p>
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
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row g-2" style="color: white; fill: white">
              <?php foreach ($templates as $t) : ?>
                <div class="col-md-6 my-1">
                  <div class="btn w-100 btn-dark text-center" style="background-color: <?= $t->color ?>;" onclick="submitTemplate(this)" data-bs-dismiss="modal" data-template="<?= $t->template ?>">
                    <div class="w-50 mx-auto my-2"><?= $t->logo ?></div>
                    <p class="mb-0"><?= $t->name ?></p>
                  </div>
                </div>
              <?php endforeach ?>
              <div class="col-12 my-1">
                <a href="https://github.com/domcloud/dom-templates/">More templates</a>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= lang('Interface.back') ?></button>
          </div>
        </div>
      </div>
    </div>

    <?= view('user/modals/domainbio') ?>

    <script>
      function submitTemplate(t) {
        fetch($(t).data('template')).then(x => x.text().then(y => window.box.template.value = y));
      }

      function generateRandomPassword(id) {
        id = id || "password";
        document.getElementById(id).value = randomPassword(12);
        document.getElementById(id).type = "text";
      }

      function randomPassword(r) {
        var s = "+()@_",
          n = "",
          h = Math.ceil(r / 3) - 1;
        for (i = 0; i < h; i++)["abcdefghijklmnopqrstuvwxyz", "ABCDEFGHIJKLMNOPQRSTUVWXYZ",
          "123456789"].forEach(r => n += r[Math.trunc(Math.random() * r.length)]);
        for (i = r - 3 * h; i-- > 0;) n += s[Math.floor(Math.random() * s.length)];
        return n.split("").sort(() => .5 - Math.random()).join("")
      }
    </script>
    <script id="plans" type="application/json">
      <?= json_encode($plans) ?>
    </script>
    <script id="schemes" type="application/json">
      <?= json_encode($schemes) ?>
    </script>
    <script>
      let plans, schemes, activedomain = null;
      const currency = '<?= lang('Interface.currency') ?>';
      const digits = '<?= lang('Interface.currency') === 'usd' ? 2 : 0 ?>';
      const coupon = JSON.parse('<?= json_encode($coupon) ?>');
      const formatter = new Intl.NumberFormat('<?= lang('Interface.codeI8LN') ?>', {
        style: 'currency',
        currency: currency,
        maximumFractionDigits: digits,
        minimumFractionDigits: digits,
      });

      window.addEventListener('DOMContentLoaded', (event) => {
        plans = JSON.parse($('#plans').html()).reduce((a, b) => (a[b.id] = b, a), {});
        schemes = JSON.parse($('#schemes').html()).reduce((a, b) => (a[b.id] = b, a), {});
        generateRandomPassword();
        recalculate();
        var fromTag = new URLSearchParams(location.search).get('from');
        if (fromTag && ('' + fromTag).startsWith('http')) {
          fetch(fromTag).then(x => x.text()).then(y => window.box.template.value = y);
        }
      });

      window.box.onsubmit = (e) => {
        $('#submitBtn').prop('disabled', true).val('⏳ <?= lang('Interface.processing') ?>...');
      }

      function checkDomain() {
        const name = window.box.domain_name;
        const scheme = window.box.domain_scheme;
        if (name.reportValidity && !name.reportValidity()) {
          return;
        }
        activedomain = null;
        name.value = String(name.value).toLowerCase();
        $('#buy-status-available,#buy-status-error,#buy-status-prompt').toggleClass('d-none', true);
        $('#buy-status-loading').toggleClass('d-none', false);
        recalculate();

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
          'usd': 0.3,
          'idr': 5000
        } [currency];
        var bww = {
          'usd': 0.1,
          'idr': 1000
        } [currency];

        var form = window.box;
        var dommod = form.domain_mode.value;
        var plan = plans[form.plan.value];
        var unit = parseInt(plan[`price_${currency}`]);
        var years = Math.min(5, parseInt(form.years.value));
        var addons = Math.min(10000, parseInt(form.addons.value));
        var exp = new Date(Date.now() + 1000 * 86400 * 365 * years);
        var scheme = 0;

        if (coupon instanceof Object) {
          if (unit == 0) {
            form.plan.value = coupon.default_plan_id;
            plan = plans[form.plan.value];
            unit = plan[`price_${currency}`];
          }
          tip = Math.max(coupon.min, Math.min(coupon.max, coupon.discount * unit));
          tip = -Math.min(unit, tip);
        }

        // Alter for free
        if (unit == 0) {
          exp = new Date(Date.now() + 1000 * 86400 * 60);
          dommod = form.domain_mode.value = 'free';
          addons = 0;
          years = 1 / 6;
        }


        $('#dm-free').toggleClass('d-none', dommod !== 'free')
          .prop('disabled', dommod !== 'free');
        $('#dm-buy').toggleClass('d-none', dommod !== 'buy')
          .prop('disabled', dommod !== 'buy');
        $('#dm-custom').toggleClass('d-none', dommod !== 'custom')
          .prop('disabled', dommod !== 'custom');

        if (dommod === 'buy') {
          var schdata = schemes[form.domain_scheme.value];
          $('#domain_available').val(activedomain && activedomain.status === 'available' && (
            activedomain.domain === window.box.domain_name.value + schdata.alias) ? '1' : '');
          scheme = schdata[`price_${currency}`] + schdata[`renew_${currency}`] * (years - 1);
        } else if (dommod == 'free') {
          form.free_cname.value = form.username.value + $('#server :selected').data('domain');
        }

        if (currency === 'usd') {
          var value = unit * years + addons * bww + scheme;
          tip = Math.round((value + tip) / (1 - 0.044) * 100) / 100 - value // paypal fee
        }


        $('#domain_mode,#years,#addons').prop('disabled', unit == 0);
        var valid = form.checkValidity();
        $('#submitBtn')
          .toggleClass('btn-outline-warning', !valid)
          .toggleClass('btn-primary', valid);

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
        $('#wrong-notice').toggleClass('d-none', unit == 0);
        $('#specdisk').text(plan['disk'] + ' MiB');
        $('#specbwt').text(plan['net'] * Math.max(years, 1) + ' GiB');
        $('#specbwb').text(plan['net'] / 4 * Math.floor(years) + addons + ' GiB');
        $('#outexp').text(exp.toISOString().substr(0, 10));
      }
    </script>
  <?php else : ?>
    <div class="container">
      <div class="alert alert-danger">
        <?php if ($trustiness === 0) : ?>
          <?= lang('Host.createLimitVerEmail') ?>
          <br>
          <a href="/user/profile">Belum menerima konfirmasi email?</a>
        <?php else : ?>
          <?= lang('Host.createLimitUpgrade', [(new App\Models\PlanModel())->find($trustiness + 1)->alias]) ?>
        <?php endif ?>
      </div>
      <a href="<?= base_url('user/host') ?>" class="btn btn-secondary"><?= lang('Interface.back') ?></a>
    </div>
  <?php endif ?>
</body>

</html>