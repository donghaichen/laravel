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
    if (isNull(request('per_page')) || request('per_page') < 0 || request('per_page') > 100)
    {
        return 10;
    }
    return request('per_page');
}
