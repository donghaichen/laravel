<?php

namespace App\Http\Controllers\Api;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Monolog\Handler\IFTTTHandler;

class UserInfoController extends Controller
{
    public function verifyGa(Request $request)
    {
        $gaCode = $request['ga_code'];
        $secret = Auth::user()->ga_secret;
        $ga = new GoogleAuthenticator();
        $oneCode = $ga->getCode($secret); //服务端计算"一次性验证码"
        if($gaCode != $oneCode){
            $msg = '验证失败';
            return error($msg);
        }
        return success();
    }

    public function qrcodeGa()
    {
        $userId = Auth::id();
        $ga = new GoogleAuthenticator();
        $ga_secret = $ga->createSecret();
        $preg = "/http(s)?:\\/\\//";
        $appUrl = preg_replace($preg, "", env('APP_URL'));
        $email = 'test@qq.com';
        $data['qrcode_url'] = "otpauth://totp/$appUrl-$email?secret=$ga_secret";
        $data['secret'] = $ga_secret;

        DB::table('users')->where('id', $userId)->update(compact('ga_secret'));
        return success($data);
    }

    public function bindMobile(Request $request)
    {
        $mobileCode = $request['mobile_code'];
        $mobile = $request['mobile'];
        if ($mobileCode == '' || strlen($mobileCode) != 4 )
        {
            $msg = '验证码验证失败';
            return error($msg);
        }
        $rs = DB::table('users')->where('mobile', $mobile)->exists();
        if ($rs == true)
        {
            $msg = '手机号码已经被绑定,请联系客服';
            return error($msg);
        }
        $userId = Auth::id();
        DB::table('users')->where('id', $userId)->update(compact('mobile'));
        return success();
    }

    public function sendMobileCode(Request $request)
    {
        $mobile = $request['mobile'];
        return success(compact($mobile), '验证码发送成功,因目前暂无验证码接口,请任意填写验证码,限制四位数');
    }
}
