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

    const CODE = [
        1000 => '调用成功',
        1001 => '一般错误提示',
        1002 => '内部错误',
        1003 => '验证不通过',
        1004 => '资金安全密码锁定',
        1005 => '资金安全密码错误，请确认后重新输入。',
        1006 => '实名认证等待审核或审核不通过',
        1009 => '此接口维护中',
        1010 => '暂不开放',
        1012 => '权限不足',
        1013 => '不能交易，若有疑问请联系在线客服',
        1014 => '预售期间不能卖出',
        2002 => '比特币账户余额不足',
        2003 => '莱特币账户余额不足',
        2005 => '以太币账户余额不足',
        2006 => 'ETC币账户余额不足',
        2007 => 'BTS币账户余额不足',
        2009 => '账户余额不足',
        3001 => '挂单没有找到',
        3002 => '无效的金额',
        3003 => '无效的数量',
        3004 => '用户不存在',
        3005 => '无效的参数',
        3006 => '无效的IP或与绑定的IP不一致',
        3007 => '请求时间已失效',
        3008 => '交易记录没有找到',
        4001 => 'API接口被锁定',
        4002 => '请求过于频繁',
    ];

    public function __construct($key = '', $secret = '', $pair = '')
    {
        $this->pair = strtolower($pair);
        $this->key = $key;
        $this->secret = $secret;
    }

    public function pair()
    {
        $url = $this->publicUrl . 'markets';
        $res = getJSON($url);
        $symbols = [];
        foreach ($res as $k => $v)
        {
            $symbols[] = strtoupper($k);
        }
        return $symbols;
    }

    public function depth()
    {
        $url = $this->publicUrl . 'depth?size=10&market=' . $this->pair;
        $res = getJSON($url);
        return $res;
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
        $code = 0;
        if (isset($res['code']) && $res['code'] == 1000)
        {
            $orderNumber = $res['id'];
            $msg = '';
        }else{
            $orderNumber = 0;
            $msg = $msg = msg(100012);
            $code = $res['code'];
        }
        $data = compact('orderNumber', 'msg', 'code');
        return $data;
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
        $res = $res['result']['coins'];

        $balance = [];
        foreach ($res as  $k => $v)
        {
            $enName = $v['enName'];
            $balance[$enName] = $v['available'];
        }
        $coin = explode('_', strtoupper($this->pair));
        $coinGoods = $coin[0];
        $coinMarket = $coin[1];
        $data[$coinGoods] = $balance[$coinGoods];
        $data[$coinMarket] = $balance[$coinMarket];
        return $data;
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
