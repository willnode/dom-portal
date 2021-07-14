<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>

  <div class="container">
    <?php /** @var \App\Entities\Domain $domain */ ?>
    <?= view('user/domain/navbar') ?>
    <div class="row">
      <div class="col-md-6">
        <div class="card">
          <div class="card-body">

            <div><?= lang('Host.statusInvoice') ?></div>
            <div class="input-group my-3">
              <h3><?= ucfirst($domain->status) ?></h3>
            </div>
            <?php $metadata = $current->metadata ?>
            <?php $money = format_money($metadata->price, $metadata->price_unit) ?>
            <p><?= $current->niceMessage . lang("Host.formatInvoiceSum", ["<b>$money</b>"])  ?></p>
            <?php if ($domain->status === 'pending') : ?>
              <form method="post" class="my-2">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="pay">
                <input type="submit" class="btn btn-primary" value="<?= lang('Host.finishPayment') ?>">
              </form>
              <?php if ($metadata->price_unit === 'usd') : ?>
                <p class="mt-2 alert alert-info"><small><i> Heads up! The merchant displayed in the PayPal confirmation page will be "Wello Soft", which means the fund will going directly to <a href="https://wellosoft.net" target="_blank">the creator of DOM Cloud</a>.</i></small></p>
              <?php endif ?>
            <?php endif ?>
          </div>

        </div>
      </div>
    </div>
    <a href="/user/domain" class="mt-3 btn btn-secondary"><?= lang('Interface.back') ?></a>
  </div>

</body>

</html>