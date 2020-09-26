<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>
  <div class="container">
    <h1 class="mb-3"><?= lang('Host.manageInvoice') ?></h1>
    <div class="row">
      <div class="col-md-6">
        <div class="card">
          <div class="card-body">
            <div><?= lang('Host.statusInvoice') ?></div>
            <div class="input-group mb-3">
              <h3><?= ucfirst($host->status) ?></h3>
            </div>
            <?php if ($host && $host->status === 'pending') : ?>
              <?php if (lang('Interface.code') == 'en') : ?>
                <p><i>Please <a href="mailto:support@domcloud.id?subject=Hello,+I+want+to+proceed+my+purchase+with+ID+<?= $current->id ?>">request support</a> to finish payment.</i></p>
              <?php else : ?>
                <form method="post" class="my-2">
                  <input type="hidden" name="action" value="pay">
                  <input type="submit" class="btn btn-primary" value="<?= lang('Host.finishPayment') ?>">
                </form>
              <?php endif ?>
              <p>
                <?php $money = format_money($current->metadata->price) ?>
                <?php $plan = (new \App\Models\PlanModel())->find($current->plan)->alias ?>
                <?= ($current->liquid ? lang('Host.formatInvoiceAlt', [
                  "<b>$plan</b>",
                  "<b>$data->domain</b>",
                ]) : lang('Host.formatInvoice', [
                  "<b>$plan</b>",
                ])) . lang("Host.formatInvoiceSum", ["<b>$money</b>"]) ?>
              </p>
              <p>
                <?= lang('Host.cancelInvoiceHint') ?>
              </p>
              <form method="post" class="my-2">
                <input type="hidden" name="action" value="cancel">
                <input type="submit" class="btn btn-danger" value="<?= lang('Host.cancelInvoice') ?>" onclick="return confirm('<?= lang('Host.cancelInvoceConfirm') ?>')">
              </form>
            <?php elseif ($host->status === 'starting') : ?>
              <p><?= lang('Host.preparingHint') ?> </p>
            <?php endif ?>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-body">
            <h3><?= lang('Interface.archives')?></h3>
            <?php foreach ($history as $item) : ?>
              <div class="card">
                <div class="card-body">
                  <div class="d-flex">
                    <div><b><?= $item->metadata->_issued ?> | Rp. <?= $item->metadata->price ?></b></div>
                    <div class="ml-auto"><?= ucfirst($item->status) ?></div>
                  </div>
                </div>
              </div>
            <?php endforeach ?>
          </div>
        </div>
      </div>
    </div>
    <a href="/user/host/detail/<?= $host->id ?>" class="mt-3 btn btn-secondary"><?= lang('Interface.back') ?></a>
  </div>

</body>

</html>