<?php
/**
 * 量化交易
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

    /*
     * 查询支持的交易所
     * todo 支持交易所存数据库或者静态文件变量
     */
    public function exchange()
    {
        $data = [
            'huobi.com',
            'gate.io',
            'zb.com',
            'binance.co',
        ];
        return success($data);
    }

    //查询两个交易所共同的交易对
    public function pair(Request $request)
    {
        //test request
        $request['exchange'] = ['zb.com', 'gate.io'];
        //test request

        $exchange = $request['exchange'];

        //交易所全程
        $exchangeFirst = $exchange[0];
        $exchangeLast = $exchange[1];

        $apiFirst = $this->requireApi($exchangeFirst);
        $apiLast = $this->requireApi($exchangeLast);

        $pairFirst = $apiFirst->pair();
        $pairLast = $apiLast->pair();
        $data = array_values(array_intersect($pairFirst, $pairLast));
        return success($data);
    }

    //查询所选择交易所余额
    public function balance(Request $request)
    {
        //test request
        $request['exchange'] = [
            [
                'exchange' => 'zb.com',
                'key' => 'f1034beb-2498-499d-9e58-9d99a7898d42',
                'secret' => '040241a0-29f0-4de0-8fd7-0271df021a77',
            ],
            [
                'exchange' =>'gate.io',
                'key' => '20278C39-F779-4461-AC4A-6C8D724B9AAF',
                'secret' => '91821e9e5e11dac397b3d12006bfe0c39e44c3e5ae2dbef55cb88c71f81395d9',
            ]
        ];
        $request['pair'] = 'BTC_USDT';
        //test request

        $exchange = $request['exchange'];
        $pair = $request['pair'];

        //交易所全称
        $exchangeFirst = $exchange[0]['exchange'];
        $keyFirst = $exchange[0]['key'];
        $secretFirst = $exchange[0]['secret'];
        $exchangeLast = $exchange[1]['exchange'];
        $keyLast = $exchange[1]['key'];
        $secretLast = $exchange[1]['secret'];

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

        $data[$exchangeFirst] = $apiFirst->balance();
        $data[$exchangeLast] = $apiLast->balance();
        return success($data);
    }

    //开始量化交易
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
         */

        //test request
        $request['exchange'] = [
            [
                'exchange' => 'zb.com',
                'key' => 'f1034beb-2498-499d-9e58-9d99a7898d42',
                'secret' => '040241a0-29f0-4de0-8fd7-0271df021a77',
            ],
             [
                'exchange' =>'gate.io',
                'key' => '20278C39-F779-4461-AC4A-6C8D724B9AAF',
                'secret' => '91821e9e5e11dac397b3d12006bfe0c39e44c3e5ae2dbef55cb88c71f81395d9',
            ]
        ];
        $request['pair'] = 'BTC_USDT';
        //test request

        $exchange = $request['exchange'];
        $pair = $request['pair'];

        //交易所全称
        $exchangeFirst = $exchange[0]['exchange'];
        $keyFirst = $exchange[0]['key'];
        $secretFirst = $exchange[0]['secret'];
        $exchangeLast = $exchange[1]['exchange'];
        $keyLast = $exchange[1]['key'];
        $secretLast = $exchange[1]['secret'];

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


        $order = [];
        //吃买单 //1/0[buy/sell]
        $tradeType = 'sell';
        $bidFirst = $depthFirst['bids'];
        $bidLast = $depthLast['bids'];
        if ($bidFirst[0][0] > $bidLast[0][0])
        {
            $price = $bidFirst[0][0];
            $amount = $bidFirst[0][1];
            $res = $apiFirst->order($price, $amount, 0);
            $orderNumber = $res['orderNumber'];
            $msg = $res['msg'];
            $code = $res['code'];
            $exchange = $exchangeFirst;
            $order[] = compact('exchange', 'orderNumber','msg',  'tradeType', 'price', 'amount', 'code');
        }elseif($bidFirst[0][0] < $bidLast[0][0])
        {
            $price = $bidFirst[0][0];
            $amount = $bidFirst[0][1];
            $res = $apiFirst->order($price, $amount, 0);
            $orderNumber = $res['orderNumber'];
            $msg = $res['msg'];
            $code = $res['code'];
            $exchange = $exchangeFirst;
            $order[] = compact('exchange', 'orderNumber','msg',  'tradeType', 'price', 'amount', 'code');
        }

        //吃卖单
        $tradeType = 'buy';
        $askFirst = $depthFirst['asks'];
        $askLast = $depthLast['asks'];
        if ($bidFirst[0][0] < $bidLast[0][0])
        {
            $price = $askFirst[0][0];
            $amount = $askLast[0][1];
            $res = $apiLast->order($price, $amount, 1);
            $orderNumber = $res['orderNumber'];
            $msg = $res['msg'];
            $code = $res['code'];
            $exchange = $exchangeLast;
            $order[] = compact('exchange', 'orderNumber','msg',  'tradeType', 'price', 'amount', 'code');
        }elseif($bidFirst[0][0] > $bidLast[0][0])
        {
            $price = $askLast[0][0];
            $amount = $askLast[0][1];
            $res = $apiLast->order($price,$amount, 1);
            $orderNumber = $res['orderNumber'];
            $msg = $res['msg'];
            $code = $res['code'];
            $exchange = $exchangeLast;
            $order[] = compact('exchange', 'orderNumber','msg',  'tradeType', 'price', 'amount', 'code');
        }

        $data = $order;
        return success($data);
    }

}
