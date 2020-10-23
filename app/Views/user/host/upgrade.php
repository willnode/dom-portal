<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>

  <div class="container">
    <h1><?= lang('Host.upgradeHost') ?></h1>
    <?php if ($purchase && $purchase->status === 'pending') : ?>
      <div class="alert alert-danger">
        Anda tidak dapat melakukan upgrade apabila masih ada transaksi belum selesai.
      </div>
    <?php else : ?>
      <form name="box" class="row" method="POST">
        <?= csrf_field() ?>
        <div class="col-lg-4">
          <div class="card">
            <div class="card-body">
              <p><?= lang('Host.chooseUpgradeMethod') ?></p>
              <div class="radio">
                <label>
                  <div>
                    <input type="radio" name="mode" value="new" onchange="syncPlans()" required class="mr-2" <?= $purchase && (($data->notification & 1) != 1) ? 'disabled' : 'checked' ?>>
                    <b><?= lang('Interface.renew') ?></b>
                  </div>
                  <div class="pl-4">
                    <?= lang('Host.renewHint') ?>
                  </div>
                </label>
                <label>
                  <div>
                    <input type="radio" name="mode" value="extend" onchange="syncPlans()" class="mr-2" <?= !$purchase ? 'disabled' : '' ?>>
                    <b><?= lang('Interface.extend') ?></b>
                  </div>
                  <div class="pl-4">
                    <?= lang('Host.extendHint') ?>
                  </div>

                </label>
                <label>
                  <div>
                    <input type="radio" name="mode" value="upgrade" onchange="syncPlans()" class="mr-2" <?= !$purchase ? 'disabled' : '' ?>>
                    <b><?= lang('Interface.upgrade') ?></b>
                  </div>
                  <div class="pl-4">
                    <?= lang('Host.upgradeHint') ?>
                  </div>
                </label>
                <label>
                  <div>
                    <input type="radio" name="mode" value="topup" onchange="syncPlans()" class="mr-2" <?= !$purchase ? 'disabled' : '' ?>>
                    <b><?= lang('Interface.topup') ?></b>
                  </div>
                  <div class="pl-4">
                    <?= lang('Host.topupHint') ?>
                  </div>
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card">
            <div class="card-body">
              <div><?= lang('Host.selectPacketType') ?></div>
              <div class="row mb-3">
                <?php foreach ($plans as $plan) : ?>
                  <div class="col-4">
                    <label>
                      <input type="radio" <?= $plan->id === 1 && $purchase ? 'disabled' : '' ?> name="plan" id="plan<?= $plan->id ?>" class="mr-2" value="<?= $plan->id ?>" required onchange="recalculate()">
                      <?= $plan->alias ?>
                    </label>
                  </div>
                <?php endforeach ?>
                <small class="col-12 form-text text-muted">
                  <a href="https://domcloud.id/price" target="_blank"><?= lang('Host.lookPacketDiff') ?></a>.
                </small>
              </div>
              <div class="row align-items-center">
                <label class="form-label col-6" for="years"><?= lang('Host.yearDuration') ?></label>
                <div class="col-6">
                  <input type="number" class="form-control my-1" name="years" id="years" value="1" min="1" max="5" onchange="recalculate()">
                </div>
                <label class="form-label col-6" for="addons">Data Add-ons (GB)</label>
                <div class="col-6">
                  <input type="number" class="form-control my-1" name="addons" id="addons" value="0" min="0" max="10000" step="10" onchange="recalculate()">
                </div>
              </div>
              <div id="domain-renew" class="d-none">
                <?php if ($data->scheme_id == 1 || $data->scheme_id === null) : ?>
                  <div class="mb-3">
                    <label class="form-label" for="domain_mode"><?= lang('Host.selectDomainKind') ?></label>
                    <select id="domain_mode" class="form-select" onchange="recalculate()">
                      <option value="free" selected><?= lang('Host.useFreeDomain') ?></option>
                      <option value="buy"><?= lang('Host.buyNewDomain') ?></option>
                      <option value="custom"><?= lang('Host.useExistingDomain') ?></option>
                    </select>
                  </div>
                  <fieldset id="dm-free">
                    <div class="mb-3">
                      <input class="form-control" id="free_cname" value="<?= $data->username ?>.dom.my.id" disabled>
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
                <?php endif ?>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card">
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
              <input type="submit" value="<?= lang('Host.orderNow') ?>" id="submitBtn" class="form-control btn-lg btn btn-primary mt-3">
            </div>
          </div>
        </div>
      </form>
    <?php endif ?>
    <a href="/user/host/detail/<?= $data->id ?>" class="mt-3 btn btn-secondary"><?= lang('Interface.back') ?></a>

  </div>

  <?= view('user/modals/domainbio') ?>

  <script id="plans" type="application/json">
    <?= json_encode($plans) ?>
  </script>
  <script id="schemes" type="application/json">
    <?= json_encode($schemes) ?>
  </script>
  <script>
    const currency = '<?= lang('Interface.currency') ?>';
    const digits = '<?= lang('Interface.currency') === 'usd' ? 2 : 0 ?>';
    const formatter = new Intl.NumberFormat('<?= lang('Interface.codeI8LN') ?>', {
      style: 'currency',
      currency: currency,
      maximumFractionDigits: digits,
      minimumFractionDigits: digits,
    });

    let plans, schemes, activedomain = null;

    window.addEventListener('DOMContentLoaded', (event) => {
      plans = JSON.parse($('#plans').html()).reduce((a, b) => (a[b.id] = b, a), {});
      schemes = JSON.parse($('#schemes').html()).reduce((a, b) => (a[b.id] = b, a), {});
      recalculate();
    });

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

    function syncPlans() {
      const mode = window.box.mode.value;
      const purchase = parseInt("<?= $purchase->metadata->plan ?? 1 ?>");
      if (mode === 'new') {
        window.box.plan.forEach(x => {
          x.disabled = (purchase != 0) && (x.value == 1);
        })
      } else if (mode === 'extend') {
        window.box.plan.forEach(x => {
          x.disabled = x.value != purchase;
          x.checked = x.value == purchase;
        })
      } else if (mode === 'upgrade') {
        window.box.plan.forEach(x => {
          x.disabled = x.value <= purchase;
          x.checked = x.value == purchase + 1;
        })
      } else if (mode === 'topup') {
        window.box.plan.forEach(x => {
          x.disabled = true;
          x.checked = false;
        })
      }
      recalculate();
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
      var form = window.box;
      var mode = form.mode.value;
      var plan = form.plan.value;
      var years = Math.min(5, parseInt(form.years.value));
      var addons = Math.min(10000, parseInt(form.addons.value));
      var oldyr = parseInt('<?= $purchase->metadata->years ?? 0 ?>');
      var oldplan = parseInt('<?= $data->plan_id ?>');
      var oldval = parseInt('<?= $data->plan->{'price_' . lang('Interface.currency')} ?? 0 ?>');
      var oldexp = new Date('<?= $data->expiry_at ?>');
      var oldadd = Math.floor(parseInt('<?= $data->addons ?>') / 1024 * 10) / 10;
      if (mode && (plan || mode == 'topup')) {
        var scheme = 0;

        // Domain Calc
        const includeDomain = form.domain_mode && mode === 'new' && plan != 1
        $('#domain-renew').toggleClass('d-none', !includeDomain);
        var dommod = includeDomain ? form.domain_mode.value : 'none';

        $('#dm-free').toggleClass('d-none', dommod !== 'free')
          .prop('disabled', dommod !== 'free');
        $('#dm-buy').toggleClass('d-none', dommod !== 'buy')
          .prop('disabled', dommod !== 'buy');
        $('#dm-custom').toggleClass('d-none', dommod !== 'custom')
          .prop('disabled', dommod !== 'custom');

        if (dommod === 'buy') {
          var schdata = schemes[form.domain_scheme.value];
          $('#domain_available').val(activedomain && activedomain.status === 'available' && (
            activedomain.domain === form.domain_name.value + schdata.alias) ? '1' : '');
          scheme = schdata[`price_${currency}`] + schdata[`renew_${currency}`] * (years - 1);
        }

        var unit = mode == 'topup' ? 0 : plans[plan]['price_' + currency];
        form.years.disabled = unit == 0 || mode == 'upgrade';
        form.addons.disabled = unit == 0 && mode != 'topup';
        if (unit == 0) years = 1 / 6;
        else if (mode === 'upgrade') form.years.value = years = oldyr;


        var cashback = mode === 'upgrade' ? oldval * oldyr : 0;
        var exp = mode === 'new' ? new Date(Date.now() + 1000 * 86400 * 365 * years) : (
          mode === 'extend' ? new Date(Number(oldexp) + 1000 * 86400 * 365 * years) : oldexp);

        // Show values
        if (unit == 0 && mode !== 'topup') {
          const free = '<?= lang('Host.free') ?>'
          $('#outprice').text('-');
          $('#outdomain').text('-');
          $('#outaddons').html('-');
          $('#outtip').text('-');
          $('#outbill').text(free);
        } else {
          $('#outprice').text(formatter.format(unit * years - cashback));
          $('#outdomain').text(formatter.format(scheme));
          $('#outaddons').html(formatter.format(addons * bww));
          $('#outtip').text(formatter.format(tip));
          $('#outbill').text(formatter.format(unit * years - cashback + addons * bww + scheme + tip));
        }
        var curplan = plans[plan ? plan : oldplan];
        $('#specdisk').text(curplan['disk'] + ' MiB');
        $('#specbwt').text(curplan['net'] * Math.max(years, 1) + ' GiB');
        $('#specbwb').text(curplan['net'] / 12 * Math.floor(years) + addons + oldadd + ' GiB');

        $('#outexp').text(exp.toISOString().substr(0, 10));
      }
      var valid = form.checkValidity();
      $('#submitBtn')
        .toggleClass('btn-outline-warning', !valid)
        .toggleClass('btn-primary', valid);
    }
  </script>
</body>

</html>