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
                Thank you for trusting DOM Cloud. The following is a description of your purchase.
                </div>
                <div></div>
            </div>
            <div class="section">
                <table border="0" style="width: 100%;">
                    <tbody>
                        <tr>
                            <td style="width: 50%">
                                <div class="section">
                                    <h4><?= $price ?></h4>
                                </div>
                                <div class="section">
                                    <?= $description ?>
                                </div>
                            </td>
                            <td style="width: 50%">
                                <p>Transaction ID <br><b><?= $id ?></b></p>
                                <p>Transaction Timestamp <br><b><?= $timestamp ?></b></p>
                                <p>Transaction Method <br><b><?= $via ?></b></p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="section">
                <p>You can save this email as proof of transaction. Meanwhile, your hosting will be active in a few minutes. If not, please check that you have completed the domain document requirements, directed the domain correctly, and cleared your DNS cache. Need help? Our <a
        href="mailto:support@domcloud.id?subject=I need help set up hosting&amp;body=Hi, I want to ask you....">Support
        Desk</a> can help you up.</p>
            </div>
            <p class="unsub"><a href="https://portal.domcloud.id">Unsubscribe</a></p>
        <?php elseif (lang('Interface.code') === 'id') : ?>
            <div class="section">
                <div>Yth, <?= esc($name) ?></div>
                <div><br></div>
                <div>
                Terimakasih sudah mempercayakan DOM Cloud. Berikut keterangan pembelian anda.
                </div>
                <div></div>
            </div>
            <div class="section">
                <table border="0" style="width: 100%;">
                    <tbody>
                        <tr>
                            <td style="width: 50%">
                                <div class="section">
                                    <h4><?= $price ?></h4>
                                </div>
                                <div class="section">
                                    <?= $description ?>
                                </div>
                            </td>
                            <td style="width: 50%">
                                <p>ID Transaksi <br><b><?= $id ?></b></p>
                                <p>Waktu Transaksi <br><b><?= $timestamp ?></b></p>
                                <p>Metode Transaksi <br><b><?= $via ?></b></p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="section">
                <p>Anda dapat menyimpan email ini sebagai bukti transaksi. Jika anda sudah membeli domain bersamaan dengan hosting, pastikan segera mengkonfirmasi email yang digunakan dalam biodata domain serta melengkapi syarat dokumen yang diperlukan (bila ada). Butuh bantuan? <a
        href="mailto:support@domcloud.id?subject=Butuh bantuan setting hosting&amp;body=Hi, Saya ingin bertanya....">Support
        Desk</a> kami dapat membantu anda.</p>
            </div>
            <p class="unsub"><a href="https://portal.domcloud.id">Unsubscribe</a></p>

        <?php endif ?>
    </div>
</body>

</html>