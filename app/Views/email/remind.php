<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <style type="text/css">
        body {
            font-family: arial, helvetica, sans-serif;
            font-size: 14px;
            line-height: 22px;
            color: #000000;
        }

        .container {
            width: 600px;
            max-width: 80%;
            margin: auto;
        }

        a {
            color: #1188E6;
            text-decoration: none;
        }

        p {
            margin: 0;
            padding: 0;
        }

        .logo {
            display: block;
            margin: auto;
            text-decoration: none;
            font-family: Helvetica, arial, sans-serif;
            font-size: 16px;
            max-width: 25% !important;
            height: auto !important;
        }

        .button {
            font-size: 16px;
            display: block;
            margin: auto;
            display: flex;
            justify-content: center;
        }

        .button a {
            background: #222222;
            padding: 10px;
            color: white;
            border-radius: 6px;
        }

        .button a:hover {
            background: #666666;
        }

        .section {
            padding: 20px 0;
        }

        .unsub {
            font-size: 12px;
            line-height: 20px;
            margin: auto;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <p></p>
        <img class="logo" width="150" alt="" src="http://cdn.mcauto-images-production.sendgrid.net/a29c06201af03bf0/70219a8f-d353-4098-bd3d-7e05101cff9d/1000x1000.png">
        <?php if (lang('Interface.code') === 'en') : ?>
            <div class="section">
                <div>Hi, <?= esc($name) ?></div>
                <div><br></div>
                <div>
                    You have a host <?= $domain ?> which will expire in <?= $remaining ?>. Please renew before <b><?= $expiry ?></b> if you still want to use it.
                </div>
                <div></div>
            </div>
            <div class="button">
                <a href="<?= $link ?>" target="_blank">Extend now</a>
            </div>
            <div class="section">
                <?= $reminder ?>
            </div>
            <p class="unsub"><a href="https://portal.domcloud.id">Unsubscribe</a></p>
        <?php elseif (lang('Interface.code') === 'id') : ?>
        <div class="section">
            <div>Yth, <?= esc($name) ?></div>
            <div><br></div>
            <div>
                Anda mempunyai Hosting <?= $domain ?> yang akan kadarluarsa dalam <?= $remaining ?>. Mohon perpanjang sebelum <b><?= $expiry ?></b> apabila anda masih ingin menggunakannya.
            </div>
            <div></div>
        </div>
        <div class="button">
            <a href="<?= $link ?>" target="_blank">Perpanjang Sekarang</a>
        </div>
        <div class="section">
           <?= $reminder ?>
        </div>
        <p class="unsub"><a href="https://portal.domcloud.id">Unsubscribe</a></p>
        <?php endif ?>
    </div>
</body>

</html>