<?php
/**
 * 用心个人中心类
 * User: donghai
 * Date: 19-1-3
 * Time: 下午7:27
 */

namespace App\Http\Controllers\Api;

use App\User;
use App\UserKey;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Monolog\Handler\IFTTTHandler;

class UserInfoController extends Controller
{

    //定义User
    private $user = (object) [];
    private $userId = (string) [];

    //为user赋值为当前授权User
    public function __construct()
    {
        $this->user = Auth::user();
        $this->userId = $this->user->id;
    }

    //谷歌验证码验证
    public function verifyGa(Request $request)
    {
        $gaCode = $request['ga_code'];
        $secret = $this->user->ga_secret;
        $userId = $this->userId;
        $ga = new GoogleAuthenticator();
        $oneCode = $ga->getCode($secret); //服务端计算"一次性验证码"
        if($gaCode != $oneCode){
            return error(100007);
        }
        $verify = 1;
        DB::table('users')->where('id', $userId)->update(compact('verify'));
        return success();
    }

    //谷歌验证二维码
    public function qrcodeGa()
    {
        $userId = $this->userId;
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

    //绑定手机
    public function bindMobile(Request $request)
    {
        $mobileCode = $request['mobile_code'];
        $mobile = $request['mobile'];
        if ($mobileCode == '' || strlen($mobileCode) != 4 )
        {
            return error(100001);
        }
        $rs = DB::table('users')->where('mobile', $mobile)->exists();
        if ($rs == true)
        {
            return error(100002);
        }
        $userId = $this->userId;
        DB::table('users')->where('id', $userId)->update(compact('mobile'));
        return success();
    }


    //验证码发送成功,因目前暂无验证码接口,请任意填写验证码,限制四位数
    public function sendMobileCode(Request $request)
    {
        $request['mobile'];
        return success();
    }

    //私钥绑定
    public function bindKey(Request $request)
    {
        $user_id = $this->userId;
        $access_key = $request['access_key'];
        $secret_key = $request['secret_key'];
        $site_id = $request['site_id'];
        $permission = $request['permission'];
        $data = compact('user_id', 'access_key', 'secret_key', 'site_id', 'permission');
        $success = UserKey::insert($data);
        return success($success);
    }

    //私钥绑定
    public function key()
    {
        $userId = $this->userId;
        $success = DB::table('user_keys as k')
            ->leftJoin('sites as s', 'k.site_id', '=', 's.id')
            ->where('k.user_id', '=', $userId)
            ->orderByDesc('k.id')
            ->orderByDesc('s.id')
            ->get();
        return success($success);
    }
}
