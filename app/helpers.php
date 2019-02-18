<?php
/**
 * 帮助函数
 * User: donghai
 * Date: 19-1-3
 * Time: 下午7:27
 */

const successStatus = 200;
const successCode = 0;
const errorStatus = 400;
const errorCode = 100000;


function lang()
{
    $lang = request('lang');
    $lang = is_null($lang) ? 'zh-CN' : $lang;
    return $lang;
}

//返回多语言指定内容
function msg($code)
{
    $lang = request('lang');
    $lang = is_null($lang) ? 'zh-CN' : $lang;
    $trans = trans('app', [], $lang)[$code];
    return $trans;
}

//成功返回
function success($data = [], $code = successCode, $msg = '')
{
    $data = compact('code', 'msg', 'data');
    return response()->json($data, successStatus);
}

//失败返回
function error($code = errorCode)
{
    if (strlen($code) == 3)
    {
        $errorStatus = $code;
    }else{
        $errorStatus = errorStatus;
    }
    $msg = msg($code);
    $data = compact('code', 'msg', 'data');
    return response()->json($data, $errorStatus);
}

//用户日志
function userLog($userId, $type, $content = '')
{
    $user_id = $userId;
    $ip = request()->getClientIp();
    $ua = $_SERVER['HTTP_USER_AGENT'];
    $success = \Illuminate\Support\Facades\DB::table('user_logs')
        ->insert(compact('user_id', 'type', 'content', 'ip', 'ua'));
    return $success;
}

//分页
function perPage()
{
    if (request('per_page') == null || request('per_page') < 0 || request('per_page') > 100)
    {
        return 10;
    }
    return request('per_page');
}

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
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    $res = json_decode(curl_exec($ch), true);
    return $res;
}
