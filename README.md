# Wxpay
微信支付JSAPI

## 下载
```
composer require datalinkage/wxpay
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
### 关于Thinkphp5.*授权目录
需要修改url的生成方式
```
/**
*文件application->config.php
*/

// 原
'url_common_param'=>false
// 改成
'url_common_param'=>true
```
现在网址的格式变成`https(http)://domain.com/MODULE/CONTROLLER/ACTION/xxx.html`
**注意：**在访问的时候请使用`https(http)://domain.com`而不是`https(http)://domain.com/index.php`，写错了会直接改变授权位置有误


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
