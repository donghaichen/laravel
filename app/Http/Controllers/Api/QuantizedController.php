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
        $exchangeFirst = $exchange[0];
        $exchangeLast = $exchange[1];

        $api0 = $this->requireApi($exchangeFirst);
        $api1 = $this->requireApi($exchangeLast);
//        exit();
        $pairFirst = $api0->pair();
        $pairLast = $api1->pair();
        $data = array_values(array_intersect($pairFirst, $pairLast));
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
 * 买单规则 如果两个交易所 买单 吃高价
 * 卖单规则 如果两个交易所 卖单吃低价
 *
 *
 * */
        //test request
        $request['exchange'] = [
            'zb.com' => [
                'key' => '',
                'secret' => '',
            ],
            'gate.io' => [
                'key' => '',
                'secret' => '',
            ]
        ];
        $request['pair'] = 'BTC_USDT';
        //test request

        $exchange = $request['exchange '];
        $pair = $request['pair'];

        //交易所全程
        $exchangeFirst = array_key_first($exchange);
        $keyFirst = $exchange[$exchangeFirst]['key'];
        $secretFirst = $exchange[$exchangeFirst]['secret'];
        $exchangeLast = array_key_last($exchange);
        $keyLast = $exchange[$exchangeLast]['key'];
        $secretLast = $exchange[$exchangeLast]['secret'];

        $apiFirst = $this->requireApi(
            $exchangeFirst,
            $keyFirst,
            $secretFirst,
            $pair
        );
        $apiLast = $this->requireApi(
            $exchangeLast,
            $keyLast,
            $secretLast,
            $pair
        );

        $depthFirst = $apiFirst->depth();
        $depthLast = $apiLast->depth();

        //吃买单 //1/0[buy/sell]
        $bidFirst = $depthFirst['bids'];
        $bidLast = $depthLast['bids'];
        if ($bidFirst[0] > $bidLast[0])
        {
            $buy = $apiFirst->order($bidFirst[0], $bidFirst[1], 0);
        }elseif($bidFirst[0] < $bidLast[0])
        {
            $buy = $apiLast->order($bidLast[0], $bidLast[1], 0);
        }

        //吃卖单
        $askFirst = $depthFirst['asks'];
        $askLast = $depthLast['asks'];
        if ($bidFirst[0] < $bidLast[0])
        {
            $sell = $apiFirst->order($askFirst[0], $askFirst[1], 1);
        }elseif($bidFirst[0] > $bidLast[0])
        {
            $sell = $apiLast->order($askLast[0], $askLast[1], 1);
        }

        $data = compact('buy', 'sell');
        return success($data);
    }

}
