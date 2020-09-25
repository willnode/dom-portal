<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('user/head') ?>

<body>
  <?= view('user/navbar') ?>

  <div class="container">
    <div class="card">
      <div class="card-body">
        <style>
          code {
            max-height: 400px;
            overflow: auto;
            display: block;
            background: whitesmoke;
            padding: 1em;
            margin-bottom: 1em;
          }
        </style>
        <code>
          <pre><?= $output ?></pre>
        </code>
        <p>Perubahan sudah dilakukan. Apabila ada yang salah dengan hasil diatas, mohon screenshot dan laporkan ke forum.</p>
      </div>
    </div>
    <a href="<?= $link ?>" class="mt-3 btn btn-secondary">Kembali</a>
  </div>

</body>

</html>