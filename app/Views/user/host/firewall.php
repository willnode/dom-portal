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
            <?php if (lang('Interface.code') === 'id') : ?>
              <h2>Cek Firewall</h2>
              <p>Firewall membantu website anda tidak diekspoitasi apabila terjadi pembobolan hacker dengan cara memblokir semua trafik keluar kecuali yang ada pada whitelist.</p>
              <p>Tergantung pada penggunaan website anda, anda mungkin ingin mematikan firewall apabila website anda membutuhkan komunikasi Host to Host atau server ke pihak ketiga.</p>
              </p>
              <p>Firewall berlaku untuk domain dan semua subdomain yang terikat.</p>
            <?php elseif (lang('Interface.code') === 'en') : ?>
              <h2>Check Firewall</h2>
              <p>Firewall helps your website not to be exploited in the event of a hacker break-in by blocking all outgoing traffic except those on the whitelist.</p>
              <p>Depending on your use of the website, you may want to turn off the firewall if your website requires Host to Host or server to third party communication.</p>
              </p>
              <p>The firewall applies to the domain and all bound subdomains.</p>
            <?php endif ?>
          </div>
        </div>
      </div>
      <div class="col-lg-8 mb-3">
        <div class="card">
          <div class="card-body">
            <?php if (lang('Interface.code') === 'id') : ?>
              <div id="tiploading">
                ⏳ Sedang mengambil data...
              </div>
              <div id="tipfound" class="d-none">
                <h5>✔ Firewall aktif</h5>
                <p>Duduk dan rileks. Website anda aman dari exploits.</p>
              </div>
              <div id="tipnull" class="d-none">
                <h5>⚠ Firewall tidak aktif</h5>
                <p>Kami tidak melarang Anda untuk mematikan Firewall untuk hosting Anda, selama Anda memoderasi keamanan situs web Anda sendiri secara teratur.<br>
                  Anda sekarang mengendalikan keamanan situs web Anda. Jika situs web dibobol, resiko ban permanen dapat terjadi kapan saja!
                </p>
              </div>
            <?php elseif (lang('Interface.code') === 'en') : ?>
              <div id="tiploading">
                ⏳ Currently gathering data...
              </div>
              <div id="tipfound" class="d-none">
                <h5>✔ Firewall is active</h5>
                <p>Sit down and relax. Your website is safe from exploits.</p>
              </div>
              <div id="tipnull" class="d-none">
                <h5>⚠ Firewall is off</h5>
                <p>We do not forbid you to turn off the Firewall for your hosting, as long as you moderate the security of your own website regularly.<br>
                  You are now in control of the security of your website. If the website is compromised, the risk of permanent ban can occur at any time!
                </p>
              </div>
            <?php endif ?>

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
          fetch(window.location, {
            method: 'POST'
          }).then(x => x.text().then(y => report(y)));
        }
        window.onload = cek;
      </script>
    </div>
    <a href="/user/host/detail/<?= $host->id ?>" class="mt-3 btn btn-secondary"><?= lang('Interface.back') ?></a>

</body>