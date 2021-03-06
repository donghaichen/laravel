<?php
/**
 * Created by PhpStorm.
 * User: donghai
 * Date: 19-2-18
 * Time: 下午3:23
 */
namespace App\Api;

class Gate extends Common implements Api
{
    private $publicUrl = 'https://data.gateio.co/api2/1/';

    private $privateUrl = 'https://api.gateio.co/api2/1/private/';

    public $pair = '';

    public $key = '';

    public $secret = '';

    public function __construct($key = '', $secret = '', $pair = '')
    {
        $this->pair = strtolower($pair);
        $this->key = $key;
        $this->secret = $secret;
    }

    public function pair()
    {
        $url = $this->publicUrl . 'pairs';
        $res = getJSON($url);
        return $res;
    }

    public function depth()
    {
        $url = $this->publicUrl . 'orderBook/' . $this->pair;
        $res = getJSON($url);
        return $res;
    }

//    public function order($type, $price, $amount, $orderType = '')
//    {
//        $rate = $price;
//        $currencyPair = $this->pair;
//        $path = $type;
//        $data = compact('rate', 'amount', 'orderType', 'currencyPair');
//        $res = query($path, $data);
//        return response($res);
//    }

    //1/0[buy/sell]
    public function order($price, $amount, $tradeType)
    {
        $url = $tradeType == 1 ? 'buy' : 'sell';
        $data = [
            'currencyPair' => $this->pair,
            'rate' => $price,
            'amount' => $amount,
        ];
        $res = $this->query($url, $data);
        //orderNumber
//       result: 是否成功 true成功 false失败
//       message: 提示消息
        $code = 0;
        if (isset($res['orderNumber']) && $res['result'] == 'true')
        {
            $orderNumber = $res['orderNumber'];
            $msg = '';
        }else{
            $orderNumber = 0;
            $msg = msg(100012);
            $code = $res['code'];
        }
        $data = compact('orderNumber', 'msg', 'code');
        return $data;
    }

    public function balance()
    {
        $res = $this->query('balances');
        $balance = $res['available'];
        $coin = explode('_', strtoupper($this->pair));
        $coinGoods = $coin[0];
        $coinMarket = $coin[1];
        $data[$coinGoods] = isset($balance[$coinGoods]) ? $balance[$coinGoods] : '0';
        $data[$coinMarket] = isset($balance[$coinMarket]) ? $balance[$coinMarket] : '0';
        return $data;
    }

    public function query($path, array $req = array())
    {
        // API settings, add your Key and Secret at here
        $key = $this->key;
        $secret = $this->secret;
        $path = $this->privateUrl . $path;

        // generate a nonce to avoid problems with 32bits systems
        $mt = explode(' ', microtime());
        $req['nonce'] = $mt[1].substr($mt[0], 2, 6);

        // generate the POST data string
        $post_data = http_build_query($req, '', '&');
        $sign = hash_hmac('sha512', urldecode($post_data), $secret);

        // generate the extra headers
        $headers = array(
            'KEY: '.$key,
            'SIGN: '.$sign
        );

        static $ch = null;
        if (is_null($ch)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; gateio PHP bot; '.php_uname('a').'; PHP/'.phpversion().')');
        }
        curl_setopt($ch, CURLOPT_URL, $path);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        // run the query
        $res = curl_exec($ch);
        $dec = json_decode($res, true);
        return $dec;
    }

}
