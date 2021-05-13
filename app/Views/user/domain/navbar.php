<ul class="nav nav-tabs mb-4">
    <?php $path = explode('/', \Config\Services::request()->detectPath() ?? '')[2] ?? '' ?>
    <li class="nav-item">
        <a class="nav-link <?= $path == 'detail' ? 'active' : '' ?>" href="/user/domain/detail/<?= $domain->id ?>">Detail</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $path == 'invoices' ? 'active' : '' ?>" href="/user/domain/invoices/<?= $domain->id ?>">Invoice</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $path == 'dns' ? 'active' : '' ?>" href="/user/domain/dns/<?= $domain->id ?>">DNS</a>
    </li>
    <li class="nav-item ms-auto">
        <span class="nav-link"><a href="http://<?= $domain->name ?>" target="_blank" rel="noopener noreferrer"><?= $domain->name ?></a></span>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $path == 'renew' ? 'active' : '' ?>" href="/user/domain/renew/<?= $domain->id ?>">Renew</a>
    </li>
</ul>
