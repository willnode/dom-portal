<!-- Modal Domain Bio -->
<div class="modal fade" id="domainBioModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" name="domainBio">
      <div class="modal-header">
        <h5 class="modal-title">Masukkan Biodata</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <fieldset class="mb-3 card">
          <div class="card-header">
            Biodata Publik / Pemilik Domain
          </div>
          <div class="card-body">
            <div class="mb-1">
              <label for=""><?= lang('Interface.fullName') ?></label>
              <div class="row g-1 align-items-center">
                <div class="col">
                  <input class="form-control" name="domain.owner.fname" required>
                </div>
                <div class="col">
                  <input class="form-control" name="domain.owner.lname">
                </div>
              </div>
            </div>
            <div class="mb-1">
              <label for=""><?= lang('Interface.companyName') ?></label>
              <input class="form-control" name="domain.owner.company" required>
            </div>
            <div class="row g-1 mb-1">
              <div class="col">
                <label for="name"><?= lang('Interface.email') ?></label>
                <input type="email" class="form-control" id="email" name="domain.owner.email" autocomplete="email" required>
              </div>
              <div class="col">
                <label for="name"><?= lang('Interface.phone') ?></label>
                <div class="input-group">
                  <input class="form-control" name="domain.owner.tel" autocomplete="tel" required>
                </div>
              </div>
            </div>
            <div class="mb-1">
              <label for="">Alamat</label>
              <div class="row g-1 mb-1">
                <div class="col">
                  <select name="domain.owner.country" autocomplete="country" class="ccode form-select" required>
                    <option disabled selected><?= lang('Domain.country') ?></option>
                  </select>
                </div>
                <div class="col">
                  <input class="form-control" autocomplete="address-level1" name="domain.owner.state" placeholder="<?= lang('Domain.state') ?>" required>
                </div>
              </div>
              <div class="row g-1 mb-1">
                <div class="col">
                  <input class="form-control" autocomplete="address-level2" name="domain.owner.city" placeholder="<?= lang('Domain.city') ?>" required>
                </div>
                <div class="col">
                  <input class="form-control" autocomplete="postal-code" name="domain.owner.postal" placeholder="<?= lang('Domain.zipCode') ?>" required>
                </div>
              </div>
              <input class="form-control mb-1" autocomplete="address-line1" name="domain.owner.address1" placeholder="Address 1" required>
              <input class="form-control mb-1" autocomplete="address-line2" name="domain.owner.address2" placeholder="Address 2">
            </div>
          </div>
        </fieldset>

        <div class="mb-3">
          <label>
            <input type="checkbox" checked onclick="$('#domainuserform').toggleClass('d-none', event.target.checked).prop('disabled', event.target.checked)"> Domain yang saya pesan merupakan milik sendiri
          </label>
        </div>

        <fieldset disabled id="domainuserform" class="mb-3 d-none card">
          <div class="card-header">
            Biodata Pribadi / Penanggung-jawab Domain
          </div>
          <div class="card-body">
            <div class="mb-1">
              <label for=""><?= lang('Interface.fullName') ?></label>
              <div class="row g-1 align-items-center">
                <div class="col">
                  <input class="form-control" name="domain.user.fname" required>
                </div>
                <div class="col">
                  <input class="form-control" name="domain.user.lname">
                </div>
              </div>
            </div>
            <div class="mb-1">
              <label for=""><?= lang('Interface.companyName') ?></label>
              <input class="form-control" name="domain.user.company" required>
            </div>
            <div class="mb-1">
              <label for="">Alamat</label>
              <div class="row g-1 mb-1">
                <div class="col">
                  <select name="domain.user.country" autocomplete="country" class="ccode form-select" required>
                    <option disabled selected><?= lang('Domain.country') ?></option>
                  </select>
                </div>
                <div class="col">
                  <input class="form-control" autocomplete="address-level1" name="domain.user.state" placeholder="<?= lang('Domain.state') ?>" required>
                </div>
              </div>
              <div class="row g-1 mb-1">
                <div class="col">
                  <input class="form-control" autocomplete="address-level2" name="domain.user.city" placeholder="<?= lang('Domain.city') ?>" required>
                </div>
                <div class="col">
                  <input class="form-control" autocomplete="postal-code" name="domain.user.postal" placeholder="<?= lang('Domain.zipCode') ?>" required>
                </div>
              </div>
              <input class="form-control mb-1" autocomplete="address-line1" name="domain.user.address1" placeholder="Address 1" required>
              <input class="form-control mb-1" autocomplete="address-line2" name="domain.user.address2" placeholder="Address 2">
            </div>
          </div>
        </fieldset>

      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary"><?= lang('Interface.save') ?></button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= lang('Interface.back') ?></button>
      </div>
    </form>
  </div>
</div>

<script id="ccodes" type="application/json">
  <?= json_encode(\App\Libraries\CountryCodes::$codes) ?>
</script>
<script>
  // https://unpkg.com/json-unflat@1.0.1/index.js
  function unflatten(n) {
    var t = {},
      r = function(r) {
        var a,
          f = r.split(".");
        f.map(function(i, u) {
          0 == u && (a = t),
            a[i] ?
            (a = a[i]) :
            f.length === u + 1 ?
            (a[i] = n[r]) :
            ((a[i] = {}), (a = a[i]));
        });
      };
    for (var a in n) r(a);
    return t;
  }

  const ccodes = JSON.parse(document.getElementById('ccodes').innerHTML);
  window.addEventListener('DOMContentLoaded', (event) => {
    $('.ccode').append(ccodes.map(x =>
      `<option value="${x.country}">${x.name}</option>`
    ).join(''));
  });

  window.domainBio.onsubmit = (e) => {
    var data = $(window.domainBio).serializeArray()
      .reduce((m, o) => (m[o.name] = o.value, m), {});
    $('#domain_bio')
      .val(JSON.stringify(unflatten(data).domain));
    $('#domainBioModal').modal('hide');
    $('#domainBioModalBtn')
      .toggleClass('btn-warning', false)
      .toggleClass('btn-outline-primary', true)
      .text('Biodata OK ☑');
    recalculate();
    e.preventDefault();
    return false;
  }
</script>