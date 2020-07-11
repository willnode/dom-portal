<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
	<?= view('user/navbar') ?>

	<div class="container">
		<div class="card">
			<div class="card-body">
        <?php if (($_GET['status'] ?? '') === 'return') : ?>
          <div class="alert alert-primary">
            Terimakasih. Apabila sistem kami sudah memverifikasi pembayaran anda,
            email konfirmasi akan dikirim pada email anda.
          </div>
        <?php endif ?>
				<h1 class="mb-2">Daftar Hosting</h1>
				<table class="table table-striped">
					<thead>
						<tr>
							<th>Domain</th>
							<th>Paket</th>
							<th>Status</th>
							<th>Masa Tenggang</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($list as $host) : ?>
							<tr>
								<td><a href="/user/hosting/detail/<?= $host->hosting_id ?>"><?= $host->domain_name ?></a></td>
								<td><?= $host->plan_alias ?></td>
								<td><?= ucfirst($host->purchase_status) ?></td>
								<td><?= ucfirst($host->purchase_expired) ?></td>
							</tr>
						<?php endforeach ?>
					</tbody>
				</table>
			</div>
		</div>
		<a class="btn btn-primary my-2" href="/user/hosting/create">Order Baru</a>
	</div>

</body>

</html>