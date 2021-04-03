<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>
  <div class="container">
    <h1>Order Domain</h1>
    <?= isset($validation) ? $validation->listErrors() : '' ?>
    <form method="POST" name="box">
      <?= csrf_field() ?>
      <div class="row">
        <div class="col-lg-6">
          <div class="card">
            <div class="card-body">
              <h3 class="card-title">Data Domain</h3>
              <div class="mb-3">
                <label class="form-label d-flex align-items-center"><?= lang('Host.findDomain') ?>
                  <button type="button" id="domainBioModalBtn" class="ms-auto btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#domainBioModal">
                    <?= lang('Domain.fillBiodata') ?>
                  </button>
                </label>
                <div class="input-group">
                  <input id="domain_bio" hidden name="domain[bio]" required>
                  <input id="domain_available" hidden <?= ENVIRONMENT === 'production' ? 'required' : '' ?>>
                  <input name="domain[name]" id="domain_name" class="form-control" pattern="^[-\w]+$" required oninput="recalculate()">
                  <select class="form-select" name="domain[scheme]" id="domain_scheme" required style="max-width: 120px" onchange="recalculate()">
                    <?php foreach ($schemes as $s) : if ($s->price_local !== 0) : ?>
                        <option value="<?= $s->id ?>"><?= $s->alias ?></option>
                    <?php endif;
                    endforeach; ?>
                  </select>
                  <input onclick="checkDomain()" type="button" value="<?= lang('Domain.check') ?>" class="btn btn-primary">
                </div>
              </div>
              <div class="mb-3">
                <label><?= lang('Host.yearDuration') ?></label>
                <div class="input-group">
                  <input name="years" class="form-control" type="number" min="1" max="5" value="1" required onchange="recalculate()">
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card">
            <div class="card-body">
              <div class="d-flex">
                <h5 id="domainname"></h5>
                <div class="ms-auto" id="outstat">-</div>
              </div>
              <div class="d-flex">
                <h6><?= lang('Host.domainPrice') ?></h6>
                <div class="ms-auto" id="outprice">-</div>
              </div>
              <div class="d-flex">
                <h6><?= lang('Host.transactionCost') ?></h6>
                <div class="ms-auto" id="outtip">-</div>
              </div>
              <hr>
              <div class="d-flex">
                <h6><?= lang('Host.totalPayment') ?></h6>
                <div class="ms-auto" id="outtotal">-</div>
              </div>
              <div class="d-flex">
                <h6><?= lang('Host.expirationDate') ?></h6>
                <div class="ms-auto" id="outexp">-</div>
              </div>
              <p><i><small>Perlu diingat anda hanya mendaftarkan domain. Apabila anda ingin mendaftarkan domain sekaligus hosting, anda dapat <a href="/user/host/create">melakukannya disini</a> </small></i></p>
              <input type="submit" id="submitBtn" class="btn btn-primary btn-block" value="<?= lang('Host.orderNow') ?>">
            </div>
          </div>
        </div>
      </div>

    </form>
  </div>

  <?= view('user/modals/domainbio') ?>

  <script id="schemes" type="application/json">
    <?= json_encode($schemes) ?>
  </script>
  <script>
    let schemes, activedomain = null;
    const currency = '<?= lang('Interface.currency') ?>';
    const digits = '<?= lang('Interface.currency') === 'usd' ? 2 : 0 ?>';
    const formatter = new Intl.NumberFormat('<?= lang('Interface.codeI8LN') ?>', {
      style: 'currency',
      currency: currency,
      maximumFractionDigits: digits,
      minimumFractionDigits: digits,
    });

    window.addEventListener('DOMContentLoaded', (event) => {
      schemes = JSON.parse($('#schemes').html()).reduce((a, b) => (a[b.id] = b, a), {});
      recalculate();
    });

    function checkDomain() {
      var name = window.box.domain_name;
      var scheme = window.box.domain_scheme;
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
      var tip = {
        'usd': 0.5,
        'idr': 5000
      } [currency];
      var form = window.box;

      var schdata = schemes[form.domain_scheme.value];
      $('#domain_available').val(activedomain && activedomain.status === 'available' && (
        activedomain.domain === form.domain_name.value + schdata.alias) ? '1' : '');
      var years = form.years.value;

      var exp = new Date(Date.now() + 1000 * 86400 * 365 * years);
      var price = schdata[`price_${currency}`] + schdata[`renew_${currency}`] * (years - 1);

      $('#domainname').text(activedomain && activedomain.domain);
      $('#outstat').text(activedomain && activedomain.status);
      $('#outprice').text(formatter.format(price));
      $('#outtip').text(formatter.format(tip));
      $('#outtotal').text(formatter.format(tip + price));
      $('#outexp').text(exp.toISOString().substr(0, 10));


      var valid = form.checkValidity();
      $('#submitBtn')
        .toggleClass('btn-outline-warning', !valid)
        .toggleClass('btn-primary', valid);
    }
  </script>
</body>

</html>