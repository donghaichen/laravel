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
        return $this->repsone($rs);
    }

    public function depth()
    {
        $url = $this->publicUrl . 'orderBook/' . $this->pair;
        $rs = $this->getJSON($url);
        return $this->response($rs);
    }

    public function order($type, $price, $amount, $orderType = '')
    {
        $rate = $price;
        $currencyPair = $this->pair;
        $path = $type;
        $data = compact('rate', 'amount', 'orderType', 'currencyPair');
        $rs = $this->query($path, $data);
        return $this->response($rs);
    }

    public function balance()
    {
        //https://api.gateio.co/api2/1/private/
        $rs = $this->query('balances');
        return $this->response($rs);
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

        //!!! please set Content-Type to application/x-www-form-urlencoded if it's not the default value

        // curl handle (initialize if required)
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

        if ($res === false) throw new Exception('Could not get reply: '.curl_error($ch));
        //var_dump($res);
        //print_r($res);
        $dec = json_decode($res, true);
        if (!$dec) throw new Exception('Invalid data received, please make sure connection is working and requested API exists: '.$res);

        return $dec;
    }

}
