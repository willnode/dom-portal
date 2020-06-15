<!DOCTYPE html>
<html lang="id">

<?= view('user/head') ?>

<body>
	<?= view('user/navbar') ?>

	<div class="container">
		<h1>Daftar Hosting</h1>
		<table class="table table-sm">
			<thead>
				<tr>
					<th>Domain</th>
					<th>Plan</th>
					<th>Status</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($list as $host) :
					$domain = $host->hosting_cname ?: $host->default_domain ?>
					<tr>
						<td><a href="/user/hosting/detail/<?=$host->hosting_id?>"><?=$domain?></a></td>
						<td><?= $host->plan_alias ?></td>
						<td><?= ucfirst($host->purchase_status) ?></td>
					</tr>
				<?php endforeach ?>
			</tbody>
		</table>

		<a class="btn btn-primary" href="/user/hosting/create">Order Baru</a>
		<a class="btn btn-secondary" href="/user/">Dashboard</a>
	</div>

</body>

</html>