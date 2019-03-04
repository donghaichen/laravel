<?php
/**
 * Created by PhpStorm.
 * User: donghai
 * Date: 19-2-27
 * Time: 下午4:35
 */

namespace App\Api;


class Binance extends Common implements Api
{
    private $publicUrl = 'https://api.binance.com/api/v1/';

    private $privateUrl = '';

    public $pair;

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
        $url = $this->publicUrl . 'exchangeInfo';
        $res = getJSON($url);
        $res = $res['symbols'];
        $symbols = [];
        var_dump($res);
        exit();
        foreach ($res as $k => $v)
        {
            $symbols[$k] = $v['baseAsset'] . '_' . $v['quoteAsset'];
        }
        return $symbols;
    }

    public function depth()
    {
        $url = $this->publicUrl . 'depth?type=step2&symbol=' . $this->pair;
        $res = getJSON($url);
        return $res;
    }

    public function order()
    {
//        https://api.gateio.co/api2/1/private/buy
//        https://api.gateio.co/api2/1/private/sell
//        "result":"true",
//		"orderNumber":"123456",
//		"rate":"1000",
//		"leftAmount":"0",
//		"filledAmount":"0.1",
//		"filledRate":"800.00",
//		"message":"Success"
        // TODO: Implement order() method.
    }

    public function balance()
    {
        //https://api.gateio.co/api2/1/private/balances
        // TODO: Implement balance() method.
    }
}
