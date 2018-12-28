<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
</head>
<body>
    <!-- Vue 默认绑定的根节点 -->
    <div id="app" class="container">
        <passport-clients></passport-clients>
        <passport-authorized-clients></passport-authorized-clients>
        <passport-personal-access-tokens></passport-personal-access-tokens>
    </div>

    <!-- 必须放在 根节点 之后，否则无法正常解析-->
    <script src="{{ mix('js/app.js') }}"></script>
</body>
</html>
