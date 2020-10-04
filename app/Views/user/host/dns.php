<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>

  <div class="container mb-3">
    <?= view('user/host/navbar') ?>
    <div class="row">
      <div class="col-md-4 mb-3">
        <div class="card">
          <div class="card-body">
            <h2>Cek Koneksi DNS</h2>
            <p>DNS adalah gerbang utama untuk mengidentifikasi alamat website anda. Disini anda dapat cek apakah
              DNS website anda bekerja.
            </p>
            <p class="mb-0">Domain yang dites: </p>
            <p><input id="sub" placeholder="subdomain" class="form-inline" style="max-width: 120px;"><b>.<?= $host->domain ?></b>
              <input type="button" value="Cek" onclick="cek()"></p>
          </div>
        </div>
      </div>
      <div class="col-md-8 mb-3">
        <div class="card">
          <div class="card-body">
            <div id="tiploading">
              ⏳ Sedang menguji website anda.
            </div>
            <h5 id="tipfound" class="d-none">
              ✔ DNS sepertinya berfungsi dengan baik
            </h5>
            <h5 id="tipnull" class="d-none">
              ⚠ Tidak ada poin DNS yang ditemukan
            </h5>
            <div id="tiperr" class="d-none">
              <h5 class="text-danger">🛑 Sepertinya DNS server mempunyai kendala</h5>
              <p>Cobalah untuk mengecek apakah Anda sudah mengarahkan Nameserver domain dengan benar dan DNS server yang tituju sedang berjalan normal.</p>
            </div>
            <table class="table table-sm" id="table">

            </table>
          </div>
        </div>
      </div>
      <script>
        function report(result) {
          if (result) {
            $('#table').html(`
          <tbody>${result.map(x => `<tr>
          <td>${x.host}</td>
          <td>${x.type}</td>
          <td>${x.ip || x.target || x.txt}</td>
          </tr>`).join('')}</tbody>
          `)
          }
          $('#tiploading').addClass('d-none');
          $(result === false ? '#tiperr' : (result.length >= 1 ? '#tipfound' : '#tipnull')).removeClass('d-none');
        }

        function cek() {
          const domain = '<?= $host->domain ?>';
          $('#table').html(``);
          $('#tipfound,#tipnull,#tiperr').addClass('d-none');
          $('#tiploading').removeClass('d-none');

          fetch(window.location, {
            method: 'post',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
            },
            body: 'sub=' + encodeURIComponent($('#sub').val())
          }).then(x => x.json().then(y => report(y)));
        }
        window.onload = cek;
      </script>
    </div>
    <a href="/user/host/detail/<?= $host->id ?>" class="mt-3 btn btn-secondary"><?= lang('Interface.back') ?></a>

</body>