<div class="d-md-none text-center mb-2">
    <a href="/user/host/" class="me-2 btn btn-sm btn-outline-secondary" title="<?= lang('Interface.back') ?>"><i class="fas fa-arrow-left"></i></a>
    <span><a href="http://<?= $host->domain ?>" target="_blank" rel="noopener noreferrer"><?= $host->domain ?></a></span>
</div>
<ul class="nav nav-tabs mb-4">
    <?php $path = explode('/', \Config\Services::request()->detectPath() ?? '')[2] ?? '' ?>
    <li class="nav-item d-none d-md-block">
        <a class="nav-link <?= $path == 'detail' ? 'active' : '' ?>" href="/user/host/detail/<?= $host->id ?>"><i class="fas fa-info me-2"></i> Info</a>
    </li>
    <li class="nav-item d-none d-md-block">
        <a class="nav-link <?= $path == 'see' ? 'active' : '' ?>" href="/user/host/see/<?= $host->id ?>"><i class="fas fa-upload me-2"></i> Manage</a>
    </li>
    <li class="nav-item d-none d-md-block">
        <a class="nav-link <?= $path == 'deploys' ? 'active' : '' ?>" href="/user/host/deploys/<?= $host->id ?>"><i class="fas fa-cogs me-2"></i> Deploy</a>
    </li>
    <div class="dropdown d-md-none">
        <button class="nav-link <?= in_array($path, ['detail', 'see', 'deploys']) ? 'active' : '' ?> dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
            Summary
        </button>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
            <li><a class="dropdown-item <?= $path == 'detail' ? 'active' : '' ?>" href="/user/host/detail/<?= $host->id ?>"><i class="fas fa-info me-2"></i> Info</a></li>
            <li><a class="dropdown-item <?= $path == 'see' ? 'active' : '' ?>" href="/user/host/see/<?= $host->id ?>"><i class="fas fa-upload me-2"></i> Manage</a></li>
            <li><a class="dropdown-item <?= $path == 'deploys' ? 'active' : '' ?>" href="/user/host/deploys/<?= $host->id ?>"><i class="fas fa-cogs me-2"></i> Deploy</a></li>
        </ul>
    </div>
    <div class="dropdown">
        <button class="nav-link <?= in_array($path, ['invoices', 'dns', 'firewall', 'nginx']) ? 'active' : '' ?> dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
            Check
        </button>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
            <li><a class="dropdown-item <?= $path == 'invoices' ? 'active' : '' ?>" href="/user/host/invoices/<?= $host->id ?>"><i class="fas fa-file-invoice-dollar me-2"></i> Invoice</a></li>
            <li><a class="dropdown-item <?= $path == 'dns' ? 'active' : '' ?>" href="/user/host/dns/<?= $host->id ?>"><i class="fas fa-globe me-2"></i> DNS</a></li>
            <li><a class="dropdown-item <?= $path == 'firewall' ? 'active' : '' ?>" href="/user/host/firewall/<?= $host->id ?>"><i class="fas fa-shield-virus me-2"></i> Firewall</a></li>
            <li><a class="dropdown-item <?= $path == 'nginx' ? 'active' : '' ?>" href="/user/host/nginx/<?= $host->id ?>"><i class="fas fa-project-diagram me-2"></i> Nginx</a></li>
        </ul>
    </div>
    <div class="dropdown">
        <button class="nav-link <?= in_array($path, ['rename', 'cname', 'upgrade', 'extend', 'transfer', 'delete']) ? 'active' : '' ?> dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
            Admin
        </button>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
            <li><a class="dropdown-item <?= $path == 'rename' ? 'active' : '' ?>" href="/user/host/rename/<?= $host->id ?>"><i class="fas fa-users-cog me-2"></i> Change Username</a></li>
            <li><a class="dropdown-item <?= $path == 'cname' ? 'active' : '' ?>" href="/user/host/cname/<?= $host->id ?>"><i class="fas fa-house-user me-2"></i> Change Domain</a></li>
            <li>
                <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item <?= $path == 'upgrade' ? 'active' : '' ?>" href="/user/host/upgrade/<?= $host->id ?>"><i class="fas fa-bolt me-2"></i> Upgrade</a></li>
            <li><a class="dropdown-item <?= $path == 'extend' ? 'active' : '' ?>" href="/user/host/upgrade/<?= $host->id ?>#extend"><i class="fas fa-business-time me-2"></i> Extend</a></li>
            <li>
                <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item <?= $path == 'transfer' ? 'active' : '' ?>" href="/user/host/transfer/<?= $host->id ?>"><i class="fas fa-people-carry me-2"></i> Transfer</a></li>
            <li><a class="dropdown-item <?= $path == 'delete' ? 'active' : '' ?>" href="/user/host/delete/<?= $host->id ?>"><i class="fas fa-trash me-2"></i> Delete</a></li>
        </ul>
    </div>
    <li class="nav-item ms-auto d-none d-md-block">
        <span class="nav-link"><a href="http://<?= $host->domain ?>" target="_blank" rel="noopener noreferrer"><?= $host->domain ?></a> <i class="ms-2 fas fa-external-link-alt"></i></span>
    </li>
</ul>