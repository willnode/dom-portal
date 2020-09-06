<nav class="navbar navbar-expand-md navbar-light bg-light mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="https://dom.my.id">
      <img src="/logo.svg" width="64px" height="60px" alt="" class="mr-2">
      <span class="d-none d-sm-inline">&nbsp; DOM Cloud Hosting</span>
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav mr-auto">
        <li class="nav-item">
          <a class="nav-link <?= ($page ?? '') === 'hosting' ? 'active' : '' ?>" href="/user/hosting"><?= lang('Interface.hosting') ?></a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= ($page ?? '') === 'domain' ? 'active' : '' ?>" href="/user/domain"><?= lang('Interface.domain') ?></a>
        </li>
        <?php if (ENVIRONMENT === 'development') : ?>
        <li class="nav-item">
          <a class="nav-link <?= ($page ?? '') === 'email' ? 'active' : '' ?>" href="/user/email"><?= lang('Interface.email') ?></a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= ($page ?? '') === 'reseller' ? 'active' : '' ?>" href="/user/reseller"><?= lang('Interface.reseller') ?></a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= ($page ?? '') === 'marketplace' ? 'active' : '' ?>" href="/user/marketplace"><?= lang('Interface.marketplace') ?></a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= ($page ?? '') === 'status' ? 'active' : '' ?>" href="/user/status"><?= lang('Interface.status') ?></a>
        </li>
        <?php endif ?>
      </ul>
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link <?= ($page ?? '') === 'profile' ? 'active' : '' ?>" href="/user/profile"><?= lang('Interface.profile') ?></a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/logout"><?= lang('Interface.logout') ?></a>
        </li>
      </ul>
    </div>
  </div>
</nav>