<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>
  <div class="container">
    <h1>Order Domain</h1>
    <?= isset($validation) ? $validation->listErrors() : '' ?>
    <form method="POST" name="upgrade">
      <?= csrf_field() ?>
      <div class="row">
        <div class="col-lg-6">
          <div class="card">
            <div class="card-body">
              <h3 class="card-title">Data Domain</h3>
              <div class="mb-3">
                <label>Cari Domain</label>
                <div class="input-group">
                  <input name="domain_name" id="domain_name" class="form-control" pattern="^[-\w]+$" required oninput="updateStat()">
                  <select class="form-select" name="domain_scheme" id="domain_scheme" required style="max-width: 120px" onchange="updateStat()">
                    <?php foreach ($schemes as $s) : if ($s->price_local !== 0) : ?>
                        <option value="<?= $s->id ?>"><?= $s->alias ?></option>
                    <?php endif;
                    endforeach; ?>
                  </select>
                  <input onclick="checkDomain()" type="button" value="Cek" class="btn btn-primary">
                </div>
              </div>
              <div class="mb-3">
                <label>Durasi Tahun</label>
                <div class="input-group">
                  <input name="years" class="form-control" type="number" min="1" max="5" value="1" required onchange="updateStat()">
                </div>
              </div>

              <div class="mb-3 row">
                <div class="col-sm-10">Pasang Proteksi Privasi?</div>
                <div class="col-sm-2">
                  <label class="form-check">
                    <input class="form-check-input" type="checkbox" name="purchase_privacy_protection" value="yes" onchange="updateStat()">
                  </label>
                </div>
              </div>
              <hr>

            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card">
            <div class="card-body">
              <div class="d-flex">
                <h5 id="domainname"></h5>
                <div class="ml-auto" id="outstat">-</div>
              </div>
              <div class="d-flex">
                <h6>Harga Paket</h6>
                <div class="ml-auto" id="outprice">-</div>
              </div>
              <div class="d-flex">
                <h6>Durasi Paket</h6>
                <div class="ml-auto" id="outyear">-</div>
              </div>
              <hr>
              <div class="d-flex">
                <h6>Total Harga Domain</h6>
                <div class="ml-auto" id="outdomain">-</div>
              </div>
              <div class="d-flex">
                <h6>Proteksi Privasi</h6>
                <div class="ml-auto" id="outpriv">-</div>
              </div>
              <div class="d-flex">
                <h6>Biaya Transaksi</h6>
                <div class="ml-auto" id="outtip">-</div>
              </div>
              <hr>
              <div class="d-flex">
                <h6>Total Pembayaran</h6>
                <div class="ml-auto" id="outtotal">-</div>
              </div>
              <p><i><small>Perlu diingat anda hanya mendaftarkan domain. Apabila anda ingin mendaftarkan domain sekaligus hosting, anda dapat <a href="/user/host/create">melakukannya disini</a> </small></i></p>
              <input type="submit" id="outsubmit" disabled class="btn btn-primary btn-block" value="Pesan">
            </div>
          </div>
        </div>
      </div>

    </form>
  </div>

  <script>
    let hostingdata = null;
    let statuses = {
      error: "Error",
      regthroughothers: "Tidak Tersedia",
      available: "Tersedia",
    };

    function checkDomain() {
      var name = window.upgrade.domain_name;
      var scheme = window.upgrade.domain_scheme;
      if (name.reportValidity && !name.reportValidity()) {
        return;
      }
      hostingdata = null;
      updateStat();
      $('#domainname').text("Loading...");

      fetch(`/user/domain/check?name=${name.value}&scheme=${scheme.value}`).then(r =>
        r.json()).then(r => {
        hostingdata = r;
        updateStat();
      }).catch(e => {
        hostingdata = null;
      });
    }

    function updateStat() {
      if (hostingdata) {
        var years = window.upgrade.years.value;
        var priv = window.upgrade.purchase_privacy_protection.checked ? 50 * 1000 : 0;
        var formatter = new Intl.NumberFormat('id-ID', {
          style: 'currency',
          currency: 'idr',
          maximumFractionDigits: 0,
          minimumFractionDigits: 0,
        });
        var price = hostingdata.price * 1000;

        $('#domainname').text(hostingdata.domain);
        $('#outstat').text(statuses[hostingdata.status] || hostingdata.status);
        $('#outprice').text(formatter.format(price));
        $('#outyear').html('&times; ' + years + ' Tahun');
        $('#outdomain').text(formatter.format(price * years));
        $('#outpriv').text(priv ? formatter.format(priv) : '');
        $('#outtip').text(formatter.format(5 * 1000));
        $('#outtotal').text(formatter.format(5 * 1000 + price * years + priv));
        var seldom = $('#domain_name').val() + $('#domain_scheme option:selected').text()
        $('#outsubmit').prop('disabled', hostingdata.status !== 'available' || hostingdata.domain !== seldom);
      } else {
        $('#domainname').text("-");
        $('#outstat').text("-");
        $('#outprice').text("-");
        $('#outyear').html("-");
        $('#outdomain').text("-");
        $('#outpriv').text("-");
        $('#outtip').text("-");
        $('#outtotal').text("-");
        $('#outsubmit').prop('disabled', true);
      }
    }
  </script>
</body>

</html>