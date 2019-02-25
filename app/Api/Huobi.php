<?php
/**
 * Created by PhpStorm.
 * User: donghai
 * Date: 19-2-18
 * Time: 下午3:23
 */
namespace App\Api;

class Huobi extends Common implements Api
{
    private $publicUrl = 'https://api.huobi.pro/market/';

    private $privateUrl = 'https://api.gateio.co/api2/1/';

    public $pair;

    public $key = '';

    public $secret = '';

    public function __construct($pair, $key, $secret)
    {
        $this->pair = strtolower($pair);
        $this->key = $key;
        $this->secret = $secret;
    }

    public function pair()
    {
        $url = $this->publicUrl . 'depth?type=step2&symbol=' . $this->pair;
        $rs = $this->getJSON($url);
        return $this->response($rs);
    }

    public function depth()
    {
        $url = $this->publicUrl . 'depth?type=step2&symbol=' . $this->pair;
        $rs = $this->$this->getJSON($url);
        return $this->response($rs);
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
