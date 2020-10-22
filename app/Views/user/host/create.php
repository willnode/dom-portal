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
                    <input class="form-control" id="password" oninput="this.type = 'password'" name="password" type="password" minlength="8" autocomplete="one-time-code" required pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$" oninput="recalculate()">
                    <input type="button" class="btn btn-success" onclick="useRandPass()" value="Random">
                  </div>
                  <small class="form-text text-muted">
                    <?= lang('Interface.passwordNotice') ?>.
                  </small>
                </div>
                <!-- Server -->
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
                <!-- Template -->
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
                    <input type="number" disabled class="form-control" id="addons" name="addons" value="0" min="0" max="10000" step="10" onchange="recalculate()">
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
                      <br><a href="https://panduan.domcloud.id/domain" target="_blank" rel="noopener noreferrer"><?= lang('Interface.learnMore') ?></a>.
                    </small>
                  </div>
                </fieldset>
                <fieldset disabled id="dm-buy" class="d-none">
                  <div class="mb-3">
                    <label class="form-label d-flex align-items-center"><?= lang('Host.findDomain') ?>
                      <button type="button" id="domainBioModalBtn" class="ml-auto btn btn-sm btn-warning" data-toggle="modal" data-target="#domainBioModal">
                        Isi Biodata Domain
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
                <input type="submit" id="submitBtn" value="<?= lang('Host.orderNow') ?>" class="form-control btn-lg btn btn-primary mt-3" disabled>
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
                  <div class="btn btn-block btn-dark text-center" style="background-color: <?= $t->color ?>;" onclick="submitT(this)" data-dismiss="modal" data-template="<?= $t->template ?>">
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
            <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= lang('Interface.back') ?></button>
          </div>
        </div>
      </div>
    </div>


    <!-- Modal Domain Bio -->
    <div class="modal fade" id="domainBioModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <form class="modal-content" name="domainBio">
          <div class="modal-header">
            <h5 class="modal-title">Masukkan Biodata</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <fieldset class="mb-3 card">
              <div class="card-header">
                Biodata Publik / Pemilik Domain
              </div>
              <div class="card-body">
                <div class="mb-1">
                  <label for=""><?= lang('Interface.fullName') ?></label>
                  <div class="row g-1 align-items-center">
                    <div class="col">
                      <input class="form-control" name="domain.bio.fname" required>
                    </div>
                    <div class="col">
                      <input class="form-control" name="domain.bio.lname">
                    </div>
                  </div>
                </div>
                <div class="mb-1">
                  <label for=""><?= lang('Interface.companyName') ?></label>
                  <input class="form-control" name="domain.bio.company" required>
                </div>
                <div class="row g-1 mb-1">
                  <div class="col">
                    <label for="name"><?= lang('Interface.email') ?></label>
                    <input type="email" class="form-control" id="email" name="domain.bio.email" autocomplete="email" required>
                  </div>
                  <div class="col">
                    <label for="name"><?= lang('Interface.phone') ?></label>
                    <div class="input-group">
                      <input class="form-control" name="domain.bio.tel" autocomplete="tel" required>
                    </div>
                  </div>
                </div>
                <div class="mb-1">
                  <label for="">Alamat</label>
                  <div class="row g-1 mb-1">
                    <div class="col">
                      <select name="domain.bio.country" autocomplete="country" class="ccode form-select" required>
                        <option disabled selected><?= lang('Domain.country') ?></option>
                      </select>
                    </div>
                    <div class="col">
                      <input class="form-control" autocomplete="address-level1" name="domain.bio.state" placeholder="<?= lang('Domain.state') ?>" required>
                    </div>
                  </div>
                  <div class="row g-1 mb-1">
                    <div class="col">
                      <input class="form-control" autocomplete="address-level2" name="domain.bio.city" placeholder="<?= lang('Domain.city') ?>" required>
                    </div>
                    <div class="col">
                      <input class="form-control" autocomplete="postal-code" name="domain.bio.postal" placeholder="<?= lang('Domain.zipCode') ?>" required>
                    </div>
                  </div>
                  <input class="form-control mb-1" autocomplete="address-line1" name="domain.bio.address1" placeholder="Address 1" required>
                  <input class="form-control mb-1" autocomplete="address-line2" name="domain.bio.address2" placeholder="Address 2">
                </div>
              </div>
            </fieldset>

            <div class="mb-3">
              <label>
                <input type="checkbox" checked onclick="$('#domainuserform').toggleClass('d-none', event.target.checked).prop('disabled', event.target.checked)"> Domain yang saya pesan merupakan milik sendiri
              </label>
            </div>

            <fieldset disabled id="domainuserform" class="mb-3 d-none card">
              <div class="card-header">
                Biodata Pribadi / Penanggung-jawab Domain
              </div>
              <div class="card-body">
                <div class="mb-1">
                  <label for=""><?= lang('Interface.fullName') ?></label>
                  <div class="row g-1 align-items-center">
                    <div class="col">
                      <input class="form-control" name="domain.user.fname" required>
                    </div>
                    <div class="col">
                      <input class="form-control" name="domain.user.lname">
                    </div>
                  </div>
                </div>
                <div class="mb-1">
                  <label for=""><?= lang('Interface.companyName') ?></label>
                  <input class="form-control" name="domain.user.company" required>
                </div>
                <div class="mb-1">
                  <label for="">Alamat</label>
                  <div class="row g-1 mb-1">
                    <div class="col">
                      <select name="domain.user.country" autocomplete="country" class="ccode form-select" required>
                        <option disabled selected><?= lang('Domain.country') ?></option>
                      </select>
                    </div>
                    <div class="col">
                      <input class="form-control" autocomplete="address-level1" name="domain.user.state" placeholder="<?= lang('Domain.state') ?>" required>
                    </div>
                  </div>
                  <div class="row g-1 mb-1">
                    <div class="col">
                      <input class="form-control" autocomplete="address-level2" name="domain.user.city" placeholder="<?= lang('Domain.city') ?>" required>
                    </div>
                    <div class="col">
                      <input class="form-control" autocomplete="postal-code" name="domain.user.postal" placeholder="<?= lang('Domain.zipCode') ?>" required>
                    </div>
                  </div>
                  <input class="form-control mb-1" autocomplete="address-line1" name="domain.user.address1" placeholder="Address 1" required>
                  <input class="form-control mb-1" autocomplete="address-line2" name="domain.user.address2" placeholder="Address 2">
                </div>
              </div>
            </fieldset>

          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary"><?= lang('Interface.save') ?></button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= lang('Interface.back') ?></button>
          </div>
        </form>
      </div>
    </div>

    <script>
      function submitT(t) {
        fetch($(t).data('template')).then(x => x.text().then(y => window.box.template.value = y));
      }

      function useRandPass() {
        document.getElementById('password').value = genRandPass(12);
        document.getElementById('password').type = 'text';
      }

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
    </script>
    <script id="plans" type="application/json">
      <?= json_encode($plans) ?>
    </script>
    <script id="schemes" type="application/json">
      <?= json_encode($schemes) ?>
    </script>
    <script id="ccodes" type="application/json">
      <?= json_encode($codes) ?>
    </script>
    <script>
      // https://unpkg.com/json-unflat@1.0.1/index.js
      function unflatten(n) {
        var t = {},
          r = function(r) {
            var a, f = r.split(".");
            f.map(function(i, u) {
              0 == u && (a = t), a[i] ? a = a[i] : f.length === u + 1 ? a[i] = n[r] : (a[i] = {}, a = a[i])
            })
          };
        for (var a in n) r(a);
        return t
      }
    </script>
    <script>
      const plans = JSON.parse(document.getElementById('plans').innerHTML).reduce((a, b) => (a[b.id] = b, a), {});
      const schemes = (x => x && x.reduce((a, b) => (a[b.id] = b, a), {}))(JSON.parse(document.getElementById('schemes').innerHTML));
      const ccodes = JSON.parse(document.getElementById('ccodes').innerHTML);
      const currency = '<?= lang('Interface.currency') ?>';
      const digits = '<?= lang('Interface.currency') === 'usd' ? 2 : 0 ?>';
      const formatter = new Intl.NumberFormat('<?= lang('Interface.codeI8LN') ?>', {
        style: 'currency',
        currency: currency,
        maximumFractionDigits: digits,
        minimumFractionDigits: digits,
      });

      window.addEventListener('DOMContentLoaded', (event) => {
        recalculate();
        useRandPass();
        $('.ccode').append(ccodes.map(x => `<option value="${x.country}">${x.name}</option>`).join(''));
        var fromTag = new URLSearchParams(location.search).get('from');
        if (fromTag && ('' + fromTag).startsWith('http')) {
          fetch(fromTag).then(x => x.text()).then(y => window.box.template.value = y);
        }
      });

      window.box.onsubmit = (e) => {
        $('#submitBtn').prop('disabled', true).val('⏳ <?= lang('Interface.processing') ?>...');
      }
      window.domainBio.onsubmit = (e) => {
        $('#domain_bio').val(JSON.stringify(unflatten($(window.domainBio).serializeArray().reduce((m, o) => [m[o.name] = o.value, m][1], {})).domain));
        recalculate();
        $('#domainBioModal').modal('hide');
        $('#domainBioModalBtn').toggleClass('btn-warning', false).toggleClass('btn-outline-primary', true).text('Biodata Terisi ☑');
        e.preventDefault();
        return false;
      }

      let activedomain = null;

      function checkDomain() {
        const name = window.box.domain_name;
        const scheme = window.box.domain_scheme;
        if (name.reportValidity && !name.reportValidity()) {
          return;
        }
        activedomain = null;
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
          'usd': 0.5,
          'idr': 5000
        } [currency];
        var bww = {
          'usd': 0.1,
          'idr': 1000
        } [currency];
        /**@type HTMLFormElement */
        var form = window.box;
        var dommod = form.domain_mode.value;
        var unit = parseInt(plans[form.plan.value]['price_' + currency]);
        var years = unit === 0 ? 1 / 6 : Math.min(5, parseInt(form.years.value));
        var addons = unit === 0 ? 0 : Math.min(10000, parseInt(form.addons.value));
        var exp = new Date(Date.now() + 1000 * 86400 * 365 * years);
        var scheme = 0;

        // Alter UI
        if (unit == 0) {
          form.free_cname.value = form.username.value + '.dom.my.id';
          dommod = form.domain_mode.value = 'free';
        }

        $('#dm-free').toggleClass('d-none', dommod !== 'free').prop('disabled', dommod !== 'free');
        $('#dm-buy').toggleClass('d-none', dommod !== 'buy').prop('disabled', dommod !== 'buy');
        $('#dm-custom').toggleClass('d-none', dommod !== 'custom').prop('disabled', dommod !== 'custom');

        if (dommod === 'buy') {
          var schdata = schemes[form.domain_scheme.value];
          $('#domain_available').val(activedomain && activedomain.status === 'available' && (
            activedomain.domain === window.box.domain_name.value + schdata.alias) ? '1' : '');
          scheme = schdata[`price_${currency}`] + schdata[`renew_${currency}`] * (years - 1);
        }

        $('#domain_mode,#years,#addons').prop('disabled', unit == 0);
        $('#submitBtn').prop('disabled', !form.checkValidity());

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