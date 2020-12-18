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
            <div class="input-group mb-3">
              <h3><?= ucfirst($host->status) ?></h3>
            </div>
            <?php if ($current && $current->status === 'pending') : ?>
              <p>
                <?php $metadata = $current->metadata ?>
                <?php $money = format_money($metadata->price) ?>
                <?php $plan = (new \App\Models\PlanModel())->find($metadata->plan)->alias ?>
                <?= ($metadata->registrar ? lang('Host.formatInvoiceAlt', [
                  "<b>$plan</b>",
                  "<b>{$metadata->domain}</b>",
                ]) : lang('Host.formatInvoice', [
                  "<b>$plan</b>",
                ])) . lang("Host.formatInvoiceSum", ["<b>$money</b>"]) ?>
              </p>

              <?php if (lang('Interface.code') == 'en') : ?>
                <p>We recommend to finish payment using TransferWise.
                  <br> Last status from TransferWise: <b><?= $metadata->_status ?? '-' ?></b><br>
                  <br> Don't have TransferWise account? <a href="https://transferwise.com/invite/u/muhammadm3473" target="_blank" rel="noopener noreferrer">Sign up here to start</a>*.</p>
                <nav>
                  <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <a class="nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="true">Common Information</a>
                    <a class="nav-link" id="nav-profile-tab" data-toggle="tab" href="#nav-profile" role="tab" aria-controls="nav-profile" aria-selected="false">More Information</a>
                  </div>
                </nav>
                <div class="tab-content" id="nav-tabContent">
                  <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                    <table class="table table-sm table-striped my-4">
                      <tbody>
                        <tr>
                          <th colspan="2">Payment Details</th>
                        </tr>
                        <tr>
                          <td>Currency</td>
                          <th style="user-select: all;">USD</th>
                        </tr>
                        <tr>
                          <td>Amount</td>
                          <th style="user-select: all;"><?= $money ?></th>
                        </tr>
                        <tr>
                          <td>Reference #</td>
                          <th style="user-select: all;"><?= $current->id ?></th>
                        </tr>
                        <tr>
                          <th colspan="2">Recipient Address</th>
                        </tr>
                        <tr>
                          <td>Country</td>
                          <th style="user-select: all;">Indonesia</th>
                        </tr>
                        <tr>
                          <td>City</td>
                          <th style="user-select: all;">Kamal</th>
                        </tr>
                        <tr>
                          <td>Address</td>
                          <th style="user-select: all;">Telang, Kamal, Bangkalan</th>
                        </tr>
                        <tr>
                          <td>Postal Code</td>
                          <th style="user-select: all;">69162</th>
                        </tr>
                        <tr>
                          <th colspan="2">Bank Details</th>
                        </tr>
                        <tr>
                          <td>Bank Account Country</td>
                          <th style="user-select: all;">United States</th>
                        </tr>
                        <tr>
                          <td>Bank Name</td>
                          <th style="user-select: all;">Community Federal Savings Bank</th>
                        </tr>
                        <tr>
                          <td>Bank Address</td>
                          <th style="user-select: all;">TransferWise<br>19 W 24th Street<br>New York NY 10010<br>United States</th>
                        </tr>
                        <tr>
                          <td>Account Holder</td>
                          <th style="user-select: all;">DOM Cloud Hosting</th>
                        </tr>
                        <tr>
                          <td>Account Type</td>
                          <th style="user-select: all;">Checking</th>
                        </tr>
                        <tr>
                          <td>Account Number</td>
                          <th style="user-select: all;">8310815903</th>
                        </tr>
                        <tr>
                          <td>ACH Routing Number</td>
                          <th style="user-select: all;">026073150</th>
                        </tr>
                        <tr>
                          <td>Wire Routing Number</td>
                          <th style="user-select: all;">026073008</th>
                        </tr>
                        <tr>
                          <td>SWIFT/BIC</td>
                          <th style="user-select: all;">CMFGUS33</th>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                    <div class="alert-info my-4 p-1">
                      <p>You need to provide <b>the reference number</b> during sending the money so we can process the payment automatically.</p>
                      <p>The amount of money that we receive must be in the exact number of <b><?= $money ?></b> or more. If you want to be safe, use TransferWise, otherwise provide more small amount of margin to cover any transaction fees. Note currently we can't refund any excess amount of money we received.</p>
                      <p>You don't need to provide all details. <a href="https://transferwise.com/help/articles/2932150" target="_blank" rel="noopener noreferrer">See the guide to transfer using USD</a> and <a href="https://transferwise.com/help/articles/2827506" target="_blank" rel="noopener noreferrer">Guide to use US bank details for receiving transfers</a>.</p>
                      <p>For inside USA transfer, use <b>ACH routing number</b>. <b>Wire routing number</b> are means for international payments (using <b>SWIFT/BIC</b>) and has higher fees. </p>
                      <p>Note that TransferWise (currently) <b>does not support direct debit</b> for inside USA transfer. Use electronic transfer instead.</p>
                      <p>Some banks requires you to tell <b>Recipient Address</b>. Note that this is different from <b>Bank Address</b>.</p>
                      <p>In some cases, you'll see the account holder as the <b>Bank Name</b>. This is fine according to TransferWise.</p>
                      <p>We'll accept your payment as soon as we get <b>funds_converted</b> status from TransferWise. Note that this is not a finalized payment. We get the money usually in 2-5 days, during that time if the transfer are failing this purchase will get rollbacked as pending.</p>
                    </div>
                  </div>
                </div>
                <p><small class="text-muted">* This sign up link contains affiliated link which may grant you (mostly) free international transaction fee up to certain amounts.</small></p>
                <p><i>Please <a href="mailto:support@domcloud.id?subject=Hello,+I+want+to+proceed+my+purchase+with+ID+<?= $current->id ?>">request support</a> if you have problem finishing the payment.</i></p>
              <?php else : ?>
                <form method="post" class="my-2">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="pay">
                  <input type="submit" class="btn btn-primary" value="<?= lang('Host.finishPayment') ?>">
                </form>
              <?php endif ?>

              <p>
                <?= lang('Host.cancelInvoiceHint') ?>
              </p>
              <form method="post" class="my-2">
                <?= csrf_field() ?>
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
            <h3><?= lang('Interface.archives') ?></h3>
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