<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>

  <div class="mx-5">
    <nav>
      <div class="nav nav-tabs" id="nav-tab" role="tablist">
        <button class="nav-link active me-auto" id="nav-updates-tab" data-bs-toggle="tab" data-bs-target="#nav-updates" type="button" role="tab" onclick="setHash(this)">
          <span class="d-none d-md-inline">Service</span> Updates
        </button>
        <?php foreach ($servers as $server) : ?>
          <button class="nav-link" id="nav-<?= $server->alias ?>-tab" data-bs-toggle="tab" data-bs-target="#nav-<?= $server->alias ?>" type="button" role="tab" onclick="setHash(this)">
            <?= $server->description ?> <span class="d-none d-md-inline">Server</span>
          </button>
        <?php endforeach ?>
      </div>
    </nav>
    <div class="tab-content" id="nav-tabContent">
      <div class="tab-pane fade show active" id="nav-updates" role="tabpanel">
        <h2 class="my-4">Service Updates</h2>
        <p>Coming soon!</p>
      </div>
      <?php foreach ($servers as $server) : ?>
        <div class="tab-pane fade server" data-server='<?= $server->id ?>' id="nav-<?= $server->alias ?>" role="tabpanel">
          <h3 class="my-4 d-flex"><?= $server->description ?> Server
            <button onclick="loadData(document.getElementById('nav-<?= $server->alias ?>'))" class="reload ms-auto btn btn-primary">
              <i class="fas fa-sync"></i>
            </button>
          </h3>
          <div class="row mb-4">
            <div class="col-lg-4">
              <div class="card">
                <div class="card-body">
                  <h5>Common Information</h5>
                  <table class="table table-sm">
                    <tbody>
                      <tr>
                        <td>CNAME</td>
                        <td><?= $server->alias ?>.domcloud.id</td>
                      </tr>
                      <tr>
                        <td>A</td>
                        <td><?= $server->ip ?></td>
                      </tr>
                      <tr>
                        <td>AAAA</td>
                        <td><?= $server->ip6 ?></td>
                      </tr>
                      <tr>
                        <td>Default Domain</td>
                        <td>*<?= $server->domain ?></td>
                      </tr>
                    </tbody>
                  </table>
                  <div class="d-none d-md-block">
                    <h5>Software Versions</h5>
                    <button class="version-btn btn btn-sm btn-outline-primary" onclick="loadVersion(document.getElementById('nav-<?= $server->alias ?>'))">Load versions</button>
                    <div class="versions" style="max-height: 300px; overflow: auto;"></div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6 col-lg-5">
              <div class="card">
                <div class="card-body">
                  <h5>Usage Metric</h5>
                  <div class="performance"></div>
                </div>
              </div>
            </div>
            <div class="col-md-6 col-lg-3">
              <div class="card">
                <div class="card-body">
                  <h5>Service Status</h5>
                  <div class="status"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach ?>
    </div>
  </div>
  <script>
    const statuses = {
      active: '<span class="text-primary"><i class="fas fa-check me-1"></i> On</span>',
      inactive: '<span class="text-secondary"><i class="fas fa-bed me-1"></i> Off</span>',
      failed: '<span class="text-danger"><i class="fas fa-sad-cry me-1"></i> Crash</span>',
    }

    function formatBytes(bytes, decimals = 2) {
      if (bytes === 0) return '0 B';

      const k = 1024;
      const dm = decimals < 0 ? 0 : decimals;
      const sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

      const i = Math.floor(Math.log(bytes) / Math.log(k));

      return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }

    function formatPercent(digit) {
      return parseFloat(digit * 100).toFixed(0) + "%"
    }


    function loadVersion(e) {
      $('.version-btn', e).text('Fetching...');
      fetch('/user/status/version/' + $(e).data('server')).then(x => x.json()).then(x => {
        $('.version-btn', e).addClass('d-none');
        $('.versions', e).html(`
        <table class="table table-sm table-striped">
              <tbody>
              ${Object.keys(x).map(y => {
                  return `<tr><td>${y}</td><td>${x[y]}</td></tr>`
                }).join('')}
              </tbody>
            </table>
        `);
      });
    }

    function setHash(s) {
      window.location.hash = s.id;
    }

    function loadData(e) {
      $('.reload i', e).addClass('fa-spin');
      fetch('/user/status/info/' + $(e).data('server')).then(x => x.json()).then(x => {
        $('.reload i', e).removeClass('fa-spin');
        $('.status', e).html(`
            <table class="table table-sm table-striped">
              <tbody>
                ${Object.keys(x.services).map(y => {
                  return `<tr><td>${y}</td><td>${statuses[x.services[y]]}</td></tr>`
                }).join('')}
              </tbody>
            </table>
          `);
        var data = [
          [`CPU Load (core count: ${x.uptime.core})`, [ // 1/5/15
            [
              [
                ['bg-primary', x.uptime.load[0] / x.uptime.core]
              ], '1m', x.uptime.load[0]
            ],
            [
              [
                ['bg-primary', x.uptime.load[1] / x.uptime.core]
              ], '5m', x.uptime.load[1]
            ],
            [
              [
                ['bg-primary', x.uptime.load[2] / x.uptime.core]
              ], '15m', x.uptime.load[2]
            ]
          ]],
          [`Memory (capacity : ${formatBytes(x.free.mem.total * 1024)}, swap: ${formatBytes(x.free.swap.total * 1024)})`, [ // 1/5/15
            [
              [
                ['bg-primary', x.free.mem.used / x.free.mem.total],
                ['bg-warning', x.free.mem.cache / x.free.mem.total]
              ], 'mem', formatPercent(x.free.mem.used / x.free.mem.total)
            ],
            [
              [
                ['bg-primary', x.free.swap.used / x.free.swap.total],
              ], 'swap', formatPercent(x.free.swap.used / x.free.swap.total)
            ],
          ]],
          [`Storage (capacity : ${formatBytes(x.df.usage.reduce((y, z) => y + z.total, 0) * 1024)})`,
            x.df.usage.map(y => [
              [
                ['bg-primary', y.used / y.total],
              ], y.mount, y.usage
            ])
          ],
          [`Inodes (capacity : ${formatBytes(x.df.inode.reduce((y, z) => y + z.total, 0)).slice(0, -1)})`,
            x.df.inode.map(y => [
              [
                ['bg-primary', y.used / y.total],
              ], y.mount, y.usage
            ])
          ],
        ];
        $('.performance', e).html(`
            <div class="list-group">
                <div class="list-group-item">
                  <h6>Time</h6>
                  <div class="row g-0">
                    <div class="col-4">Clock</div>
                    <div class="col-8 text-end">${x.uptime.time}</div>
                  </div>
                  <div class="row g-0">
                    <div class="col-4">Uptime</div>
                    <div class="col-8 text-end">${x.uptime.up}</div>
                  </div>
                </div>
                ${data.map(y => {
                  return `<div class="list-group-item">
                  <h6>${y[0]}</h6>
                  ${
                    y[1].map(z => {
                      return `<div class="row g-0"><div class="col-2">${z[1]}</div>
                      <div class="col progress">
                      ${z[0].map(ax => {
                        return `<div class="progress-bar ${ax[0]}" style="width: ${ax[1] * 100}%"></div>`
                      }).join('')}
                      </div>
                      <div class="col-2 text-end">${z[2]}</div></div>`
                    }).join('')
                  }</div>`
                }).join('')}
              </div>
          `);
      });
    }

    window.addEventListener('DOMContentLoaded', (event) => {
      $('.server').each((i, e) => {
        loadData(e);
      });
      if (window.location.hash) {
        var h = document.getElementById(window.location.hash.slice(1));
        var i = bootstrap.Tab.getInstance(h);
        if (!i) {
          i = new bootstrap.Tab(h);
        }
        i.show();
      }
    });
  </script>
</body>

</html>