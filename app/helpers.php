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

