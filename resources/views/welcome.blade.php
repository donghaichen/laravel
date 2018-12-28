<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 13px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
            .inp {
                border: 1px solid gray;
                padding: 0 10px;
                width: 200px;
                height: 30px;
                font-size: 18px;
            }
            .btn {
                border: 1px solid gray;
                width: 100px;
                height: 30px;
                font-size: 18px;
                cursor: pointer;
            }
            #embed-captcha {
                width: 300px;
                margin: 0 auto;
            }
            .show {
                display: block;
            }
            .hide {
                display: none;
            }
            #notice {
                color: red;
            }
        </style>
    </head>
    <body>

    <h1>极验验证SDKDemo</h1>
    <form class="popup" action="/api/v1/login" method="post">
        <h2>嵌入式Demo，使用表单形式提交二次验证所需的验证结果值</h2>
        <br>
        <p>
            <input class="inp" name="email" type="email" value="13917335080@126.com">
            <input class="inp" name="password" type="password" value="123456">
            {{--<label for="username2">用户名：</label>--}}
            {{--<input class="inp" id="username2" type="text" value="极验验证">--}}
        </p>
        <br>
        <p>
            {{--<label for="password2">密&nbsp;&nbsp;&nbsp;&nbsp;码：</label>--}}
            {{--<input class="inp" id="password2" type="password" value="123456">--}}
        </p>

        <div id="embed-captcha"></div>
        <p id="wait" class="show">正在加载验证码......</p>
        <p id="notice" class="hide">请先完成验证</p>

        <br>
        <input class="btn" id="embed-submit" type="submit" value="提交">
    </form>
    <div class="get_on_ga_code"
         style="width: 400px; height:400px; background-color: #ffffff; padding: 25px;"></div>
    <div>如果您无法扫描二维码，可以将该16位密钥手动输入到谷歌验证APP中:
        <small class="get_on_ga_other"></small>
    </div>

    <script src="http://apps.bdimg.com/libs/jquery/1.9.1/jquery.js"></script>
    <script src="http://static.geetest.com/static/tools/gt.js"></script>
    <script src="https://cdn.bootcss.com/jquery.qrcode/1.0/jquery.qrcode.min.js"></script>

    <script>
        var handlerEmbed = function (captchaObj) {
            $("#embed-submit").click(function (e) {
                var validate = captchaObj.getValidate();
                if (!validate) {
                    $("#notice")[0].className = "show";
                    setTimeout(function () {
                        $("#notice")[0].className = "hide";
                    }, 2000);
                    e.preventDefault();
                }
            });
            // 将验证码加到id为captcha的元素里，同时会有三个input的值：geetest_challenge, geetest_validate, geetest_seccode
            captchaObj.appendTo("#embed-captcha");
            captchaObj.onReady(function () {
                $("#wait")[0].className = "hide";
            });
            // 更多接口参考：http://www.geetest.com/install/sections/idx-client-sdk.html
        };
        $.ajax({
            // 获取id，challenge，success（是否启用failback）
            url: "/api/v1/geetest?t=" + (new Date()).getTime(), // 加随机数防止缓存
            type: "get",
            dataType: "json",
            success: function (data) {
                console.log(data);
                var data = data.data;
                // 使用initGeetest接口
                // 参数1：配置参数
                // 参数2：回调，回调的第一个参数验证码对象，之后可以使用它做appendTo之类的事件
                initGeetest({
                    gt: data.gt,
                    challenge: data.challenge,
                    new_captcha: data.new_captcha,
                    product: "embed", // 产品形式，包括：float，embed，popup。注意只对PC版验证码有效
                    offline: !data.success // 表示用户后台检测极验服务器是否宕机，一般不需要关注
                    // 更多配置参数请参见：http://www.geetest.com/install/sections/idx-client-sdk.html#config
                }, handlerEmbed);
            }
        });

        function qr(rs) {
            $(".get_on_ga_other").html(rs.secret);
            $(".get_on_ga_code").qrcode({
                render: "canvas",
                width: 100,
                height: 100,
                text: rs.qrcode_url
            });
        }
        $.getJSON('/api/v1/qrcodeGa',function (rs) {
            qr(rs)
        })
    </script>
        <div class="flex-center position-ref full-height">
            @if (Route::has('login'))
                <div class="top-right links">
                    @auth
                        <a href="{{ url('/home') }}">Home</a>
                    @else
                        <a href="{{ route('login') }}">Login</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}">Register</a>
                        @endif
                    @endauth
                </div>
            @endif

            <div class="content">
                <div class="title m-b-md">
                    Laravel
                </div>

                <div class="links">
                    <a href="https://laravel.com/docs">Documentation</a>
                    <a href="https://laracasts.com">Laracasts</a>
                    <a href="https://laravel-news.com">News</a>
                    <a href="https://nova.laravel.com">Nova</a>
                    <a href="https://forge.laravel.com">Forge</a>
                    <a href="https://github.com/laravel/laravel">GitHub</a>
                </div>
            </div>
        </div>
    </body>
</html>
