<?php
/**
 * Created by PhpStorm.
 * User: donghai
 * Date: 19-2-18
 * Time: 下午5:19
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
class QuantizedController extends Controller
{
    //加载API文件
    public function requireApi($exchange, $key = '', $secret = '', $pair = '')
    {
        $exchange = explode('.', $exchange);
        $exchange = ucfirst($exchange[0]);
        $path = 'Api/'. $exchange . '.php';
        require_once app_path($path);

        //交易所名称去除后缀
        $class = "\App\Api\\" . ucfirst($exchange);
        return new $class($key, $secret, $pair);
    }

    public function exchange()
    {
        $data = [
            'huobi.com',
            'gate.io',
            'zb.com',
            'binance.co,'
        ];
        return success($data);
    }

    public function pair(Request $request)
    {
        //test request
        $request['exchange'] = ['zb.com', 'gate.io'];
        //test request

        $exchange = $request['exchange '];

        //交易所全程
        $exchange0 = $exchange[0];
        $exchange1 = $exchange[1];

        $api0 = $this->requireApi($exchange0);
        $api1 = $this->requireApi($exchange1);
//        exit();
        $pair0 = $api0->pair();
        $pair1 = $api1->pair();
        $data = array_values(array_intersect($pair0, $pair1));
        return success($data);
    }

    public function balance(Request $request)
    {

        //test request
        $request = [
            'exchange' => 'zb.io',
            'key' => '',
            'secret' => ''
        ];
        //test request

        $exchange = $request['exchange'];
        $key = $request['key'];
        $secret = $request['secret'];
        $api = $this->requireApi($exchange, $key, $secret);
        $data = $api->balance();
        return success($data);
    }

    public function order(Request $request)
    {
        /*
 * 低价买 高价卖
 * 查询交易所当前价格
 * 查询当前挂单
 * 对比双方挂单
 * 先吃买单
 * 买单规则 如果两个交易所 买单有低价 吃低价的买单
 * 卖单规则 如果两个交易所 卖单有高价 吃高价的卖单
 *
 *
 * */
        $exchange = $request['exchange'];
        $pair = $request['pair'];
        return success();
    }



}
