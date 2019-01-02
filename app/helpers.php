<?php
const successStatus = 200;
const successCode = 0;
const errorStatus = 400;
const errorCode = 100000;
function success($data = [], $msg = '')
{
    $code = successCode;
    $data = compact('code', 'msg', 'data');
    return response()->json($data, successStatus);
}

function error($msg, $data = [])
{
    $code = errorCode;
    $data = compact('code', 'msg', 'data');
    return response()->json($data, errorStatus);
}

function userLog($userId, $type, $content = '')
{
    $user_id = $userId;
    $ip = request()->getClientIp();
    $ua = $_SERVER['HTTP_USER_AGENT'];
    $success = \Illuminate\Support\Facades\DB::table('user_logs')
        ->insert(compact('user_id', 'type', 'content', 'ip', 'ua'));
    return $success;
}

