<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <a class="navbar-brand" href="https://dom.my.id">DOM Cloud Portal</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item <?= ($page ?? '') === 'hosting' ? 'active' : '' ?>">
        <a class="nav-link" href="/user/hosting"><?= lang('Interface.hosting') ?></a>
      </li>
	</ul>
	<ul class="navbar-nav">
      <li class="nav-item <?= ($page ?? '') === 'profile' ? 'active' : '' ?>">
        <a class="nav-link" href="/user/profile"><?= lang('Interface.profile') ?></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="/logout"><?= lang('Interface.logout') ?></a>
      </li>
    </ul>
  </div>
</nav>