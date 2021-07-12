<nav class="navbar navbar-expand-md navbar-light bg-light mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="https://domcloud.id">
      <img src="/logo.svg" width="64px" height="60px" alt="" class="me-1">
      <span class="d-none d-sm-inline">&nbsp; DOM Cloud Hosting</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link <?= ($page ?? '') === 'hosting' ? 'active' : '' ?>" href="/user/host"><i class="fas fa-home me-1"></i> <?= lang('Interface.hosting') ?></a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= ($page ?? '') === 'domain' ? 'active' : '' ?>" href="/user/domain"><i class="fas fa-atlas me-1"></i> <?= lang('Interface.domain') ?></a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= ($page ?? '') === 'status' ? 'active' : '' ?>" href="/user/status"><i class="fas fa-tachometer-alt me-1"></i> <?= lang('Interface.status') ?></a>
        </li>
        <?php if ($_SESSION['login'] == 1) : ?>
          <li class="nav-item">
            <a class="nav-link <?= ($page ?? '') === 'sales' ? 'active' : '' ?>" href="/user/sales"><i class="fas fa-scroll me-1"></i> <?= lang('Interface.sales') ?></a>
          </li>
        <?php endif ?>
        <li class="nav-item">
            <a class="nav-link" href="<?= lang('Interface.helpLink') ?>"><i class="fas fa-question-circle me-1"></i> <?= lang('Interface.help') ?>?</a>
        </li>
      </ul>

      <ul class="navbar-nav">

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle p-0" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span title="<?= esc($_SESSION['email'] ?? '') ?>"><?= esc($_SESSION['name'] ?? '') ?></span>
            <img class="rounded-circle mx-1 bg-white" width="60" height="60" src="<?= isset($_SESSION['email']) ? get_gravatar($_SESSION['email'], 60, 'identicon') : '' ?>" alt="">
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
            <li>
              <a class="dropdown-item" href="/user/profile"><?= lang('Interface.profile') ?></a>
            </li>
            <li>
              <a class="dropdown-item" href="/logout"><?= lang('Interface.logout') ?></a>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<noscript>
  <div class="container my-4 alert alert-danger text-center">
    Please enable Javascript in your browser.
  </div>
</noscript>