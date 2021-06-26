<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>

  <div class="container mb-3">
    <?= view('user/host/navbar') ?>
    <div class="row">
      <div class="col-lg-4 mb-3">
        <div class="card">
          <div class="card-body">
            <h2>Cek Koneksi DNS</h2>
            <p>DNS adalah gerbang utama untuk mengidentifikasi alamat website anda. Disini anda dapat cek apakah
              DNS website anda bekerja.
            </p>
            <p class="mb-0">Domain yang dites: </p>
            <p><input id="sub" placeholder="subdomain" class="form-inline" style="max-width: 120px;"><b>.<?= $host->domain ?></b>
              <input type="button" value="<?= lang('Domain.check') ?>" onclick="cek()">
            </p>
          </div>
        </div>
      </div>
      <div class="col-lg-8 mb-3">
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
            <table class="table table-sm table-striped" id="table">

            </table>
          </div>
        </div>
      </div>
      <script>
        const types = {
          1: 'A',
          2: 'NS',
          5: 'CNAME',
          6: 'SOA',
          12: 'PTR',
          15: 'MX',
          16: 'TXT',
          28: 'AAAA',
          29: 'LOC',
          257: 'CAA',
        }
        function report(result) {
          if (result) {
            $('#table').html(`
            <thead><tr><td>Type</td><td>Value</td></tr></thead>
          <tbody>${(result.Answer || []).map(x => `<tr>
          <td>${types[x.type] || '?'}</td>
          <td>${x.data}</td>
          </tr>`).join('')}</tbody>
          <tfoot><tr><td colspan=3>${result.Comment}</td></tr></tfoot>
          `)
          }
          $('#tiploading').addClass('d-none');
          $(!result.Answer && !result.Authority ? '#tiperr' : (result.Answer ? '#tipfound' : '#tipnull')).removeClass('d-none');
        }

        function cek() {
          const domain = '<?= $host->domain ?>';
          $('#table').html(``);
          $('#tipfound,#tipnull,#tiperr').addClass('d-none');
          $('#tiploading').removeClass('d-none');
          let completedomain = $('#sub').val() + '.' + domain;
          if (completedomain.startsWith('.')) completedomain = completedomain.slice(1);
          fetch(`https://dns.google/resolve?name=${completedomain}&type=ALL`).then(x => x.json().then(y => report(y)));
        }
        window.onload = cek;
      </script>
    </div>
    <a href="/user/host/detail/<?= $host->id ?>" class="mt-3 btn btn-secondary"><?= lang('Interface.back') ?></a>

</body>