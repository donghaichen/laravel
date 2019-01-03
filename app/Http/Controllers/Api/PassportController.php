<?php
/**
 * Created by PhpStorm.
 * User: cn
 * Date: 2018/12/9
 * Time: 3:26
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use App\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class PassportController extends Controller
{
    protected $logTable = 'log_send';

    public function test()
    {
        return error('100002');
    }

    public function sendMail(Request $request)
    {
        $code = rand(1000,9999);
        $data = compact('code');
        $type = 'email';
        $ip = $request->getClientIp();
        $ua = $_SERVER['HTTP_USER_AGENT'];
        $subject = $content =  lang('200001') . $data['code'];
        $to = request('email');
        Mail::send('email.test',
            $data,
            function($message) use($to, $subject)  {
                $message->to($to, 'App')->subject($subject);
                $message->from(config('mail.username'),'App');
            });
        $success = DB::table($this->logTable)->insert(compact('type', 'to', 'code', 'content', 'ip', 'ua'));
        return success($success);
    }

    /**
     * 使用Get的方式返回：challenge和capthca_id 此方式以实现前后端完全分离的开发模式 专门实现failback
     * @author Tanxu
     */
    public function geetest()
    {
        $data = array(
            "user_id" => "App", # 网站用户id
            "client_type" => "h5", #web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
            "ip_address" => "127.0.0.1" # 请在此处传输用户请求验证时所携带的IP
        );
        $geettest = new GeetestController();
        $geettest->pre_process($data, 1);
        $success = $geettest->get_response_str();
        return success($success);
    }

    //因API 不能存session ，所以使用服务器宕机模式,走failback模式
    private function verifyLoginServlet($request)
    {
        if (empty($request['geetest_challenge']) || empty( $request['geetest_challenge']))
        {
            return false;
        }else{
            return true;
        }
    }

    /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $verifyLoginServlet = $this->verifyLoginServlet($request);
        if ($verifyLoginServlet == false)
        {
            return error(100003);
        }

        if(Auth::attempt(['email' => $request['email'], 'password' => $request['password']]))
        {
            $user = Auth::user();
            $success['token'] =  $user->createToken('App')->accessToken;
            userLog($user->id, 'login');
            return success($success);
        }
        else{
            return error(100004);
        }
    }

    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
            'confirm_password' => 'required|same:password',
            'code' => ['required', 'numeric'],
        ]);

        if ($validator->fails())
        {
            $msg = $validator->errors();
            return error($msg);
        }

        if (isset($request['recommend_email']) || isset($request['recommend_id']))
        {
            $email = $request['recommend_email'];
            $pregEmail = '/^[a-zA-Z0-9]+([-_.][a-zA-Z0-9]+)*@([a-zA-Z0-9]+[-.])+([a-z]{2,5})$/ims';
            if(preg_match($pregEmail, $email)){
                $recommend = DB::table('users')
                    ->where('email', $email)
                    ->value('id');
            }else{
                $recommend = DB::table('users')
                    ->where('id', $request['recommend_id'])
                    ->value('id');
            }
            if ($recommend <= 0)
            {
                return error(100005);
            }
            $data['recommend'] = $recommend;
        }
        $exists = DB::table($this->logTable)
            ->where('to', $input['email'])
            ->where('code', $input['code'])
            ->where('created_at','>', date('Y-m-d H:i:s', time() - 10 * 60))
            ->exists();

        if ($exists == false)
        {
            return error(100001);
        }

        $data['email'] = $input['email'];
        $data['password'] = bcrypt($input['password']);
        $user = User::create($data);
        $success['token'] =  $user->createToken('App')->accessToken;
        $success['name'] =  $user->name;
        return success($success);
    }

    public function resetPasswd(Request $request)
    {
        $exists = DB::table($this->logTable)
            ->where('to', Auth::user()->email)
            ->where('code', $request['email_code'])
            ->where('created_at','>', date('Y-m-d H:i:s', time() - 10 * 60))
            ->exists();

        if ($exists == false)
        {
           return error(100001);
        }

        $password = bcrypt($request['password']);
        $userId = Auth::id();
        DB::table('users')->where('id', $userId)->update(compact('password'));
        return success();
    }

    public function forgetPasswd(Request $request)
    {
        //检查邮箱是否存在
        $exists = User::where('email', $request['email'])->exists();

        if ($exists == false)
        {
            return error(100006);
        }

        //检查验证码是否过期
        $exists = DB::table($this->logTable)
            ->where('to', $request['email'])
            ->where('code', $request['email_code'])
            ->where('created_at','>', date('Y-m-d H:i:s', time() - 10 * 60))
            ->exists();
        if ($exists == false)
        {
            return error(100001);
        }

        $password = bcrypt($request['password']);
        $userId = Auth::id();
        DB::table('users')->where('id', $userId)->update(compact('password'));
        return success();
    }

    /**
     * details api
     *
     * @return \Illuminate\Http\Response
     */
    public function userInfo()
    {
        $user = Auth::user()->toArray();
        $userLog = UserLog::where('user_id', $user['id'])
            ->where('type', 'login')
            ->orderBy('id', 'desc')
            ->first()
            ->toArray();
        $user['last_login_at'] = $userLog['created_at'];
        $user['last_login_ip'] = $userLog['ip'];
        return success($user);
    }

    public function site()
    {
        $success = DB::table('sites')
            ->where('lang', lang())
            ->get();
        return success($success);
    }

    public function queryLog()
    {
        DB::connection()->enableQueryLog();  // 开启QueryLog
        \App\User::find(1);
        dump(DB::getQueryLog());
    }

    public function createSite()
    {
        $data = [
            'name' => '火币',
            'url' =>'www.huobi.com',
            'lang' =>'zh-CN'
        ];
        $success = DB::table('sites')->insert($data);
        return success($success);
    }

}
