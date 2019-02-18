<?php
/**
 * 接口类
 * User: donghai
 * Date: 19-2-18
 * Time: 下午3:16
 */
namespace App\Api;
interface Api
{
    //获取挂单
    public function depth();

    //下单
    public function order();

    //获取余额
    public function balance();

}
