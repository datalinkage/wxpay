# Wxpay
微信支付JSAPI

## 下载
```
composer require ekeylee/wxpay
```

## 配置
```
 /**
     * 微信配置（假定值）
     * @return array
     */
    private function wechatInfo(){
        $mp = [
            'appid'=>'xxxxxxxxx',//微信appid
            'paykey'=>'xxxxxxxxxxx',//微信支付密码
            'mch_id'=>'xxxxxxxx',//商户号
        ];
        return $mp;
    }
```
如果是thinkphp5.0版本，可使用根域名做授权目录


## 使用方法
thinkphp5.0

```
<?php
namespace app\index\controller;
vendor("datalinkage.wxpay.Wxpay");
class Index 
{
    public function index()
    {
      $object = new \Wxpay('0.01','李益达','obmm3s4oXl_xj03qHUfOCP36UbkQ');//金额，商品信息，openid
      $param = $object->getWxpayConfig();//此为所有参数回执
      dump($param);
    }
}
```
