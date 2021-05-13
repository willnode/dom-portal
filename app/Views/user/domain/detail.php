<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>

  <div class="container">
    <?php /** @var \App\Entities\Domain $domain */ ?>
    <h1 class="mb-2">Detail Domain</h1>
    <div class="row">
      <div class="col-md-6">
        <div class="card">
          <div class="card-body">
            <div><?= lang('Domain.domainStatus') ?></div>
            <div class="input-group mb-3">
              <h3><?= ucfirst($domain->status) ?></h3>
              <?php if ($domain->status === 'pending') : ?>
                <a href="/user/domain/invoices/<?= $domain->id ?>" class="ms-auto btn btn-primary"><?= lang('Host.finishPayment') ?></a>
              <?php elseif ($domain->status === 'active') : ?>
                <a href="http://<?= $domain->name ?>" target="_blank" rel="noopener noreferrer" class="ms-auto btn btn-primary"><?= lang('Host.openWebsite') ?></a>
              <?php endif ?>
            </div>
            <div><?= lang('Domain.domainName') ?></div>
            <div class="input-group mb-3">
              <input value="<?= $domain->name ?>" type="text" class="form-control" readonly>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-body">
            <table class="table table-sm">
              <tbody>
                <tr>
                  <td colspan="2"><b>Status Domain</b></td>
                </tr>
                <tr>
                  <td>Domain</td>
                  <td id="inid"></td>
                </tr>
                <tr>
                  <td>Status</td>
                  <td id="instat"></td>
                </tr>
                <tr>
                  <td>Tanggal Registrasi</td>
                  <td id="inreg"></td>
                </tr>
                <tr>
                  <td>Tanggal Expirasi</td>
                  <td id="inexp"></td>
                </tr>
                <tr>
                  <td>Proteksi Transfer</td>
                  <td id="intp"></td>
                </tr>
                <tr>
                  <td colspan="2"><b>Name Server</b></td>
                </tr>
                <tr>
                  <td colspan="2" id="inns"></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <a href="/user/domain" class="mt-3 btn btn-secondary">Kembali</a>
  </div>
  <script>
      window.addEventListener('DOMContentLoaded', (event) => {
        fetch('/user/domain/info_domain/<?= $domain->id?>').then(x => x.json()).then(x => {
          $('#inid').text(x.domain);
          $('#instat').text(x.status);
          $('#inreg').text(x.startdate);
          $('#inexp').text(x.enddate);
          $('#intp').text(x.transferlock ? '✅' : '❌');
          var ul = $('<ul>');
          [x.ns1, x.ns2, x.ns3, x.ns4, x.ns5, x.ns6, x.ns7, x.ns8].forEach((ns, i) => {
            if (typeof ns === 'string') {
              ul.append($('<li>').text(ns));
            }
          });
          $('#inns').html('');
          $('#inns').append(ul);
        });
      });
  </script>
</body>

</html>