<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>
  <div class="container">
    <div class="d-flex mb-3">
      <h1>Deployment Hosting</h1>
      <a href="/user/hosting/detail/<?= $host->id ?>" class="mt-3 ml-auto btn btn-secondary">Kembali</a>
    </div>
    <?php

                                    use CodeIgniter\I18n\Time;

$i = 0;
    foreach (array_reverse($deploys) as $deploy) : ?>
      <form class="card" method="POST">
        <div class="card-body">
          <details <?= $i++ === 0 ? 'open' : '' ?>>
            <summary><?= $deploy->updated_at->humanize() ?></summary>
            <div class="row my-2">
              <div class="col-md-6">
                <textarea name="template" id="template" class="form-control font-monospace h-100" style="min-height: 200px;"><?= $deploy->template ?></textarea>
              </div>
              <div class="col-md-6 bg-dark text-white">
                <?php if ($deploy->result) : ?>
                  <pre style="white-space: pre-wrap;"><?= $deploy->result ?></pre>
                <?php else : ?>
                  <p class="text-center">
                    Operasi masih belum selesai. Akan dibatalkan <?= $deploy->created_at->addMinutes($host->plan_id * 5)->humanize() ?>
                  </p>
                <?php endif ?>
              </div>
            </div>
            <?php if ($deploy->result && $deploy && $deploy->created_at->addDays(1)->getTimestamp() > time()) : ?>
              <input type="submit" value="Deploy Ulang">
            <?php endif ?>
          </details>
        </div>
      </form>
    <?php endforeach ?>
  </div>

</body>

</html>