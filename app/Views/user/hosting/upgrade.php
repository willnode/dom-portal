<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>

  <div class="container">
    <h1>Upgrade Hosting</h1>
    <?php if ($data->purchase_status === 'pending') : ?>
      <div class="alert alert-danger">
        Anda tidak dapat melakukan upgrade apabila masih ada transaksi belum selesai.
      </div>
    <?php else : ?>
      <form name="upgrade" class="row" method="POST">
        <div class="col-lg-5">
          <div class="card">
            <div class="card-body">
              <p>Pilih metode upgrade</p>
              <div class="radio">
                <label>
                  <div>
                    <input type="radio" name="mode" value="new" onchange="syncPlans()" required class="mr-2" <?= $data->plan_price != 0 ? 'disabled' : 'checked' ?>>
                    <b>Beli Baru</b>
                  </div>
                  <div class="pl-4">
                    Metode ini mengganti pembelian paket sebelumnya.
                    Anda bebas memilih jenis paket yang ingin diterapkan.
                  </div>
                </label>
                <label>
                  <div>
                    <input type="radio" name="mode" value="extend" onchange="syncPlans()" class="mr-2" <?= $data->plan_price == 0 ? 'disabled' : '' ?>>
                    <b>Perpanjang</b>
                  </div>
                  <div class="pl-4">
                    Metode ini memperpanjang jangka waktu hosting.
                    Anda hanya bisa memperpanjang jangka waktu dalam jenis paket yang sama.
                    <!--div class="form-check">
                      <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
                      <label class="form-check-label" for="flexCheckDefault">
                        Juga perpanjang domain
                      </label>
                    </div-->
                  </div>

                </label>
                <label>
                  <div>
                    <input type="radio" name="mode" value="upgrade" onchange="syncPlans()" class="mr-2" <?= $data->plan_price == 0 ? 'disabled' : '' ?>>
                    <b>Upgrade</b>
                  </div>
                  <div class="pl-4">
                    Metode ini menggabungkan paket sebelumnya (tidak menambah jangka waktu hosting).
                    Anda hanya bisa memilih paket yang lebih tinggi dengan manfaat mendapatkan
                    potongan sesuai dengan pembelian sebelumnya.
                  </div>
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-3">
          <div class="card">
            <div class="card-body">
              <p>Pilih jenis paket</p>
              <?php foreach ($plans as $plan) : ?>
                <div class="radio">
                  <label>
                    <input type="radio" name="plan" id="plan<?= $plan->plan_id ?>" class="mr-2" value="<?= $plan->plan_id ?>" required onchange="recalculate()">
                    <?= $plan->plan_alias ?>
                  </label>
                </div>
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
                <h6>Hosting Lama</h6>
                <div class="ml-auto" id="outdisc">-</div>
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
      </form>
    <?php endif ?>
    <a href="/user/hosting/detail/<?= $data->hosting_id ?>" class="mt-3 btn btn-secondary">Kembali</a>

  </div>

  <script id="plans" type="application/json">
    <?= json_encode($plans) ?>
  </script>
  <script>
    var plans = JSON.parse(document.getElementById('plans').innerHTML);
    plans = plans.reduce((a, b) => (a[b.plan_id] = b, a), {});

    function syncPlans() {
      var mode = window.upgrade.mode.value;
      var current = parseInt("<?= $data->plan_id ?>");
      if (mode === 'new') {
        window.upgrade.plan.forEach(x => {
          x.disabled = false;
        })
      } else if (mode === 'extend') {
        window.upgrade.plan.forEach(x => {
          x.disabled = x.value != current;
          x.checked = x.value == current;
        })
      } else if (mode === 'upgrade') {
        window.upgrade.plan.forEach(x => {
          x.disabled = x.value <= current;
          x.checked = false;
        })
      }
      recalculate();
    }

    function recalculate() {
      var mode = window.upgrade.mode.value;
      var plan = window.upgrade.plan.value;
      var years = parseInt(window.upgrade.years.value);
      var oldyr = parseInt('<?= $data->purchase_years ?>');
      var oldval = parseInt('<?= $data->plan_price ?>') * 1000;
      var oldexp = new Date('<?= $data->purchase_expired ?>');
      if (mode && plan) {
        var unit = parseInt(plans[plan].plan_price) * 1000;
        window.upgrade.years.disabled = unit == 0 || mode == 'upgrade';
        if (unit == 0) years = 0.25;
        else if (mode === 'upgrade') window.upgrade.years.value = years = oldyr;
        var formatter = new Intl.NumberFormat('id-ID', {
          style: 'currency',
          currency: 'IDR',
          maximumFractionDigits: 0,
          minimumFractionDigits: 0,
        });
        var cashback = mode === 'upgrade' ? oldval * oldyr : 0;
        var tip = 5000;
        var exp = mode === 'new' ? new Date(Date.now() + 1000 * 86400 * 365 * years) : (
          mode === 'extend' ? new Date(Number(oldexp) + 1000 * 86400 * 365 * years) : oldexp);
        $('#outprice').text(unit == 0 ? 'Gratis' : formatter.format(unit));
        $('#outyear').html(unit == 0 ? '3 Bulan' : '&times; ' + years + ' Tahun');
        $('#outtotal').text(unit == 0 ? 'Gratis' : formatter.format(unit * years));
        $('#outdisc').text(!cashback ? '-' : formatter.format(-cashback));
        $('#outtip').text(unit == 0 ? '-' : formatter.format(tip));
        $('#outbill').text(unit == 0 ? 'Gratis' : formatter.format(unit * years - cashback + tip));
        $('#outexp').text(exp.toISOString().substr(0, 10));
      }
    }
  </script>
</body>

</html>