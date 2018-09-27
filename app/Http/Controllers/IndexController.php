<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GatewayClient\Gateway;

class IndexController extends Controller
{
    public function __construct()
    {
        Gateway::$registerAddress = '127.0.0.1:1236';
    }

    /**
     * web客户端
     * @param string $uid
     * @param string $to_uid
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function client()
    {
        return view('client');
    }

    /**
     * 绑定uid
     * @return mixed
     */
    public function bind()
    {
        // 假设用户已经登录，用户uid和群组id在session中
        $uid = request('uid');
        $client_id = request('client_id');

        $res = request()->all();
        $res['type'] = 'bind';
        $res['time'] = date('H:i:s');
        // client_id与uid绑定
        Gateway::bindUid($client_id, $uid);
        Gateway::sendToUid($uid, json_encode($res));
        return response()->json($res);
    }

    /**
     * 发送消息
     * @return mixed
     */
    public function send()
    {
        $uid = request('uid');
        $to_uid = request('to_uid');
        $res = request()->all();
        $res['type'] = 'send';
        $res['time'] = date('H:i:s');
        // 向任意uid的网站页面发送数据
        Gateway::sendToUid($uid, json_encode($res));
        Gateway::sendToUid($to_uid, json_encode($res));
        return response()->json($res);
    }
}
