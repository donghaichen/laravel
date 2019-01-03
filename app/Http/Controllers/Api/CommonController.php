<?php
/**
 * 杂项类
 * User: donghai
 * Date: 19-1-3
 * Time: 下午7:27
 */

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class CommonController extends Controller
{
    //获取API站点配置信息
    public function site()
    {
        $success = DB::table('sites')
            ->where('lang', lang())
            ->get();
        return success($success);
    }

    //开启QueryLog
    public function queryLog()
    {
        DB::connection()->enableQueryLog();
        \App\User::find(1);
        dump(DB::getQueryLog());
    }

    //添加API站点
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
