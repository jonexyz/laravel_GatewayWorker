<html>
<head>
    <title>{{ request('uid') }} => {{ request('to_uid') }}</title>
    <link href="https://cdn.bootcss.com/normalize/8.0.0/normalize.css" rel="stylesheet">
    <script src="https://cdn.bootcss.com/jquery/1.12.4/jquery.js"></script>
    <script src="https://cdn.bootcss.com/json5/0.5.1/json5.js"></script>
    <style>
        * {
            padding: 0;
            margin: 0;
        }

        .chat-main {
            width: 600px;
            margin: 30px auto;
            box-shadow: 0 0 1px gray;
            border: 1px solid gray;
            line-height: 1.5em;
        }

        .chat-header {
            border-bottom: 1px solid gray;
            padding: 5px 15px;
        }

        .chat-log {
            height: 200px;
            overflow-y: auto;
            border-bottom: 1px solid gray;
            padding: 5px 15px;
        }

        .chat-log dl {
            margin: 15px 0;
        }

        .chat-log dl dd {
            display: inline-block;
            border: 1px solid gray;
            padding: 5px 15px;
            border-radius: 10px;
            border-top-left-radius: 0;
        }

        .chat-log dl.me dd {
            border-radius: 10px;
            border-top-right-radius: 0;
        }

        .chat-log dl.me {
            text-align: right;
        }

        .chat-log dl.me dd {
            text-align: left;
        }

        .user-link {
            float: right;
        }

        .user-link a {
            margin-left: 5px;
        }

        .hide {
            display: none;
        }

        .inline-block {
            display: inline-block;
        }

        .btn {
            text-align: right;
            padding: 5px 15px 15px;
        }

        #btn-send {
            display: inline-block;
            background: white;
            border: 1px solid gray;
            line-height: 2em;
            padding: 0 2em;
            outline: none;
        }

        #btn-send:focus {
            background: white;
            border-color: green;
        }

        #message {
            display: block;
            width: 570px;
            height: 100px;
            margin: 15px auto 0;
            border: 1px solid gray;
            overflow-x: hidden;
            overflow-y: auto;
            resize: none;
            outline: none;
            padding: 10px;
        }

        #message:focus {
            border-color: green;
        }

        .chat-body > .tpl {
            display: none;
        }
    </style>
</head>
<body>

<div class="hide">
    bind<input type="text" id="bind" value="{{ url('index/bind') }}"><br>
    send<input type="text" id="send" value="{{ url('index/send') }}"><br>
</div>

<div class="chat-main">
    <div class="chat-header">
        <div class="chat-title inline-block">
            {{ request('uid') }} => {{ request('to_uid') }}
        </div>
        <div class="user-link inline-block">
            <span class="inline-block">模拟用户</span>
            <a class="inline-block" href="{{ url('index/index',['uid'=>1111,'to_uid'=>2222]) }}">1111</a>
            <a class="inline-block" href="{{ url('index/index',['uid'=>2222,'to_uid'=>1111]) }}" target="_blank">2222</a>
        </div>
    </div>
    <div class="chat-body">
        <div class="chat-log">

        </div>
        <dl class="tpl">
            <dt>1111(12:00:00)</dt>
            <dd>aaaabbbbbb</dd>
        </dl>
    </div>
    <div class="chat-footer">
        <form action="" id="form">
            <div class="hide">
                cliend_id<input type="text" name="client_id" id="client_id" value="{{ request('uid') }}"><br>
                uid<input type="text" name="uid" id="uid" value="{{ request('uid') }}"><br>
                to_uid<input type="text" name="to_uid" value="{{ request('to_uid') }}"><br>
            </div>
            <textarea name="message" id="message" cols="30" rows="10"></textarea>
            <div class="btn">
                <button type="button" id="btn-send">发 送</button>
            </div>
        </form>
    </div>
</div>


<script>

    /**
     * 与GatewayWorker建立websocket连接，域名和端口改为你实际的域名端口，
     * 其中端口为Gateway端口，即start_gateway.php指定的端口。
     * start_gateway.php 中需要指定websocket协议，像这样
     * $gateway = new Gateway(websocket://0.0.0.0:7272);
     */
    ws = new WebSocket("ws://192.168.230.132:7272");
    // 服务端主动推送消息时会触发这里的onmessage
    ws.onmessage = function (e) {
        // json数据转换成js对象
        var data = JSON5.parse(e.data);
        var type = data.type || '';
        switch (type) {
            // Events.php中返回的init类型的消息，将client_id发给后台进行uid绑定
            case 'init':
                $('#client_id').val(data.client_id);
                // 利用jquery发起ajax请求，将client_id发给后端进行uid绑定
                $.get($('#bind').val(), $('#form').serialize(), function (res) {
                    console.log('bind', res);
                }, 'json');
                break;
            case 'send':
                var $tpl = $('.chat-body>.tpl').clone().show();

                if (data.uid == $('#uid').val()) {
                    $tpl.find('dt').html('(' + data.time + ') ' + data.uid);
                    $tpl.addClass('me')
                } else {
                    $tpl.find('dt').html(data.uid + ' (' + data.time + ')');
                }

                $tpl.find('dd').html(data.message.replace(/\n/gim, '<br>'));

                $('.chat-log').append($tpl);

                scrollBottom();
                break;
            // 当mvc框架调用GatewayClient发消息时直接alert出来
            default:
                console.log('default', e.data);
        }
    };

    $('#form').submit(function (e) {
        return false;
    });

    var isScrollBottom = true;

    function scrollBottom(){
        if (isScrollBottom) {
            $('.chat-log').scrollTop($('.chat-log')[0].scrollHeight);
        }
    }

    $('#btn-send').click(function (e) {
        if ($.trim($('#message').val())) {
            $.get($('#send').val(), $('#form').serialize(), function (res) {
                console.log('send', res);
                $('#message').val('');
                scrollBottom();
            }, 'json');
        }
    });

    $('.chat-log').scroll(function (e) {
        var outerHeight = $(this).outerHeight();
        var scrollTop = $(this).scrollTop();
        var scrollHeight = $(this)[0].scrollHeight;
        if (outerHeight + scrollTop >= scrollHeight - 15) {
            isScrollBottom = true;
        } else {
            isScrollBottom = false;
        }
    })
</script>

</body>
</html>