<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>

  <div class="container mb-3">
    <?= view('user/host/navbar') ?>

    <div class="row">
      <div class="col-md-6 mb-3">
        <div class="card">
          <div class="card-body">
            <h2>Cek Koneksi HTTPS</h2>
            <p>HTTPS adalah komponen wajib untuk setiap website. Halaman ini dapat memandu anda mengecek apakah HTTPS sudah aktif.</p>
            <p>Memeriksa website <b><?= $host->domain ?></b>...</p>
            <ul>
              <li>Website Online? <span id="check-0">...</span></li>
              <li>Website Bisa Diverifikasi? <span id="check-1">...</span></li>
              <li>HTTPS Aktif? <span id="check-2">...</span></li>
              <li>HTTPS Redirect? <span id="check-3">...</span></li>
            </ul>
          </div>
        </div>
      </div>
      <div class="col-md-6 mb-3">
        <div class="card">
          <div class="card-body">
            <div id="tiploading">
              ⏳ Sedang menguji website anda.
            </div>
            <div class="d-none" id="tip">
              <div class="mb-3" id="tip-0-0">
                <h4 class="text-danger">🛑 Website anda tidak aktif</h4>
                <p>Mohon periksa terlebih dahulu apakah server DNS berfungsi dan anda sudah mengaturnya dengan benar</p>
                <p><a href="https://panduan.domcloud.id/manage-domain.html" target="_blank" rel="noopener noreferrer">Panduan Mengatur Domain</a></p>
              </div>
              <div class="mb-3" id="tip-0-1">
                <h6>✔ Website anda aktif (1/4)</h6>
              </div>
              <div class="mb-3" id="tip-1-0">
                <h4>❔ Website anda tidak memasang folder .well-known</h4>
                <p>Folder kosong ini harusnya ada di root server agar Let's Encrypt dapat memverifikasi SSL website anda.</p>
                <p><a href="https://panduan.domcloud.id/install-ssl.html" target="_blank" rel="noopener noreferrer">Panduan Mengatur SSL</a></p>
              </div>
              <div class="mb-3" id="tip-1-1">
                <h6>✔ Website anda dapat diverifikasi (2/4)</h6>
              </div>
              <div class="mb-3" id="tip-2-0">
                <h4>⚠ Website anda tidak mendukung HTTPS</h4>
                <p>Mohon periksa apakah anda sudah menyalakan server SSL/HTTPS dan apakah anda sudah memasang sertifikat SSL dengan benar.</p>
                <form method="POST">
                  <?= csrf_field() ?>
                  <p><input type="hidden" name="action" value="fix3"><input type="submit" class="btn btn-primary" value="Try Fix"></p>
                </form>
                <p><a href="https://panduan.domcloud.id/install-ssl.html" target="_blank" rel="noopener noreferrer">Panduan Mengatur SSL</a></p>
              </div>
              <div class="mb-3" id="tip-2-1">
                <h6>✔ Website anda mendukung HTTPS (3/4)</h6>
              </div>
              <div class="mb-3" id="tip-3-0">
                <h4>ℹ Website anda tidak mengalihkan trafik ke HTTPS</h4>
                <p>Website anda sudah mendukung HTTPS, namun alangkah lebih apabila trafik dari HTTP juga otomatis dialihkan ke HTTPS</p>
                <p><a href="https://panduan.domcloud.id/install-ssl.html" target="_blank" rel="noopener noreferrer">Panduan Mengatur Redirect SSL</a></p>
              </div>
              <div class="mb-3" id="tip-3-1">
                <h6>✔ Website anda mengalihkan trafik ke HTTPS (4/4)</h6>
              </div>
            </div>
          </div>
        </div>
      </div>
      <script>
        function report(result) {
          let rec = 9;
          result.forEach((e, i) => {
            $('#check-' + i).text(result[i] ? '✅' : '❌');
            if (result[i]) {
              $(`#tip-${i}-${0}`).addClass('d-none');
            } else {
              $(`#tip-${i}-${1}`).addClass('d-none');
              if (rec < i) {
                $(`#tip-${i}-${0}`).addClass('d-none');
              } else {
                rec = i;
              }
            }
          });
          $('#tip').removeClass('d-none');
          $('#tiploading').addClass('d-none');
        }
        window.onload = function() {
          fetch(window.location, {
            method: 'post'
          }).then(x => x.json().then(y => report(y)));
        }
      </script>
    </div>
    <a href="/user/host/detail/<?= $host->id ?>" class="mt-3 btn btn-secondary"><?= lang('Interface.back') ?></a>

</body>