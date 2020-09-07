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
            <h1>Gabung dalam Program Reseller</h1>
            <p>Program Reseller adalah kesempatan anda untuk mendapatkan komisi, dengan cara
              mengajak sanak famili, rekan, kenalan, atau klien anda mendaftarkan Hosting ke DOM Cloud.</p>
            <p>Komisi yang anda dapat berupa 50% dari tiap hasil pembelian hosting pertama orang lain yang
              menggunakan kode invite anda. Hasil komisi nantinya dapat dicairkan pada rekening anda
              ketika jumlah komisi mencapai 300 ribu rupiah keatas.</p>
            <p>Program Reseller saat ini hanya dapat memproses rekening bank tertentu di Indonesia.</p>
          </div>
        </div>
      </div>
      <div class="col-md-6 mb-3">
        <div class="card">
          <div class="card-body">
            <h3>Data Pribadi</h3>
            <div class="mb-3">
              <label for="name">Pekerjaan</label>
              <input class="form-control" name="reseller_job" autocomplete="name" required>
            </div>
            <h3>Data Pembayaran</h3>
            <div class="mb-3">
              <label for="name">Nomor Rekening</label>
              <div class="row g-1">
                <div class="col-3">
                  <select class="form-select" name="reseller_card_kind" required>
                    <option value="BNI">BNI</option>
                    <option value="BRI">BRI</option>
                    <option value="Mandiri">Mandiri</option>
                    <option value="BTN">BTN</option>
                  </select>
                </div>
                <div class="col-9">
                  <input class="form-control" name="reseller_card_number" required>
                </div>
              </div>
            </div>
            <div class="mb-3">
              <label for="name">Atas Nama Rekening</label>
              <input class="form-control" name="reseller_card_as" required>
            </div>
            <div class="form-check">
              <input class="form-check-input" required type="checkbox" value="" id="flexCheckDefault">
              <label class="form-check-label" for="flexCheckDefault">
                Data pembayaran sudah akurat dan saya menyetujui aturan main layanan.
              </label>
            </div>
            <input type="submit" value="Simpan" class="btn btn-primary mt-3">
          </div>
        </div>
      </div>
    </form>
  </div>
</body>

</html>