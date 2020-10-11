<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>
  <div class="container">
    <?= view('user/host/navbar') ?>
    <div class="card">
      <div class="card-body">

        <div class="row my-2">
          <div class="col-md-6">
            <form>
              <div class="mb-3 ">
                <h2>NginX Configurator</h2>
              </div>
              <div class="mb-3 row align-items-center">
                <div class="col">
                  <label for="addons">
                    Root path
                  </label>
                </div>
                <div class="col">
                  <input class="form-control" placeholder="Unmodified">
                </div>
              </div>
              <div class="mb-3 row align-items-center">
                <div class="col">
                  <label for="addons">
                    SSL Mode
                  </label>
                </div>
                <div class="col">
                  <select class="form-select">
                    <option>On</option>
                    <option>Off</option>
                    <option>Enforce</option>
                  </select>
                </div>
              </div>
              <div class="mb-3 row align-items-center">
                <div class="col">
                  <label for="addons">
                    Gzip Mode
                  </label>
                </div>
                <div class="col">
                  <select class="form-select">
                    <option>Off</option>
                    <option>On (HTML only)</option>
                    <option>On (All text files)</option>
                  </select>
                </div>
              </div>
              <div class="mb-3 row align-items-center">
                <div class="col">
                  <label for="addons">
                    Dynamic Content
                  </label>
                </div>
                <div class="col">
                  <select class="form-select">
                    <option>PHP</option>
                    <option>Python</option>
                    <option>Ruby</option>
                    <option>Node.js</option>
                    <option>Generic</option>
                    <option>Off</option>
                  </select>
                </div>
              </div>
              <div class="mb-3 row align-items-center">
                <div class="col">
                  <label for="addons">
                    Error Pages
                  </label>
                </div>
                <div class="col">
                  <select class="form-select">
                    <option>Off</option>
                    <option>HTML</option>
                    <option>PHP</option>
                  </select>
                </div>
              </div>
              <div class="mb-3 row align-items-center">
                <div class="col">
                  <label for="addons">
                    Using Index File
                  </label>
                </div>
                <div class="col">
                  <select class="form-select">
                    <option>Off</option>
                    <option>On</option>
                  </select>
                </div>
              </div>
            </form>
          </div>
          <div class="col-md-6 bg-dark text-white">
            <pre id="config" style="tab-size: 4;">Getting NginX info...</pre>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script>
    window.onload = function() {
      fetch(window.location, {
        method: 'post'
      }).then(x => x.text().then(y => $('#config').text(y)));
    }
  </script>
</body>

</html>