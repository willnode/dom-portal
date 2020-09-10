<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
</head>

<body>
	<main>
		<header>
			<h1>Pembayaran Diterima</h1>
		</header>
		<p>Yth, <?= $name ?></p>
		<p>Terima kasih sudah mempercayakan DOM Cloud. Berikut detail pembayaran anda:</p>
		<table style="width: 100%">
			<tbody>
				<tr>
					<td>ID Transaksi</td>
					<td><?= $id_payment ?> / <?= $id_purchase ?></td>
				</tr>
				<tr>
					<td>Nama Transaksi</td>
					<td><?= $name_purchase ?></td>
				</tr>
				<tr>
					<td>Nilai Transaksi</td>
					<td><?= $amount_purchase ?></td>
				</tr>
				<tr>
					<td>Waktu Transaksi</td>
					<td><?= $time_purchase ?></td>
				</tr>
				<tr>
					<td>Media Transaksi</td>
					<td><?= $via_purchase ?></td>
				</tr>
			</tbody>
		</table>
		<p>Pembelian anda akan segera aktif; Apabila masih belum, mohon kontak kami.</p>
		<hr>
		<footer>
			<p>DOM Cloud Hosting</p>
			<p>https://domcloud.id</p>
		</footer>
	</main>
</body>

</html>