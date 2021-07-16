<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>
  <div class="container">
    <?= view('user/host/navbar') ?>
    <div class="row">
      <div class="col-md-6">
        <div class="card">
          <div class="card-body">
            <div><?= lang('Host.statusInvoice') ?></div>
            <div class="input-group my-3">
              <h3><?= ucfirst($host->status) ?></h3>
            </div>
            <?php if ($current && $current->status === 'pending') : ?>
              <p>
                <?php $metadata = $current->metadata ?>
                <?php $money = format_money($metadata->price, $metadata->price_unit) ?>
                <?= $current->niceMessage . lang("Host.formatInvoiceSum", ["<b>$money</b>"]) ?>
              </p>

              <form method="post" class="my-2">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="pay">
                <input type="submit" class="btn btn-primary" value="<?= lang('Host.finishPayment') ?>">
              </form>

              <?php if ($metadata->price_unit === 'usd') : ?>
                <p class="mt-2 alert alert-info"><small><i> Heads up! The merchant displayed in the PayPal confirmation page will be "Wello Soft", which means the fund will going directly to <a href="https://wellosoft.net" target="_blank">the creator of DOM Cloud</a>.</i></small></p>
              <?php elseif ($metadata->price_unit === 'idr') : ?>
                <p class="mt-2 alert alert-info"><small><i> Perhatian! Anda akan dialihkan ke pihak yang mengurusi pembayaran iPaymu. Silahkan menggunakan Virtual Account apabila ingin membayar menggunakan mobile banking (BRI/BNI/Mandiri), Convenience Store untuk membayar menggunakai gerai terdekat (Indomart/Alfamart), QRIS untuk menggunakan Scan QR Code ke payment digital (OVO/ShopeePay/GoPay/Dana/LinkAja). </i></small></p>
              <?php endif ?>
              <p>
                <?= lang('Host.cancelInvoiceHint') ?>
              </p>
              <form method="post" class="my-2">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="cancel">
                <input type="submit" class="btn btn-danger" value="<?= lang('Host.cancelInvoice') ?>" onclick="return confirm('<?= lang('Host.cancelInvoceConfirm') ?>')">
              </form>
            <?php elseif ($host->status === 'active') : ?>
              <?php if (lang('Interface.code') === 'id') : ?>
                <p> Hosting anda sudah aktif dan dapat diakses sekarang. </p>
                <p> Sisa Kadarluarsa Langganan: <?= humanize($host->expiry_at) ?>. </p>
                <p><a target="_blank" href="http://<?= $host->domain ?>">Buka Website</a></p>
                <p><a href="/user/host/see/<?= $host->id ?>">Edit Website</a></p>
              <?php else : ?>
                <p> Your hosting is active and accessible now. </p>
                <p> Remaining Subscription Expiration: <?= humanize($host->expiry_at) ?>. </p>
                <p><a target="_blank" href="http://<?= $host->domain ?>">Open Website</a></p>
                <p><a href="/user/host/see/<?= $host->id ?>">Edit Website</a></p>
              <?php endif ?>
            <?php elseif ($host->status === 'expired') : ?>
              <?php if (lang('Interface.code') === 'id') : ?>
                <p> ATTENTION: Your Subscription Status has expired and needs to be updated immediately before it is permanently deleted by the system. </p>
              <?php else : ?>
              <?php endif ?>
            <?php elseif ($host->status === 'starting') : ?>
              <p><?= lang('Host.preparingHint') ?> </p>
            <?php endif ?>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-body">
            <h3 class="text-center mb-3"><?= lang('Interface.archives') ?></h3>
            <?php /** @var \App\Entities\Purchase[] $history */ ?>
            <?php foreach ($history as $item) : ?>
              <div class="card">
                <div class="card-body">
                  <table class="table table-sm">
                    <tbody>
                      <tr>
                        <td>ID Pembelian</td>
                        <td><b><?= $item->metadata->_id ?></b></td>
                      </tr>
                      <tr>
                        <td>Status</td>
                        <td><b><?= ucfirst($item->status) ?></b></td>
                      </tr>
                      <tr>
                        <td>Tanggal Beli</td>
                        <td><?= $item->metadata->_issued ?></td>
                      </tr>
                      <tr>
                        <td>Tanggal Kadarluarsa</td>
                        <td><?= $item->metadata->expiration ?></td>
                      </tr>
                      <tr>
                        <td>Paket Hosting</td>
                        <td><?= $item->metadata->plan ? (new App\Models\PlanModel())->find($item->metadata->plan)->alias : '-' ?></td>
                      </tr>
                      <tr>
                        <td>Registrasi Domain</td>
                        <td><?= $item->metadata->registrar['domain'] ?? '-' ?></td>
                      </tr>
                      <tr>
                        <td>Tambahan Addons</td>
                        <td><?= $item->metadata->addons ? $item->metadata->addons . ' GB' : '-' ?></td>
                      </tr>
                      <tr>
                        <td>Durasi</td>
                        <td><?= $item->metadata->years ?> Tahun</td>
                      </tr>
                      <tr>
                        <td>Harga</td>
                        <td><?= format_money($item->metadata->price, $item->metadata->price_unit) ?></td>
                      </tr>
                      <tr>
                        <td>Via</td>
                        <td><?= $item->metadata->_via ?></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            <?php endforeach ?>
            <?php if (!$history) : ?>
              <p class="text-center text-black-50"><?= lang('Host.invoiceNotFound') ?></p>
            <?php endif ?>
          </div>
        </div>
      </div>
    </div>
    <a href="/user/host/detail/<?= $host->id ?>" class="mt-3 btn btn-secondary"><?= lang('Interface.back') ?></a>
  </div>

</body>

</html>