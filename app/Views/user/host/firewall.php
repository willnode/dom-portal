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
            <h2>Cek Firewall</h2>
            <p>Firewall membantu website anda tidak diekspoitasi apabila terjadi pembobolan hacker dengan cara memblokir trafik keluar kecuali yang ada pada whitelist.</p>
            <p>Tergantung pada penggunaan website anda, anda mungkin ingin mematikan firewall apabila website anda membutuhkan komunikasi Host to Host atau server ke pihak ketiga.</p>
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
              ✔ Firewall aktif untuk website anda
            </h5>
            <h5 id="tipnull" class="d-none">
              ⚠ Firewall tidak aktif
            </h5>
          </div>
        </div>
      </div>
      <script>
        function report(result) {
          $('#tiploading').addClass('d-none');
          $(result === '1' ? '#tipfound' : '#tipnull').removeClass('d-none');
        }

        function cek() {
          const domain = '<?= $host->domain ?>';
          $('#tipfound,#tipnull,#tiperr').addClass('d-none');
          $('#tiploading').removeClass('d-none');
          let completedomain = $('#sub').val() + '.' + domain;
          if (completedomain.startsWith('.')) completedomain = completedomain.slice(1);
          fetch(window.location).then(x => x.text().then(y => report(y)));
        }
        window.onload = cek;
      </script>
    </div>
    <a href="/user/host/detail/<?= $host->id ?>" class="mt-3 btn btn-secondary"><?= lang('Interface.back') ?></a>

</body>