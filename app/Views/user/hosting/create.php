<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>
  <div class="container">
    <h1>Buat Hosting</h1>
    <?= $validation ? $validation->listErrors() : '' ?>
    <form method="POST" name="upgrade">
      <div class="row">
        <div class="col-lg-5">
          <label class="card">
            <div class="card-body">
              <h3 class="card-title">Data Hosting</h3>
              <div class="form-group">
                <label for="username">ID Hosting</label>
                <input class="form-control" id="username" minlength="5" maxlength="32" name="username" placeholder="hanya alfanumerik (cth. 'tokoku')" oninput="recalculate()" required>
              </div>
              <div class="form-group">
                <label for="password">Password Admin Hosting</label>
                <input class="form-control" id="password" minlength="8" name="password" required>
              </div>
              <div class="form-group">
                <label for="slave">Server Slave</label>
                <select class="form-control" id="slave" name="slave" required>
                  <?php foreach ($slaves as $slave) : ?>
                    <label class="form-check">
                      <option value="<?= $slave->slave_id ?>"><?= $slave->slave_alias . " (Penggunaan: " . ($slave->utilization * 100) . "%)" ?></option>
                    </label>
                  <?php endforeach ?>
                </select>
              </div>
              <div class="form-group">
                <label for="username">Domain Hosting</label>
                <input class="form-control" id="cname" name="cname" value="dom.my.id" disabled oninput="recalculate()" required placeholder="masukkan domain kustom (cth. 'tokoku.my.id')" pattern="^[a-zA-Z0-9][a-zA-Z0-9_.-]{1,61}[a-zA-Z0-9]\.[a-zA-Z]{2,}$">
              </div>
              <div class="form-group">
                <label for="template">Pilih Template</label>
                <select name="template" class="form-control">
                  <option>Kosong</option>
                  <option value="wordpress">WordPress</option>
                  <option value="phpbb">phpBB</option>
                  <option value="opencart">OpenCart</option>
                </select>
              </div>
            </div>
          </label>
        </div>
        <div class="col-lg-3">
          <div class="card">
            <div class="card-body">
              <h2 class="card-title">Paket Hosting</h2>
              <p>Pilih jenis paket</p>
              <?php foreach ($plans as $plan) : ?>
                <label class="form-check">
                  <input class="form-check-input" type="radio" name="plan" value="<?= $plan->plan_id ?>" onchange="recalculate()" required>
                  <?= ucfirst($plan->plan_alias) ?>
                </label>
              <?php endforeach ?>
              <p>(<a href="https://dom.my.id/price" target="_blank">Lihat perbandingan paket</a>)</p>
              <p>Pilih jangka tahun</p>
              <input type="number" class="form-control" name="years" value="1" min="1" max="5" onchange="recalculate()">
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card">
            <div class="card-body">
              <div class="d-flex">
                <h6>Harga Paket</h6>
                <div class="ml-auto" id="outprice">-</div>
              </div>
              <div class="d-flex">
                <h6>Masa Hosting</h6>
                <div class="ml-auto" id="outyear">-</div>
              </div>
              <hr>
              <div class="d-flex">
                <h6>Biaya Hosting</h6>
                <div class="ml-auto" id="outtotal">-</div>
              </div>
              <div class="d-flex">
                <h6>Biaya Transaksi</h6>
                <div class="ml-auto" id="outtip">-</div>
              </div>
              <hr>
              <div class="d-flex">
                <h6>Total Pembayaran</h6>
                <div class="ml-auto" id="outbill">-</div>
              </div>
              <div class="d-flex">
                <h6>Tanggal Kadaluarsa</h6>
                <div class="ml-auto" id="outexp">-</div>
              </div>
              <input type="submit" value="Bayar" class="btn btn-primary mt-3">
            </div>
          </div>
        </div>
      </div>

    </form>
  </div>

  <script id="plans" type="application/json">
    <?= json_encode($plans) ?>
  </script>
  <script>
    var plans = JSON.parse(document.getElementById('plans').innerHTML);
    plans = plans.reduce((a, b) => (a[b.plan_id] = b, a), {});

    function recalculate() {
      var plan = window.upgrade.plan.value || 1;
      var years = parseInt(window.upgrade.years.value);
      if (plan) {
        var unit = parseInt(plans[plan].plan_price);
        window.upgrade.cname.disabled = unit == 0;
        window.upgrade.years.disabled = unit == 0;
        if (window.upgrade.cname.disabled) {
          window.upgrade.cname.value = window.upgrade.username.value + '.dom.my.id';
        } else if (window.upgrade.cname.value.endsWith('dom.my.id')) {
          window.upgrade.cname.value = '';
        }
        unit *= 10000;
        if (unit == 0) years = 0.25;
        var formatter = new Intl.NumberFormat('id-ID', {
          style: 'currency',
          currency: 'IDR',
          maximumFractionDigits: 0,
          minimumFractionDigits: 0,
        });
        var tip = 5000;
        var exp = new Date(Date.now() + 1000 * 86400 * 365 * years);
        $('#outprice').text(unit == 0 ? 'Gratis' : formatter.format(unit));
        $('#outyear').html(unit == 0 ? '3 Bulan' : '&times; ' + years + ' Tahun');
        $('#outtotal').text(unit == 0 ? 'Gratis' : formatter.format(unit * years));
        $('#outtip').text(unit == 0 ? '-' : formatter.format(tip));
        $('#outbill').text(unit == 0 ? 'Gratis' : formatter.format(unit * years + tip));
        $('#outexp').text(exp.toISOString().substr(0, 10));
      }
    }
  </script>
</body>

</html>