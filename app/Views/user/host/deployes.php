<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>
  <div class="container">
    <?= view('user/host/navbar') ?>

    <?php if ($host->status === 'active') : ?>
      <div class="d-flex">
        <button type="button" class="btn btn-primary mb-3 me-auto" data-bs-toggle="modal" data-bs-target="#deployModal">
          Deploy New
        </button>
        <form method="POST">
          <?= csrf_field() ?>
          <input type="hidden" name="delete" value="y">
          <button onclick="return confirm('Hapus semua deploy?')" class="btn btn-danger mb-3">
            Clear
          </button>
        </form>
      </div>
    <?php endif ?>
    <?php $i = 0;
    foreach (array_reverse($deploys) as $deploy) : ?>
      <form class="card" method="POST" onsubmit="return putToDeploy(this)">
        <div class="card-body">
          <details <?= $i++ === 0 ? 'open' : '' ?>>
            <summary><?= humanize($deploy->updated_at) ?></summary>
            <div class="row my-2">
              <div class="col-md-6">
                <textarea name="template" class="form-control font-monospace h-100" style="min-height: 200px;"><?= $deploy->template ?></textarea>
              </div>
              <div class="col-md-6 bg-dark text-white">
                <?php if ($deploy->result) : ?>
                  <pre class="output-highlight" style="white-space: pre-wrap;"><?= esc($deploy->result) ?></pre>
                <?php else : ?>
                  <p class="text-center">
                    <i><?= lang('Host.waitingDeployHint', [humanize($deploy->created_at->addMinutes($host->plan_id * 5 + 5))]) ?></i>
                  </p>
                <?php endif ?>
              </div>
            </div>
            <input type="submit" value="<?= lang('Host.redeploy') ?>">
          </details>
        </div>
      </form>
    <?php endforeach ?>
  </div>

  <div class="modal fade" id="deployModal" tabindex="-1" aria-labelledby="deployModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form class="modal-content" method="POST" onsubmit="return isItSafe(this)">
        <div class="modal-header">
          <h5 class="modal-title" id="deployModalLabel">New deploy</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <?= csrf_field() ?>
          <textarea name="template" id="template" class="form-control font-monospace h-100" style="min-height: 200px;"></textarea>
          <a href="https://github.com/domcloud/dom-templates" target="_blank" rel="noopener noreferrer">Deploy Config Reference</a>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= lang('Interface.cancel') ?></button>
          <button type="submit" class="btn btn-primary"><?= lang('Host.deployNow') ?></button>
        </div>
      </form>
    </div>
  </div>

  <script>
    document.querySelectorAll('.output-highlight').forEach(el => {
      var code = el.innerHTML;
      code = code.replace(/^(#----- .+ -----#)$/gm, '<b class="text-warning">$1</b>');
      code = code.replace(/(\[password\])/g, '<span class="text-decoration-line-through">$1</span>');
      code = code.replace(/^\$&gt; (.+)/gm,
        '<span class="text-muted" style="word-break: break-all; ">$$&gt; <span style="user-select: all;">$1</span></span>');
      code = code.replace(/^(Exit status: .+)/gim, '<span class="text-info">$1</span>');
      el.innerHTML = code;
    });

    function isItSafe(f) {
      var t = f.template.value;
      if (/^source:/gm.test(t)) {
        return confirm('Anda menyebutkan parameter source, yang berarti file HOME anda akan di-OVERWRITE secara PERMANEN. Aksi ini tidak dapat dikembalikan. Apakah anda yakin?');
      }
      return true;
    }

    function putToDeploy(f) {
      var t = f.template.value;
      console.log(t);
      $('#template').val(t);
      $('#deployModal').modal('show');
      return false;
    }
  </script>

</body>

</html>