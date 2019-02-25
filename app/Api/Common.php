<?php
/**
 * Created by PhpStorm.
 * User: donghai
 * Date: 19-2-18
 * Time: 下午4:46
 */
namespace App\Api;

class Common
{
    //CURL POST请求
    public function query($url, array $data = [], array $headers = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $res = json_decode(curl_exec($ch), true);
        return $res;
    }

    //CURL GET请求
    public function getJson($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $res = json_decode(curl_exec($ch), true);
        return $res;
    }

    //接口json输出
    public function response($data = [], $msg = '', $code = 0)
    {
        header('content-type:application/json;charset=utf-8');
        echo json_encode([
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        ]);
        exit();
    }
}
