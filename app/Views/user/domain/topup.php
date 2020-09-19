<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>

  <div class="container">

    <form method="POST" class="row">
      <div class="col-md-6 mb-3">
        <div class="card">
          <div class="card-body">
            <h1>Top Up Untuk Pembelian Domain</h1>
            <p>Tidak seperti pembelian hosting, anda dapat menambah kredit sebelum membeli domain. Hal ini anda dapat
              manfaatkan agar anda tidak dikenai biaya transaksi berulang-ulang apabila membeli banyak domain.</p>
            <p>Perlu diketahui, pembayaran untuk topup tidak dapat kembalikan ataupun dicairkan,
              dan tidak dapat digunakan untuk pembelian hosting ataupun domain yang mengikuti
              pembelian atau perpanjangan hosting.</p>
          </div>
        </div>
      </div>
      <div class="col-md-6 mb-3">
        <div class="card">
          <div class="card-body">
            <div class="mb-3">
              <label for="name">Jumlah Topup</label>
              <input class="form-control" name="amount" id="amount" oninput="updateStat()" type="number" min="25000" max="500000" step="5000" required>
            </div>
            <div class="d-flex mb-3">
              <h5>Jumlah Topup</h5>
              <div class="ml-auto" id="topup">-</div>
            </div>
            <div class="d-flex mb-3">
              <h5>Harga Bayar</h5>
              <div class="ml-auto" id="topay">-</div>
            </div>
            <div class="d-flex mb-3">
              <h5>Kredit Setelah Topup</h5>
              <div class="ml-auto" id="total">-</div>
            </div>
          </div>
        </div>
      </div>
    </form>
    <script>
      var formatter = new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'idr',
        maximumFractionDigits: 0,
        minimumFractionDigits: 0,
      });

      function updateStat() {
        const val = Math.min(500000, parseInt($('#amount').val() || '0'));
        $('#topup').text(formatter.format(val));
        $('#topay').text(formatter.format(val + 5000));
        $('#total').text(formatter.format(val));
      }
    </script>
  </div>
</body>

</html>