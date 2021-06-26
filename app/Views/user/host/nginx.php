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
            <div class="mb-3 ">
              <h2>NginX Configurator</h2>
            </div>
            <?php if ($host->plan_id > 2) : ?>
              <form method="GET">
                <label for="subdomain">
                  Subdomain:&nbsp;
                </label>
                <input type="text" id="subdomain" name="subdomain" value="<?= $_GET['subdomain'] ?? '' ?>">
                <input type="submit" value="Check" class="btn btn-primary btn-sm">
              </form>
            <?php endif ?>
            <form method="POST" action="/user/host/deploys/<?= $host->id ?>">
              <p>
                <a href="https://github.com/domcloud/dom-nginx" target="_blank" rel="noopener noreferrer">
                  Help on configuration
                </a>
              </p>
              <?= csrf_field() ?>
              <textarea class="form-control font-monospace" name="template" id="template" cols="30" rows="10" required></textarea>
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

    /**
     * @param {String} t
     */
    function tryParse(t) {
      var x = 'nginx:\n';
      // check ssl config
      var listen80 = /^\s*listen [\d\.]+;$/gm.test(t);
      var listen443 = /^\s*listen [\d\.]+:443 ssl( http2)?;$/gm.test(t);
      x += '  ssl: ' + (listen80 ? (listen443 ? 'on' : 'off') : 'enforce') + '\n';
      // check passenger config
      var r = /^\s*passenger_(\w+) '?(.+?)'?;$/gm;
      var p = r.exec(t);
      if (p) {
        x += '  passenger:\n';
        do {
          x += '    ' + p[1] + ": " + p[2] + "\n";
        }
        while (p = r.exec(t));
      }
      // check location config
      var locations = t.match(/^\s*location .+?{.+?}$/gms);
      var lastLoc = locations[locations.length - 1];
      if (lastLoc.includes('return')) {
        x += '  fastcgi: off\n';
      }
      if (locations.length > 1) {
        x += '  locations:\n';
        x += locations.slice(null, -1).map(l => {
          var y = '';
          y += '  - match: ' + l.match(/^\s*location (.+?) {/m)[1].trim() + '\n';
          l.split('\n').slice(1).forEach(ll => {
            var ls = ll.trim().split(' ');
            if (ls.length > 1)
              y += '    ' + ls[0] + ': ' + ls.slice(1).join(' ').slice(0, -1) + '\n';
          });
          return y;
        }).join('');
      }
      var sub = new URL(window.location.href).searchParams.get('subdomain');
      if (sub) {
        x += 'subdomain: '+sub;
      }
      return x;
    }
  </script>
</body>

</html>