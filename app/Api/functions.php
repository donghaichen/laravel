<?php
/**
 * Created by PhpStorm.
 * User: donghai
 * Date: 19-2-27
 * Time: 下午3:58
 */

//CURL POST请求
function query($url, array $data = [], array $headers = [])
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
function getJson($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT_MS, 200);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    $res = json_decode(curl_exec($ch), true);
    return $res;
}

//接口json输出
function response($data = [], $msg = '', $code = 0)
{
    header('content-type:application/json;charset=utf-8');
    echo json_encode([
        'code' => $code,
        'msg' => $msg,
        'data' => $data
    ]);
    exit();
}