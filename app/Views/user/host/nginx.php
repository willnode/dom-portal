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
            <form method="POST" action="/user/host/deploys/<?= $host->id ?>">
              <div class="mb-3 ">
                <h2>NginX Configurator (Beta)</h2>
              </div>
              <p>
                <a href="https://github.com/domcloud/dom-nginx" target="_blank" rel="noopener noreferrer">
                  Help on configuration
                </a>
              </p>
              <?= csrf_field() ?>
              <textarea class="form-control font-monospace" name="template" id="template" cols="30" rows="10"></textarea>
              <input type="submit" value="Save" class="btn btn-primary my-1">
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
      }).then(x => x.text().then(y => [$('#config').text(y), $('#template').val(tryParse(y))]));
    }

    function tryParse(t) {
      var x = 'nginx:\n';
      // check ssl config
      var listen80 = /^\s*listen [\d\.]+;$/gm.test(t);
      var listen443 = /^\s*listen [\d\.]+:443 ssl;$/gm.test(t);
      x += '  ssl:' + (listen80 ? (listen443 ? 'on' : 'off') : 'enforce') + '\n';
      // check location config
      var locations = t.match(/^\s*location .+?{.+?}$/gms);
      if (locations.length > 1) {
        x += '  locations:\n';
        x += locations.slice(null, -1).map(l => {
          var y = '';
          y += '  - match: ' + l.match(/^\s*location (.+?) {/m)[1].trim() + '\n';
          l.split('\n').slice(1).forEach(ll => {
            ll = ll.trim().split(' ', 2);
            if (ll.length > 1)
              y += '    ' + ll[0] + ': ' + ll[1] + '\n';
          });
          return y;
        }).join('');
      }
      console.log(locations);
      return x;
    }
  </script>
</body>

</html>