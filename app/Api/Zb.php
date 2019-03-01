<?php
/**
 * Created by PhpStorm.
 * User: donghai
 * Date: 19-2-27
 * Time: 下午4:57
 */

namespace App\Api;


class Zb extends Common implements Api
{
    private $publicUrl = 'http://api.zb.cn/data/v1/';

    private $privateUrl = 'https://trade.zb.cn/api/';

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
        $url = $this->publicUrl . 'markets';
        $rs = getJSON($url);
        $symbols = [];
        foreach ($rs as $k => $v)
        {
            $symbols[] = strtoupper($k);
        }
        return $symbols;
    }

    public function depth()
    {
        $url = $this->publicUrl . 'depth?size=10&market=' . $this->pair;
        $rs = getJSON($url);
        return $rs;
    }

    //1/0[buy/sell]
    public function order($price, $amount, $tradeType)
    {
        $parameters = [
            "accesskey"=> $this->key,
            "amount"=> $amount,
            "currency"=> $this->pair,
            "method"=>"order",
            "price"=> $price,
            "tradeType"=> $tradeType
        ];
        $url= $this->privateUrl . "order";
        $post = $this->createSign($parameters);
        $res = $this->httpRequest($url,$post);
//        code : 返回代码
//message : 提示信息
//id : 委托挂单号
        return $res;
    }

    public function balance()
    {

        // TODO: Implement balance() method.
//        https://trade.zb.cn/api/getAccountInfo?accesskey=youraccesskey&method=getAccountInfo
//        &sign=请求加密签名串&reqTime=当前时间毫秒数
        $parameters = [
            "accesskey" => $this->key,
            "method" => "getAccountInfo"
        ];
        $url = $this->privateUrl . 'getAccountInfo';
        $post = $this->createSign($parameters);
        $res = $this->httpRequest($url,$post);
        $res = $res['coins'];
        $balance = [];
        foreach ($res as  $k => $v)
        {
            $enName = $v['enName'];
            $balance[$enName] = $v['available'];
        }
        return $balance;
    }

    public function createSign($pParams = [])
    {
        $tPreSign = http_build_query($pParams, '', '&');
        $SecretKey = sha1($this->secret);
        $tSign = hash_hmac('md5',$tPreSign,$SecretKey);
        $pParams['sign'] = $tSign;
        $pParams['reqTime'] = time() * 1000;
        $tResult = http_build_query($pParams, '', '&');
        return $tResult;
    }

    public function httpRequest($pUrl, $pData)
    {
        $tCh = curl_init();
        curl_setopt($tCh, CURLOPT_POST, true);
        curl_setopt($tCh, CURLOPT_POSTFIELDS, $pData);
        curl_setopt($tCh, CURLOPT_HTTPHEADER, array("Content-type: application/x-www-form-urlencoded"));
        curl_setopt($tCh, CURLOPT_URL, $pUrl);
        curl_setopt($tCh, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($tCh, CURLOPT_SSL_VERIFYPEER, false);
        $tResult = curl_exec($tCh);
        curl_close($tCh);
        $tResult = json_decode($tResult,true);
        return $tResult;
    }
}
