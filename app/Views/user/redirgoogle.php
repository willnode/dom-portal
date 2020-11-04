<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <form method="GET" name="box">
        <input type="hidden" name="id_token">
    </form>
    <script>
        if (window.location.hash) {
            var param = new URLSearchParams("?" + window.location.hash);
            if (window.box.id_token.value = param.get('id_token')) {
                window.box.submit();
            }
        }
    </script>
</body>

</html>